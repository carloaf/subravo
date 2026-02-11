<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SUBRAVO') }} - @yield('title', 'Sistema')</title>

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

    <!-- Custom Styles -->
    <style>
        [x-cloak] { display: none !important; }

        /* Custom checkbox styles */
        .sr-only {
            position: absolute;
            width: 1px; height: 1px; padding: 0; margin: -1px;
            overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0;
        }

        input[type="checkbox"]:checked + div {
            background-color: rgb(252 211 77 / 0.5);
        }
        input[type="checkbox"]:checked + div svg {
            display: block !important;
        }

        .transition-colors {
            transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 150ms;
        }

        /* Enhanced form inputs */
        .input-enhanced {
            width: 100%;
            padding: 14px 16px;
            font-size: 0.875rem;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            background-color: white;
            color: #111827;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            min-height: 48px;
        }
        .input-enhanced::placeholder { color: #6b7280; font-weight: 500; }
        .input-enhanced:hover { border-color: #9ca3af; }
        .input-enhanced:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
        .input-enhanced:disabled { background-color: #f3f4f6; color: #6b7280; cursor: not-allowed; }

        .select-enhanced {
            width: 100%;
            padding: 14px 16px;
            font-size: 0.875rem;
            border: 2px solid #d1d5db;
            border-radius: 8px;
            background-color: white;
            color: #111827;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 12px center;
            background-repeat: no-repeat;
            background-size: 16px 16px;
            appearance: none;
            min-height: 48px;
        }
        .select-enhanced:hover { border-color: #9ca3af; }
        .select-enhanced:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }
    </style>
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <div class="min-h-screen flex flex-col">

        {{-- ================================================================== --}}
        {{-- TOP NAVIGATION — Padrão SAGA                                       --}}
        {{-- ================================================================== --}}
        @auth
        <nav class="bg-white shadow" x-data="{ mobileOpen: false }">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    {{-- Logo + Links --}}
                    <div class="flex">
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('dashboard') }}" class="flex items-center">
                                <img src="{{ asset('images/folhaint_transparent.png') }}" alt="Logo" class="w-8 h-8 object-contain">
                                <h1 class="ml-2 text-xl font-bold text-emerald-600">SUBRAVO</h1>
                            </a>
                        </div>

                        {{-- Links desktop --}}
                        <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                            @php $currentRoute = request()->route()?->getName() ?? ''; @endphp

                            <a href="{{ route('dashboard') }}"
                               class="{{ $currentRoute === 'dashboard' ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Dashboard
                            </a>
                            <a href="{{ route('products.index') }}"
                               class="{{ str_starts_with($currentRoute, 'products.') || str_starts_with($currentRoute, 'categories.') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Produtos
                            </a>
                            <a href="{{ route('stock.index') }}"
                               class="{{ str_starts_with($currentRoute, 'stock.') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Estoque
                            </a>
                            <a href="{{ route('loans.index') }}"
                               class="{{ str_starts_with($currentRoute, 'loans.') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Cautelas
                            </a>
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('admin.users.index') }}"
                               class="{{ str_starts_with($currentRoute, 'admin.users') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Usuários
                            </a>
                            <a href="{{ route('admin.reports.index') }}"
                               class="{{ str_starts_with($currentRoute, 'admin.reports') ? 'border-emerald-500 text-gray-900' : 'border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700' }} inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                Relatórios
                            </a>
                            @endif
                        </div>
                    </div>

                    {{-- User info + Logout (desktop) --}}
                    <div class="hidden sm:flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <div class="w-8 h-8 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-full flex items-center justify-center">
                                <span class="text-xs font-bold text-emerald-800">
                                    {{ strtoupper(substr(auth()->user()->war_name, 0, 2)) }}
                                </span>
                            </div>
                            <span class="text-sm font-medium text-gray-700">{{ auth()->user()->war_name }}</span>
                            @if(auth()->user()->isAdmin())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Admin</span>
                            @endif
                        </div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-500 hover:text-red-600 px-3 py-2 text-sm font-medium transition-colors">
                                Sair
                            </button>
                        </form>
                    </div>

                    {{-- Hamburger mobile --}}
                    <div class="flex items-center sm:hidden">
                        <button @click="mobileOpen = !mobileOpen" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': mobileOpen, 'inline-flex': ! mobileOpen }" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! mobileOpen, 'inline-flex': mobileOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Menu mobile --}}
            <div x-show="mobileOpen" x-cloak class="sm:hidden" x-transition>
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('dashboard') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ $currentRoute === 'dashboard' ? 'border-emerald-500 text-emerald-700 bg-emerald-50' : 'border-transparent text-gray-600 hover:bg-gray-50' }} text-base font-medium">Dashboard</a>
                    <a href="{{ route('products.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ str_starts_with($currentRoute, 'products.') ? 'border-emerald-500 text-emerald-700 bg-emerald-50' : 'border-transparent text-gray-600 hover:bg-gray-50' }} text-base font-medium">Produtos</a>
                    <a href="{{ route('stock.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ str_starts_with($currentRoute, 'stock.') ? 'border-emerald-500 text-emerald-700 bg-emerald-50' : 'border-transparent text-gray-600 hover:bg-gray-50' }} text-base font-medium">Estoque</a>
                    <a href="{{ route('loans.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ str_starts_with($currentRoute, 'loans.') ? 'border-emerald-500 text-emerald-700 bg-emerald-50' : 'border-transparent text-gray-600 hover:bg-gray-50' }} text-base font-medium">Cautelas</a>
                    @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.users.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ str_starts_with($currentRoute, 'admin.users') ? 'border-emerald-500 text-emerald-700 bg-emerald-50' : 'border-transparent text-gray-600 hover:bg-gray-50' }} text-base font-medium">Usuários</a>
                    <a href="{{ route('admin.reports.index') }}" class="block pl-3 pr-4 py-2 border-l-4 {{ str_starts_with($currentRoute, 'admin.reports') ? 'border-emerald-500 text-emerald-700 bg-emerald-50' : 'border-transparent text-gray-600 hover:bg-gray-50' }} text-base font-medium">Relatórios</a>
                    @endif
                </div>
                <div class="pt-4 pb-3 border-t border-gray-200">
                    <div class="flex items-center px-4">
                        <div class="w-10 h-10 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-full flex items-center justify-center">
                            <span class="text-sm font-bold text-emerald-800">{{ strtoupper(substr(auth()->user()->war_name, 0, 2)) }}</span>
                        </div>
                        <div class="ml-3">
                            <div class="text-base font-medium text-gray-800">{{ auth()->user()->war_name }}</div>
                            <div class="text-sm font-medium text-gray-500">{{ auth()->user()->rank->abbreviation ?? '' }} — {{ ucfirst(auth()->user()->role) }}</div>
                        </div>
                    </div>
                    <div class="mt-3 space-y-1">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-left pl-3 pr-4 py-2 border-l-4 border-transparent text-base font-medium text-gray-600 hover:bg-gray-50 hover:text-red-600">
                                Sair
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>
        @endauth

        {{-- ================================================================== --}}
        {{-- PAGE HEADING                                                       --}}
        {{-- ================================================================== --}}
        @hasSection('page-title')
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">@yield('page-title')</h1>
                @yield('header-actions')
            </div>
        </header>
        @endif

        {{-- ================================================================== --}}
        {{-- PAGE CONTENT                                                       --}}
        {{-- ================================================================== --}}
        <main class="flex-1 py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- Flash messages --}}
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded"
                         x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                        <div class="flex items-center justify-between">
                            <span>{{ session('success') }}</span>
                            <button @click="show = false" class="text-green-500 hover:text-green-700 ml-2">&times;</button>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded"
                         x-data="{ show: true }" x-show="show"
                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                        <div class="flex items-center justify-between">
                            <span>{{ session('error') }}</span>
                            <button @click="show = false" class="text-red-500 hover:text-red-700 ml-2">&times;</button>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>

        {{-- ================================================================== --}}
        {{-- FOOTER                                                             --}}
        {{-- ================================================================== --}}
        <footer class="bg-gray-50 border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                <div class="text-center text-sm text-gray-400 italic">
                    &copy; {{ date('Y') }} SUBRAVO - Desenv: Augusto
                </div>
            </div>
        </footer>
    </div>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('scripts')
    @yield('scripts')
</body>
</html>
