{{-- Department List --}}
<div class="space-y-4">

    {{-- Header --}}
    <div class="card">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1 flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por nome ou código..."
                       class="form-input max-w-xs">
            </div>
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                <button type="button" wire:click="openCreateModal" class="btn-primary flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Novo Departamento
                </button>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Departamento</th>
                    <th>Código</th>
                    <th>Departamento Pai</th>
                    <th>Colaboradores</th>
                    <th>Status</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departments as $dept)
                    <tr wire:key="{{ $dept->id }}">
                        <td>
                            <div>
                                <p class="font-medium">{{ $dept->name }}</p>
                                @if($dept->description)
                                    <p class="text-xs text-neutral-muted truncate max-w-xs">{{ $dept->description }}</p>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($dept->code)
                                <span class="font-mono text-xs bg-neutral-bg border border-neutral-border px-2 py-0.5 rounded">
                                    {{ $dept->code }}
                                </span>
                            @else
                                <span class="text-neutral-muted">—</span>
                            @endif
                        </td>
                        <td class="text-neutral-muted">{{ $dept->parent?->name ?? '—' }}</td>
                        <td>
                            <span class="font-semibold text-neutral-text">
                                {{ $dept->employees()->count() }}
                            </span>
                        </td>
                        <td>
                            @if($dept->is_active)
                                <span class="badge-active">Ativo</span>
                            @else
                                <span class="badge-inactive">Inativo</span>
                            @endif
                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                    <button type="button" wire:click="openEditModal({{ $dept->id }})"
                                            class="btn-secondary py-1 px-2 text-xs">
                                        Editar
                                    </button>
                                    <button type="button" wire:click="toggleStatus({{ $dept->id }})"
                                            wire:confirm="{{ $dept->is_active ? 'Desativar departamento?' : 'Ativar departamento?' }}"
                                            class="btn-secondary py-1 px-2 text-xs {{ $dept->is_active ? 'text-amber-600' : 'text-green-600' }}">
                                        {{ $dept->is_active ? 'Desativar' : 'Ativar' }}
                                    </button>
                                    <button type="button" wire:click="delete({{ $dept->id }})"
                                            wire:confirm="Excluir o departamento '{{ $dept->name }}'? Esta ação não pode ser desfeita."
                                            class="btn-secondary py-1 px-2 text-xs text-red-600 hover:border-red-300">
                                        Excluir
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-neutral-muted">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            @if($search)
                                Nenhum departamento encontrado para "<strong>{{ $search }}</strong>".
                            @else
                                Nenhum departamento cadastrado ainda.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $departments->links() }}

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-lg max-h-screen overflow-y-auto"
                 x-data x-transition>
                <div class="flex items-center justify-between p-6 border-b border-neutral-border">
                    <h3 class="font-semibold text-neutral-text">
                        {{ $isEditing ? 'Editar Departamento' : 'Novo Departamento' }}
                    </h3>
                    <button type="button" wire:click="$set('showModal', false)"
                            class="text-neutral-muted hover:text-neutral-text p-1 rounded">✕</button>
                </div>

                <form wire:submit="save" class="p-6 space-y-4">

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

                    {{-- Name --}}
                    <div>
                        <label class="form-label">Nome *</label>
                        <input type="text" wire:model="name"
                               class="form-input @error('name') border-red-500 @enderror"
                               placeholder="ex: Recursos Humanos">
                        @error('name') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        {{-- Code --}}
                        <div>
                            <label class="form-label">Código</label>
                            <input type="text" wire:model="code"
                                   class="form-input font-mono @error('code') border-red-500 @enderror"
                                   placeholder="ex: RH">
                            @error('code') <p class="form-error">{{ $message }}</p> @enderror
                        </div>

                        {{-- Parent --}}
                        <div>
                            <label class="form-label">Departamento Pai</label>
                            <select wire:model="parent_id"
                                    class="form-select @error('parent_id') border-red-500 @enderror">
                                <option value="0">Nenhum (raiz)</option>
                                @foreach($parentOptions as $opt)
                                    <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Description --}}
                    <div>
                        <label class="form-label">Descrição</label>
                        <textarea wire:model="description" rows="3"
                                  class="form-input @error('description') border-red-500 @enderror"
                                  placeholder="Responsabilidades e escopo do departamento..."></textarea>
                        @error('description') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    {{-- Status --}}
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="is_active" id="is_active_check"
                               class="w-4 h-4 rounded accent-santander-red cursor-pointer">
                        <label for="is_active_check" class="form-label mb-0 cursor-pointer">
                            Departamento ativo
                        </label>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $isEditing ? 'Salvar alterações' : 'Criar departamento' }}</span>
                            <span wire:loading>Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
