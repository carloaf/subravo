<?php

namespace App\Models;

use App\Scopes\SubunitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Loan extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::addGlobalScope(new SubunitScope());
    }

    protected $fillable = [
        'loan_number',
        'borrower_type',
        'borrower_user_id',
        'borrower_name',
        'borrower_section',
        'borrower_organization_id',
        'borrower_cpf',
        'borrower_phone',
        'borrower_identity_number',
        'borrower_rank',
        'borrower_war_name',
        'signer_name',
        'signer_rank',
        'signer_war_name',
        'signer_identity_number',
        'signer_cpf',
        'signer_phone',
        'loaned_by',
        'subunit',
        'loan_date',
        'expected_return_date',
        'actual_return_date',
        'received_by',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'loan_date'            => 'datetime',
            'expected_return_date' => 'date',
            'actual_return_date'   => 'datetime',
        ];
    }

    /**
     * Status possíveis do empréstimo.
     */
    public const STATUSES = [
        'active'   => 'Ativo',
        'returned' => 'Devolvido',
        'partial'  => 'Devolução Parcial',
        'overdue'  => 'Vencido',
    ];

    // ─── Relationships ───────────────────────────────────────────

    public function borrower()
    {
        return $this->belongsTo(User::class, 'borrower_user_id');
    }

    public function loanedBy()
    {
        return $this->belongsTo(User::class, 'loaned_by');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function borrowerOrganization()
    {
        return $this->belongsTo(Organization::class, 'borrower_organization_id');
    }

    public function items()
    {
        return $this->hasMany(LoanItem::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            // Status explicitamente marcado como vencido pelo comando
            $q->where('status', 'overdue')
              // Ou ativo mas já passou da data prevista
              ->orWhere(function ($sub) {
                  $sub->where('status', 'active')
                      ->whereNotNull('expected_return_date')
                      ->where('expected_return_date', '<', now());
              });
        });
    }

    public function scopeForBorrower($query, int $userId)
    {
        return $query->where('borrower_user_id', $userId);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('loan_date', [$startDate, $endDate]);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'active'
            && $this->expected_return_date
            && $this->expected_return_date->lt(now());
    }

    public function isIndividual(): bool
    {
        return $this->borrower_type === 'individual';
    }

    public function isSection(): bool
    {
        return $this->borrower_type === 'section';
    }

    public function getStatusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    /**
     * Nome de exibição do mutuário (indivíduo ou seção).
     */
    public function getBorrowerDisplayName(): string
    {
        if ($this->isIndividual()) {
            return $this->borrower_name ?: ($this->borrower?->full_name ?: ($this->borrower?->getDisplayName() ?? 'N/A'));
        }

        $org = $this->borrowerOrganization?->getDisplayName() ?? '';
        return trim("{$this->borrower_section} {$org}");
    }

    public function getBorrowerIdentityDisplay(): ?string
    {
        return $this->borrower_identity_number ?: $this->borrower?->identity_number;
    }

    public function getBorrowerRankDisplay(): ?string
    {
        return $this->borrower_rank ?: $this->borrower?->rank?->abbreviation;
    }

    public function getBorrowerWarNameDisplay(): ?string
    {
        return $this->borrower_war_name ?: $this->borrower?->war_name;
    }

    public function getSignerDisplayName(): string
    {
        return $this->signer_name ?: $this->getBorrowerDisplayName();
    }

    public function hasSignerDetails(): bool
    {
        return filled($this->signer_name)
            || filled($this->signer_rank)
            || filled($this->signer_war_name)
            || filled($this->signer_identity_number)
            || filled($this->signer_cpf)
            || filled($this->signer_phone);
    }

    public function getSignerRankDisplay(): ?string
    {
        return $this->signer_rank ?: $this->getBorrowerRankDisplay();
    }

    public function getSignerWarNameDisplay(): ?string
    {
        return $this->signer_war_name ?: $this->getBorrowerWarNameDisplay();
    }

    public function getSignerIdentityDisplay(): ?string
    {
        return $this->signer_identity_number ?: $this->getBorrowerIdentityDisplay();
    }

    public function getSignatureDisplay(): string
    {
        $name = $this->hasSignerDetails()
            ? ($this->signer_name ?: $this->getBorrowerDisplayName())
            : $this->getBorrowerDisplayName();

        $rank = $this->hasSignerDetails()
            ? ($this->signer_rank ?: $this->getBorrowerRankDisplay())
            : $this->getBorrowerRankDisplay();

        return trim($name . ($rank ? ' - ' . $rank : ''));
    }

    /**
     * Gera número de cautela sequencial: CAUTELA-{ANO}-{SEQUENCIAL:06d}
     */
    public static function generateLoanNumber(): string
    {
        $year = now()->year;
        $prefix = "CAUTELA-{$year}-";

        $lastNumber = static::where('loan_number', 'like', "{$prefix}%")
            ->orderByDesc('loan_number')
            ->value('loan_number');

        if ($lastNumber) {
            $seq = (int) substr($lastNumber, -6);
            $nextSeq = $seq + 1;
        } else {
            $nextSeq = 1;
        }

        return $prefix . str_pad($nextSeq, 6, '0', STR_PAD_LEFT);
    }
}
