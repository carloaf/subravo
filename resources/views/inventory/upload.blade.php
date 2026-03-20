@extends('layouts.app')

@section('title', 'Carregar Inventario - HelpSub')
@section('page-title', 'Carregar Inventário PDF')

@section('content')

<div class="max-w-2xl mx-auto">
    <x-card>
        <div class="p-6">
            {{-- Informações --}}
            <div class="mb-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-blue-800">Formato Aceito</h4>
                        <p class="text-sm text-blue-700 mt-1">
                            PDF de <strong>Relação de Material Carga da Dependência</strong> emitido pelo SISCOFIS OM.
                            O sistema extrairá automaticamente: nome do material, Nr Ficha, Cód Mat, Conta Contábil,
                            quantidade, valores e números patrimoniais.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Formulário de Upload --}}
            <form method="POST" action="{{ route('inventory.store') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-6" x-data="{ fileName: '', dragging: false }">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Arquivo PDF *</label>

                    <div class="relative"
                         @dragover.prevent="dragging = true"
                         @dragleave.prevent="dragging = false"
                         @drop.prevent="dragging = false; $refs.fileInput.files = $event.dataTransfer.files; fileName = $refs.fileInput.files[0]?.name || ''">

                        <div :class="dragging ? 'border-emerald-500 bg-emerald-50' : 'border-gray-300 bg-gray-50'"
                             class="border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/50"
                             @click="$refs.fileInput.click()">

                            <svg class="w-12 h-12 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                            </svg>

                            <p class="text-sm text-gray-600" x-show="!fileName">
                                <span class="font-semibold text-emerald-600">Clique para selecionar</span> ou arraste o PDF aqui
                            </p>
                            <p class="text-sm font-medium text-emerald-700" x-show="fileName" x-text="fileName"></p>
                            <p class="text-xs text-gray-400 mt-1">PDF até 20MB</p>
                        </div>

                        <input type="file" name="pdf_file" accept=".pdf,application/pdf"
                               x-ref="fileInput"
                               @change="fileName = $event.target.files[0]?.name || ''"
                               class="hidden" required>
                    </div>

                    @error('pdf_file')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <x-btn variant="secondary" href="{{ route('inventory.index') }}">Cancelar</x-btn>
                    <x-btn type="submit" variant="primary"
                           icon="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12">
                        Processar PDF
                    </x-btn>
                </div>
            </form>
        </div>
    </x-card>
</div>

@endsection
