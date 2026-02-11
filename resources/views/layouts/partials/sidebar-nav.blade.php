{{-- Sidebar navigation items --}}
@php
    $currentRoute = request()->route()?->getName() ?? '';
    $user = Auth::user();
@endphp

{{-- Dashboard --}}
<a href="{{ route('dashboard') }}"
   class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition
          {{ $currentRoute === 'dashboard'
              ? 'bg-subravo-700 text-white'
              : 'text-subravo-200 hover:bg-subravo-800 hover:text-white' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
    </svg>
    <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen" x-transition.opacity>Dashboard</span>
</a>

{{-- Seção: Estoque --}}
<div class="mt-6 mb-2 px-3" x-show="sidebarOpen" x-transition.opacity>
    <p class="text-xs font-semibold text-subravo-500 uppercase tracking-wider">Estoque</p>
</div>

{{-- Produtos --}}
<a href="{{ route('products.index') }}"
   class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition
          {{ str_starts_with($currentRoute, 'products.')
              ? 'bg-subravo-700 text-white'
              : 'text-subravo-200 hover:bg-subravo-800 hover:text-white' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
    </svg>
    <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen" x-transition.opacity>Produtos</span>
</a>

{{-- Itens do Estoque --}}
<a href="{{ route('stock.index') }}"
   class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition
          {{ str_starts_with($currentRoute, 'stock.') && $currentRoute !== 'stock.movements'
              ? 'bg-subravo-700 text-white'
              : 'text-subravo-200 hover:bg-subravo-800 hover:text-white' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
    </svg>
    <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen" x-transition.opacity>Estoque</span>
</a>

{{-- Categorias --}}
<a href="{{ route('categories.index') }}"
   class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition
          {{ str_starts_with($currentRoute, 'categories.')
              ? 'bg-subravo-700 text-white'
              : 'text-subravo-200 hover:bg-subravo-800 hover:text-white' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
    </svg>
    <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen" x-transition.opacity>Categorias</span>
</a>

{{-- Seção: Empréstimos --}}
<div class="mt-6 mb-2 px-3" x-show="sidebarOpen" x-transition.opacity>
    <p class="text-xs font-semibold text-subravo-500 uppercase tracking-wider">Empréstimos</p>
</div>

{{-- Cautelas --}}
<a href="{{ route('loans.index') }}"
   class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition
          {{ str_starts_with($currentRoute, 'loans.')
              ? 'bg-subravo-700 text-white'
              : 'text-subravo-200 hover:bg-subravo-800 hover:text-white' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
    </svg>
    <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen" x-transition.opacity>Cautelas</span>
</a>

{{-- Movimentações --}}
<a href="{{ route('stock.movements') }}"
   class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition
          {{ $currentRoute === 'stock.movements'
              ? 'bg-subravo-700 text-white'
              : 'text-subravo-200 hover:bg-subravo-800 hover:text-white' }}">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
    </svg>
    <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen" x-transition.opacity>Movimentações</span>
</a>

{{-- Seção: Administração (apenas admin) --}}
@if($user->isAdmin())
    <div class="mt-6 mb-2 px-3" x-show="sidebarOpen" x-transition.opacity>
        <p class="text-xs font-semibold text-subravo-500 uppercase tracking-wider">Administração</p>
    </div>

    {{-- Usuários --}}
    <a href="{{ route('admin.users.index') }}"
       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition
              {{ str_starts_with($currentRoute, 'admin.users')
                  ? 'bg-subravo-700 text-white'
                  : 'text-subravo-200 hover:bg-subravo-800 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
        </svg>
        <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen" x-transition.opacity>Usuários</span>
    </a>

    {{-- Relatórios --}}
    <a href="{{ route('admin.reports.index') }}"
       class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition
              {{ str_starts_with($currentRoute, 'admin.reports')
                  ? 'bg-subravo-700 text-white'
                  : 'text-subravo-200 hover:bg-subravo-800 hover:text-white' }}">
        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <span class="ml-3 whitespace-nowrap" x-show="sidebarOpen" x-transition.opacity>Relatórios</span>
    </a>
@endif
