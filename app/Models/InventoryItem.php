<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryItem extends Model
{
    protected $fillable = [
        'inventory_upload_id',
        'material_type',
        'material_name',
        'ficha_number',
        'material_code',
        'accounting_account',
        'acervo',
        'quantity',
        'unit_value',
        'total_value',
        'patrimony_numbers',
        'raw_text',
    ];

    protected $casts = [
        'unit_value'         => 'decimal:2',
        'total_value'        => 'decimal:2',
        'patrimony_numbers'  => 'array',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function upload(): BelongsTo
    {
        return $this->belongsTo(InventoryUpload::class, 'inventory_upload_id');
    }

    // ── Helpers ───────────────────────────────────────────────

    public function hasPatrimonyNumbers(): bool
    {
        return !empty($this->patrimony_numbers);
    }

    public function getPatrimonyCountAttribute(): int
    {
        return is_array($this->patrimony_numbers) ? count($this->patrimony_numbers) : 0;
    }

    public function getFormattedUnitValueAttribute(): string
    {
        return 'R$ ' . number_format($this->unit_value, 2, ',', '.');
    }

    public function getFormattedTotalValueAttribute(): string
    {
        return 'R$ ' . number_format($this->total_value, 2, ',', '.');
    }
}
