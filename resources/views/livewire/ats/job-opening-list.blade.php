<div class="p-6 space-y-6">

    {{-- ── Header ─────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">Vagas (ATS)</h1>
            <p class="text-sm text-neutral-muted mt-0.5">Gerencie processos seletivos e candidatos</p>
        </div>
        <button type="button" wire:click="openCreateModal" class="btn-primary flex-shrink-0">
            + Nova Vaga
        </button>
    </div>

    {{-- ── Filters ──────────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Buscar por título..."
               class="form-input max-w-xs">

        <select wire:model.live="filterStatus" class="form-select w-44">
            <option value="">Todos os status</option>
            <option value="draft">Rascunho</option>
            <option value="open">Aberta</option>
            <option value="paused">Pausada</option>
            <option value="closed">Encerrada</option>
        </select>

        <select wire:model.live="filterDept" class="form-select w-48">
            <option value="">Todos os departamentos</option>
            @foreach($departments as $dept)
                <option value="{{ $dept->id }}">{{ $dept->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- ── Openings Table ───────────────────────────────────────────────────── --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-border bg-neutral-bg">
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Título / Cargo</th>
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs hidden md:table-cell">Departamento</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs hidden lg:table-cell">Vagas</th>
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs hidden lg:table-cell">Modalidade</th>
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Status</th>
                    <th class="text-center px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs hidden md:table-cell">Candidatos</th>
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs hidden lg:table-cell">Prazo</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-border">
                @forelse($openings as $opening)
                    <tr class="hover:bg-neutral-bg/60 transition-colors">
                        <td class="px-4 py-3">
                            <div class="font-medium text-neutral-text">{{ $opening->title }}</div>
                            @if($opening->jobPosition)
                                <div class="text-xs text-neutral-muted">{{ $opening->jobPosition->title }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-neutral-muted hidden md:table-cell">
                            {{ $opening->department->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-center text-neutral-text hidden lg:table-cell">
                            {{ $opening->vacancies }}
                        </td>
                        <td class="px-4 py-3 hidden lg:table-cell">
                            @php
                                $modeLabel = match($opening->mode) {
                                    'onsite'  => 'Presencial',
                                    'remote'  => 'Remoto',
                                    'hybrid'  => 'Híbrido',
                                    default   => '—',
                                };
                                $typeLabel = match($opening->type) {
                                    'internal' => 'Interna',
                                    'external' => 'Externa',
                                    'both'     => 'Ambas',
                                    default    => '—',
                                };
                            @endphp
                            <span class="text-neutral-text">{{ $modeLabel }}</span>
                            <span class="text-neutral-muted text-xs ml-1">· {{ $typeLabel }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $statusClass = match($opening->status) {
                                    'open'   => 'badge-active',
                                    'draft'  => 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700',
                                    'paused' => 'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700',
                                    'closed' => 'badge-inactive',
                                    default  => 'badge-inactive',
                                };
                                $statusLabel = match($opening->status) {
                                    'open'   => 'Aberta',
                                    'draft'  => 'Rascunho',
                                    'paused' => 'Pausada',
                                    'closed' => 'Encerrada',
                                    default  => '—',
                                };
                            @endphp
                            <span class="{{ $statusClass }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-center hidden md:table-cell">
                            <button type="button" wire:click="viewCandidates({{ $opening->id }})"
                                    class="inline-flex items-center gap-1 text-santander-red font-medium hover:underline text-sm">
                                {{ $opening->candidates_count }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                                </svg>
                            </button>
                        </td>
                        <td class="px-4 py-3 text-neutral-muted text-xs hidden lg:table-cell">
                            {{ $opening->deadline?->format('d/m/Y') ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <button type="button" wire:click="viewCandidates({{ $opening->id }})"
                                        title="Ver candidatos"
                                        class="p-1.5 rounded text-neutral-muted hover:text-santander-red hover:bg-red-50 transition-colors md:hidden">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                                    </svg>
                                </button>
                                <button type="button" wire:click="openEditModal({{ $opening->id }})"
                                        title="Editar"
                                        class="p-1.5 rounded text-neutral-muted hover:text-santander-red hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414a2 2 0 01.586-1.414z"/>
                                    </svg>
                                </button>
                                <button type="button" wire:click="deleteOpening({{ $opening->id }})"
                                        wire:confirm="Excluir esta vaga? Só é possível se não houver candidatos."
                                        title="Excluir"
                                        class="p-1.5 rounded text-neutral-muted hover:text-red-600 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a1 1 0 011-1h6a1 1 0 011 1v2"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-neutral-muted">
                            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0H8m8 0a2 2 0 012 2v10a2 2 0 01-2 2H8a2 2 0 01-2-2V8a2 2 0 012-2"/>
                            </svg>
                            Nenhuma vaga encontrada.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($openings->hasPages())
        <div class="mt-4">
            {{ $openings->links() }}
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════════
         CREATE / EDIT OPENING MODAL
    ════════════════════════════════════════════════════════════════════════ --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto"
                 x-data x-transition>

                {{-- Modal header --}}
                <div class="flex items-center justify-between p-6 border-b border-neutral-border sticky top-0 bg-white z-10">
                    <h3 class="font-semibold text-neutral-text">
                        {{ $isEditing ? 'Editar Vaga' : 'Nova Vaga' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)"
                            class="text-neutral-muted hover:text-neutral-text p-1 rounded">✕</button>
                </div>

                <form wire:submit="saveOpening" class="p-6 space-y-4">

                    {{-- Tenant selector (super_admin only) --}}
                    @if(auth()->user()->hasRole('super_admin'))
                    <div>
                        <label class="form-label">Tenant *</label>
                        <select wire:model.live="selected_tenant_id"
                                class="form-select @error('selected_tenant_id') border-red-500 @enderror">
                            <option value="">— Selecione um tenant —</option>
                            @foreach($tenants as $tenant)
                                <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                            @endforeach
                        </select>
                        @error('selected_tenant_id') <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    @endif

                    {{-- Title --}}
                    <div>
                        <label class="form-label">Título da Vaga *</label>
                        <input type="text" wire:model="title"
                               class="form-input @error('title') border-red-500 @enderror"
                               placeholder="ex: Analista de Sistemas Sênior">
                        @error('title') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    {{-- Department / Position --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Departamento *</label>
                            <select wire:model="department_id"
                                    class="form-select @error('department_id') border-red-500 @enderror">
                                <option value="0">— Selecione —</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Cargo *</label>
                            <select wire:model="job_position_id"
                                    class="form-select @error('job_position_id') border-red-500 @enderror">
                                <option value="0">— Selecione —</option>
                                @foreach($jobPositions as $pos)
                                    <option value="{{ $pos->id }}">{{ $pos->title }}</option>
                                @endforeach
                            </select>
                            @error('job_position_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Type / Mode / Vacancies / Deadline --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div>
                            <label class="form-label">Tipo</label>
                            <select wire:model="type" class="form-select">
                                <option value="internal">Interna</option>
                                <option value="external">Externa</option>
                                <option value="both">Ambas</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Modalidade</label>
                            <select wire:model="mode" class="form-select">
                                <option value="onsite">Presencial</option>
                                <option value="remote">Remoto</option>
                                <option value="hybrid">Híbrido</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Nº Vagas *</label>
                            <input type="number" wire:model="vacancies" min="1" max="999"
                                   class="form-input @error('vacancies') border-red-500 @enderror">
                            @error('vacancies') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Prazo</label>
                            <input type="date" wire:model="deadline"
                                   class="form-input @error('deadline') border-red-500 @enderror">
                            @error('deadline') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Status / Salary --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Status</label>
                            <select wire:model="status" class="form-select">
                                <option value="draft">Rascunho</option>
                                <option value="open">Aberta</option>
                                <option value="paused">Pausada</option>
                                <option value="closed">Encerrada</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Salário Ofertado (R$)</label>
                            <input type="number" wire:model="salary_offered" min="0" step="0.01"
                                   placeholder="0.00"
                                   class="form-input @error('salary_offered') border-red-500 @enderror">
                            @error('salary_offered') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="form-label">Descrição</label>
                        <textarea wire:model="description" rows="3"
                                  class="form-input @error('description') border-red-500 @enderror"
                                  placeholder="Sobre a vaga, responsabilidades..."></textarea>
                        @error('description') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    {{-- Requirements --}}
                    <div>
                        <label class="form-label">Requisitos</label>
                        <textarea wire:model="requirements" rows="3"
                                  class="form-input @error('requirements') border-red-500 @enderror"
                                  placeholder="Formação, habilidades, experiência..."></textarea>
                        @error('requirements') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $isEditing ? 'Salvar alterações' : 'Criar vaga' }}</span>
                            <span wire:loading>Salvando...</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════════
         CANDIDATES SLIDE-IN PANEL
    ════════════════════════════════════════════════════════════════════════ --}}
    @if($showCandidates)
        {{-- Backdrop --}}
        <div class="fixed inset-0 z-40 bg-black/40" wire:click="closeCandidates"></div>

        {{-- Panel --}}
        <div class="fixed right-0 top-0 z-50 h-full w-full max-w-2xl bg-white shadow-2xl flex flex-col"
             x-data x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0">

            {{-- Panel header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-neutral-border bg-neutral-bg flex-shrink-0">
                <div>
                    <h3 class="font-semibold text-neutral-text">Candidatos</h3>
                    <p class="text-xs text-neutral-muted mt-0.5 truncate max-w-sm">{{ $activeOpeningTitle }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" wire:click="openAddCandidate" class="btn-primary text-xs py-1.5 px-3">
                        + Adicionar
                    </button>
                    <button type="button" wire:click="closeCandidates"
                            class="p-1.5 rounded text-neutral-muted hover:text-neutral-text hover:bg-neutral-border transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Pipeline legend --}}
            <div class="px-6 py-2 border-b border-neutral-border bg-white flex-shrink-0 overflow-x-auto">
                <div class="flex items-center gap-1 text-xs whitespace-nowrap">
                    @foreach([
                        ['applied','Inscrito','bg-gray-200 text-gray-700'],
                        ['screening','Triagem','bg-yellow-100 text-yellow-700'],
                        ['interview','Entrevista','bg-blue-100 text-blue-700'],
                        ['technical','Teste Téc.','bg-purple-100 text-purple-700'],
                        ['offer','Proposta','bg-orange-100 text-orange-700'],
                        ['hired','Contratado','bg-green-100 text-green-700'],
                        ['rejected','Rejeitado','bg-red-100 text-red-600'],
                    ] as [$key, $label, $cls])
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full {{ $cls }} font-medium">{{ $label }}</span>
                        @if(!$loop->last)<span class="text-neutral-muted">›</span>@endif
                    @endforeach
                </div>
            </div>

            {{-- Candidates list --}}
            <div class="flex-1 overflow-y-auto p-6 space-y-3">
                @forelse($candidates as $c)
                    <div class="rounded-lg border border-neutral-border p-4 hover:border-santander-red/30 transition-colors">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="font-medium text-neutral-text truncate">{{ $c->name }}</div>
                                <div class="text-xs text-neutral-muted mt-0.5">{{ $c->email }}</div>
                                @if($c->phone)
                                    <div class="text-xs text-neutral-muted">{{ $c->phone }}</div>
                                @endif
                                @if($c->linkedin_url)
                                    <a href="{{ $c->linkedin_url }}" target="_blank" rel="noopener noreferrer"
                                       class="text-xs text-santander-red hover:underline mt-0.5 inline-block">LinkedIn ↗</a>
                                @endif
                                @if($c->notes)
                                    <p class="text-xs text-neutral-muted mt-1 line-clamp-2">{{ $c->notes }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                @php
                                    $stageCls = match($c->status) {
                                        'applied'   => 'bg-gray-200 text-gray-700',
                                        'screening' => 'bg-yellow-100 text-yellow-700',
                                        'interview' => 'bg-blue-100 text-blue-700',
                                        'technical' => 'bg-purple-100 text-purple-700',
                                        'offer'     => 'bg-orange-100 text-orange-700',
                                        'hired'     => 'bg-green-100 text-green-700',
                                        'rejected'  => 'bg-red-100 text-red-600',
                                        default     => 'bg-gray-100 text-gray-600',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $stageCls }}">
                                    {{ $c->status_label }}
                                </span>
                                <div class="flex items-center gap-1">
                                    @if(!in_array($c->status, ['hired', 'rejected']))
                                        <button type="button" wire:click="advanceCandidate({{ $c->id }})"
                                                title="Avançar etapa"
                                                class="p-1 rounded text-green-600 hover:bg-green-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                        <button type="button" wire:click="rejectCandidate({{ $c->id }})"
                                                wire:confirm="Rejeitar este candidato?"
                                                title="Rejeitar"
                                                class="p-1 rounded text-red-500 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                    <button type="button" wire:click="openEditCandidate({{ $c->id }})"
                                            title="Editar"
                                            class="p-1 rounded text-neutral-muted hover:text-santander-red hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414a2 2 0 01.586-1.414z"/>
                                        </svg>
                                    </button>
                                    <button type="button" wire:click="deleteCandidate({{ $c->id }})"
                                            wire:confirm="Remover este candidato?"
                                            title="Remover"
                                            class="p-1 rounded text-neutral-muted hover:text-red-600 hover:bg-red-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M1 7h22M8 7V5a1 1 0 011-1h6a1 1 0 011 1v2"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-neutral-muted">
                        <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                        </svg>
                        <p class="text-sm">Nenhum candidato cadastrado.</p>
                        <button type="button" wire:click="openAddCandidate" class="mt-3 text-santander-red text-sm font-medium hover:underline">
                            Adicionar primeiro candidato →
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

    {{-- ════════════════════════════════════════════════════════════════════════
         ADD / EDIT CANDIDATE MODAL
    ════════════════════════════════════════════════════════════════════════ --}}
    @if($showCandidateModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50"
             wire:click.self="$set('showCandidateModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-lg max-h-[90vh] overflow-y-auto"
                 x-data x-transition>

                <div class="flex items-center justify-between p-6 border-b border-neutral-border">
                    <h3 class="font-semibold text-neutral-text">
                        {{ $editingCandidate ? 'Editar Candidato' : 'Adicionar Candidato' }}
                    </h3>
                    <button type="button" wire:click="$set('showCandidateModal', false)"
                            class="text-neutral-muted hover:text-neutral-text p-1 rounded">✕</button>
                </div>

                <form wire:submit="saveCandidate" class="p-6 space-y-4">

                    {{-- Name --}}
                    <div>
                        <label class="form-label">Nome *</label>
                        <input type="text" wire:model="cName"
                               class="form-input @error('cName') border-red-500 @enderror"
                               placeholder="Nome completo">
                        @error('cName') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    {{-- Email / Phone --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">E-mail *</label>
                            <input type="email" wire:model="cEmail"
                                   class="form-input @error('cEmail') border-red-500 @enderror"
                                   placeholder="candidato@email.com">
                            @error('cEmail') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Telefone</label>
                            <input type="text" wire:model="cPhone"
                                   class="form-input"
                                   placeholder="(11) 9 0000-0000">
                        </div>
                    </div>

                    {{-- LinkedIn / Status --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">LinkedIn</label>
                            <input type="url" wire:model="cLinkedin"
                                   class="form-input @error('cLinkedin') border-red-500 @enderror"
                                   placeholder="https://linkedin.com/in/...">
                            @error('cLinkedin') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Status</label>
                            <select wire:model="cStatus" class="form-select">
                                <option value="applied">Inscrito</option>
                                <option value="screening">Triagem</option>
                                <option value="interview">Entrevista</option>
                                <option value="technical">Teste Técnico</option>
                                <option value="offer">Proposta</option>
                                <option value="hired">Contratado</option>
                                <option value="rejected">Rejeitado</option>
                            </select>
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label class="form-label">Observações</label>
                        <textarea wire:model="cNotes" rows="3"
                                  class="form-input @error('cNotes') border-red-500 @enderror"
                                  placeholder="Anotações sobre o candidato..."></textarea>
                        @error('cNotes') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showCandidateModal', false)" class="btn-secondary">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $editingCandidate ? 'Salvar' : 'Adicionar' }}</span>
                            <span wire:loading>Salvando...</span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    @endif

</div>
