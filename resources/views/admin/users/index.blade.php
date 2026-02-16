@extends('layouts.app')

@section('title', 'Usuários — SUBRAVO')
@section('page-title', 'Gerenciar Usuários')

@section('header-actions')
    <x-btn variant="primary" href="{{ route('admin.users.create') }}" icon="M12 6v6m0 0v6m0-6h6m-6 0H6" size="sm">
        Novo Usuário
    </x-btn>
@endsection

@section('content')

<div class="space-y-4">
    {{-- Filtros --}}
    <x-card>
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nome, identidade ou email..."
                       style="transition: all 0.3s ease; background: rgba(255, 255, 255, 0.9);"
                       class="w-full px-4 py-2.5 rounded-lg border-2 border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 focus:outline-none text-sm text-gray-900 placeholder-gray-400">
            </div>
            <x-select name="role" label="" placeholder="Todos os Perfis"
                      :options="['admin' => 'Administrador', 'almoxarife' => 'Almoxarife', 'solicitante' => 'Solicitante', 'auditor' => 'Auditor']"
                      :selected="request('role')" />
            <x-select name="is_active" label="" placeholder="Todos os Status"
                      :options="['1' => 'Ativos', '0' => 'Inativos']"
                      :selected="request('is_active')" />
            <div class="flex items-end space-x-2">
                <x-btn variant="primary" type="submit" size="sm">Filtrar</x-btn>
                @if(request()->hasAny(['search', 'role', 'is_active']))
                    <x-btn variant="secondary" href="{{ route('admin.users.index') }}" size="sm">Limpar</x-btn>
                @endif
            </div>
        </form>
    </x-card>

    {{-- Tabela --}}
    <x-card>
        @if($users->isEmpty())
            <x-empty-state title="Nenhum usuário encontrado" message="Ajuste os filtros ou cadastre um novo usuário."
                           action="{{ route('admin.users.create') }}" actionLabel="Novo Usuário" />
        @else
            <x-table>
                <x-slot:header>
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Identidade</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nome / Nome de Guerra</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Posto/Grad.</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">OM</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Perfil</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Ações</th>
                    </tr>
                </x-slot:header>

                @foreach($users as $user)
                    <tr class="border-t border-gray-100 hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-mono text-gray-700">{{ $user->identity_number }}</td>
                        <td class="px-4 py-3">
                            <div class="text-sm font-medium text-gray-900">{{ $user->war_name }}</div>
                            <div class="text-xs text-gray-500">{{ $user->full_name }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $user->rank?->abbreviation ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $user->organization?->abbreviation ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $roleColors = ['admin' => 'purple', 'almoxarife' => 'blue', 'solicitante' => 'gray', 'auditor' => 'amber'];
                                $roleLabels = ['admin' => 'Admin', 'almoxarife' => 'Almoxarife', 'solicitante' => 'Solicitante', 'auditor' => 'Auditor'];
                            @endphp
                            <x-badge :color="$roleColors[$user->role] ?? 'gray'">
                                {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <x-badge :color="$user->is_active ? 'green' : 'red'">
                                {{ $user->is_active ? 'Ativo' : 'Inativo' }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-1.5 text-gray-400 hover:text-emerald-600 rounded" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @if($user->id !== auth()->user()->id)
                                    <form method="POST" action="{{ route('admin.users.toggle', $user) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="p-1.5 rounded {{ $user->is_active ? 'text-gray-400 hover:text-red-600' : 'text-gray-400 hover:text-green-600' }}"
                                                title="{{ $user->is_active ? 'Desativar' : 'Ativar' }}"
                                                onclick="return confirm('{{ $user->is_active ? 'Desativar este usuário?' : 'Reativar este usuário?' }}')">
                                            @if($user->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            @endif
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-table>

            <div class="mt-4">
                {{ $users->links() }}
            </div>
        @endif
    </x-card>
</div>

@endsection
