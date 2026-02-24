@extends('layouts.app')

@section('title', 'Comparar Inventários — SMARTSUB')
@section('page-title', 'Comparar Inventários')

@section('header-actions')
    <x-btn variant="secondary" href="{{ route('inventory.index') }}" size="sm">← Voltar</x-btn>
@endsection

@section('content')

{{-- Instruções --}}
<div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
    <div class="flex items-start">
        <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <h4 class="text-sm font-semibold text-blue-800">Comparação Temporal de Inventários</h4>
            <p class="text-sm text-blue-700 mt-1">
                Selecione dois inventários para comparar a evolução dos materiais ao longo do tempo.
                O sistema identificará itens adicionados, removidos e alterações em quantidades, valores e números patrimoniais.
            </p>
        </div>
    </div>
</div>

@if($uploads->count() < 2)
    <x-empty-state
        title="Inventários insuficientes"
        message="É necessário ter pelo menos 2 inventários processados para realizar uma comparação. Carregue mais PDFs."
        action="{{ route('inventory.create') }}"
        actionLabel="Carregar PDF"
    />
@else
    <x-card>
        <form method="POST" action="{{ route('inventory.compare.results') }}" class="p-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Inventário Anterior --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">
                        <svg class="w-4 h-4 inline mr-1 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Inventário Anterior (base)
                    </label>
                    <select name="old_upload_id" required
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 bg-white">
                        <option value="">Selecione o inventário anterior...</option>
                        @foreach($uploads as $upload)
                            <option value="{{ $upload->id }}"
                                    @selected(old('old_upload_id', $selectedOld) == $upload->id)>
                                {{ $upload->filename }}
                                — {{ $upload->created_at->format('d/m/Y') }}
                                ({{ $upload->total_items }} itens, R$ {{ number_format($upload->total_value, 2, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('old_upload_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Inventário Mais Recente --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-3">
                        <svg class="w-4 h-4 inline mr-1 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Inventário Mais Recente
                    </label>
                    <select name="new_upload_id" required
                            class="w-full px-4 py-3 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 bg-white">
                        <option value="">Selecione o inventário recente...</option>
                        @foreach($uploads as $upload)
                            <option value="{{ $upload->id }}"
                                    @selected(old('new_upload_id', $selectedNew) == $upload->id)>
                                {{ $upload->filename }}
                                — {{ $upload->created_at->format('d/m/Y') }}
                                ({{ $upload->total_items }} itens, R$ {{ number_format($upload->total_value, 2, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                    @error('new_upload_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Seta visual --}}
            <div class="hidden lg:flex justify-center my-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </div>

            <div class="mt-6 flex justify-center">
                <x-btn type="submit" variant="primary" size="lg"
                       icon="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                    Comparar Inventários
                </x-btn>
            </div>
        </form>
    </x-card>

    {{-- Lista rápida dos inventários --}}
    <div class="mt-6">
        <h3 class="text-sm font-bold text-gray-600 uppercase mb-3">Inventários Disponíveis</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($uploads as $upload)
                <div class="bg-white rounded-lg border border-gray-200 p-3 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-900 truncate" title="{{ $upload->filename }}">{{ $upload->filename }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $upload->dependency ?? '—' }} / {{ $upload->unit ?? '—' }}
                            </p>
                        </div>
                        <span class="text-xs font-mono text-gray-400">{{ $upload->created_at->format('d/m/Y') }}</span>
                    </div>
                    <div class="mt-2 flex items-center gap-3 text-xs text-gray-600">
                        <span>{{ $upload->total_items }} itens</span>
                        <span>R$ {{ number_format($upload->total_value, 2, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

@endsection
