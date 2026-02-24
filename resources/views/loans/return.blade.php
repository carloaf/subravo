@extends('layouts.app')

@section('title', 'Devolução — Cautela #' . $loan->loan_number . ' — SMARTSUB')
@section('page-title', 'Devolução — #' . $loan->loan_number)

@section('content')

<div class="space-y-6">
    {{-- Resumo da cautela --}}
    <x-card title="Resumo da Cautela">
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Cautela</dt>
                <dd class="font-semibold text-gray-900">{{ $loan->loan_number }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Mutuário</dt>
                <dd class="text-gray-900">
                    @if($loan->borrower_type === 'individual')
                        {{ $loan->borrower?->getDisplayName() ?? '—' }}
                    @else
                        {{ $loan->borrower_section }}
                    @endif
                </dd>
            </div>
            <div>
                <dt class="text-gray-500">Data Empréstimo</dt>
                <dd class="text-gray-900">{{ $loan->loan_date->format('d/m/Y') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Status</dt>
                <dd>
                    @php
                        $statusColors = ['active' => 'amber', 'partial' => 'blue', 'overdue' => 'red'];
                        $statusLabels = ['active' => 'Ativa', 'partial' => 'Parcial', 'overdue' => 'Atrasada'];
                    @endphp
                    <x-badge :color="$statusColors[$loan->status] ?? 'gray'">
                        {{ $statusLabels[$loan->status] ?? ucfirst($loan->status) }}
                    </x-badge>
                </dd>
            </div>
        </dl>
    </x-card>

    {{-- Form de Devolução --}}
    <form method="POST" action="{{ route('loans.processReturn', $loan) }}" class="space-y-6">
        @csrf

        <x-card title="Itens Pendentes de Devolução" subtitle="Informe a quantidade devolvida e a condição de cada item">
            @if($pendingItems->isEmpty())
                <x-empty-state title="Sem itens pendentes" message="Todos os itens desta cautela já foram devolvidos." />
            @else
                <div class="space-y-4">
                    @foreach($pendingItems as $index => $item)
                        @php
                            $remaining = $item->quantity - $item->returned_quantity;
                        @endphp
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <input type="hidden" name="returns[{{ $index }}][loan_item_id]" value="{{ $item->id }}">

                            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                                {{-- Nome do produto --}}
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">
                                        {{ $item->stockItem?->product?->name ?? 'Produto removido' }}
                                    </p>
                                    <p class="text-xs text-gray-500 mt-0.5">
                                        @if($item->stockItem?->batch) Lote: {{ $item->stockItem->batch }} @endif
                                        @if($item->stockItem?->serial_number) — Nº {{ $item->stockItem->serial_number }} @endif
                                        &middot; Cautelado: {{ $item->quantity }}
                                        &middot; Já devolvido: {{ $item->returned_quantity }}
                                        &middot; <strong>Pendente: {{ $remaining }}</strong>
                                    </p>
                                </div>

                                {{-- Quantidade devolução --}}
                                <div class="w-28">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Devolver</label>
                                    <input type="number" name="returns[{{ $index }}][quantity]"
                                           value="{{ old("returns.{$index}.quantity", $remaining) }}"
                                           min="0" max="{{ $remaining }}" required
                                           class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    @error("returns.{$index}.quantity")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Condição retorno --}}
                                <div class="w-36">
                                    <label class="block text-xs font-medium text-gray-500 mb-1">Condição</label>
                                    <select name="returns[{{ $index }}][condition_in]" required
                                            class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                        @foreach(\App\Models\LoanItem::CONDITIONS as $val => $label)
                                            <option value="{{ $val }}" @selected(old("items.{$index}.condition_in", $item->condition_out) === $val)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("returns.{$index}.condition_in")
                                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        {{-- Observações da devolução --}}
        <x-card title="Observações da Devolução">
            <textarea name="notes" rows="3" placeholder="Observações sobre a devolução (opcional)"
                      class="w-full rounded-lg border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">{{ old('return_notes') }}</textarea>
        </x-card>

        {{-- Ações --}}
        @if($pendingItems->isNotEmpty())
            <div class="flex items-center justify-end space-x-3">
                <x-btn variant="secondary" href="{{ route('loans.show', $loan) }}">Cancelar</x-btn>
                <x-btn variant="success" type="submit" icon="M5 13l4 4L19 7">
                    Confirmar Devolução
                </x-btn>
            </div>
        @else
            <x-btn variant="secondary" href="{{ route('loans.show', $loan) }}">Voltar</x-btn>
        @endif
    </form>
</div>

@endsection
