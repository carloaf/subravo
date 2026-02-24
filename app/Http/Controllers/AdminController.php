<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Listagem de usuários com busca e filtros.
     */
    public function users(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acesso restrito ao administrador.');
        }

        $query = User::with(['rank', 'organization']);

        // Busca
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'ilike', "%{$search}%")
                  ->orWhere('war_name', 'ilike', "%{$search}%")
                  ->orWhere('identity_number', 'ilike', "%{$search}%")
                  ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Filtro por perfil
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filtro por status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $users = $query->orderBy('war_name')->paginate(15)->appends($request->query());

        return view('admin.users.index', compact('users'));
    }

    /**
     * Formulário de criação de usuário.
     */
    public function createUser()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acesso restrito ao administrador.');
        }

        $ranks         = Rank::ordered()->get();
        $organizations = Organization::orderBy('abbreviation')->get();
        $roles         = ['admin' => 'Administrador', 'manager' => 'Gerente', 'user' => 'Usuário'];

        return view('admin.users.create', compact('ranks', 'organizations', 'roles'));
    }

    /**
     * Salva novo usuário.
     */
    public function storeUser(Request $request)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acesso restrito ao administrador.');
        }

        $validated = $request->validate([
            'identity_number' => 'required|string|max:20|unique:users,identity_number',
            'password'        => 'required|string|min:8|confirmed',
            'full_name'       => 'required|string|max:255',
            'war_name'        => 'required|string|max:100',
            'email'           => 'nullable|email|max:255|unique:users,email',
            'rank_id'         => 'required|exists:ranks,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'subunit'         => 'nullable|string|max:100',
            'armed_force'     => 'nullable|in:EB,MB,FAB',
            'gender'          => 'nullable|in:M,F',
            'role'            => 'required|in:admin,manager,user',
            'is_active'       => 'boolean',
        ]);

        try {
            $validated['is_active'] = $request->boolean('is_active', true);

            $user = User::create($validated);

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Usuário \"{$user->getDisplayName()}\" criado com sucesso.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao criar usuário. Tente novamente.');
        }
    }

    /**
     * Formulário de edição de usuário.
     */
    public function editUser(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acesso restrito ao administrador.');
        }

        $ranks         = Rank::ordered()->get();
        $organizations = Organization::orderBy('abbreviation')->get();
        $roles         = ['admin' => 'Administrador', 'manager' => 'Gerente', 'user' => 'Usuário'];

        return view('admin.users.edit', compact('user', 'ranks', 'organizations', 'roles'));
    }

    /**
     * Atualiza usuário existente.
     */
    public function updateUser(Request $request, User $user)
    {
        \Log::info('updateUser called for user ID: ' . $user->id);

        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acesso restrito ao administrador.');
        }

        $validated = $request->validate([
            'identity_number' => 'required|string|max:20|unique:users,identity_number,' . $user->id,
            'password'        => 'nullable|string|min:8|confirmed',
            'full_name'       => 'required|string|max:255',
            'war_name'        => 'required|string|max:100',
            'email'           => 'nullable|email|max:255|unique:users,email,' . $user->id,
            'rank_id'         => 'required|exists:ranks,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'subunit'         => 'nullable|string|max:100',
            'armed_force'     => 'nullable|in:EB,MB,FAB',
            'gender'          => 'nullable|in:M,F',
            'role'            => 'required|in:admin,manager,user',
            'is_active'       => 'boolean',
        ]);

        try {
            $validated['is_active'] = $request->boolean('is_active', $user->is_active);

            // Ensure rank_id is integer
            if (isset($validated['rank_id'])) {
                $validated['rank_id'] = (int) $validated['rank_id'];
            }

            // Só atualiza senha se fornecida
            if (empty($validated['password'])) {
                unset($validated['password']);
            }

            $user->update($validated);

            // Log the update
            \Log::info("User {$user->id} updated successfully", $validated);

            // Refresh the user to ensure we have the latest data
            $user->refresh();

            return redirect()
                ->route('admin.users.index')
                ->with('success', "Usuário \"{$user->getDisplayName()}\" atualizado com sucesso.");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Erro ao atualizar usuário. Tente novamente.');
        }
    }

    /**
     * Ativa/desativa usuário (toggle).
     */
    public function toggleUser(User $user)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Acesso restrito ao administrador.');
        }

        // Não pode desativar a si mesmo
        if ($user->id === Auth::user()->id) {
            return back()->with('error', 'Não é possível desativar sua própria conta.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'ativado' : 'desativado';

        return back()->with('success', "Usuário \"{$user->getDisplayName()}\" {$status} com sucesso.");
    }

    // ══════════════════════════════════════════════════════════
    //  Reset de Dados de Estoque
    // ══════════════════════════════════════════════════════════

    /**
     * Exibe a tela de confirmação do reset de dados de estoque.
     */
    public function resetStockConfirm()
    {
        $counts = [
            'movements'   => DB::table('stock_movements')->count(),
            'stock_items' => DB::table('stock_items')->count(),
            'durable'     => DB::table('durable_goods_inventory')->count(),
            'products'    => DB::table('products')->count(),
            'loan_items'  => DB::table('loan_items')->count(),
            'loans'       => DB::table('loans')->count(),
            'inv_uploads' => DB::table('inventory_uploads')->count(),
            'inv_items'   => DB::table('inventory_items')->count(),
        ];

        return view('admin.reset-stock', compact('counts'));
    }

    /**
     * Executa o reset de dados de estoque.
     */
    public function resetStockExecute(Request $request)
    {
        $request->validate([
            'reset_stock'    => 'required|in:1',
            'reset_products' => 'nullable|in:1',
            'reset_durable'  => 'nullable|in:1',
            'reset_loans'    => 'nullable|in:1',
            'confirm_text'   => 'required|in:CONFIRMAR RESET',
        ], [
            'reset_stock.required' => 'É obrigatório marcar a opção de Estoque.',
            'confirm_text.in'      => 'Digite exatamente: CONFIRMAR RESET',
        ]);

        $resetProducts = $request->boolean('reset_products');
        $resetDurable  = $request->boolean('reset_durable');
        $resetLoans    = $request->boolean('reset_loans');

        $stats = [];

        try {
            DB::transaction(function () use ($resetProducts, $resetDurable, $resetLoans, &$stats) {
                // 1. Movimentações
                $stats['movements'] = DB::table('stock_movements')->delete();

                // 2. Cautelas (FK impede deletar stock_items se existirem)
                $loanItems = DB::table('loan_items')->count();
                if ($resetLoans || $loanItems > 0) {
                    $stats['loan_items'] = DB::table('loan_items')->delete();
                    $stats['loans']      = DB::table('loans')->delete();
                }

                // 3. Estoque
                $stats['stock_items'] = DB::table('stock_items')->delete();

                // 4. Uso Duradouro
                if ($resetDurable) {
                    $stats['durable'] = DB::table('durable_goods_inventory')->delete();
                }

                // 5. Produtos
                if ($resetProducts) {
                    $stats['products'] = DB::table('products')->delete();
                }
            });

            \Illuminate\Support\Facades\Log::warning('RESET DE DADOS executado', [
                'user'  => Auth::user()->war_name,
                'stats' => $stats,
                'ip'    => $request->ip(),
            ]);

            return redirect()->route('admin.reset-stock')
                ->with('success', 'Reset executado com sucesso!')
                ->with('reset_stats', $stats);

        } catch (\Exception $e) {
            return back()->with('error', "Erro durante reset: {$e->getMessage()}");
        }
    }
}
