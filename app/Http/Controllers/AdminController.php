<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\Rank;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $roles         = ['admin' => 'Administrador', 'almoxarife' => 'Almoxarife', 'solicitante' => 'Solicitante', 'auditor' => 'Auditor'];

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
            'role'            => 'required|in:admin,almoxarife,solicitante,auditor',
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
        $roles         = ['admin' => 'Administrador', 'almoxarife' => 'Almoxarife', 'solicitante' => 'Solicitante', 'auditor' => 'Auditor'];

        return view('admin.users.edit', compact('user', 'ranks', 'organizations', 'roles'));
    }

    /**
     * Atualiza usuário existente.
     */
    public function updateUser(Request $request, User $user)
    {
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
            'role'            => 'required|in:admin,almoxarife,solicitante,auditor',
            'is_active'       => 'boolean',
        ]);

        try {
            $validated['is_active'] = $request->boolean('is_active', $user->is_active);

            // Só atualiza senha se fornecida
            if (empty($validated['password'])) {
                unset($validated['password']);
            }

            $user->update($validated);

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
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Não é possível desativar sua própria conta.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $status = $user->is_active ? 'ativado' : 'desativado';

        return back()->with('success', "Usuário \"{$user->getDisplayName()}\" {$status} com sucesso.");
    }
}
