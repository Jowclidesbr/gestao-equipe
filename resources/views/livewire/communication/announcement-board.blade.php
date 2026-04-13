<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">Comunicados</h1>
            <p class="text-sm text-neutral-muted mt-0.5">Mural de comunicação interna</p>
        </div>
        @if($canManage)
            <button type="button" wire:click="openCreateModal" class="btn-primary flex-shrink-0">
                + Novo Comunicado
            </button>
        @endif
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Buscar comunicado..."
               class="form-input max-w-xs">
        <select wire:model.live="filterPriority" class="form-select w-44">
            <option value="">Todas as prioridades</option>
            <option value="normal">Normal</option>
            <option value="important">Importante</option>
            <option value="urgent">Urgente</option>
        </select>
    </div>

    {{-- Cards grid --}}
    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
        @forelse($announcements as $a)
            @php
                $priorityStyle = match($a->priority) {
                    'urgent'    => 'border-l-red-500 bg-red-50/30',
                    'important' => 'border-l-orange-400 bg-orange-50/20',
                    default     => 'border-l-blue-400',
                };
                $priorityBadge = match($a->priority) {
                    'urgent'    => 'bg-red-100 text-red-700',
                    'important' => 'bg-orange-100 text-orange-700',
                    default     => 'bg-blue-100 text-blue-700',
                };
                $priorityLabel = match($a->priority) {
                    'urgent'    => 'Urgente',
                    'important' => 'Importante',
                    default     => 'Normal',
                };
                $isRead = $a->isReadBy(auth()->id());
            @endphp
            <div class="card border-l-4 {{ $priorityStyle }} p-4 cursor-pointer hover:shadow-md transition-shadow relative"
                 wire:click="viewDetail({{ $a->id }})">

                @if($a->is_pinned)
                    <div class="absolute top-2 right-2">
                        <svg class="w-4 h-4 text-santander-red" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2h2a1 1 0 01.8 1.6L15 12v3a1 1 0 01-1 1h-3v4l-1-1-1 1v-4H6a1 1 0 01-1-1v-3L2.2 8.6A1 1 0 013 7h2V5z"/>
                        </svg>
                    </div>
                @endif

                <div class="flex items-center gap-2 mb-2">
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium {{ $priorityBadge }}">{{ $priorityLabel }}</span>
                    @if($a->audience === 'department' && $a->department)
                        <span class="text-[10px] text-neutral-muted">{{ $a->department->name }}</span>
                    @elseif($a->audience === 'team' && $a->team_filter)
                        <span class="text-[10px] text-neutral-muted">{{ $a->team_filter === 'run_the_bank' ? 'Run The Bank' : 'Change The Bank' }}</span>
                    @endif
                </div>

                <h3 class="font-semibold text-sm text-neutral-text mb-1 {{ !$isRead ? 'font-bold' : '' }}">
                    @if(!$isRead)<span class="inline-block w-2 h-2 rounded-full bg-santander-red mr-1"></span>@endif
                    {{ $a->title }}
                </h3>

                <p class="text-xs text-neutral-muted line-clamp-3">{{ \Str::limit(strip_tags($a->body), 150) }}</p>

                <div class="flex items-center justify-between mt-3 pt-2 border-t border-neutral-border/50">
                    <div class="flex items-center gap-1.5">
                        @php $initials = collect(explode(' ', $a->author->name ?? ''))->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->join(''); @endphp
                        <div class="w-5 h-5 rounded-full flex items-center justify-center text-[8px] font-bold text-white"
                             style="background:linear-gradient(135deg,#EC0000,#7B0000)">{{ $initials }}</div>
                        <span class="text-[10px] text-neutral-muted">{{ $a->author->name }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-[10px] text-neutral-muted">
                        <span>{{ $a->reads_count }} leitura{{ $a->reads_count !== 1 ? 's' : '' }}</span>
                        <span>{{ $a->published_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full card p-12 text-center text-neutral-muted">
                <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                </svg>
                Nenhum comunicado publicado.
            </div>
        @endforelse
    </div>

    @if($announcements->hasPages())
        <div class="mt-4">{{ $announcements->links() }}</div>
    @endif

    {{-- Detail Modal --}}
    @if($showDetail && $detailAnnouncement)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="closeDetail">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-2xl max-h-[90vh] overflow-y-auto" x-data x-transition>
                <div class="flex items-center justify-between p-6 border-b border-neutral-border sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="font-semibold text-neutral-text">{{ $detailAnnouncement->title }}</h3>
                        <p class="text-xs text-neutral-muted mt-0.5">
                            Por {{ $detailAnnouncement->author->name }} · {{ $detailAnnouncement->published_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($canManage)
                            <button type="button" wire:click="togglePin({{ $detailAnnouncement->id }})"
                                    title="{{ $detailAnnouncement->is_pinned ? 'Desfixar' : 'Fixar' }}"
                                    class="p-1.5 rounded text-neutral-muted hover:text-santander-red hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="{{ $detailAnnouncement->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h6a2 2 0 012 2v2h2a1 1 0 01.8 1.6L15 12v3a1 1 0 01-1 1h-3v4l-1-1-1 1v-4H6a1 1 0 01-1-1v-3L2.2 8.6A1 1 0 013 7h2V5z"/>
                                </svg>
                            </button>
                            <button type="button" wire:click="closeDetail" x-on:click="$nextTick(() => $wire.openEditModal({{ $detailAnnouncement->id }}))"
                                    title="Editar"
                                    class="p-1.5 rounded text-neutral-muted hover:text-santander-red hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414a2 2 0 01.586-1.414z"/>
                                </svg>
                            </button>
                        @endif
                        <button type="button" wire:click="closeDetail"
                                class="text-neutral-muted hover:text-neutral-text p-1 rounded">✕</button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="prose prose-sm max-w-none text-neutral-text">
                        {!! nl2br(e($detailAnnouncement->body)) !!}
                    </div>

                    @if($canManage && $detailAnnouncement->reads->count() > 0)
                        <div class="mt-6 pt-4 border-t border-neutral-border">
                            <p class="text-xs font-semibold text-neutral-muted uppercase mb-2">
                                Lido por ({{ $detailAnnouncement->reads->count() }})
                            </p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($detailAnnouncement->reads as $read)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-green-50 text-green-700 text-[10px]">
                                        {{ $read->user->name }} · {{ $read->read_at->format('d/m H:i') }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Create/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-lg max-h-[90vh] overflow-y-auto" x-data x-transition>

                <div class="flex items-center justify-between p-6 border-b border-neutral-border">
                    <h3 class="font-semibold text-neutral-text">{{ $isEditing ? 'Editar Comunicado' : 'Novo Comunicado' }}</h3>
                    <button type="button" wire:click="$set('showModal', false)"
                            class="text-neutral-muted hover:text-neutral-text p-1 rounded">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">

                    @if($tenants->count() > 0)
                        <div>
                            <label class="form-label">Tenant *</label>
                            <select wire:model="selected_tenant_id" class="form-select @error('selected_tenant_id') border-red-500 @enderror">
                                <option value="">Selecione o tenant</option>
                                @foreach($tenants as $tenant)
                                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                @endforeach
                            </select>
                            @error('selected_tenant_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label class="form-label">Título *</label>
                        <input type="text" wire:model="title"
                               class="form-input @error('title') border-red-500 @enderror"
                               placeholder="Título do comunicado">
                        @error('title') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="form-label">Mensagem *</label>
                        <textarea wire:model="body" rows="5"
                                  class="form-input @error('body') border-red-500 @enderror"
                                  placeholder="Conteúdo do comunicado..."></textarea>
                        @error('body') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Prioridade</label>
                            <select wire:model="priority" class="form-select">
                                <option value="normal">Normal</option>
                                <option value="important">Importante</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Público</label>
                            <select wire:model.live="audience" class="form-select">
                                <option value="all">Todos</option>
                                <option value="department">Departamento</option>
                                <option value="team">Equipe</option>
                            </select>
                        </div>
                    </div>

                    @if($audience === 'department')
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
                    @endif

                    @if($audience === 'team')
                        <div>
                            <label class="form-label">Equipe *</label>
                            <select wire:model="team_filter"
                                    class="form-select @error('team_filter') border-red-500 @enderror">
                                <option value="">— Selecione —</option>
                                <option value="run_the_bank">Run The Bank</option>
                                <option value="change_the_bank">Change The Bank</option>
                            </select>
                            @error('team_filter') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Publicar em *</label>
                            <input type="datetime-local" wire:model="published_at"
                                   class="form-input @error('published_at') border-red-500 @enderror">
                            @error('published_at') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Expira em</label>
                            <input type="datetime-local" wire:model="expires_at" class="form-input">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_pinned" id="is_pinned" class="rounded border-neutral-border text-santander-red focus:ring-santander-red">
                        <label for="is_pinned" class="text-sm text-neutral-text">Fixar no topo do mural</label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Cancelar</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $isEditing ? 'Salvar' : 'Publicar' }}</span>
                            <span wire:loading>Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
