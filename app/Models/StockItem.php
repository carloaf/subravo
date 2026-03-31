<?php

namespace App\Models;

use App\Scopes\SubunitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StockItem extends Model
{
    use HasFactory;

    protected static array $resolvedUnitCostCache = [];

    protected static function booted(): void
    {
        static::addGlobalScope(new SubunitScope());

        // Impedir que a quantidade fique negativa
        static::saving(function (StockItem $item) {
            if ($item->quantity < 0) {
                throw new \DomainException("Quantidade do item de estoque não pode ser negativa.");
            }

            // Auto-calcular expiration_date com base na durabilidade do produto (IRDU)
            if (!$item->expiration_date && $item->product_id) {
                $product = $item->relationLoaded('product') ? $item->product : Product::find($item->product_id);

                if ($product && $product->shelf_life_months) {
                    $baseDate = $item->siscofis_entry_date ?? $item->created_at ?? now();
                    $item->expiration_date = $baseDate->copy()->addMonths($product->shelf_life_months);
                }
            }
        });
    }

    protected $fillable = [
        'product_id',
        'serial_number',
        'batch',
        'expiration_date',
        'siscofis_entry_date',
        'location',
        'subunit',
        'quantity',
        'unit_cost',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date'     => 'date',
            'siscofis_entry_date' => 'date',
            'quantity'            => 'integer',
            'unit_cost'           => 'decimal:2',
        ];
    }

    /**
     * Formata custo unitário em formato BR.
     */
    public function getFormattedUnitCostAttribute(): string
    {
        $resolvedUnitCost = $this->getResolvedUnitCost();

        if ($resolvedUnitCost === null) {
            return '—';
        }

        return 'R$ ' . number_format($resolvedUnitCost, 2, ',', '.');
    }

    /**
     * Status possíveis do item.
     */
    public const STATUSES = [
        'available'      => 'Disponível',
        'loaned'         => 'Emprestado',
        'damaged'        => 'Danificado',
        'decommissioned' => 'Descarregado',
    ];

    public const STATUS_SHORT_LABELS = [
        'available'      => 'D',
        'loaned'         => 'E',
        'damaged'        => 'A',
        'decommissioned' => 'X',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function loanItems()
    {
        return $this->hasMany(LoanItem::class);
    }

    public function movements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeLoaned($query)
    {
        return $query->where('status', 'loaned');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', 'available')
            ->whereNotNull('expiration_date')
            ->where('expiration_date', '<=', now()->addDays($days));
    }

    public function scopeByProduct($query, int $productId)
    {
        return $query->where('product_id', $productId);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isLoaned(): bool
    {
        return $this->status === 'loaned';
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusShortLabel(): string
    {
        return self::STATUS_SHORT_LABELS[$this->status] ?? strtoupper(substr($this->getStatusLabel(), 0, 1));
    }

    public function getLotDisplayLabel(): string
    {
        if ($this->batch) {
            return $this->batch;
        }

        if ($this->serial_number) {
            return 'SERIE-' . $this->serial_number;
        }

        return 'SEM LOTE';
    }

    public function getResolvedUnitCost(): ?float
    {
        if ($this->unit_cost !== null) {
            return (float) $this->unit_cost;
        }

        $uploadId = $this->extractInventoryUploadIdFromBatch();
        $materialCode = $this->product?->siscofis_code;

        if (!$uploadId || blank($materialCode)) {
            return null;
        }

        $cacheKey = $uploadId . ':' . $materialCode;

        if (array_key_exists($cacheKey, self::$resolvedUnitCostCache)) {
            return self::$resolvedUnitCostCache[$cacheKey];
        }

        $resolved = DurableGoodsInventory::query()
            ->withoutGlobalScopes()
            ->where('inventory_upload_id', $uploadId)
            ->where('material_code', $materialCode)
            ->max('unit_value');

        self::$resolvedUnitCostCache[$cacheKey] = $resolved !== null ? (float) $resolved : null;

        return self::$resolvedUnitCostCache[$cacheKey];
    }

    protected function extractInventoryUploadIdFromBatch(): ?int
    {
        if (blank($this->batch) || !Str::startsWith($this->batch, 'INV-')) {
            return null;
        }

        if (preg_match('/^INV-(\d+)(?:-\d+)?$/', $this->batch, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/UP(\d+)$/', $this->batch, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    /**
     * Verifica se o item está próximo do vencimento.
     */
    public function isExpiringSoon(int $days = 30): bool
    {
        if (!$this->expiration_date) {
            return false;
        }

        return $this->expiration_date->lte(now()->addDays($days));
    }

    /**
     * Verifica se o item está vencido.
     */
    public function isExpired(): bool
    {
        if (!$this->expiration_date) {
            return false;
        }

        return $this->expiration_date->lt(now());
    }
}
