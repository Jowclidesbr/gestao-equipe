{{-- Employee Self-Service Dashboard --}}
<div class="space-y-6">

    @php $emp = auth()->user()->employee; @endphp

    {{-- Welcome banner --}}
    <div class="rounded-card overflow-hidden" style="background: linear-gradient(135deg, #EC0000 0%, #B30000 100%);">
        <div class="px-6 py-8 flex items-center justify-between">
            <div>
                <p class="text-white/70 text-sm">Olá,</p>
                <h2 class="text-2xl font-bold text-white">{{ auth()->user()->name }}</h2>
                <p class="text-white/70 text-sm mt-1">
                    {{ $emp?->jobPosition->title ?? '' }}
                    {{ $emp?->department ? '· ' . $emp->department->name : '' }}
                </p>
            </div>
            <img src="{{ auth()->user()->avatar_url }}" alt=""
                 class="w-16 h-16 rounded-full border-4 border-white/30 object-cover hidden sm:block">
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="metric-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="metric-label">Saldo de Férias</p>
                    <p class="metric-value text-santander-red">{{ $metrics['balance'] ?? 0 }}</p>
                    <p class="text-xs text-neutral-muted">dias disponíveis</p>
                </div>
                <div class="metric-icon bg-red-50">
                    <svg class="w-5 h-5 text-santander-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            @if(isset($metrics['concession_end']) && $metrics['concession_end'])
                <p class="text-xs text-neutral-muted mt-2">
                    Limite para gozar: <strong>{{ $metrics['concession_end']->format('d/m/Y') }}</strong>
                </p>
            @endif
        </div>

        <div class="metric-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="metric-label">Pedidos Pendentes</p>
                    <p class="metric-value {{ ($metrics['pending_requests'] ?? 0) > 0 ? 'text-yellow-600' : '' }}">
                        {{ $metrics['pending_requests'] ?? 0 }}
                    </p>
                    <p class="text-xs text-neutral-muted">aguardando aprovação</p>
                </div>
                <div class="metric-icon bg-yellow-50">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="metric-card">
            <div class="flex items-start justify-between">
                <div>
                    <p class="metric-label">Férias Aprovadas</p>
                    <p class="metric-value text-green-600">{{ $metrics['approved_requests'] ?? 0 }}</p>
                    <p class="text-xs text-neutral-muted">em {{ now()->year }}</p>
                </div>
                <div class="metric-icon bg-green-50">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="card">
        <h3 class="font-semibold text-neutral-text mb-3">Ações Rápidas</h3>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('employee.vacation.create') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Solicitar Férias
            </a>
            <a href="{{ route('employee.vacation.index') }}" class="btn-secondary">
                Minhas Solicitações
            </a>
        </div>
    </div>

    {{-- Recent requests --}}
    @if(!empty($metrics['recent_requests']) && $metrics['recent_requests']->isNotEmpty())
        <div class="card">
            <h3 class="font-semibold text-neutral-text mb-4">Últimas Solicitações</h3>
            <div class="space-y-3">
                @foreach($metrics['recent_requests'] as $req)
                    <div class="flex items-center justify-between py-2 border-b border-neutral-border last:border-b-0">
                        <div>
                            <p class="text-sm font-medium">
                                {{ $req->start_date->format('d/m/Y') }} – {{ $req->end_date->format('d/m/Y') }}
                            </p>
                            <p class="text-xs text-neutral-muted">{{ $req->days_requested }} dias</p>
                        </div>
                        <span class="{{ $req->status_badge_class }}">{{ $req->status_label }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
