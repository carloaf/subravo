<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

/**
 * SubunitScope — Isolamento de dados por Subunidade.
 *
 * Aplicado automaticamente em StockItem, Loan, InventoryUpload e DurableGoodsInventory.
 *
 * Regras:
 * - Qualquer usuário COM subunidade: enxerga apenas dados da sua subunidade
 * - Qualquer usuário SEM subunidade: enxerga tudo (sem filtro)
 * - Sem sessão (CLI/artisan): sem filtro
 */
class SubunitScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! Auth::check()) {
            return;
        }

        $user = Auth::user();

        // Sem subunidade definida — não filtra (evita bloqueio total)
        if (blank($user->subunit)) {
            return;
        }

        $builder->where($model->getTable() . '.subunit', $user->subunit);
    }
}
