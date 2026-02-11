<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockItem extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        // Impedir que a quantidade fique negativa
        static::saving(function (StockItem $item) {
            if ($item->quantity < 0) {
                throw new \DomainException("Quantidade do item de estoque não pode ser negativa.");
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
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'expiration_date'     => 'date',
            'siscofis_entry_date' => 'date',
            'quantity'            => 'integer',
        ];
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
