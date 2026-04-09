<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — Gestão de Equipe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-neutral-bg text-neutral-text" x-data="{ sidebarOpen: false }">

{{-- ═══════════════════════════════════════════
     SIDEBAR
═══════════════════════════════════════════ --}}
<aside class="sidebar" :class="{ '-translate-x-full': !sidebarOpen }" x-cloak>

    {{-- Logo --}}
    <div class="sidebar-logo">
        <div class="w-8 h-8 bg-santander-red rounded-lg flex items-center justify-center flex-shrink-0">
            <svg viewBox="0 0 24 24" fill="white" class="w-5 h-5">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
            </svg>
        </div>
        <div>
            <p class="text-white font-bold text-sm leading-none">Gestão de Equipe</p>
            @if(app()->bound('tenant'))
                <p class="text-white/40 text-xs mt-0.5">{{ app('tenant')->name }}</p>
            @endif
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-3" x-data="{ active: '{{ request()->routeIs('admin.*') ? 'admin' : (request()->routeIs('vacation.*') ? 'vacations' : (request()->routeIs('employee.*') ? 'employees' : '')) }}' }">

        @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isManager())
            <p class="sidebar-section-title">Principal</p>

            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <p class="sidebar-section-title">RH</p>

            <a href="{{ route('admin.employees.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.employees.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Colaboradores
            </a>

            <a href="{{ route('admin.vacations.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.vacations.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Férias
                @php $pending = \App\Models\VacationRequest::where('tenant_id', auth()->user()->tenant_id)->where('status','pending')->count(); @endphp
                @if($pending > 0)
                    <span class="ml-auto bg-santander-red text-white text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $pending }}</span>
                @endif
            </a>

            <a href="{{ route('admin.departments.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Departamentos
            </a>

            <p class="sidebar-section-title">Recrutamento</p>

            <a href="{{ route('admin.job-openings.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('admin.job-openings.*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Vagas (ATS)
            </a>

        @endif

        {{-- Employee Portal --}}
        @if(auth()->user()->isEmployee() || auth()->user()->isManager())
            <p class="sidebar-section-title">Meu Portal</p>

            <a href="{{ route('employee.dashboard') }}"
               class="sidebar-nav-item {{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Meu Painel
            </a>

            <a href="{{ route('employee.vacation.create') }}"
               class="sidebar-nav-item {{ request()->routeIs('employee.vacation.create') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4v16m8-8H4"/>
                </svg>
                Solicitar Férias
            </a>

            <a href="{{ route('employee.vacation.index') }}"
               class="sidebar-nav-item {{ request()->routeIs('employee.vacation.index') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                Minhas Férias
            </a>
        @endif

    </nav>

    {{-- User footer --}}
    <div class="border-t border-white/10 px-4 py-4">
        <div class="flex items-center gap-3">
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                 class="w-8 h-8 rounded-full object-cover flex-shrink-0">
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                <p class="text-white/40 text-xs truncate">{{ auth()->user()->getRoleNames()->first() }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Sair"
                        class="text-white/40 hover:text-white transition p-1 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- ═══════════════════════════════════════════
     MAIN CONTENT
═══════════════════════════════════════════ --}}
<div class="pl-64">

    {{-- Top bar --}}
    <header class="sticky top-0 z-30 bg-white border-b border-neutral-border px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-4">
            {{-- Mobile hamburger --}}
            <button @click="sidebarOpen = !sidebarOpen"
                    class="lg:hidden p-1 rounded text-neutral-muted hover:text-neutral-text">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </svg>
            </button>
            <h1 class="text-lg font-semibold text-neutral-text">{{ $title ?? 'Dashboard' }}</h1>
        </div>

        <div class="flex items-center gap-3">
            {{-- Breadcrumb date --}}
            <span class="text-xs text-neutral-muted hidden sm:block">{{ now()->format('d \d\e F \d\e Y') }}</span>

            {{-- Avatar --}}
            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}"
                 class="w-8 h-8 rounded-full object-cover">
        </div>
    </header>

    {{-- Page content --}}
    <main class="p-6">
        {{-- Flash messages --}}
        @if(session('success'))
            <div class="alert-success mb-4">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert-danger mb-4">
                <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}
    </main>
</div>

{{-- ═══════════════════════════════════════════
     TOAST NOTIFICATIONS (Livewire events)
═══════════════════════════════════════════ --}}
<div
    x-data="{
        toasts: [],
        add(type, message) {
            const id = Date.now();
            this.toasts.push({ id, type, message });
            setTimeout(() => this.remove(id), 4000);
        },
        remove(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
    }"
    @toast.window="add($event.detail.type, $event.detail.message)"
    class="fixed bottom-5 right-5 z-50 space-y-2 w-80"
    x-cloak>

    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="flex items-start gap-3 p-4 rounded-xl shadow-card-lg text-sm font-medium"
            :class="{
                'bg-green-600 text-white': toast.type === 'success',
                'bg-red-600 text-white':   toast.type === 'error',
                'bg-blue-600 text-white':  toast.type === 'info',
            }">
            <span x-text="toast.message" class="flex-1"></span>
            <button @click="remove(toast.id)" class="opacity-70 hover:opacity-100">✕</button>
        </div>
    </template>
</div>

@livewireScripts
<script>
(function(){
    var uri = '{{ url("livewire/update") }}';
    var tag = document.querySelector('script[data-update-uri]');
    if (tag) tag.setAttribute('data-update-uri', uri);
    if (window.livewireScriptConfig) window.livewireScriptConfig.uri = uri;
    document.addEventListener('livewire:init', function(){
        if (window.Livewire && window.Livewire.config) window.Livewire.config.uri = uri;
    });
})();
</script>
</body>
</html>
