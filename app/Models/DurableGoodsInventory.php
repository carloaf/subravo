<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DurableGoodsInventory extends Model
{
    protected $table = 'durable_goods_inventory';

    protected $fillable = [
        'inventory_upload_id',
        'material_name',
        'ficha_number',
        'material_code',
        'accounting_account',
        'quantity',
        'unit_value',
        'total_value',
        'raw_text',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_value' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    /**
     * Upload de inventário ao qual este item pertence.
     */
    public function inventoryUpload(): BelongsTo
    {
        return $this->belongsTo(InventoryUpload::class);
    }

    /**
     * Formata valor unitário em formato BR.
     */
    public function getFormattedUnitValueAttribute(): string
    {
        return 'R$ ' . number_format($this->unit_value, 2, ',', '.');
    }

    /**
     * Formata valor total em formato BR.
     */
    public function getFormattedTotalValueAttribute(): string
    {
        return 'R$ ' . number_format($this->total_value, 2, ',', '.');
    }
}
