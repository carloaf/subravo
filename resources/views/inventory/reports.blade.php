@extends('layouts.app')

@section('title', 'Relatórios de Inventário — SUBRAVO')
@section('page-title', 'Relatórios de Inventário')

@section('header-actions')
    <div class="flex gap-2">
        <x-btn variant="outline" href="{{ route('inventory.materials') }}" size="sm"
               icon="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z">
            Buscar Materiais
        </x-btn>
        <x-btn variant="primary" href="{{ route('inventory.index') }}" size="sm"
               icon="M15 19l-7-7 7-7">
            Voltar
        </x-btn>
    </div>
@endsection

@section('content')

<div class="max-w-4xl mx-auto">
    <x-card>
        <div class="mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-2">Gerar Relatório</h3>
            <p class="text-sm text-gray-600">Selecione os filtros e o formato desejado para gerar o relatório do inventário.</p>
        </div>

        <form method="POST" action="{{ route('inventory.reports.generate') }}" class="space-y-6">
            @csrf

            {{-- Tipo de Relatório --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Tipo de Relatório</label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="report_type" value="general" class="sr-only peer" checked>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="font-semibold text-gray-900">Geral</span>
                            </div>
                            <p class="text-xs text-gray-600">Todos os itens de inventários processados</p>
                        </div>
                    </label>

                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="report_type" value="by_material" class="sr-only peer">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                                <span class="font-semibold text-gray-900">Por Tipo de Material</span>
                            </div>
                            <p class="text-xs text-gray-600">Agrupado por tipo de material</p>
                        </div>
                    </label>

                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="report_type" value="by_upload" class="sr-only peer">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <span class="font-semibold text-gray-900">Por Upload</span>
                            </div>
                            <p class="text-xs text-gray-600">Filtrar por arquivo específico</p>
                        </div>
                    </label>

                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="report_type" value="by_dependency" class="sr-only peer">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <span class="font-semibold text-gray-900">Por Dependência</span>
                            </div>
                            <p class="text-xs text-gray-600">Filtrar por dependência/unidade</p>
                        </div>
                    </label>

                    <label class="relative flex items-start p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50">
                        <input type="radio" name="report_type" value="monthly_consolidated" class="sr-only peer">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="font-semibold text-gray-900">Consolidado Mensal</span>
                            </div>
                            <p class="text-xs text-gray-600">Todas as dependências de um mês específico</p>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Campo de Mês/Ano (aparece quando Consolidado Mensal é selecionado) --}}
            <div x-data="{ showMonthField: false }">
                <div class="hidden" 
                     x-show="showMonthField"
                     x-transition>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        Mês/Ano do Relatório Consolidado
                    </label>
                    <input type="month" 
                           name="month_year" 
                           value="{{ now()->format('Y-m') }}"
                           class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                    <p class="mt-1 text-xs text-gray-500">
                        Exemplo: fevereiro2026 (02/2026)
                    </p>
                </div>

                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const radios = document.querySelectorAll('input[name="report_type"]');
                        const monthField = document.querySelector('[x-data]');
                        
                        radios.forEach(radio => {
                            radio.addEventListener('change', function() {
                                if (this.value === 'monthly_consolidated') {
                                    monthField.querySelector('[x-show]').classList.remove('hidden');
                                } else {
                                    monthField.querySelector('[x-show]').classList.add('hidden');
                                }
                            });
                        });
                    });
                </script>
            </div>

            {{-- Filtros --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Específico (Opcional)</label>
                    <select name="upload_id"
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                        <option value="">Todos os uploads</option>
                        @foreach($uploads as $upload)
                            <option value="{{ $upload->id }}">
                                {{ $upload->filename }} — {{ $upload->dependency }} ({{ $upload->created_at->format('d/m/Y') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo de Material (Opcional)</label>
                    <select name="material_type"
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                        <option value="">Todos os tipos</option>
                        @foreach($materialTypes as $type)
                            <option value="{{ $type }}">{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Dependência (Opcional)</label>
                    <select name="dependency"
                            class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                        <option value="">Todas as dependências</option>
                        @foreach($dependencies as $dep)
                            <option value="{{ $dep }}">{{ $dep }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Buscar Material (Opcional)</label>
                    <input type="text" name="search" 
                           placeholder="Nome do material..."
                           class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Código do Material (Opcional)</label>
                    <input type="text" name="material_code" 
                           placeholder="Ex: 123456789"
                           class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Número de Ficha (Opcional)</label>
                    <input type="text" name="ficha_number" 
                           placeholder="Ex: 00123"
                           class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
                </div>
            </div>

            {{-- Formato --}}
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-3">Formato de Saída</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 flex-1">
                        <input type="radio" name="format" value="pdf" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500" checked>
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>
                            <span class="font-semibold text-gray-900">PDF</span>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 p-4 border-2 border-gray-300 rounded-lg cursor-pointer hover:border-emerald-500 transition-colors has-[:checked]:border-emerald-500 has-[:checked]:bg-emerald-50 flex-1">
                        <input type="radio" name="format" value="excel" class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                        <div class="flex items-center gap-2">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="font-semibold text-gray-900">Excel (.xlsx)</span>
                        </div>
                    </label>
                </div>
            </div>

            {{-- Botões --}}
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <x-btn type="button" variant="secondary" href="{{ route('inventory.index') }}" size="md">
                    Cancelar
                </x-btn>
                <x-btn type="submit" variant="primary" size="md" 
                       icon="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    Gerar Relatório
                </x-btn>
            </div>
        </form>
    </x-card>
</div>

@endsection
