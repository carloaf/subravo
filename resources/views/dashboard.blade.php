@extends('layouts.app')

@section('title', 'SUBRAVO — Dashboard')

@push('styles')
<style>
    .header-enhanced {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        position: relative;
        overflow: hidden;
    }
    .header-enhanced::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.1"/><circle cx="10" cy="90" r="0.5" fill="white" opacity="0.1"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>') repeat;
        opacity: 0.1;
    }
    .logo-enhanced {
        animation: logoGlow 3s ease-in-out infinite alternate;
    }
    @keyframes logoGlow {
        from { filter: drop-shadow(0 0 10px rgba(255, 215, 0, 0.5)); }
        to   { filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.8)); }
    }
    .user-info-bubble {
        background: rgba(255,255,255,0.1);
        backdrop-filter: blur(15px);
        border: 1px solid rgba(255,255,255,0.2);
    }
    .status-indicator { animation: pulse 2s infinite; }
    @keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.5; } }
    .quick-stat {
        background: rgba(255,255,255,0.12);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255,255,255,0.18);
        transition: all 0.3s ease;
    }
    .quick-stat:hover {
        background: rgba(255,255,255,0.22);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }
</style>
@endpush

@section('content')

{{-- ================================================================== --}}
{{-- ENHANCED DASHBOARD HEADER — Padrão SAGA                           --}}
{{-- ================================================================== --}}
<div class="header-enhanced -mx-4 sm:-mx-6 lg:-mx-8 -mt-6 mb-8 shadow-2xl rounded-b-2xl">
    <div class="relative max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            {{-- Logo + Title --}}
            <div class="flex items-center space-x-4">
                <div class="logo-enhanced">
                    <img src="{{ asset('images/folhaint_transparent.png') }}" alt="Logo" class="w-14 h-14 object-contain">
                </div>
                <div>
                    <h1 class="text-3xl font-bold">
                        <span class="bg-gradient-to-r from-yellow-200 to-yellow-100 bg-clip-text text-transparent">SUBRAVO</span>
                    </h1>
                    <p class="text-green-100 text-sm font-medium">Sub-Chefia de Material Bélico — Controle de Estoque</p>
                </div>
            </div>

            {{-- User Info + Status --}}
            <div class="flex items-center space-x-4">
                <div class="hidden md:flex items-center space-x-2 text-green-200">
                    <div class="w-3 h-3 bg-green-400 rounded-full status-indicator"></div>
                    <span class="text-sm font-medium">Sistema Online</span>
                </div>

                <div class="user-info-bubble rounded-xl px-4 py-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-white to-emerald-100 rounded-full flex items-center justify-center shadow-lg">
                            <span class="text-sm font-bold text-emerald-800">
                                {{ strtoupper(substr($user->war_name, 0, 2)) }}
                            </span>
                        </div>
                        <div class="hidden lg:block">
                            <p class="text-white font-semibold text-sm">{{ $user->war_name }}</p>
                            <p class="text-green-200 text-xs">
                                {{ $user->rank->abbreviation ?? '' }}
                                @if($user->isAdmin())
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        Admin
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Stats Bar (glassmorphism) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
            <div class="quick-stat rounded-xl px-4 py-3 text-center">
                <p class="text-2xl font-bold text-white">{{ $totalProducts }}</p>
                <p class="text-green-100 text-xs font-medium">Produtos</p>
                <p class="text-green-200 text-xs">{{ $totalCategories }} categorias</p>
            </div>
            <div class="quick-stat rounded-xl px-4 py-3 text-center">
                <p class="text-2xl font-bold text-white">{{ $availableStock }}</p>
                <p class="text-green-100 text-xs font-medium">Itens em Estoque</p>
                <p class="text-green-200 text-xs">{{ $availableLots }} lotes</p>
            </div>
            <div class="quick-stat rounded-xl px-4 py-3 text-center">
                <p class="text-2xl font-bold text-white">{{ $activeLoans }}</p>
                <p class="text-green-100 text-xs font-medium">Empréstimos Ativos</p>
                @if($overdueLoans > 0)
                    <p class="text-yellow-200 text-xs font-semibold">{{ $overdueLoans }} vencido(s)</p>
                @else
                    <p class="text-green-200 text-xs">Nenhum vencido</p>
                @endif
            </div>
            <div class="quick-stat rounded-xl px-4 py-3 text-center">
                <p class="text-2xl font-bold {{ $alertCount > 0 ? 'text-yellow-200' : 'text-white' }}">{{ $alertCount }}</p>
                <p class="text-green-100 text-xs font-medium">Alertas</p>
                <p class="{{ $alertCount > 0 ? 'text-yellow-200' : 'text-green-200' }} text-xs">{{ $alertCount > 0 ? 'Requer atenção' : 'Tudo normal' }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Alertas --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

    {{-- Produtos com estoque baixo --}}
    <x-card title="Estoque Baixo" subtitle="Produtos abaixo do mínimo">
        @if($lowStockProducts->isEmpty())
            <div class="flex items-center text-sm text-gray-400 py-4">
                <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Todos os produtos estão acima do estoque mínimo.
            </div>
        @else
            <div class="divide-y divide-gray-100 -mx-6 -mb-6">
                @foreach($lowStockProducts as $product)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                            <p class="text-xs text-gray-400">{{ $product->category->name ?? '—' }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-bold text-red-600">{{ $product->getAvailableStock() }}</span>
                            <span class="text-xs text-gray-400"> / mín {{ $product->minimum_stock }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    {{-- Itens próximos da validade --}}
    <x-card title="Próximos da Validade" subtitle="Vencimento nos próximos 30 dias">
        @if($expiringItems->isEmpty())
            <div class="flex items-center text-sm text-gray-400 py-4">
                <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Nenhum item próximo da validade.
            </div>
        @else
            <div class="divide-y divide-gray-100 -mx-6 -mb-6">
                @foreach($expiringItems as $item)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $item->product->name }}</p>
                            <p class="text-xs text-gray-400">Lote: {{ $item->batch ?? '—' }}</p>
                        </div>
                        <x-badge color="{{ $item->isExpired() ? 'red' : 'amber' }}">
                            {{ $item->expiration_date->format('d/m/Y') }}
                        </x-badge>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    {{-- Empréstimos Vencidos --}}
    <x-card title="Empréstimos Vencidos" subtitle="Cautelas com devolução em atraso">
        @if($overdueLoansCollection->isEmpty())
            <div class="flex items-center text-sm text-gray-400 py-4">
                <svg class="w-5 h-5 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Nenhum empréstimo vencido.
            </div>
        @else
            <div class="divide-y divide-gray-100 -mx-6 -mb-6">
                @foreach($overdueLoansCollection as $overdue)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <a href="{{ route('loans.show', $overdue) }}" class="text-sm font-medium text-red-700 hover:text-red-900">
                                {{ $overdue->loan_number }}
                            </a>
                            <p class="text-xs text-gray-400">{{ $overdue->getBorrowerDisplayName() }}</p>
                        </div>
                        <div class="text-right">
                            <x-badge color="red">
                                {{ $overdue->expected_return_date->diffForHumans() }}
                            </x-badge>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>
</div>

{{-- Últimos registros --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

    {{-- Últimos empréstimos --}}
    <x-card title="Últimos Empréstimos" subtitle="Cautelas mais recentes">
        @if($recentLoans->isEmpty())
            <div class="flex items-center text-sm text-gray-400 py-4">
                <svg class="w-5 h-5 mr-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Nenhum empréstimo registrado.
            </div>
        @else
            <div class="divide-y divide-gray-100 -mx-6 -mb-6">
                @foreach($recentLoans as $loan)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <a href="{{ route('loans.show', $loan) }}" class="text-sm font-medium text-gray-900 hover:text-emerald-600">{{ $loan->loan_number }}</a>
                            <p class="text-xs text-gray-400">{{ $loan->getBorrowerDisplayName() }}</p>
                        </div>
                        @php
                            $statusColor = match($loan->status) {
                                'active'   => 'blue',
                                'returned' => 'green',
                                'partial'  => 'amber',
                                'overdue'  => 'red',
                                default    => 'gray',
                            };
                        @endphp
                        <x-badge color="{{ $statusColor }}">{{ $loan->getStatusLabel() }}</x-badge>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>

    {{-- Últimas movimentações --}}
    <x-card title="Últimas Movimentações" subtitle="Registro de auditoria">
        @if($recentMovements->isEmpty())
            <div class="flex items-center text-sm text-gray-400 py-4">
                <svg class="w-5 h-5 mr-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
                Nenhuma movimentação registrada.
            </div>
        @else
            <div class="divide-y divide-gray-100 -mx-6 -mb-6">
                @foreach($recentMovements as $move)
                    <div class="flex items-center justify-between px-6 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $move->stockItem?->product?->name ?? '—' }}</p>
                            <p class="text-xs text-gray-400">{{ $move->performedBy?->getDisplayName() ?? '—' }} &middot; {{ $move->created_at->format('d/m H:i') }}</p>
                        </div>
                        <x-badge color="{{ in_array($move->movement_type, ['entry','return']) ? 'green' : (in_array($move->movement_type, ['loan','exit']) ? 'amber' : 'gray') }}">
                            {{ $move->getTypeLabel() }}
                        </x-badge>
                    </div>
                @endforeach
            </div>
        @endif
    </x-card>
</div>

{{-- Info do perfil --}}
<div class="mt-6">
    <x-card title="Informações do Usuário">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase">Posto/Graduação</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $user->rank?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase">Organização Militar</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $user->organization?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-400 font-medium uppercase">Número de Identidade</p>
                <p class="text-sm font-semibold text-gray-900 mt-0.5">{{ $user->identity_number }}</p>
            </div>
        </div>
    </x-card>
</div>
@endsection
