<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }} — Gestão de Equipe</title>
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#EC0000">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="{{ asset('images/icon-192.png') }}">
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
                    ['route'=>'admin.vacation-calendar.index','match'=>'admin.vacation-calendar.*','label'=>'Calendário Férias',
                     'icon'=>'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
                    ['route'=>'admin.absences.index','match'=>'admin.absences.*','label'=>'Afastamentos',
                     'icon'=>'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
                    ['route'=>'admin.departments.index','match'=>'admin.departments.*','label'=>'Departamentos',
                     'icon'=>'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                    ['route'=>'admin.orgchart.index','match'=>'admin.orgchart.*','label'=>'Organograma',
                     'icon'=>'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['route'=>'admin.time-clock.index','match'=>'admin.time-clock.*','label'=>'Ponto Eletrônico',
                     'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route'=>'admin.documents.index','match'=>'admin.documents.*','label'=>'Documentos',
                     'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['route'=>'admin.reports.index','match'=>'admin.reports.*','label'=>'Relatórios',
                     'icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ];
                $nav3 = [
                    ['route'=>'admin.job-openings.index','match'=>'admin.job-openings.*','label'=>'Vagas (ATS)',
                     'icon'=>'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                    ['route'=>'admin.announcements.index','match'=>'admin.announcements.*','label'=>'Comunicados',
                     'icon'=>'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
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
                    ['route'=>'employee.time-clock.index','match'=>'employee.time-clock.*','label'=>'Meu Ponto',
                     'icon'=>'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['route'=>'employee.documents.index','match'=>'employee.documents.*','label'=>'Meus Documentos',
                     'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['route'=>'employee.announcements.index','match'=>'employee.announcements.*','label'=>'Comunicados',
                     'icon'=>'M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z'],
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
                    {{-- Global Search --}}
                    @can('viewAny', \App\Models\Employee::class)
                    <livewire:global-search />
                    @endcan
                    @php $unread=auth()->user()->unreadNotifications()->count(); $notifications=auth()->user()->notifications()->latest()->take(10)->get(); @endphp
                    <div class="relative" x-data="{ notif: false }" @click.outside="notif=false">
                        <button @click="notif=!notif" class="w-8 h-8 flex items-center justify-center rounded-lg text-neutral-muted hover:bg-neutral-bg relative">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if($unread>0)<span class="absolute top-1 right-1 w-2 h-2 rounded-full bg-red-600 ring-2 ring-white"></span>@endif
                        </button>
                        <div x-show="notif" x-cloak
                             x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-75"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             class="absolute right-0 top-full mt-1.5 w-80 bg-white rounded-xl shadow-lg ring-1 ring-black/5 overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                                <p class="text-xs font-semibold">Notificações</p>
                                @if($unread>0)
                                    <span class="text-[10px] bg-red-100 text-red-700 px-1.5 py-0.5 rounded-full font-semibold">{{ $unread }} nova{{ $unread>1?'s':'' }}</span>
                                @endif
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-gray-50">
                                @forelse($notifications as $notif_item)
                                    <div class="px-4 py-3 {{ is_null($notif_item->read_at) ? 'bg-blue-50/50' : '' }} hover:bg-gray-50">
                                        <p class="text-xs font-medium text-neutral-text">{{ $notif_item->data['title'] ?? class_basename($notif_item->type) }}</p>
                                        <p class="text-[11px] text-neutral-muted mt-0.5">{{ $notif_item->data['message'] ?? ($notif_item->data['body'] ?? '') }}</p>
                                        <p class="text-[10px] text-neutral-muted/60 mt-1">{{ $notif_item->created_at->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <div class="px-4 py-8 text-center">
                                        <svg class="w-8 h-8 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                        <p class="text-xs text-neutral-muted">Nenhuma notificação</p>
                                    </div>
                                @endforelse
                            </div>
                            @if($notifications->count() > 0 && $unread > 0)
                                <div class="px-4 py-2 border-t border-gray-100 text-center">
                                    <form method="POST" action="{{ route('notifications.read-all') }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-santander-red hover:text-santander-red-dark font-medium">Marcar todas como lidas</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
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
                            <div class="py-1">
                                <a href="{{ route('profile.edit') }}" class="w-full flex items-center gap-2.5 px-3 py-2 text-xs text-neutral-text hover:bg-neutral-bg">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Meu Perfil
                                </a>
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
            <span class="text-[11px] text-neutral-muted">© {{ date('Y') }} {{ auth()->user()->tenant?->name ?? 'Gestão de Equipe' }} — Uso interno corporativo</span>
            <span class="text-[11px] text-neutral-muted">v1.0.0 MVP</span>
        </footer>

    </div>{{-- /main --}}

</div>{{-- /app-shell --}}

{{-- Toast Notification System --}}
<div x-data="toastSystem()" @toast.window="addToast($event.detail)" class="fixed top-4 right-4 z-[100] space-y-2 pointer-events-none" style="max-width:380px;">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.visible"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-x-8"
             x-transition:enter-end="opacity-100 translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-x-0"
             x-transition:leave-end="opacity-0 translate-x-8"
             class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-lg shadow-lg border text-sm"
             :class="{
                 'bg-green-50 border-green-200 text-green-800': toast.type === 'success',
                 'bg-red-50 border-red-200 text-red-800': toast.type === 'error',
                 'bg-amber-50 border-amber-200 text-amber-800': toast.type === 'warning',
                 'bg-blue-50 border-blue-200 text-blue-800': toast.type === 'info',
             }">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <template x-if="toast.type === 'success'">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </template>
                <template x-if="toast.type === 'error'">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </template>
                <template x-if="toast.type === 'warning'">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </template>
                <template x-if="toast.type === 'info'">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </template>
            </svg>
            <p class="flex-1" x-text="toast.message"></p>
            <button @click="removeToast(toast.id)" class="flex-shrink-0 opacity-50 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>
</div>
<script>
function toastSystem() {
    return {
        toasts: [],
        counter: 0,
        addToast(detail) {
            const id = ++this.counter;
            this.toasts.push({ id, type: detail.type || 'info', message: detail.message || '', visible: true });
            setTimeout(() => this.removeToast(id), 4000);
        },
        removeToast(id) {
            const t = this.toasts.find(t => t.id === id);
            if (t) t.visible = false;
            setTimeout(() => { this.toasts = this.toasts.filter(t => t.id !== id); }, 300);
        }
    };
}
</script>

{{-- Page Loading Indicator --}}
<div x-data="{ loading: false }"
     x-on:livewire:navigate-start.window="loading = true"
     x-on:livewire:navigate-end.window="loading = false"
     x-show="loading" x-cloak
     class="fixed top-0 left-0 right-0 z-[110] h-0.5">
    <div class="h-full bg-santander-red animate-pulse" style="animation: loading-bar 1s ease-in-out infinite;">
    </div>
</div>
<style>
@keyframes loading-bar {
    0% { width: 0; }
    50% { width: 70%; }
    100% { width: 100%; }
}
</style>

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
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('{{ asset("sw.js") }}')
            .then(function(reg) { console.log('SW registered:', reg.scope); })
            .catch(function(err) { console.log('SW failed:', err); });
    });
}
</script>
</body>
</html>
