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
