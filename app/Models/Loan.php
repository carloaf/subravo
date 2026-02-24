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
        'borrower_section',
        'borrower_organization_id',
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
            return $this->borrower?->getDisplayName() ?? 'N/A';
        }

        $org = $this->borrowerOrganization?->getDisplayName() ?? '';
        return trim("{$this->borrower_section} {$org}");
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
