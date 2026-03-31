<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IrduItem extends Model
{
    protected $fillable = [
        'annex',
        'annex_title',
        'item_number',
        'material_name',
        'duration_text',
        'duration_months',
        'dotacoes',
    ];

    protected function casts(): array
    {
        return [
            'item_number' => 'integer',
            'duration_months' => 'integer',
            'dotacoes' => 'array',
        ];
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeByAnnex($query, string $annex)
    {
        return $query->where('annex', $annex);
    }

    public function scopeWithDefinedDuration($query)
    {
        return $query->whereNotNull('duration_months');
    }

    public function scopeIndeterminate($query)
    {
        return $query->whereNull('duration_months');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function isIndeterminate(): bool
    {
        return $this->duration_months === null;
    }

    public function getDurationDisplay(): string
    {
        if ($this->isIndeterminate()) {
            return 'Indeterminado';
        }

        if ($this->duration_months >= 12 && $this->duration_months % 12 === 0) {
            $years = $this->duration_months / 12;
            return $years === 1 ? '1 Ano' : "{$years} Anos";
        }

        return $this->duration_months === 1
            ? '1 Mês'
            : "{$this->duration_months} Meses";
    }

    /**
     * Converte texto de duração IRDU para meses.
     */
    public static function parseDurationToMonths(string $durationText): ?int
    {
        $text = mb_strtolower(trim($durationText));

        if ($text === 'indeterminado') {
            return null;
        }

        if (preg_match('/^(\d+)\s*ano/', $text, $m)) {
            return (int) $m[1] * 12;
        }

        if (preg_match('/^(\d+)\s*mes/', $text, $m)) {
            return (int) $m[1];
        }

        return null;
    }
}
