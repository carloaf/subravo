<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Impede que usuários desativados continuem navegando.
     * Se um admin desativar a conta enquanto o usuário está logado,
     * ele será deslogado na próxima requisição.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !Auth::user()->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['identity_number' => 'Sua conta foi desativada.']);
        }

        return $next($request);
    }
}
