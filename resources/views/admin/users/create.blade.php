@extends('layouts.app')

@section('title', 'Novo Usuário — SMARTSUB')
@section('page-title', 'Cadastrar Usuário')

@section('content')

<form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
    @csrf

    {{-- Acesso --}}
    <x-card title="Dados de Acesso" subtitle="Credenciais de login do sistema">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-input name="identity_number" label="Nº Identidade" required placeholder="Ex: 012345678-9" />
            <x-input name="password" label="Senha" type="password" required hint="Mínimo 8 caracteres" />
            <x-input name="password_confirmation" label="Confirmar Senha" type="password" required />
        </div>
    </x-card>

    {{-- Dados Pessoais --}}
    <x-card title="Dados Pessoais">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-input name="full_name" label="Nome Completo" required placeholder="Nome completo do militar" />
            <x-input name="war_name" label="Nome de Guerra" required placeholder="Nome de guerra" />
            <x-input name="email" label="E-mail" type="email" placeholder="email@exemplo.mil.br" />
            <x-select name="rank_id" label="Posto / Graduação" required
                      :options="$ranks->mapWithKeys(fn($r) => [$r->id => $r->abbreviation . ' — ' . $r->name])->toArray()"
                      placeholder="— Selecione —" />
            <x-select name="organization_id" label="Organização Militar"
                      :options="$organizations->mapWithKeys(fn($o) => [$o->id => $o->abbreviation . ' — ' . $o->name])->toArray()"
                      placeholder="— Selecione —" />
            <x-input name="subunit" label="Subunidade" placeholder="Ex: 1ª Cia, SApInt" />
            <x-select name="armed_force" label="Força"
                      :options="['EB' => 'Exército Brasileiro', 'MB' => 'Marinha do Brasil', 'FAB' => 'Força Aérea Brasileira']"
                      placeholder="— Selecione —" />
            <x-select name="gender" label="Gênero"
                      :options="['M' => 'Masculino', 'F' => 'Feminino']"
                      placeholder="— Selecione —" />
        </div>
    </x-card>

    {{-- Perfil e Status --}}
    <x-card title="Perfil e Permissões">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-select name="role" label="Perfil de Acesso" required :options="$roles" placeholder="— Selecione —" />

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" checked
                           class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Usuário ativo (pode acessar o sistema)</span>
                </label>
            </div>
        </div>
    </x-card>

    {{-- Ações --}}
    <div class="flex items-center justify-end space-x-3">
        <x-btn variant="secondary" href="{{ route('admin.users.index') }}">Cancelar</x-btn>
        <x-btn variant="primary" type="submit" icon="M12 6v6m0 0v6m0-6h6m-6 0H6">
            Cadastrar Usuário
        </x-btn>
    </div>
</form>

@endsection
