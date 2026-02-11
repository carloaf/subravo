<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
        'order',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getDisplayName(): string
    {
        return $this->abbreviation ?: $this->name;
    }
}
