@extends('layouts.app')

@section('title', 'Editar Usuário — SUBRAVO')
@section('page-title', 'Editar: ' . $user->getDisplayName())

@section('content')

<form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- Acesso --}}
    <x-card title="Dados de Acesso" subtitle="Alteração de identidade e senha">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-input name="identity_number" label="Nº Identidade" required :value="$user->identity_number" />
            <x-input name="password" label="Nova Senha" type="password" hint="Deixe em branco para manter a atual" />
            <x-input name="password_confirmation" label="Confirmar Nova Senha" type="password" />
        </div>
    </x-card>

    {{-- Dados Pessoais --}}
    <x-card title="Dados Pessoais">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-input name="full_name" label="Nome Completo" required :value="$user->full_name" />
            <x-input name="war_name" label="Nome de Guerra" required :value="$user->war_name" />
            <x-input name="email" label="E-mail" type="email" :value="$user->email" />
            <x-select name="rank_id" label="Posto / Graduação" required
                      :options="$ranks->mapWithKeys(fn($r) => [$r->id => $r->abbreviation . ' — ' . $r->name])->toArray()"
                      :selected="old('rank_id', $user->rank_id)" placeholder="— Selecione —" />
            <x-select name="organization_id" label="Organização Militar"
                      :options="$organizations->mapWithKeys(fn($o) => [$o->id => $o->abbreviation . ' — ' . $o->name])->toArray()"
                      :selected="old('organization_id', $user->organization_id)" placeholder="— Selecione —" />
            <x-input name="subunit" label="Subunidade" :value="$user->subunit" />
            <x-select name="armed_force" label="Força"
                      :options="['EB' => 'Exército Brasileiro', 'MB' => 'Marinha do Brasil', 'FAB' => 'Força Aérea Brasileira']"
                      :selected="old('armed_force', $user->armed_force)" placeholder="— Selecione —" />
            <x-select name="gender" label="Gênero"
                      :options="['M' => 'Masculino', 'F' => 'Feminino']"
                      :selected="old('gender', $user->gender)" placeholder="— Selecione —" />
        </div>
    </x-card>

    {{-- Perfil e Status --}}
    <x-card title="Perfil e Permissões">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <x-select name="role" label="Perfil de Acesso" required :options="$roles"
                      :selected="old('role', $user->role)" placeholder="— Selecione —" />

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $user->is_active))
                           class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                    <span class="text-sm text-gray-700">Usuário ativo (pode acessar o sistema)</span>
                </label>
                @if($user->id === auth()->id())
                    <p class="mt-1 text-xs text-amber-600">Você não pode desativar sua própria conta.</p>
                @endif
            </div>
        </div>
    </x-card>

    {{-- Info --}}
    <x-card title="Informações do Registro">
        <dl class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
                <dt class="text-gray-500">Cadastrado em</dt>
                <dd class="text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Último acesso</dt>
                <dd class="text-gray-900">{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Nunca' }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">Cautelas ativas</dt>
                <dd class="text-gray-900">{{ $user->borrowedLoans()->where('status', 'active')->count() }}</dd>
            </div>
            <div>
                <dt class="text-gray-500">ID</dt>
                <dd class="text-gray-900 font-mono text-xs">{{ $user->id }}</dd>
            </div>
        </dl>
    </x-card>

    {{-- Ações --}}
    <div class="flex items-center justify-end space-x-3">
        <x-btn variant="secondary" href="{{ route('admin.users.index') }}">Cancelar</x-btn>
        <x-btn variant="primary" type="submit" icon="M5 13l4 4L19 7">
            Salvar Alterações
        </x-btn>
    </div>
</form>

@endsection
