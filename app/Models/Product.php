<?php

namespace App\Models;

use App\Scopes\SubunitScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'subunit',
        'name',
        'siscofis_code',
        'description',
        'category_id',
        'unit',
        'minimum_stock',
        'is_serialized',
        'is_durable',
        'shelf_life_months',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new SubunitScope());
    }

    protected function casts(): array
    {
        return [
            'is_serialized' => 'boolean',
            'is_durable' => 'boolean',
            'minimum_stock' => 'integer',
            'shelf_life_months' => 'integer',
        ];
    }

    // ─── Relationships ───────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockItems()
    {
        return $this->hasMany(StockItem::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeByCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSerialized($query)
    {
        return $query->where('is_serialized', true);
    }

    // ─── Stock helpers ───────────────────────────────────────────

    /**
     * Quantidade total disponível em estoque.
     */
    public function getAvailableStock(): int
    {
        return $this->stockItems()
            ->where('status', 'available')
            ->sum('quantity');
    }

    /**
     * Quantidade total em empréstimo.
     */
    public function getLoanedStock(): int
    {
        return $this->stockItems()
            ->where('status', 'loaned')
            ->sum('quantity');
    }

    /**
     * Verifica se o estoque está abaixo do mínimo.
     */
    public function isBelowMinimum(): bool
    {
        return $this->getAvailableStock() < $this->minimum_stock;
    }

    /**
     * Retorna uma versão reduzida do nome, preservando atributos úteis como cor e tamanho.
     */
    public function getShortDisplayNameAttribute(): string
    {
        $segments = preg_split('/\s*\/\s*/', $this->name) ?: [$this->name];
        $baseName = trim(array_shift($segments) ?: $this->name);
        $details = [];

        foreach ($segments as $segment) {
            foreach (preg_split('/\s*;\s*/', $segment) ?: [$segment] as $piece) {
                if (preg_match('/^(Cor|Tamanho|Tipo|Modelo|Numera(?:cao|ção)|Nº|Num\.)\s*:\s*(.+)$/iu', trim($piece), $matches)) {
                    $details[] = trim($matches[2]);
                }
            }
        }

        $details = array_values(array_unique(array_filter($details)));

        return trim(implode(' ', array_merge([$baseName], $details)));
    }
}
