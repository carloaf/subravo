@extends('layouts.app')

@section('title', 'Uso Duradouro - HelpSub')
@section('page-title', 'Material de Uso Duradouro')

@section('content')

{{-- Info header --}}
<div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-lg shadow-lg p-6 mb-6 text-white">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold mb-2">Controle de Material Permanente</h2>
            <p class="text-emerald-50 text-sm">Monitoramento de produtos duráveis com controle de validade e localização</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="text-right">
                <div class="text-3xl font-bold">{{ $durableProducts->count() }}</div>
                <div class="text-emerald-100 text-sm">Produtos Cadastrados</div>
            </div>
            <a href="{{ route('durables.pdf') }}"
               target="_blank"
               class="flex items-center gap-2 bg-white/20 hover:bg-white/30 border border-white/40 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-200 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir Relação
            </a>
            <a href="{{ route('durables.excel') }}"
               class="flex items-center gap-2 bg-white/20 hover:bg-white/30 border border-white/40 text-white text-sm font-semibold px-4 py-2 rounded-lg transition-all duration-200 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exportar Excel
            </a>
        </div>
    </div>
</div>

{{-- Quick stats --}}
@php
    $totalInventorySISCOFIS = $durableItems->sum('inventory_qty');
    $totalItems             = $durableItems->sum('total');
    $totalAvailable         = $durableItems->sum('available');
    $totalLoaned            = $durableItems->sum('loaned');
    $totalDamaged           = $durableItems->sum('damaged');
    $totalExpiring          = $durableItems->sum('expiring_soon');
    $totalExpired           = $durableItems->sum('expired');
    $globalDiff             = $totalInventorySISCOFIS - $totalItems;
    $countDivergentes       = $durableItems->filter(fn($i) => $i->inventory_qty > 0 && $i->inventory_qty !== $i->total)->count();
@endphp

<div class="bg-white rounded-lg shadow-lg p-6 mb-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Inventário SISCOFIS --}}
        <div class="border-r border-gray-200 pr-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">📋 Inventário SISCOFIS</h3>
            <div class="flex items-baseline gap-2">
                <div class="text-4xl font-bold text-emerald-600">{{ number_format($totalInventorySISCOFIS, 0, ',', '.') }}</div>
                <div class="text-sm text-gray-500">unidades registradas</div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                Quantidade oficial do último inventário importado do SISCOFIS
            </p>
        </div>

        {{-- Estoque Controlado --}}
        <div class="border-r border-gray-200 px-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">📦 Estoque Controlado</h3>
            <div class="flex items-baseline gap-2">
                <div class="text-4xl font-bold text-emerald-600">{{ number_format($totalItems, 0, ',', '.') }}</div>
                <div class="text-sm text-gray-500">itens individualizados</div>
            </div>
            <p class="text-xs text-gray-500 mt-2">
                Itens com controle individual (lote, série, empréstimos)
            </p>
        </div>

        {{-- Divergência --}}
        <div class="pl-6">
            <h3 class="text-sm font-semibold {{ $countDivergentes > 0 ? 'text-amber-600' : 'text-gray-500' }} uppercase mb-3">⚖ Divergência</h3>
            <div class="flex items-baseline gap-2">
                <div class="text-4xl font-bold {{ $globalDiff === 0 ? 'text-emerald-600' : 'text-amber-600' }}">
                    {{ $globalDiff >= 0 ? '+' : '' }}{{ number_format($globalDiff, 0, ',', '.') }}
                </div>
                <div class="text-sm text-gray-500">unidades</div>
            </div>
            <p class="text-xs mt-2 {{ $countDivergentes > 0 ? 'text-amber-700' : 'text-gray-500' }}">
                @if($countDivergentes > 0)
                    {{ $countDivergentes }} {{ $countDivergentes === 1 ? 'produto divergente' : 'produtos divergentes' }}
                    <a href="{{ request()->fullUrlWithQuery(['divergent_only' => 1]) }}" class="ml-1 underline font-semibold">ver só eles →</a>
                @else
                    Estoque em sincronia com o SISCOFIS ✓
                @endif
            </p>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-emerald-600">{{ $totalAvailable }}</div>
        <div class="text-xs text-gray-500 uppercase">Disponíveis</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-blue-600">{{ $totalLoaned }}</div>
        <div class="text-xs text-gray-500 uppercase">Emprestados</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-red-600">{{ $totalDamaged }}</div>
        <div class="text-xs text-gray-500 uppercase">Danificados</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-2xl font-bold text-amber-600">{{ $totalExpiring + $totalExpired }}</div>
        <div class="text-xs text-gray-500 uppercase">Vencidos/Vencendo</div>
    </div>
