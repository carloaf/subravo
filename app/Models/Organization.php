<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'abbreviation',
        'is_host',
    ];

    protected function casts(): array
    {
        return [
            'is_host' => 'boolean',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function loans()
    {
        return $this->hasMany(Loan::class, 'borrower_organization_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeHost($query)
    {
        return $query->where('is_host', true);
    }

    public function scopeGuest($query)
    {
        return $query->where('is_host', false);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getDisplayName(): string
    {
        return $this->abbreviation ?: $this->name;
    }
}
