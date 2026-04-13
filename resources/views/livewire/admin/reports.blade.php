<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">Relatórios & Analytics</h1>
            <p class="text-sm text-neutral-muted mt-0.5">Indicadores de RH consolidados</p>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="filterDept" class="input-field text-sm !py-1.5 !w-auto">
                <option value="">Todos os departamentos</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="period" class="input-field text-sm !py-1.5 !w-auto">
                <option value="month">Mês atual</option>
                <option value="quarter">Últimos 3 meses</option>
                <option value="year">Último ano</option>
            </select>
        </div>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="card p-4">
            <p class="text-xs text-neutral-muted uppercase tracking-wide">Headcount Ativo</p>
            <p class="text-2xl font-bold text-neutral-text mt-1">{{ $headcount }}</p>
            <p class="text-xs text-neutral-muted mt-1">{{ $totalInactive }} inativos · {{ $totalOnLeave }} afastados</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-neutral-muted uppercase tracking-wide">Turnover</p>
            <p class="text-2xl font-bold {{ $turnoverRate > 10 ? 'text-red-600' : 'text-neutral-text' }} mt-1">{{ $turnoverRate }}%</p>
            <p class="text-xs text-neutral-muted mt-1">{{ $admissions }} admissões · {{ $dismissals }} desligamentos</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-neutral-muted uppercase tracking-wide">Horas Extra</p>
            <p class="text-2xl font-bold text-amber-600 mt-1">{{ intdiv($totalOvertime, 60) }}h{{ str_pad($totalOvertime % 60, 2, '0', STR_PAD_LEFT) }}</p>
            <p class="text-xs text-neutral-muted mt-1">Déficit: {{ intdiv($totalDeficit, 60) }}h{{ str_pad($totalDeficit % 60, 2, '0', STR_PAD_LEFT) }}</p>
        </div>
        <div class="card p-4">
            <p class="text-xs text-neutral-muted uppercase tracking-wide">Férias</p>
            <p class="text-2xl font-bold text-neutral-text mt-1">{{ $vacationsApproved }}</p>
            <p class="text-xs text-neutral-muted mt-1">aprovadas no período · {{ $vacationsPending }} pendentes</p>
        </div>
    </div>

    {{-- Charts row --}}
    <div class="grid md:grid-cols-2 gap-6">

        {{-- Headcount by department (horizontal bar) --}}
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-neutral-text mb-4">Headcount por Departamento</h2>
            @php $maxDept = $byDepartment->max('total') ?: 1; @endphp
            @forelse($byDepartment as $item)
                <div class="mb-3">
                    <div class="flex justify-between text-xs text-neutral-muted mb-1">
                        <span>{{ $item['name'] }}</span>
                        <span class="font-semibold text-neutral-text">{{ $item['total'] }}</span>
                    </div>
                    <div class="w-full bg-neutral-100 rounded-full h-2.5 dark:bg-neutral-700">
                        <div class="bg-primary h-2.5 rounded-full transition-all"
                             style="width: {{ round($item['total'] / $maxDept * 100) }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-neutral-muted">Nenhum dado.</p>
            @endforelse
        </div>

        {{-- Admission trend (simple bar chart) --}}
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-neutral-text mb-4">Admissões — Últimos 6 Meses</h2>
            @php $maxAdm = $admissionTrend->max('count') ?: 1; @endphp
            <div class="flex items-end gap-2 h-40">
                @foreach($admissionTrend as $point)
                    <div class="flex-1 flex flex-col items-center justify-end h-full">
                        <span class="text-xs font-semibold text-neutral-text mb-1">{{ $point['count'] }}</span>
                        <div class="w-full bg-primary/80 rounded-t transition-all"
                             style="height: {{ $maxAdm > 0 ? round($point['count'] / $maxAdm * 100) : 0 }}%"></div>
                        <span class="text-[10px] text-neutral-muted mt-1">{{ $point['month'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Breakdown row --}}
    <div class="grid sm:grid-cols-3 gap-6">

        {{-- By shift --}}
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-neutral-text mb-3">Por Turno</h2>
            @php
                $shiftLabels = ['I' => 'Turno I — 08h–17h', 'II' => 'Turno II — 15h–00h', 'III' => 'Turno III — 00h–08h', 'Não definido' => 'Não definido'];
                $shiftColors = ['I' => 'bg-blue-500', 'II' => 'bg-amber-500', 'III' => 'bg-violet-500', 'Não definido' => 'bg-neutral-400'];
            @endphp
            @foreach($byShift as $key => $val)
                <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 last:border-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $shiftColors[$key] ?? 'bg-neutral-400' }}"></span>
                        <span class="text-sm text-neutral-text">{{ $shiftLabels[$key] ?? $key }}</span>
                    </div>
                    <span class="text-sm font-semibold text-neutral-text">{{ $val }}</span>
                </div>
            @endforeach
            @if($byShift->isEmpty())
                <p class="text-sm text-neutral-muted">Nenhum dado.</p>
            @endif
        </div>

        {{-- By team --}}
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-neutral-text mb-3">Por Equipe</h2>
            @php
                $teamLabels = ['run_the_bank' => 'Run The Bank', 'change_the_bank' => 'Change The Bank', 'Não definido' => 'Não definido'];
                $teamColors = ['run_the_bank' => 'bg-green-500', 'change_the_bank' => 'bg-orange-500', 'Não definido' => 'bg-neutral-400'];
            @endphp
            @foreach($byTeam as $key => $val)
                <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 last:border-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $teamColors[$key] ?? 'bg-neutral-400' }}"></span>
                        <span class="text-sm text-neutral-text">{{ $teamLabels[$key] ?? $key }}</span>
                    </div>
                    <span class="text-sm font-semibold text-neutral-text">{{ $val }}</span>
                </div>
            @endforeach
            @if($byTeam->isEmpty())
                <p class="text-sm text-neutral-muted">Nenhum dado.</p>
            @endif
        </div>

        {{-- By contract --}}
        <div class="card p-5">
            <h2 class="text-sm font-semibold text-neutral-text mb-3">Por Modalidade</h2>
            @php
                $ctLabels = ['clt' => 'CLT', 'pj' => 'PJ', 'intern' => 'Estágio', 'temporary' => 'Temporário'];
                $ctColors = ['clt' => 'bg-emerald-500', 'pj' => 'bg-sky-500', 'intern' => 'bg-pink-500', 'temporary' => 'bg-yellow-500'];
            @endphp
            @foreach($byContract as $key => $val)
                <div class="flex items-center justify-between py-1.5 border-b border-neutral-100 last:border-0">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full {{ $ctColors[$key] ?? 'bg-neutral-400' }}"></span>
                        <span class="text-sm text-neutral-text">{{ $ctLabels[$key] ?? $key }}</span>
                    </div>
                    <span class="text-sm font-semibold text-neutral-text">{{ $val }}</span>
                </div>
            @endforeach
            @if($byContract->isEmpty())
                <p class="text-sm text-neutral-muted">Nenhum dado.</p>
            @endif
        </div>
    </div>

</div>
