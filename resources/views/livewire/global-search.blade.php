<div class="relative" x-data="{ focused: false }" x-on:click.outside="focused = false; $wire.close()">

    <div class="relative flex items-center">
        <svg class="absolute left-3 w-4 h-4 text-neutral-muted pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text"
               wire:model.live.debounce.300ms="query"
               x-on:focus="focused = true"
               placeholder="Buscar colaborador, vaga…"
               class="pl-9 pr-4 py-2 bg-white/10 border border-white/20 rounded-lg text-sm text-white placeholder-white/60 focus:outline-none focus:ring-2 focus:ring-white/40 w-64 transition-all"
               autocomplete="off">
        @if($query)
            <button type="button" wire:click="close" class="absolute right-2 text-white/60 hover:text-white">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>

    @if($isOpen && ($employees->isNotEmpty() || $jobOpenings->isNotEmpty()))
    <div class="absolute top-full left-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-neutral-border z-50 overflow-hidden">

        @if($employees->isNotEmpty())
            <div class="px-3 py-2 bg-neutral-bg border-b border-neutral-border">
                <p class="text-[10px] font-semibold text-neutral-muted uppercase tracking-wider">Colaboradores</p>
            </div>
            @foreach($employees as $emp)
                <a href="{{ route('admin.employees.index') }}"
                   wire:click="close"
                   class="flex items-center gap-3 px-3 py-2.5 hover:bg-neutral-bg transition-colors">
                    @php $initials = collect(explode(' ', $emp->user->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
                    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-[11px] font-bold text-white"
                         style="background:linear-gradient(135deg,#EC0000,#7B0000);">{{ $initials }}</div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-neutral-text truncate">{{ $emp->user->name }}</p>
                        <p class="text-xs text-neutral-muted truncate">{{ $emp->department?->name ?? 'Sem departamento' }}</p>
                    </div>
                    <span class="ml-auto flex-shrink-0 text-[10px] px-2 py-0.5 rounded-full
                        {{ $emp->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-neutral-100 text-neutral-500' }}">
                        {{ $emp->status === 'active' ? 'Ativo' : 'Inativo' }}
                    </span>
                </a>
            @endforeach
        @endif

        @if($jobOpenings->isNotEmpty())
            <div class="px-3 py-2 bg-neutral-bg border-t border-b border-neutral-border">
                <p class="text-[10px] font-semibold text-neutral-muted uppercase tracking-wider">Vagas</p>
            </div>
            @foreach($jobOpenings as $opening)
                <a href="{{ route('admin.ats.openings') }}"
                   wire:click="close"
                   class="flex items-center gap-3 px-3 py-2.5 hover:bg-neutral-bg transition-colors">
                    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center bg-blue-100 text-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-neutral-text truncate">{{ $opening->title }}</p>
                        <p class="text-xs text-neutral-muted">{{ $opening->candidates_count ?? 0 }} candidatos</p>
                    </div>
                </a>
            @endforeach
        @endif

        @if($employees->isEmpty() && $jobOpenings->isEmpty())
            <div class="px-3 py-4 text-center text-sm text-neutral-muted">Nenhum resultado encontrado.</div>
        @endif

    </div>
    @elseif($isOpen)
    <div class="absolute top-full left-0 mt-2 w-80 bg-white rounded-xl shadow-2xl border border-neutral-border z-50 p-4 text-center text-sm text-neutral-muted">
        Nenhum resultado para <strong>"{{ $query }}"</strong>.
    </div>
    @endif

</div>
