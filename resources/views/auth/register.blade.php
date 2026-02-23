<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cadastro - SUBRAVO</title>

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
        /* Modern Register — padrão SAGA adaptado para SUBRAVO */
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

        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(107, 114, 128, 0.3);
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

            {{-- Register Card --}}
            <div class="w-full max-w-2xl relative z-10 mx-auto">
                <div class="card-enhanced rounded-2xl px-8 py-10">
                    {{-- Header --}}
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Cadastro de Novo Usuário</h2>
                        <p class="text-gray-600">Preencha os dados para criar sua conta</p>
                    </div>

                    {{-- Flash Messages --}}
                    @if (session('success'))
                        <div class="mb-6 p-6 bg-green-50 border-l-4 border-green-400 rounded-r-lg">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-green-800">{{ session('success') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif

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
                                    <h3 class="text-lg font-semibold text-red-800 mb-2">Erros no cadastro:</h3>
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
                    <form method="POST" action="{{ route('register.submit') }}" class="space-y-6">
                        @csrf

                        {{-- Linha 1: Identidade e Nome Completo --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                <p class="text-xs text-gray-500 mt-1">9-10 dígitos (somente números)</p>
                            </div>

                            {{-- Nome Completo --}}
                            <div>
                                <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">Nome Completo *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <input type="text" id="full_name" name="full_name"
                                        value="{{ old('full_name') }}"
                                        class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                        placeholder="Nome completo do militar"
                                        required autocomplete="name">
                                </div>
                            </div>
                        </div>

                        {{-- Linha 2: Nome de Guerra e Email --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Nome de Guerra --}}
                            <div>
                                <label for="war_name" class="block text-sm font-medium text-gray-700 mb-2">Nome de Guerra</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                    </div>
                                    <input type="text" id="war_name" name="war_name"
                                        value="{{ old('war_name') }}"
                                        class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                        placeholder="Nome de guerra (opcional)"
                                        autocomplete="nickname">
                                </div>
                            </div>

                            {{-- Email --}}
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <input type="email" id="email" name="email"
                                        value="{{ old('email') }}"
                                        class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                        placeholder="email@exemplo.com"
                                        autocomplete="email">
                                </div>
                            </div>
                        </div>

                        {{-- Linha 3: Posto/Graduação e Organização --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Posto/Graduação --}}
                            <div>
                                <label for="rank_id" class="block text-sm font-medium text-gray-700 mb-2">Posto/Graduação *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                        </svg>
                                    </div>
                                    <select id="rank_id" name="rank_id"
                                        class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                        required>
                                        <option value="">Selecione...</option>
                                        @foreach(\App\Models\Rank::orderBy('order')->get() as $rank)
                                            <option value="{{ $rank->id }}" {{ old('rank_id') == $rank->id ? 'selected' : '' }}>
                                                {{ $rank->name }} ({{ $rank->abbreviation }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Organização --}}
                            <div>
                                <label for="organization_id" class="block text-sm font-medium text-gray-700 mb-2">Organização *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                    </div>
                                    <select id="organization_id" name="organization_id"
                                        class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                        required>
                                        <option value="">Selecione...</option>
                                        @foreach(\App\Models\Organization::orderBy('name')->get() as $org)
                                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>
                                                {{ $org->name }} ({{ $org->abbreviation }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Linha 4: Subunidade, Força Armada, Gênero --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            {{-- Subunidade --}}
                            <div>
                                <label for="subunit" class="block text-sm font-medium text-gray-700 mb-2">Subunidade</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </div>
                                    <input type="text" id="subunit" name="subunit"
                                        value="{{ old('subunit') }}"
                                        class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                        placeholder="Ex: 1ª Cia, Pel Com, etc."
                                        autocomplete="organization">
                                </div>
                            </div>

                            {{-- Força Armada --}}
                            <div>
                                <label for="armed_force" class="block text-sm font-medium text-gray-700 mb-2">Força Armada *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                    </div>
                                    <select id="armed_force" name="armed_force"
                                        class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                        required>
                                        <option value="">Selecione...</option>
                                        <option value="EB" {{ old('armed_force') == 'EB' ? 'selected' : '' }}>Exército Brasileiro</option>
                                        <option value="MB" {{ old('armed_force') == 'MB' ? 'selected' : '' }}>Marinha do Brasil</option>
                                        <option value="FAB" {{ old('armed_force') == 'FAB' ? 'selected' : '' }}>Força Aérea Brasileira</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Gênero --}}
                            <div>
                                <label for="gender" class="block text-sm font-medium text-gray-700 mb-2">Gênero *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                    </div>
                                    <select id="gender" name="gender"
                                        class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                        required>
                                        <option value="">Selecione...</option>
                                        <option value="M" {{ old('gender') == 'M' ? 'selected' : '' }}>Masculino</option>
                                        <option value="F" {{ old('gender') == 'F' ? 'selected' : '' }}>Feminino</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Linha 5: Senha e Confirmação --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                        placeholder="Mínimo 8 caracteres"
                                        required autocomplete="new-password">
                                </div>
                            </div>

                            {{-- Confirmação de Senha --}}
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirmar Senha *</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="input-modern w-full pl-10 pr-4 py-3 rounded-lg border-2 focus:outline-none"
                                        placeholder="Repita a senha"
                                        required autocomplete="new-password">
                                </div>
                            </div>
                        </div>

                        {{-- Botões --}}
                        <div class="flex gap-4 pt-4">
                            <a href="{{ route('login') }}"
                                class="btn-secondary flex-1 px-6 py-3 text-white font-semibold rounded-lg focus:outline-none focus:ring-4 focus:ring-gray-300 text-center">
                                <span class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                                    </svg>
                                    Voltar ao Login
                                </span>
                            </a>

                            <button type="submit"
                                class="btn-primary flex-1 px-6 py-3 text-white font-semibold rounded-lg focus:outline-none focus:ring-4 focus:ring-emerald-300">
                                <span class="flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                                    </svg>
                                    Cadastrar
                                </span>
                            </button>
                        </div>
                    </form>

                    {{-- Info --}}
                    <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                        <p class="text-xs text-gray-500">
                            Após o cadastro, você poderá fazer login imediatamente.<br>
                            Novos usuários são criados com perfil de "Solicitante".
                        </p>
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