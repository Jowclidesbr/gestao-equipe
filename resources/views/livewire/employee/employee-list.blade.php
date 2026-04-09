{{-- Employee List --}}
<div class="space-y-4">

    {{-- Header --}}
    <div class="card">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1 flex items-center gap-2">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por nome ou e-mail..."
                       class="form-input max-w-xs">
                <select wire:model.live="filterDept" class="form-select text-sm">
                    <option value="">Todos os depto.</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
                <select wire:model.live="filterStatus" class="form-select text-sm">
                    <option value="active">Ativos</option>
                    <option value="inactive">Inativos</option>
                    <option value="on_leave">De licença</option>
                    <option value="terminated">Desligados</option>
                    <option value="">Todos</option>
                </select>
            </div>
            @can('create', \App\Models\Employee::class)
                <button type="button" wire:click="openCreateModal" class="btn-primary flex-shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Novo Colaborador
                </button>
            @endcan
        </div>
    </div>

    {{-- Table --}}
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Cargo</th>
                    <th>Departamento</th>
                    <th>Tipo</th>
                    <th>Admissão</th>
                    <th>Status</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                    <tr wire:key="{{ $emp->id }}">
                        <td>
                            <div class="flex items-center gap-2">
                                <img src="{{ $emp->user->avatar_url }}" alt=""
                                     class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                <div>
                                    <p class="font-medium">{{ $emp->user->name }}</p>
                                    <p class="text-xs text-neutral-muted">{{ $emp->user->email }}</p>
                                    @if($emp->presence_days)
                                        <p class="text-xs text-neutral-muted mt-0.5">
                                            {{ collect($emp->presence_days)->map(fn($d) => match($d) {
                                                'monday'=>'Seg','tuesday'=>'Ter','wednesday'=>'Qua',
                                                'thursday'=>'Qui','friday'=>'Sex','saturday'=>'Sáb','sunday'=>'Dom',
                                                default=>ucfirst($d)
                                            })->join(' · ') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $emp->jobPosition->title ?? '—' }}</td>
                        <td>{{ $emp->department->name ?? '—' }}</td>
                        <td class="uppercase text-xs font-semibold">{{ $emp->contract_type }}</td>
                        <td class="text-neutral-muted whitespace-nowrap">{{ $emp->admission_date->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $badgeMap = [
                                    'active'   => 'badge-active',
                                    'inactive' => 'badge-inactive',
                                    'on_leave' => 'badge-pending',
                                    'terminated' => 'badge-rejected',
                                ];
                                $labelMap = [
                                    'active'   => 'Ativo',
                                    'inactive' => 'Inativo',
                                    'on_leave' => 'Licença',
                                    'terminated' => 'Desligado',
                                ];
                            @endphp
                            <span class="{{ $badgeMap[$emp->status] ?? 'badge' }}">
                                {{ $labelMap[$emp->status] ?? $emp->status }}
                            </span>
                        </td>
                        <td class="text-right">
                            @can('update', $emp)
                                <button type="button" wire:click="openEditModal({{ $emp->id }})"
                                        class="btn-secondary py-1 px-2 text-xs">
                                    Editar
                                </button>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-neutral-muted">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            Nenhum colaborador encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $employees->links() }}

    {{-- Create / Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-xl max-h-screen overflow-y-auto"
                 x-transition>
                <div class="flex items-center justify-between p-6 border-b border-neutral-border">
                    <h3 class="font-semibold text-neutral-text">
                        {{ $isEditing ? 'Editar Colaborador' : 'Novo Colaborador' }}
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

                    {{-- Photo upload --}}
                    <div x-data="{ previewUrl: null }" class="flex items-center gap-4">
                        <div class="flex-shrink-0">
                            <div x-show="!previewUrl">
                                <img src="{{ $isEditing && $editingId ? (\App\Models\Employee::find($editingId)?->user?->avatar_url ?? '') : '' }}"
                                     class="w-16 h-16 rounded-full object-cover border-2 border-neutral-border bg-neutral-bg"
                                     alt="Foto atual"
                                     x-show="!previewUrl"
                                     onerror="this.src='https://ui-avatars.com/api/?name=Novo&color=EC0000&background=FFE5E5&bold=true'">
                            </div>
                            <img x-show="previewUrl" :src="previewUrl"
                                 class="w-16 h-16 rounded-full object-cover border-2 border-santander-red/40" alt="Preview">
                        </div>
                        <div class="flex-1 min-w-0">
                            <label class="form-label">Foto do colaborador</label>
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <span class="btn-secondary text-xs py-1.5 px-3 group-hover:border-santander-red group-hover:text-santander-red transition-colors">
                                    Escolher arquivo
                                </span>
                                <span class="text-xs text-neutral-muted truncate" x-text="previewUrl ? 'Imagem selecionada' : 'Nenhum arquivo'"></span>
                                <input type="file" wire:model="photo" accept="image/*" class="sr-only"
                                       x-on:change="
                                           const f = $event.target.files[0];
                                           if (f) { const r = new FileReader(); r.onload = e => previewUrl = e.target.result; r.readAsDataURL(f); }
                                       ">
                            </label>
                            <p class="text-xs text-neutral-muted mt-1">JPG, PNG, GIF — máx. 2 MB</p>
                            @error('photo') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="form-label">Nome completo *</label>
                            <input type="text" wire:model="name" class="form-input @error('name') border-red-500 @enderror">
                            @error('name') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="form-label">E-mail corporativo *</label>
                            <input type="email" wire:model="email" class="form-input @error('email') border-red-500 @enderror">
                            @error('email') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Matrícula</label>
                            <input type="text" wire:model="employee_code" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">CPF</label>
                            <input type="text" wire:model="cpf" placeholder="000.000.000-00" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" wire:model="birth_date" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Admissão *</label>
                            <input type="date" wire:model="admission_date"
                                   class="form-input @error('admission_date') border-red-500 @enderror">
                            @error('admission_date') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Departamento *</label>
                            <select wire:model="department_id" class="form-select @error('department_id') border-red-500 @enderror">
                                <option value="0">Selecionar...</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                @endforeach
                            </select>
                            @error('department_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Cargo *</label>
                            <select wire:model="job_position_id" class="form-select @error('job_position_id') border-red-500 @enderror">
                                <option value="0">Selecionar...</option>
                                @foreach($jobPositions as $jp)
                                    <option value="{{ $jp->id }}">{{ $jp->title }}</option>
                                @endforeach
                            </select>
                            @error('job_position_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="form-label">Tipo de Contrato *</label>
                            <select wire:model="contract_type" class="form-select">
                                <option value="clt">CLT</option>
                                <option value="pj">PJ</option>
                                <option value="intern">Estágio</option>
                                <option value="temporary">Temporário</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Modalidade *</label>
                            <select wire:model.live="work_mode" class="form-select">
                                <option value="onsite">Presencial</option>
                                <option value="remote">Remoto</option>
                                <option value="hybrid">Híbrido</option>
                            </select>
                        </div>

                        {{-- Presence days (shown for onsite / hybrid) --}}
                        @if(in_array($work_mode, ['onsite', 'hybrid']))
                        <div class="col-span-2">
                            <label class="form-label">Dias Presenciais</label>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @foreach([
                                    'monday'    => 'Seg',
                                    'tuesday'   => 'Ter',
                                    'wednesday' => 'Qua',
                                    'thursday'  => 'Qui',
                                    'friday'    => 'Sex',
                                    'saturday'  => 'Sáb',
                                    'sunday'    => 'Dom',
                                ] as $dayKey => $dayLabel)
                                    <label class="inline-flex items-center gap-1.5 cursor-pointer select-none
                                                  px-3 py-1.5 rounded-full border text-sm font-medium transition-colors
                                                  {{ in_array($dayKey, $presence_days)
                                                     ? 'bg-santander-red text-white border-santander-red'
                                                     : 'bg-white text-neutral-text border-neutral-border hover:border-santander-red' }}">
                                        <input type="checkbox" wire:model.live="presence_days"
                                               value="{{ $dayKey }}" class="sr-only">
                                        {{ $dayLabel }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                        @endif
                        @if($isEditing)
                        <div class="col-span-2">
                            <label class="form-label">Status</label>
                            <select wire:model="status" class="form-select">
                                <option value="active">Ativo</option>
                                <option value="inactive">Inativo</option>
                                <option value="on_leave">De Licença</option>
                                <option value="terminated">Desligado</option>
                            </select>
                        </div>
                        @endif
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">
                            Cancelar
                        </button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $isEditing ? 'Salvar alterações' : 'Criar colaborador' }}</span>
                            <span wire:loading>Salvando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
