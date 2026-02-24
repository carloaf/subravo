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
 * - Admin: enxerga TODOS os dados (sem filtro)
 * - Manager e User: enxergam apenas os dados da sua subunidade
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

        // Admin enxerga tudo
        if ($user->isAdmin()) {
            return;
        }

        // Sem subunidade definida — não filtra (evita bloqueio total)
        if (blank($user->subunit)) {
            return;
        }

        $builder->where($model->getTable() . '.subunit', $user->subunit);
    }
}
