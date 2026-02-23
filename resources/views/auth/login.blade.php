<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SUBRAVO</title>

    <!-- Favicons -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('android-chrome-192x192.png') }}">
    <link rel="icon" type="image/png" sizes="512x512" href="{{ asset('android-chrome-512x512.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { devtools: false }</script>

    <style>
        /* Modern Login — padrão SAGA adaptado para SUBRAVO */
        .page-container {
            background: linear-gradient(135deg, #047857 0%, #065f46 50%, #064e3b 100%);
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        .page-container::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="3" fill="rgba(255,255,255,0.1)"/><circle cx="70" cy="30" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="30" cy="70" r="4" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2.5" fill="rgba(255,255,255,0.1)"/></svg>');
            animation: float 20s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .card-enhanced {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.1);
        }

        .input-modern {
            border-color: #d1d5db;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .input-modern:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            background: rgba(255, 255, 255, 1);
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:active {
            transform: translateY(0);
        }
    </style>
</head>
<body class="font-sans antialiased">
    <div class="page-container flex items-center justify-center p-4">
        <div class="w-full max-w-4xl">

            {{-- Header com logo --}}
            <div class="text-center mb-8 relative z-10">
                <div class="flex items-center justify-center mb-4">
                    <img src="{{ asset('images/folhaint_transparent.png') }}" alt="11º D Sup Logo" class="w-12 h-12 object-contain mr-3">
                    <h1 class="text-4xl font-bold text-white">Sistema SUBRAVO</h1>
                </div>
                <p class="text-xl text-emerald-100 mb-2">Sistema de Controle de Estoque e Empréstimo de Material</p>
                <div class="w-24 h-1 bg-emerald-300 mx-auto mt-4 rounded-full"></div>
            </div>

            {{-- Login Card --}}
            <div class="w-full max-w-md relative z-10 mx-auto">
                <div class="card-enhanced rounded-2xl px-8 py-10">
                    {{-- Header --}}
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Acesso ao Sistema</h2>
                        <p class="text-gray-600">Identifique-se para continuar</p>
                    </div>

                    {{-- Erros de Validação --}}
                    @if ($errors->any())
                        <div class="mb-6 p-6 bg-red-50 border-l-4 border-red-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-semibold text-red-800 mb-2">Erro no login:</h3>
                                    <ul class="list-disc list-inside space-y-1 text-red-700">
                                        @foreach ($errors->all() as $error)
                                            <li class="text-sm">{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Formulário --}}
                    <form method="POST" action="{{ route('login.submit') }}" class="space-y-6">
                        @csrf

                        {{-- Identidade Militar --}}
                        <div>
                            <label for="identity_number" class="block text-sm font-medium text-gray-700 mb-2">Número de Identidade *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2" />
                                    </svg>
                                </div>
                                <input type="text" id="identity_number" name="identity_number"
                                    value="{{ old('identity_number') }}"
                                    class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                    placeholder="000000000"
                                    required autofocus autocomplete="username" inputmode="numeric">
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Número da identidade militar (somente dígitos)</p>
                        </div>

                        {{-- Senha --}}
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Senha *</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                </div>
                                <input type="password" id="password" name="password"
                                    class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                    placeholder="Digite sua senha"
                                    required autocomplete="current-password">
                            </div>
                        </div>

                        {{-- Lembrar-me --}}
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <input type="checkbox" name="remember"
                                    class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-gray-300 rounded">
                                <span class="ml-2 block text-sm text-gray-700">Lembrar de mim</span>
                            </label>
                        </div>

                        {{-- Botão --}}
                        <div>
                            <button type="submit"
                                class="btn-primary w-full px-6 py-3 text-white font-semibold rounded-lg focus:outline-none focus:ring-4 focus:ring-emerald-300">
                                <span class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                                    </svg>
                                    Entrar
                                </span>
                            </button>
                        </div>
                    </form>

                    {{-- Info --}}
                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-500 mb-4">
                            Sistema restrito para militares autorizados.<br>
                            Cadastro exclusivo pelo administrador do sistema.
                        </p>
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-emerald-700 bg-emerald-100 hover:bg-emerald-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                            Cadastrar Novo Usuário
                        </a>
                    </div>
                </div>
            </div>

            {{-- Footer --}}
            <footer class="mt-8 relative z-10">
                <div class="text-center text-sm text-emerald-200/60 italic">
                    &copy; {{ date('Y') }} SUBRAVO - Desenv: Augusto
                </div>
            </footer>
        </div>
    </div>
</body>
</html>
