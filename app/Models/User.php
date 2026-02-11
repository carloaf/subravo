<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'identity_number',
        'password',
        'full_name',
        'war_name',
        'email',
        'rank_id',
        'organization_id',
        'subunit',
        'armed_force',
        'gender',
        'role',
        'is_active',
        'avatar_url',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'  => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ─── Auth overrides ──────────────────────────────────────────

    /**
     * Campo utilizado para login (identity_number em vez de email).
     */
    public function getAuthIdentifierName(): string
    {
        return 'identity_number';
    }

    // ─── Relationships ───────────────────────────────────────────

    public function rank()
    {
        return $this->belongsTo(Rank::class);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class)->withDefault();
    }

    public function borrowedLoans()
    {
        return $this->hasMany(Loan::class, 'borrower_user_id');
    }

    public function issuedLoans()
    {
        return $this->hasMany(Loan::class, 'loaned_by');
    }

    public function receivedReturns()
    {
        return $this->hasMany(Loan::class, 'received_by');
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'performed_by');
    }

    // ─── Role helpers ────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isAlmoxarife(): bool
    {
        return $this->role === 'almoxarife';
    }

    public function isSolicitante(): bool
    {
        return $this->role === 'solicitante';
    }

    public function isAuditor(): bool
    {
        return $this->role === 'auditor';
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    // ─── Display helpers ─────────────────────────────────────────

    public function getDisplayName(): string
    {
        $rankAbbr = $this->rank?->abbreviation ?? '';
        return trim("{$rankAbbr} {$this->war_name}");
    }

    public function getArmedForceFullName(): ?string
    {
        return match ($this->armed_force) {
            'EB'  => 'Exército Brasileiro',
            'MB'  => 'Marinha do Brasil',
            'FAB' => 'Força Aérea Brasileira',
            default => null,
        };
    }
}
