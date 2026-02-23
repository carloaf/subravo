@extends('layouts.app')

@section('title', 'Relatórios — SUBRAVO')
@section('page-title', 'Central de Relatórios')

@section('content')

<div class="space-y-6">

    {{-- Seletor de relatório --}}
    <form method="POST" action="{{ route('admin.reports.generate') }}" target="_blank" class="space-y-6" x-data="{ reportType: '' }">
        @csrf

        {{-- Cards de tipos de relatório --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @php
                $reports = [
                    ['type' => 'stock_summary', 'title' => 'Resumo do Estoque', 'desc' => 'Visão geral de todos os produtos com quantidades disponíveis, emprestadas e mínimos.', 'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'color' => 'subravo'],
                    ['type' => 'loans_active', 'title' => 'Cautelas Ativas', 'desc' => 'Lista de todas as cautelas atualmente em aberto, com mutuários e itens.', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'amber'],
                    ['type' => 'loans_history', 'title' => 'Histórico de Cautelas', 'desc' => 'Histórico completo de todas as cautelas com filtro por período.', 'icon' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10', 'color' => 'blue'],
                    ['type' => 'movements', 'title' => 'Movimentações', 'desc' => 'Registro de todas as entradas, saídas e ajustes de estoque.', 'icon' => 'M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4', 'color' => 'purple'],
                    ['type' => 'low_stock', 'title' => 'Estoque Baixo', 'desc' => 'Produtos que estão abaixo do estoque mínimo definido. Requer reposição.', 'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'red'],
                    ['type' => 'expiring', 'title' => 'Próximos da Validade', 'desc' => 'Itens de estoque com data de validade nos próximos 60 dias.', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'color' => 'orange'],
                ];
            @endphp

            @foreach($reports as $r)
                <label class="cursor-pointer group" :class="reportType === '{{ $r['type'] }}' ? 'ring-2 ring-emerald-500 rounded-xl' : ''">
                    <input type="radio" name="report_type" value="{{ $r['type'] }}" x-model="reportType" class="sr-only" required>
                    <div class="p-4 bg-white rounded-xl border border-gray-200 hover:border-emerald-300 hover:shadow-md transition h-full"
                         :class="reportType === '{{ $r['type'] }}' ? 'border-emerald-400 bg-emerald-50' : ''">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0 p-2 rounded-lg bg-gray-100 group-hover:bg-emerald-100"
                                 :class="reportType === '{{ $r['type'] }}' ? 'bg-emerald-200' : ''">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $r['icon'] }}"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900">{{ $r['title'] }}</h3>
                                <p class="text-xs text-gray-500 mt-1">{{ $r['desc'] }}</p>
                            </div>
                        </div>
                    </div>
                </label>
            @endforeach
        </div>

        {{-- Opções de filtro --}}
        <x-card title="Opções do Relatório">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <x-input name="date_from" label="Data Inicial" type="date" hint="Para relatórios com período" />
                <x-input name="date_to" label="Data Final" type="date" />
                <x-select name="format" label="Formato" required
                          :options="['screen' => 'Visualizar na Tela', 'pdf' => 'Baixar PDF', 'excel' => 'Baixar Excel (XLSX)']"
                          selected="screen" />
            </div>
        </x-card>

        {{-- Gerar --}}
        <div class="flex justify-end">
            <x-btn variant="primary" type="submit" icon="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                Gerar Relatório
            </x-btn>
        </div>
    </form>
</div>

@endsection
