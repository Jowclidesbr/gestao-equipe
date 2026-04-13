<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">Ponto Eletrônico</h1>
            <p class="text-sm text-neutral-muted mt-0.5">Registros de entrada, saída e intervalo</p>
        </div>
        <button type="button" wire:click="openManualEntry(0)" class="btn-primary flex-shrink-0">
            + Registro Manual
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Buscar colaborador..."
               class="form-input max-w-xs">

        <input type="date" wire:model.live="filterDate"
               class="form-input w-44">

        <select wire:model.live="filterDept" class="form-select w-48">
            <option value="">Todos os departamentos</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterStatus" class="form-select w-44">
            <option value="">Todos os status</option>
            <option value="complete">Completos</option>
            <option value="incomplete">Incompletos</option>
        </select>

        @if($search || $filterDept || $filterStatus)
            <button type="button" wire:click="$set('search',''); $set('filterDept',''); $set('filterStatus','')"
                    class="text-sm text-santander-red hover:underline">
                Limpar filtros
            </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-border bg-neutral-bg">
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Colaborador</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Entrada</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Saída Int.</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Retorno Int.</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Saída</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Trabalhado</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Extra / Déficit</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-border">
                @forelse($employees as $emp)
                    @php
                        $empEntries = $entries->get($emp->id, collect());
                        $summary    = $summaries->get($emp->id);
                        $clockIn    = $empEntries->firstWhere('type', 'clock_in');
                        $breakOut   = $empEntries->firstWhere('type', 'break_out');
                        $breakIn    = $empEntries->firstWhere('type', 'break_in');
                        $clockOut   = $empEntries->firstWhere('type', 'clock_out');
                    @endphp
                    <tr class="hover:bg-neutral-bg/60 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @php $initials = collect(explode(' ', $emp->user->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
                                <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold text-white"
                                     style="background:linear-gradient(135deg,#EC0000,#7B0000);">{{ $initials }}</div>
                                <div>
                                    <p class="font-medium text-neutral-text text-sm">{{ $emp->user->name }}</p>
                                    <p class="text-[10px] text-neutral-muted">{{ $emp->department->name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($clockIn)
                                <span class="text-green-600 font-mono text-xs font-medium">{{ substr($clockIn->time, 0, 5) }}</span>
                            @else
                                <span class="text-neutral-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($breakOut)
                                <span class="font-mono text-xs">{{ substr($breakOut->time, 0, 5) }}</span>
                            @else
                                <span class="text-neutral-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($breakIn)
                                <span class="font-mono text-xs">{{ substr($breakIn->time, 0, 5) }}</span>
                            @else
                                <span class="text-neutral-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($clockOut)
                                <span class="text-red-600 font-mono text-xs font-medium">{{ substr($clockOut->time, 0, 5) }}</span>
                            @else
                                <span class="text-neutral-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center font-mono text-xs font-medium">
                            {{ $summary ? $summary->worked_format : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-xs">
                            @if($summary && $summary->overtime_minutes > 0)
                                <span class="text-blue-600 font-medium">+{{ $summary->overtime_format }}</span>
                            @elseif($summary && $summary->deficit_minutes > 0)
                                <span class="text-orange-600 font-medium">-{{ $summary->deficit_format }}</span>
                            @else
                                <span class="text-neutral-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($summary)
                                @php
                                    $statusClass = match($summary->status) {
                                        'complete'   => 'badge-active',
                                        'incomplete' => 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700',
                                        'absent'     => 'badge-rejected',
                                        default      => 'badge-inactive',
                                    };
                                    $statusLabel = match($summary->status) {
                                        'complete'   => 'Completo',
                                        'incomplete' => 'Incompleto',
                                        'absent'     => 'Ausente',
                                        'holiday'    => 'Feriado',
                                        'day_off'    => 'Folga',
                                        default      => $summary->status,
                                    };
                                @endphp
                                <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Sem registro</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button type="button" wire:click="openManualEntry({{ $emp->id }})"
                                    title="Adicionar registro"
                                    class="p-1.5 rounded text-neutral-muted hover:text-santander-red hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-12 text-center text-neutral-muted">
                            Nenhum colaborador encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($employees->hasPages())
        <div class="mt-4">{{ $employees->links() }}</div>
    @endif

    {{-- Manual Entry Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-md" x-data x-transition>

                <div class="flex items-center justify-between p-6 border-b border-neutral-border">
                    <h3 class="font-semibold text-neutral-text">Registro Manual de Ponto</h3>
                    <button type="button" wire:click="$set('showModal', false)"
                            class="text-neutral-muted hover:text-neutral-text p-1 rounded">✕</button>
                </div>

                <form wire:submit="saveManualEntry" class="p-6 space-y-4">

                    <div>
                        <label class="form-label">Colaborador *</label>
                        <select wire:model="modalEmployeeId"
                                class="form-select @error('modalEmployeeId') border-red-500 @enderror">
                            <option value="0">— Selecione —</option>
                            @foreach($allEmployees as $e)
                                <option value="{{ $e->id }}">{{ $e->user->name }}</option>
                            @endforeach
                        </select>
                        @error('modalEmployeeId') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ clock: '' }" x-init="setInterval(() => clock = new Date().toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit', second:'2-digit'}), 1000)">
                        <label class="form-label">Horário atual</label>
                        <div class="form-input bg-neutral-100 text-center font-mono text-lg font-bold text-neutral-text cursor-default" x-text="clock"></div>
                    </div>

                    <div>
                        <label class="form-label">Tipo *</label>
                        <select wire:model="modalType" class="form-select">
                            <option value="clock_in">Entrada</option>
                            <option value="break_out">Saída Intervalo</option>
                            <option value="break_in">Retorno Intervalo</option>
                            <option value="clock_out">Saída</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label">Observação</label>
                        <input type="text" wire:model="modalNote"
                               class="form-input" placeholder="Motivo do registro manual">
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Cancelar</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>Registrar</span>
                            <span wire:loading>Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
