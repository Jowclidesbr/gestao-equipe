<div class="p-6 space-y-5"
     x-data="kanbanBoard()" x-init="init()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <a href="{{ route('admin.job-openings.index') }}"
               class="text-xs text-neutral-muted hover:text-santander-red transition-colors">
                ← Voltar para Vagas
            </a>
            <h1 class="text-xl font-bold text-neutral-text mt-1">{{ $opening->title }}</h1>
            <p class="text-sm text-neutral-muted">
                {{ $opening->department->name ?? '—' }} · {{ $opening->jobPosition->title ?? '—' }}
                · {{ $opening->vacancies }} vaga(s)
            </p>
        </div>
        <button type="button" wire:click="openAddCandidate('applied')" class="btn-primary flex-shrink-0">
            + Novo Candidato
        </button>
    </div>

    {{-- Kanban Board --}}
    <div class="flex gap-4 overflow-x-auto pb-4" style="min-height: 500px;">
        @foreach($stages as $stageKey => $stageLabel)
            @php
                $stageCandidates = $candidates->get($stageKey, collect());
                $stageColor = match($stageKey) {
                    'applied'   => 'border-blue-400',
                    'screening' => 'border-amber-400',
                    'interview' => 'border-purple-400',
                    'technical' => 'border-cyan-400',
                    'offer'     => 'border-green-400',
                    'hired'     => 'border-emerald-500',
                    'rejected'  => 'border-red-400',
                };
                $headerBg = match($stageKey) {
                    'applied'   => 'bg-blue-50 text-blue-700',
                    'screening' => 'bg-amber-50 text-amber-700',
                    'interview' => 'bg-purple-50 text-purple-700',
                    'technical' => 'bg-cyan-50 text-cyan-700',
                    'offer'     => 'bg-green-50 text-green-700',
                    'hired'     => 'bg-emerald-50 text-emerald-700',
                    'rejected'  => 'bg-red-50 text-red-700',
                };
            @endphp
            <div class="flex-shrink-0 w-64 flex flex-col rounded-lg border-t-4 {{ $stageColor }} bg-white shadow-sm"
                 data-stage="{{ $stageKey }}"
                 @dragover.prevent="dragOver($event)"
                 @drop.prevent="drop($event, '{{ $stageKey }}')">

                {{-- Column header --}}
                <div class="px-3 py-2.5 {{ $headerBg }} rounded-t-lg flex items-center justify-between">
                    <span class="text-xs font-bold uppercase tracking-wider">{{ $stageLabel }}</span>
                    <span class="text-xs font-bold bg-white/60 rounded-full px-2 py-0.5">{{ $stageCandidates->count() }}</span>
                </div>

                {{-- Cards area --}}
                <div class="flex-1 p-2 space-y-2 min-h-[80px] overflow-y-auto max-h-[60vh]"
                     data-stage="{{ $stageKey }}">
                    @forelse($stageCandidates as $candidate)
                        <div class="bg-white border border-neutral-200 rounded-lg p-3 shadow-sm cursor-grab
                                    hover:shadow-md hover:border-neutral-300 transition-all group"
                             draggable="true"
                             data-candidate-id="{{ $candidate->id }}"
                             @dragstart="dragStart($event, {{ $candidate->id }})"
                             @dragend="dragEnd($event)">

                            <div class="flex items-start justify-between gap-1">
                                <div class="min-w-0">
                                    <p class="font-medium text-sm text-neutral-text truncate">{{ $candidate->name }}</p>
                                    <p class="text-[10px] text-neutral-muted truncate">{{ $candidate->email }}</p>
                                </div>
                                <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity flex gap-0.5">
                                    <button type="button" wire:click="openEditCandidate({{ $candidate->id }})"
                                            class="p-1 rounded text-neutral-muted hover:text-amber-600 hover:bg-amber-50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="deleteCandidate({{ $candidate->id }})"
                                            wire:confirm="Remover este candidato?"
                                            class="p-1 rounded text-neutral-muted hover:text-red-600 hover:bg-red-50">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            @if($candidate->phone)
                                <p class="text-[10px] text-neutral-muted mt-1">{{ $candidate->phone }}</p>
                            @endif
                            @if($candidate->linkedin_url)
                                <a href="{{ $candidate->linkedin_url }}" target="_blank" rel="noopener"
                                   class="text-[10px] text-blue-500 hover:underline mt-0.5 block truncate">LinkedIn</a>
                            @endif
                            @if($candidate->notes)
                                <p class="text-[10px] text-neutral-muted mt-1.5 bg-neutral-50 rounded px-2 py-1 line-clamp-2">{{ $candidate->notes }}</p>
                            @endif

                            <p class="text-[9px] text-neutral-muted/60 mt-1.5">{{ $candidate->updated_at->diffForHumans() }}</p>
                        </div>
                    @empty
                        <div class="flex items-center justify-center h-16 text-xs text-neutral-muted/50">
                            Arraste para cá
                        </div>
                    @endforelse
                </div>

                {{-- Add button --}}
                <div class="p-2 border-t border-neutral-100">
                    <button type="button" wire:click="openAddCandidate('{{ $stageKey }}')"
                            class="w-full text-xs text-neutral-muted hover:text-santander-red py-1.5 rounded hover:bg-red-50 transition-colors">
                        + Adicionar
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Candidate Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-md" x-transition>
                <div class="flex items-center justify-between p-6 border-b border-neutral-border">
                    <h3 class="font-semibold text-neutral-text">
                        {{ $isEditing ? 'Editar Candidato' : 'Novo Candidato' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)"
                            class="text-neutral-muted hover:text-neutral-text p-1 rounded">✕</button>
                </div>
                <form wire:submit="saveCandidate" class="p-6 space-y-4">
                    <div>
                        <label class="form-label">Nome *</label>
                        <input type="text" wire:model="candidateName"
                               class="form-input @error('candidateName') border-red-500 @enderror">
                        @error('candidateName') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">E-mail *</label>
                        <input type="email" wire:model="candidateEmail"
                               class="form-input @error('candidateEmail') border-red-500 @enderror">
                        @error('candidateEmail') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Telefone</label>
                            <input type="text" wire:model="candidatePhone" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Estágio</label>
                            <select wire:model="candidateStatus" class="form-select">
                                @foreach($stages as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="form-label">LinkedIn</label>
                        <input type="url" wire:model="candidateLinkedin" placeholder="https://linkedin.com/in/..."
                               class="form-input @error('candidateLinkedin') border-red-500 @enderror">
                        @error('candidateLinkedin') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="form-label">Observações</label>
                        <textarea wire:model="candidateNotes" rows="3" class="form-input"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Cancelar</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $isEditing ? 'Salvar' : 'Adicionar' }}</span>
                            <span wire:loading>Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
        function kanbanBoard() {
            return {
                draggedId: null,

                init() {},

                dragStart(e, candidateId) {
                    this.draggedId = candidateId;
                    e.dataTransfer.effectAllowed = 'move';
                    e.dataTransfer.setData('text/plain', candidateId);
                    e.target.classList.add('opacity-50', 'rotate-2');
                },

                dragEnd(e) {
                    e.target.classList.remove('opacity-50', 'rotate-2');
                    this.draggedId = null;
                },

                dragOver(e) {
                    e.dataTransfer.dropEffect = 'move';
                },

                drop(e, newStatus) {
                    const candidateId = parseInt(e.dataTransfer.getData('text/plain'));
                    if (candidateId && newStatus) {
                        @this.call('moveCandidate', candidateId, newStatus);
                    }
                    this.draggedId = null;
                }
            };
        }
    </script>
</div>