</div>

{{-- Filtros e ordenação --}}
<x-card class="mb-6">
    <form method="GET" class="flex flex-wrap gap-3 items-center">
        <div class="flex gap-2">
            <select name="sort"
                    style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                    class="px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                <option value="name"     @selected(request('sort', 'name') === 'name')>Nome</option>
                <option value="total"    @selected(request('sort') === 'total')>Total</option>
                <option value="available" @selected(request('sort') === 'available')>Disponíveis</option>
                <option value="loaned"   @selected(request('sort') === 'loaned')>Emprestados</option>
                <option value="expiring" @selected(request('sort') === 'expiring')>Validade</option>
                <option value="diff"     @selected(request('sort') === 'diff')>Divergência</option>
            </select>
            <select name="dir"
                    style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                    class="px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900">
                <option value="asc"  @selected(request('dir', 'asc') === 'asc')>⬆ Crescente</option>
                <option value="desc" @selected(request('dir') === 'desc')>⬇ Decrescente</option>
            </select>
        </div>
        <label class="flex items-center gap-2 text-sm text-amber-700 font-semibold cursor-pointer select-none">
            <input type="checkbox" name="divergent_only" value="1"
                   @checked(request('divergent_only'))
                   class="w-4 h-4 rounded text-amber-500 border-amber-400 focus:ring-amber-400">
            Só divergentes
        </label>
        <x-btn type="submit" variant="outline" size="sm">Filtrar / Ordenar</x-btn>
        @if(request()->hasAny(['sort', 'dir', 'divergent_only']))
            <x-btn variant="secondary" href="{{ route('durables.index') }}" size="sm">Limpar</x-btn>
        @endif
    </form>
</x-card>

{{-- Lista de produtos duráveis --}}
@if($durableItems->isEmpty())
    <x-empty-state
        title="Nenhum produto durável cadastrado"
        message="Os produtos marcados como 'Uso Duradouro' aparecerão aqui."
    />
