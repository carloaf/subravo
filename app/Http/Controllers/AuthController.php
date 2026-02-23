<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Exibe o formulário de login.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Processa o login por identidade + senha.
     */
    public function login(Request $request)
    {
        $request->validate([
            'identity_number' => 'required|string',
            'password'        => 'required|string',
        ], [
            'identity_number.required' => 'Informe o número de identidade.',
            'password.required'        => 'Informe a senha.',
        ]);

        $credentials = [
            'identity_number' => $request->identity_number,
            'password'        => $request->password,
        ];

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['identity_number' => 'Credenciais inválidas. Verifique seu número de identidade e senha.'])
                ->onlyInput('identity_number');
        }

        // Verificar se o usuário está ativo
        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            return back()
                ->withErrors(['identity_number' => 'Sua conta está desativada. Procure o administrador do sistema.'])
                ->onlyInput('identity_number');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Exibe o formulário de cadastro de usuário.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Processa o cadastro de novo usuário.
     */
    public function register(Request $request)
    {
        $request->validate([
            'identity_number' => 'required|string|unique:users,identity_number|regex:/^\d{9,10}$/',
            'full_name'       => 'required|string|max:255',
            'war_name'        => 'nullable|string|max:255',
            'email'           => 'nullable|email|max:255',
            'rank_id'         => 'required|exists:ranks,id',
            'organization_id' => 'required|exists:organizations,id',
            'subunit'         => 'nullable|string|max:255',
            'armed_force'     => 'required|in:EB,MB,FAB',
            'gender'          => 'required|in:M,F',
            'password'        => 'required|string|min:8|confirmed',
        ], [
            'identity_number.required' => 'Número de identidade é obrigatório.',
            'identity_number.unique'   => 'Este número de identidade já está cadastrado.',
            'identity_number.regex'    => 'Número de identidade deve ter 9-10 dígitos.',
            'full_name.required'        => 'Nome completo é obrigatório.',
            'rank_id.required'          => 'Posto/graduação é obrigatório.',
            'organization_id.required'  => 'Organização é obrigatória.',
            'armed_force.required'      => 'Força armada é obrigatória.',
            'gender.required'           => 'Gênero é obrigatório.',
            'password.required'         => 'Senha é obrigatória.',
            'password.min'              => 'Senha deve ter pelo menos 8 caracteres.',
            'password.confirmed'        => 'Confirmação de senha não confere.',
        ]);

        $user = \App\Models\User::create([
            'identity_number' => $request->identity_number,
            'password'        => \Illuminate\Support\Facades\Hash::make($request->password),
            'full_name'       => $request->full_name,
            'war_name'        => $request->war_name,
            'email'           => $request->email,
            'rank_id'         => $request->rank_id,
            'organization_id' => $request->organization_id,
            'subunit'         => $request->subunit,
            'armed_force'     => $request->armed_force,
            'gender'          => $request->gender,
            'role'            => 'solicitante', // Novo usuário começa como solicitante
            'is_active'       => true,
        ]);

        // Log de criação
        \Illuminate\Support\Facades\Log::info("Novo usuário cadastrado: {$user->identity_number} - {$user->full_name}");

        return redirect()->route('login')
            ->with('success', 'Usuário cadastrado com sucesso! Faça o login para continuar.');
    }

    /**
     * Realiza o logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
