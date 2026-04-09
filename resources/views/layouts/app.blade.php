<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — Gestão de Equipe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-wrap { transition: width 220ms cubic-bezier(.4,0,.2,1); }
        .main-content { transition: padding-left 220ms cubic-bezier(.4,0,.2,1); }
        .nav-item-active::before {
            content: ''; position: absolute; left: 0; top: 50%;
            transform: translateY(-50%); width: 3px; height: 60%;
            background: #EC0000; border-radius: 0 3px 3px 0;
        }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,.15); border-radius: 99px; }
        .nav-tooltip { pointer-events: none; opacity: 0; transition: opacity 120ms ease; }
        .group:hover .nav-tooltip { opacity: 1; }
    </style>
</head>
<body class="h-full bg-neutral-bg text-neutral-text">

<div x-data="{ open: false, mobile: false }" class="flex h-full">

    {{-- MOBILE OVERLAY --}}
    <div x-show="mobile" x-cloak @click="mobile=false"
         class="fixed inset-0 z-40 bg-black/50 lg:hidden"></div>

    {{-- SIDEBAR --}}
    <aside wire:ignore
           class="fixed inset-y-0 left-0 z-50 flex flex-col sidebar-wrap overflow-hidden
                  -translate-x-full lg:translate-x-0"
           :class="{ 'w-64': !open, 'w-[72px]': open, '!translate-x-0': mobile }"
           style="background:linear-gradient(180deg,#1a1a1c 0%,#111113 100%); width:16rem;">

        <div class="absolute top-0 left-0 right-0 h-0.5 bg-red-600 opacity-80"></div>

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-4 py-5 flex-shrink-0 border-b border-white/10">
            <div class="w-9 h-9 rounded-xl flex-shrink-0 flex items-center justify-center shadow-lg"
                 style="background:linear-gradient(135deg,#EC0000,#B30000);">
                <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div x-show="!open" x-cloak class="min-w-0">
                <p class="text-white font-bold text-sm truncate">Gestão de Equipe</p>
                <p class="text-white/40 text-[11px]">Plataforma Corporativa</p>
            </div>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto sidebar-scroll py-3 px-2">

            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin() || auth()->user()->isManager())

                <div x-show="!open" x-cloak class="px-3 py-2">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-white/25">Principal</p>
                </div>

                @php
                $nav1 = [
                    ['route'=>'admin.dashboard','match'=>'admin.dashboard','label'=>'Dashboard',
                     'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ];
                $pending = \App\Models\VacationRequest::when(auth()->user()->tenant_id, fn($q)=>$q->where('tenant_id',auth()->user()->tenant_id))->where('status','pending')->count();
                $nav2 = [
                    ['route'=>'admin.employees.index','match'=>'admin.employees.*','label'=>'Colaboradores',
                     'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                    ['route'=>'admin.vacations.index','match'=>'admin.vacations.*','label'=>'Férias','badge'=>$pending,
                     'icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['route'=>'admin.departments.index','match'=>'admin.departments.*','label'=>'Departamentos',
                     'icon'=>'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ];
                $nav3 = [
                    ['route'=>'admin.job-openings.index','match'=>'admin.job-openings.*','label'=>'Vagas (ATS)',
                     'icon'=>'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ];
                @endphp

                @foreach($nav1 as $item)@include('layouts.partials.nav-item',$item)@endforeach

                <div x-show="!open" x-cloak class="px-3 pt-4 pb-1">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-white/25">Recursos Humanos</p>
                </div>
                @foreach($nav2 as $item)@include('layouts.partials.nav-item',$item)@endforeach

                <div x-show="!open" x-cloak class="px-3 pt-4 pb-1">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-white/25">Recrutamento</p>
                </div>
                @foreach($nav3 as $item)@include('layouts.partials.nav-item',$item)@endforeach

            @endif

            @if(auth()->user()->isEmployee() || auth()->user()->isManager())
                <div x-show="!open" x-cloak class="px-3 pt-4 pb-1">
                    <p class="text-[10px] font-semibold uppercase tracking-widest text-white/25">Meu Portal</p>
                </div>
                @php
                $navP = [
                    ['route'=>'employee.dashboard','match'=>'employee.dashboard','label'=>'Meu Painel',
                     'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['route'=>'employee.vacation.index','match'=>'employee.vacation.index','label'=>'Minhas Férias',
                     'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['route'=>'employee.vacation.create','match'=>'employee.vacation.create','label'=>'Solicitar Férias',
                     'icon'=>'M12 4v16m8-8H4'],
                ];
                @endphp
                @foreach($navP as $item)@include('layouts.partials.nav-item',$item)@endforeach
            @endif

        </nav>

        {{-- Collapse toggle --}}
        <div class="hidden lg:flex justify-end px-3 py-2 border-t border-white/10 flex-shrink-0">
            <button type="button" @click="open = !open"
                    class="w-8 h-8 rounded-lg flex items-center justify-center text-white/40 hover:text-white hover:bg-white/10 transition-all">
                <svg class="w-4 h-4 transition-transform" :class="{'rotate-180':open}"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </div>

        {{-- User footer --}}
        <div class="border-t border-white/10 px-3 py-3 flex-shrink-0">
            @php $initials=collect(explode(' ',auth()->user()->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center text-white text-xs font-bold"
                     style="background:linear-gradient(135deg,#EC0000,#7B0000);">{{ $initials }}</div>
                <div x-show="!open" x-cloak class="flex-1 min-w-0">
                    <p class="text-white text-xs font-semibold truncate">{{ auth()->user()->name }}</p>
                    <p class="text-white/40 text-[10px]">{{ str_replace('_',' ',auth()->user()->getRoleNames()->first()??'') }}</p>
                </div>
                <div x-show="!open" x-cloak class="flex-shrink-0">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-7 h-7 flex items-center justify-center rounded-lg text-white/30 hover:text-red-400 hover:bg-white/10 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </aside>

    {{-- MAIN --}}
    <div class="flex flex-col flex-1 min-h-screen main-content"
         :class="open ? 'lg:pl-[72px]' : 'lg:pl-64'">

        {{-- Top bar --}}
        <header class="sticky top-0 z-30 flex-shrink-0 bg-white/90 backdrop-blur border-b border-black/[0.07]">
            <div class="flex items-center justify-between px-5 py-3">
                <div class="flex items-center gap-3">
                    <button type="button" @click="mobile=!mobile" class="lg:hidden w-8 h-8 flex items-center justify-center rounded-lg text-neutral-muted hover:bg-neutral-bg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <div>
                        <h1 class="text-sm font-semibold text-neutral-text">{{ $title ?? 'Dashboard' }}</h1>
                        <p class="text-[11px] text-neutral-muted hidden sm:block">{{ now()->isoFormat('dddd, D [de] MMMM [de] YYYY') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @php $unread=auth()->user()->unreadNotifications()->count(); @endphp
                    <button class="w-8 h-8 flex items-center justify-center rounded-lg text-neutral-muted hover:bg-neutral-bg relative">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        @if($unread>0)<span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-600 ring-2 ring-white"></span>@endif
                    </button>
                    <div class="w-px h-5 bg-gray-200"></div>
                    <div class="relative" x-data="{ dd: false }" @click.outside="dd=false">
                        <button type="button" @click="dd=!dd" class="flex items-center gap-2 pl-1 pr-2.5 py-1 rounded-lg hover:bg-neutral-bg">
                            <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-[10px] font-bold"
                                 style="background:linear-gradient(135deg,#EC0000,#7B0000);">{{ $initials }}</div>
                            <div class="hidden sm:block text-left">
                                <p class="text-xs font-semibold text-neutral-text">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-neutral-muted">{{ str_replace('_',' ',ucfirst(auth()->user()->getRoleNames()->first()??'')) }}</p>
                            </div>
                            <svg class="w-3 h-3 text-neutral-muted transition-transform" :class="{'rotate-180':dd}"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="dd" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-1.5 w-48 bg-white rounded-xl shadow-lg ring-1 ring-black/5 overflow-hidden z-50">
                            <div class="px-3 py-2.5 border-b border-gray-100">
                                <p class="text-xs font-semibold truncate">{{ auth()->user()->name }}</p>
                                <p class="text-[11px] text-neutral-muted truncate">{{ auth()->user()->email }}</p>
                            </div>
                            <div class="border-t border-gray-100 py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Sair da conta
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 p-5 lg:p-6">
            @if(session('success'))
                <div class="flex items-center gap-2 mb-5 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-2 mb-5 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    {{ session('error') }}
                </div>
            @endif
            {{ $slot }}
        </main>

        <footer class="px-6 py-3 border-t border-gray-200 flex items-center justify-between flex-shrink-0">
            <span class="text-[11px] text-neutral-muted">© {{ date('Y') }} Santander — Uso interno corporativo</span>
            <span class="text-[11px] text-neutral-muted">v1.0.0 MVP</span>
        </footer>

    </div>{{-- /main --}}

</div>{{-- /app-shell --}}

@livewireScripts
<script>
(function(){
    var full = '{{ url("livewire/update") }}';
    var tag  = document.querySelector('script[data-update-uri]');
    if (tag) tag.setAttribute('data-update-uri', full);
    if (window.livewireScriptConfig) window.livewireScriptConfig.uri = full;
    document.addEventListener('livewire:init', function(){
        if (window.Livewire && window.Livewire.config) window.Livewire.config.uri = full;
    });
})();
</script>
</body>
</html>
