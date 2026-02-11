<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_item_id',
        'movement_type',
        'quantity',
        'reference_type',
        'reference_id',
        'performed_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * Tipos de movimentação.
     */
    public const TYPES = [
        'entry'         => 'Entrada',
        'exit'          => 'Saída',
        'loan'          => 'Empréstimo',
        'return'        => 'Devolução',
        'adjustment'    => 'Ajuste',
        'decommission'  => 'Descarga',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }

    public function performedBy()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeByType($query, string $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    public function scopeForStockItem($query, int $stockItemId)
    {
        return $query->where('stock_item_id', $stockItemId);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getTypeLabel(): string
    {
        return self::TYPES[$this->movement_type] ?? $this->movement_type;
    }

    /**
     * Registra uma movimentação de estoque.
     */
    public static function record(
        int $stockItemId,
        string $type,
        int $quantity,
        int $performedBy,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null,
    ): static {
        return static::create([
            'stock_item_id'  => $stockItemId,
            'movement_type'  => $type,
            'quantity'       => $quantity,
            'reference_type' => $referenceType,
            'reference_id'   => $referenceId,
            'performed_by'   => $performedBy,
            'notes'          => $notes,
        ]);
    }
}
