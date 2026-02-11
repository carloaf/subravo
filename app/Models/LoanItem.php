<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'loan_id',
        'stock_item_id',
        'quantity',
        'returned_quantity',
        'condition_out',
        'condition_in',
    ];

    protected function casts(): array
    {
        return [
            'quantity'          => 'integer',
            'returned_quantity' => 'integer',
        ];
    }

    /**
     * Condições pré-definidas para os itens.
     */
    public const CONDITIONS = [
        'novo'      => 'Novo',
        'bom'       => 'Bom Estado',
        'regular'   => 'Regular',
        'ruim'      => 'Ruim',
        'danificado' => 'Danificado',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Quantidade pendente de devolução.
     */
    public function getPendingQuantity(): int
    {
        return $this->quantity - $this->returned_quantity;
    }

    /**
     * Verifica se o item foi totalmente devolvido.
     */
    public function isFullyReturned(): bool
    {
        return $this->returned_quantity >= $this->quantity;
    }

    /**
     * Verifica se houve devolução parcial.
     */
    public function isPartiallyReturned(): bool
    {
        return $this->returned_quantity > 0 && $this->returned_quantity < $this->quantity;
    }
}
