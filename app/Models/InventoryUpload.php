<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryUpload extends Model
{
    protected $fillable = [
        'filename',
        'stored_path',
        'dependency',
        'unit',
        'unit_code',
        'uploaded_by',
        'status',
        'total_items',
        'total_value',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'total_value'  => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    // ── Status constants ──────────────────────────────────────
    const STATUSES = [
        'pending'    => 'Pendente',
        'processing' => 'Processando',
        'completed'  => 'Concluído',
        'error'      => 'Erro',
    ];

    // ── Relationships ─────────────────────────────────────────

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function durableGoods(): HasMany
    {
        return $this->hasMany(DurableGoodsInventory::class);
    }

    // ── Helpers ───────────────────────────────────────────────

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending'    => 'yellow',
            'processing' => 'blue',
            'completed'  => 'green',
            'error'      => 'red',
            default      => 'gray',
        };
    }

    public function getHeaderDisplayAttribute(): string
    {
        $parts = array_filter([$this->dependency, $this->unit, $this->unit_code]);
        return implode(' / ', $parts) ?: $this->filename;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function hasError(): bool
    {
        return $this->status === 'error';
    }

    // ── Scopes ────────────────────────────────────────────────

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }
}
