<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">Afastamentos</h1>
            <p class="text-sm text-neutral-muted mt-0.5">Licenças médicas, maternidade, acidentes e outros afastamentos</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="openCreate" class="btn-primary flex-shrink-0">
                + Novo Afastamento
            </button>
            <a href="{{ route('admin.export.absences.xlsx', ['type' => $filterType]) }}"
               class="btn-secondary py-1.5 px-3 text-xs">XLSX</a>
            <a href="{{ route('admin.export.absences.pdf', ['type' => $filterType]) }}"
               class="btn-secondary py-1.5 px-3 text-xs">PDF</a>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Buscar colaborador..."
               class="form-input max-w-xs">
        <select wire:model.live="filterType" class="form-select w-52">
            <option value="">Todos os tipos</option>
            <option value="sick_leave">Licença Médica</option>
            <option value="accident">Acidente de Trabalho</option>
            <option value="maternity">Licença Maternidade</option>
            <option value="paternity">Licença Paternidade</option>
            <option value="bereavement">Luto</option>
            <option value="jury_duty">Serviço Jurídico</option>
            <option value="unpaid">Sem Remuneração</option>
            <option value="other">Outro</option>
        </select>
        <select wire:model.live="filterStatus" class="form-select w-44">
            <option value="">Todos os status</option>
            <option value="pending">Pendentes</option>
            <option value="approved">Aprovados</option>
            <option value="rejected">Rejeitados</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-border bg-neutral-bg">
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Colaborador</th>
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Tipo</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Início</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Fim</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Dias</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">CID</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Doc.</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Situação</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-border">
                @forelse($absences as $ab)
                    @php
                        $days = $ab->start_date->diffInDays($ab->end_date) + 1;
                        $typeBadge = match($ab->type) {
                            'sick_leave'  => 'bg-red-100 text-red-700',
                            'accident'    => 'bg-orange-100 text-orange-700',
                            'maternity'   => 'bg-pink-100 text-pink-700',
                            'paternity'   => 'bg-blue-100 text-blue-700',
                            'bereavement' => 'bg-gray-100 text-gray-700',
                            'jury_duty'   => 'bg-indigo-100 text-indigo-700',
                            'unpaid'      => 'bg-yellow-100 text-yellow-700',
                            default       => 'bg-neutral-100 text-neutral-700',
                        };
                    @endphp
                    <tr class="hover:bg-neutral-bg/60 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @php $initials = collect(explode(' ', $ab->employee->user->name))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
                                <div class="w-7 h-7 rounded-full flex-shrink-0 flex items-center justify-center text-[10px] font-bold text-white"
                                     style="background:linear-gradient(135deg,#EC0000,#7B0000);">{{ $initials }}</div>
                                <div>
                                    <p class="font-medium text-neutral-text text-sm">{{ $ab->employee->user->name }}</p>
                                    <p class="text-[10px] text-neutral-muted">{{ $ab->employee->department->name ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadge }}">
                                {{ $ab->type_label }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center text-xs text-neutral-muted">{{ $ab->start_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center text-xs text-neutral-muted">{{ $ab->end_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center text-xs font-semibold">{{ $days }}</td>
                        <td class="px-4 py-3 text-center text-xs font-mono">{{ $ab->cid_code ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($ab->document_path)
                                <button type="button" wire:click="downloadDocument({{ $ab->id }})"
                                        title="Baixar atestado"
                                        class="text-primary hover:text-santander-red-dark transition-colors">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </button>
                            @else
                                <span class="text-neutral-muted text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusBadge = match($ab->status ?? 'pending') {
                                    'approved' => 'bg-green-100 text-green-700',
                                    'rejected' => 'bg-red-100 text-red-700',
                                    default    => 'bg-yellow-100 text-yellow-700',
                                };
                                $statusLabel = match($ab->status ?? 'pending') {
                                    'approved' => 'Aprovado',
                                    'rejected' => 'Rejeitado',
                                    default    => 'Pendente',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusBadge }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-1">
                                @if(($ab->status ?? 'pending') === 'pending')
                                    <button type="button" wire:click="approve({{ $ab->id }})"
                                            title="Aprovar"
                                            class="p-1.5 rounded text-neutral-muted hover:text-green-600 hover:bg-green-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="reject({{ $ab->id }})"
                                            title="Rejeitar"
                                            class="p-1.5 rounded text-neutral-muted hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                @endif
                                <button type="button" wire:click="viewDetail({{ $ab->id }})"
                                        title="Detalhes"
                                        class="p-1.5 rounded text-neutral-muted hover:text-primary hover:bg-blue-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                                <button type="button" wire:click="openEdit({{ $ab->id }})"
                                        title="Editar"
                                        class="p-1.5 rounded text-neutral-muted hover:text-amber-600 hover:bg-amber-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button type="button" wire:click="deleteAbsence({{ $ab->id }})"
                                        wire:confirm="Tem certeza que deseja excluir este afastamento?"
                                        title="Excluir"
                                        class="p-1.5 rounded text-neutral-muted hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-neutral-muted">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Nenhum afastamento registrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($absences->hasPages())
        <div class="mt-4">{{ $absences->links() }}</div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetail && $viewing)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="$set('showDetail', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-md" x-transition>
                <div class="flex items-center justify-between p-6 border-b border-neutral-border">
                    <h3 class="font-semibold text-neutral-text">Detalhes do Afastamento</h3>
                    <button type="button" wire:click="$set('showDetail', false)"
                            class="text-neutral-muted hover:text-neutral-text p-1 rounded">✕</button>
                </div>
                <div class="p-6 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">Colaborador</span>
                        <span class="font-medium text-neutral-text">{{ $viewing->employee->user->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">Departamento</span>
                        <span class="text-neutral-text">{{ $viewing->employee->department->name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">Tipo</span>
                        <span class="font-medium text-neutral-text">{{ $viewing->type_label }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">Período</span>
                        <span class="text-neutral-text">{{ $viewing->start_date->format('d/m/Y') }} — {{ $viewing->end_date->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">Dias</span>
                        <span class="font-semibold text-neutral-text">{{ $viewing->start_date->diffInDays($viewing->end_date) + 1 }}</span>
                    </div>
                    @if($viewing->cid_code)
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">CID-10</span>
                        <span class="font-mono text-neutral-text">{{ $viewing->cid_code }}</span>
                    </div>
                    @endif
                    @if($viewing->notes)
                    <div>
                        <span class="text-neutral-muted block mb-1">Observações</span>
                        <p class="bg-neutral-50 rounded p-3 text-neutral-text text-xs">{{ $viewing->notes }}</p>
                    </div>
                    @endif
                    @if($viewing->document_path)
                    <div class="flex justify-between items-center">
                        <span class="text-neutral-muted">Documento</span>
                        <button type="button" wire:click="downloadDocument({{ $viewing->id }})"
                                class="text-primary hover:underline text-xs font-medium">
                            Baixar atestado
                        </button>
                    </div>
                    @endif
                    @if($viewing->registeredBy)
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">Registrado por</span>
                        <span class="text-neutral-text text-xs">{{ $viewing->registeredBy->name }} em {{ $viewing->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-lg max-h-screen overflow-y-auto" x-transition>
                <div class="flex items-center justify-between p-6 border-b border-neutral-border">
                    <h3 class="font-semibold text-neutral-text">
                        {{ $isEditing ? 'Editar Afastamento' : 'Novo Afastamento' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)"
                            class="text-neutral-muted hover:text-neutral-text p-1 rounded">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">

                    <div>
                        <label class="form-label">Colaborador *</label>
                        <select wire:model="employee_id"
                                class="form-select @error('employee_id') border-red-500 @enderror">
                            <option value="0">— Selecione —</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->user->name }}</option>
                            @endforeach
                        </select>
                        @error('employee_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Tipo de Afastamento *</label>
                        <select wire:model="type" class="form-select @error('type') border-red-500 @enderror">
                            <option value="sick_leave">Licença Médica</option>
                            <option value="accident">Acidente de Trabalho</option>
                            <option value="maternity">Licença Maternidade</option>
                            <option value="paternity">Licença Paternidade</option>
                            <option value="bereavement">Luto</option>
                            <option value="jury_duty">Serviço Jurídico</option>
                            <option value="unpaid">Sem Remuneração</option>
                            <option value="other">Outro</option>
                        </select>
                        @error('type') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Data Início *</label>
                            <input type="date" wire:model="start_date"
                                   class="form-input @error('start_date') border-red-500 @enderror">
                            @error('start_date') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Data Fim *</label>
                            <input type="date" wire:model="end_date"
                                   class="form-input @error('end_date') border-red-500 @enderror">
                            @error('end_date') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Código CID-10</label>
                        <input type="text" wire:model="cid_code" placeholder="Ex: J11"
                               class="form-input max-w-[120px] @error('cid_code') border-red-500 @enderror">
                        @error('cid_code') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Atestado / Documento</label>
                        @if($existingDocPath)
                            <p class="text-xs text-green-600 mb-1">Documento anexado. Envie outro para substituir.</p>
                        @endif
                        <input type="file" wire:model="document" accept=".pdf,.jpg,.jpeg,.png"
                               class="form-input text-sm">
                        <p class="text-xs text-neutral-muted mt-1">PDF, JPG ou PNG — máx. 10 MB</p>
                        @error('document') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Observações</label>
                        <textarea wire:model="notes" rows="3" placeholder="Detalhes adicionais..."
                                  class="form-input @error('notes') border-red-500 @enderror"></textarea>
                        @error('notes') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Cancelar</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $isEditing ? 'Salvar alterações' : 'Registrar' }}</span>
                            <span wire:loading>Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