@else
    <div class="space-y-4">
        @foreach($durableItems as $item)
            <x-card>
                {{-- Header do produto --}}
                <div class="flex items-start justify-between mb-4 pb-4 border-b">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-lg font-bold text-gray-900">{{ $item->product->name }}</h3>
                            <x-badge color="emerald">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                </svg>
                                Uso Duradouro
                            </x-badge>
                            @php $diff = $item->inventory_qty - $item->total; @endphp
                            @if($item->inventory_qty > 0 && $diff !== 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-bold
                                    {{ $diff > 0 ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                    ⚖ SISCOFIS {{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 0, ',', '.') }} vs estoque
                                </span>
                            @elseif($item->inventory_qty > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">✓ Em sincronia</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-4 text-sm text-gray-600">
                            <span><strong>Categoria:</strong> {{ $item->product->category->name }}</span>
                            <span><strong>Código SISCOFIS:</strong> {{ $item->product->siscofis_code ?? '—' }}</span>
                            @if($item->product->shelf_life_months)
                                <span><strong>Validade:</strong> {{ $item->product->shelf_life_months }} meses</span>
                            @else
                                <span class="text-emerald-600 font-semibold">✓ Sem validade</span>
                            @endif
                        </div>
                    </div>
                    <a href="{{ route('stock.index', ['product_id' => $item->product->id]) }}"
                       class="text-emerald-600 hover:text-emerald-700 text-sm font-medium">
                        Ver itens →
                    </a>
                </div>

                {{-- Estatísticas --}}
                <div class="mb-4">
                    {{-- Inventário SISCOFIS --}}
                    @if(isset($item->inventory_qty) && $item->inventory_qty > 0)
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-4 mb-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <div>
                                        <div class="text-xs text-emerald-600 font-semibold uppercase">Inventário SISCOFIS</div>
                                        <div class="text-2xl font-bold text-emerald-900">{{ number_format($item->inventory_qty, 0, ',', '.') }} <span class="text-sm font-normal text-emerald-600">unidades</span></div>
                                    </div>
                                </div>
                                @if($item->inventory_date)
                                    <div class="text-right text-xs text-emerald-600">
                                        <div>Última atualização:</div>
                                        <div class="font-semibold">{{ $item->inventory_date->format('d/m/Y H:i') }}</div>
                                    </div>
                                @endif
                            </div>

                            {{-- Lotes do inventário SISCOFIS --}}
                            @if($item->inventory_lots->count() > 1 || ($item->inventory_lots->count() === 1 && $item->inventory_lots->first()->ficha_number))
                                <details class="mt-3">
                                    <summary class="cursor-pointer text-xs font-semibold text-emerald-700 hover:text-emerald-900 select-none">
                                        ▸ Ver {{ $item->inventory_lots->count() }} {{ $item->inventory_lots->count() === 1 ? 'lote' : 'lotes' }} do SISCOFIS
                                    </summary>
                                    <div class="mt-2 overflow-x-auto">
                                        <table class="min-w-full divide-y divide-emerald-200 text-sm">
                                            <thead class="bg-emerald-100">
                                                <tr>
                                                    <th class="px-3 py-1.5 text-left text-xs font-semibold text-emerald-700 uppercase">Nº Ficha</th>
                                                    <th class="px-3 py-1.5 text-left text-xs font-semibold text-emerald-700 uppercase">Cód. Material</th>
                                                    <th class="px-3 py-1.5 text-center text-xs font-semibold text-emerald-700 uppercase">Qtd</th>
                                                    <th class="px-3 py-1.5 text-right text-xs font-semibold text-emerald-700 uppercase">Valor Unit.</th>
                                                    <th class="px-3 py-1.5 text-right text-xs font-semibold text-emerald-700 uppercase">Valor Total</th>
                                                    <th class="px-3 py-1.5 text-left text-xs font-semibold text-emerald-700 uppercase">Conta Contábil</th>
                                                    <th class="px-3 py-1.5 text-left text-xs font-semibold text-emerald-700 uppercase">Entrada SISCOFIS</th>
                                                    <th class="px-3 py-1.5 text-left text-xs font-semibold text-emerald-700 uppercase">Validade</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-emerald-100">
                                                @foreach($item->inventory_lots as $lot)
                                                    @php
                                                        // Suporta todos os formatos de batch key gerados pelo sync:
                                                        // - antigo: FICHA-xxx
                                                        // - v2: FICHA-xxx-UPn
                                                        // - v3 (atual): FICHA-xxx-Vvalue-UPn
                                                        $fichaPrefix  = $lot->ficha_number ? 'FICHA-' . $lot->ficha_number : null;
                                                        $matchedStock = $fichaPrefix
                                                            ? $item->items->first(fn($si) =>
                                                                $si->batch === $fichaPrefix ||
                                                                str_starts_with($si->batch, $fichaPrefix . '-')
                                                              )
                                                            : null;
                                                    @endphp
                                                    <tr class="hover:bg-emerald-50">
                                                        <td class="px-3 py-1.5 text-emerald-800 font-mono text-xs">{{ $lot->ficha_number ?? '—' }}</td>
                                                        <td class="px-3 py-1.5 text-gray-600 text-xs">{{ $lot->material_code ?? '—' }}</td>
                                                        <td class="px-3 py-1.5 text-center font-semibold text-gray-900">{{ $lot->quantity }}</td>
                                                        <td class="px-3 py-1.5 text-right text-gray-900">
                                                            R$ {{ number_format((float) $lot->unit_value, 2, ',', '.') }}
                                                        </td>
                                                        <td class="px-3 py-1.5 text-right font-semibold text-emerald-900">
                                                            R$ {{ number_format((float) $lot->total_value, 2, ',', '.') }}
                                                        </td>
                                                        <td class="px-3 py-1.5 text-gray-500 text-xs">{{ $lot->accounting_account ?? '—' }}</td>
                                                        <td class="px-3 py-1.5 text-gray-600 text-xs">
                                                            {{ $matchedStock?->siscofis_entry_date?->format('d/m/Y') ?? '—' }}
                                                        </td>
                                                        <td class="px-3 py-1.5 text-xs">
                                                            @if($matchedStock?->expiration_date)
                                                                <x-badge :color="$matchedStock->isExpired() ? 'red' : ($matchedStock->isExpiringSoon() ? 'amber' : 'gray')" size="sm">
                                                                    {{ $matchedStock->expiration_date->format('d/m/Y') }}
                                                                </x-badge>
                                                            @else
                                                                <span class="text-gray-400">Sem validade</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="bg-emerald-50">
                                                <tr>
                                                    <td colspan="2" class="px-3 py-1.5 text-xs font-semibold text-emerald-700 uppercase">Total</td>
                                                    <td class="px-3 py-1.5 text-center font-bold text-emerald-900">{{ $item->inventory_lots->sum('quantity') }}</td>
                                                    <td class="px-3 py-1.5"></td>
                                                    <td class="px-3 py-1.5 text-right font-bold text-emerald-900">
                                                        R$ {{ number_format((float) $item->inventory_lots->sum('total_value'), 2, ',', '.') }}
                                                    </td>
                                                    <td colspan="3" class="px-3 py-1.5"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </details>
                            @endif
                        </div>
                    @endif

                    {{-- Estoque Controlado --}}
                    @if(isset($item->total) && $item->total > 0)
                        <div class="bg-gray-50 rounded-lg p-3 mb-3">
                            <div class="text-xs text-gray-500 font-semibold uppercase mb-2">Estoque Controlado (itens individualizados)</div>
                            <div class="grid grid-cols-4 gap-3">
                                <div class="text-center">
                                    <div class="text-xl font-bold text-gray-900">{{ $item->total }}</div>
                                    <div class="text-xs text-gray-500">Total</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xl font-bold text-emerald-600">{{ $item->available }}</div>
                                    <div class="text-xs text-emerald-700">Disponíveis</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xl font-bold text-blue-600">{{ $item->loaned }}</div>
                                    <div class="text-xs text-blue-700">Emprestados</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-xl font-bold text-red-600">{{ $item->damaged }}</div>
                                    <div class="text-xs text-red-700">Danificados</div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-3">
                            <div class="flex items-center gap-2 text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="text-sm">Nenhum item individualizado no estoque controlado. Use o botão "Criar Item de Estoque" para adicionar.</span>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Alertas de validade --}}
                @if($item->expired > 0 || $item->expiring_soon > 0)
                    <div class="mt-4 space-y-2">
                        @if($item->expired > 0)
                            <div class="flex items-center gap-2 p-3 bg-rose-50 border border-rose-200 rounded-lg">
                                <svg class="w-5 h-5 text-rose-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-medium text-rose-900">
                                    <strong>{{ $item->expired }}</strong> {{ $item->expired === 1 ? 'item vencido' : 'itens vencidos' }} — Descarregar imediatamente
                                </span>
                            </div>
                        @endif
                        @if($item->expiring_soon > 0)
                            <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span class="text-sm font-medium text-amber-900">
                                    <strong>{{ $item->expiring_soon }}</strong> {{ $item->expiring_soon === 1 ? 'item vence' : 'itens vencem' }} nos próximos 30 dias
                                </span>
                            </div>
                        @endif
                    </div>
                @endif

            </x-card>
        @endforeach
    </div>
@endif

@endsection
