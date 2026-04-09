{{-- Vacation Request List --}}
<div class="space-y-4">

    {{-- Header & Filters --}}
    <div class="card">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por colaborador..."
                       class="form-input max-w-xs">
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="filterStatus" class="form-select text-sm">
                    <option value="">Todos os status</option>
                    <option value="pending">Aguardando</option>
                    <option value="approved">Aprovadas</option>
                    <option value="rejected">Rejeitadas</option>
                    <option value="cancelled">Canceladas</option>
                </select>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Colaborador</th>
                    <th>Período</th>
                    <th>Dias</th>
                    <th>Abono</th>
                    <th>Status</th>
                    <th>Solicitado em</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    <tr wire:key="{{ $req->id }}">
                        <td>
                            <div class="flex items-center gap-2">
                                <img src="{{ $req->employee->user->avatar_url }}" alt=""
                                     class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                                <div>
                                    <p class="font-medium">{{ $req->employee->user->name }}</p>
                                    <p class="text-xs text-neutral-muted">{{ $req->employee->department->name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap">
                            {{ $req->start_date->format('d/m/Y') }} – {{ $req->end_date->format('d/m/Y') }}
                        </td>
                        <td>{{ $req->days_requested }}</td>
                        <td>{{ $req->sell_days > 0 ? $req->sell_days . ' dias' : '—' }}</td>
                        <td>
                            <span class="{{ $req->status_badge_class }}">{{ $req->status_label }}</span>
                        </td>
                        <td class="text-neutral-muted whitespace-nowrap">
                            {{ $req->submitted_at?->format('d/m/Y H:i') ?? '—' }}
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if($req->isPending())
                                    @can('approve', $req)
                                        <button type="button" wire:click="approve({{ $req->id }})"
                                                wire:confirm="Confirmar aprovação das férias?"
                                                class="btn-success py-1 px-2 text-xs">
                                            Aprovar
                                        </button>
                                        <button type="button" wire:click="openRejectModal({{ $req->id }})"
                                                class="btn-danger py-1 px-2 text-xs">
                                            Rejeitar
                                        </button>
                                    @endcan
                                @endif
                                <span class="text-neutral-muted text-xs px-2">
                                    {{ $req->approver?->name ? 'por ' . $req->approver->name : '' }}
                                </span>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-neutral-muted">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Nenhuma solicitação encontrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $requests->links() }}

    {{-- Reject Modal --}}
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" wire:click.self="$set('showRejectModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-md p-6"
                 x-transition>
                <h3 class="text-base font-semibold text-neutral-text mb-4">Rejeitar Solicitação</h3>

                <div>
                    <label class="form-label">Motivo da rejeição <span class="text-santander-red">*</span></label>
                    <textarea wire:model="rejectNotes" rows="4" placeholder="Informe o motivo detalhado..."
                              class="form-input resize-none"></textarea>
                    @error('rejectNotes') <p class="form-error">{{ $message }}</p> @enderror
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" wire:click="$set('showRejectModal', false)" class="btn-secondary">Cancelar</button>
                    <button type="button" wire:click="confirmReject" class="btn-danger">Confirmar Rejeição</button>
                </div>
            </div>
        </div>
    @endif

</div>
