<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">Documentos</h1>
            <p class="text-sm text-neutral-muted mt-0.5">Gerencie documentos dos colaboradores</p>
        </div>
        <button type="button" wire:click="openUploadModal(0)" class="btn-primary flex-shrink-0">
            + Enviar Documento
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-3">
        <input type="text" wire:model.live.debounce.300ms="search"
               placeholder="Buscar por título..."
               class="form-input max-w-xs">

        <select wire:model.live="filterType" class="form-select w-44">
            <option value="">Todos os tipos</option>
            <option value="contract">Contrato</option>
            <option value="rg">RG</option>
            <option value="cpf">CPF</option>
            <option value="diploma">Diploma</option>
            <option value="medical">Atestado Médico</option>
            <option value="certification">Certificação</option>
            <option value="other">Outro</option>
        </select>

        <select wire:model.live="filterEmployee" class="form-select w-48">
            <option value="">Todos os colaboradores</option>
            @foreach($employees as $emp)
                <option value="{{ $emp->id }}">{{ $emp->user->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-neutral-border bg-neutral-bg">
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Documento</th>
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs">Colaborador</th>
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs hidden md:table-cell">Tipo</th>
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs hidden lg:table-cell">Tamanho</th>
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs hidden lg:table-cell">Validade</th>
                    <th class="text-left px-4 py-3 font-semibold text-neutral-muted uppercase tracking-wide text-xs hidden md:table-cell">Enviado em</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-border">
                @forelse($documents as $doc)
                    @php
                        $typeLabel = match($doc->type) {
                            'contract'      => 'Contrato',
                            'rg'            => 'RG',
                            'cpf'           => 'CPF',
                            'diploma'       => 'Diploma',
                            'medical'       => 'Atestado',
                            'certification' => 'Certificação',
                            default         => 'Outro',
                        };
                        $typeColor = match($doc->type) {
                            'contract' => 'bg-blue-100 text-blue-700',
                            'medical'  => 'bg-red-100 text-red-700',
                            'diploma','certification' => 'bg-green-100 text-green-700',
                            default    => 'bg-gray-100 text-gray-600',
                        };
                        $isExpired  = $doc->expires_at && $doc->expires_at->isPast();
                        $expiresSoon = $doc->expires_at && !$isExpired && $doc->expires_at->diffInDays(now()) <= 30;
                    @endphp
                    <tr class="hover:bg-neutral-bg/60 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-neutral-muted flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="font-medium text-neutral-text">{{ $doc->title }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-neutral-text">{{ $doc->employee->user->name ?? '—' }}</td>
                        <td class="px-4 py-3 hidden md:table-cell">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">{{ $typeLabel }}</span>
                        </td>
                        <td class="px-4 py-3 text-neutral-muted text-xs hidden lg:table-cell">
                            @if($doc->file_size)
                                {{ number_format($doc->file_size / 1024, 0) }} KB
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs hidden lg:table-cell">
                            @if($doc->expires_at)
                                <span class="{{ $isExpired ? 'text-red-600 font-medium' : ($expiresSoon ? 'text-orange-600 font-medium' : 'text-neutral-muted') }}">
                                    {{ $doc->expires_at->format('d/m/Y') }}
                                    @if($isExpired) (vencido) @elseif($expiresSoon) (vence em breve) @endif
                                </span>
                            @else
                                <span class="text-neutral-muted">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-neutral-muted text-xs hidden md:table-cell">
                            {{ $doc->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ asset('storage/' . $doc->file_path) }}" target="_blank"
                                   title="Baixar"
                                   class="p-1.5 rounded text-neutral-muted hover:text-santander-red hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </a>
                                <button type="button" wire:click="openEditModal({{ $doc->id }})"
                                        title="Editar"
                                        class="p-1.5 rounded text-neutral-muted hover:text-santander-red hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                              d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 012.828 2.828L11.828 15.828a2 2 0 01-1.414.586H8v-2.414a2 2 0 01.586-1.414z"/>
                                    </svg>
                                </button>
                                <button type="button" wire:click="deleteDocument({{ $doc->id }})"
                                        wire:confirm="Excluir este documento?"
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
                        <td colspan="7" class="px-4 py-12 text-center text-neutral-muted">
                            <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Nenhum documento encontrado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($documents->hasPages())
        <div class="mt-4">{{ $documents->links() }}</div>
    @endif

    {{-- Upload/Edit Modal --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-md" x-data x-transition>

                <div class="flex items-center justify-between p-6 border-b border-neutral-border">
                    <h3 class="font-semibold text-neutral-text">{{ $isEditing ? 'Editar Documento' : 'Enviar Documento' }}</h3>
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
                        <label class="form-label">Título *</label>
                        <input type="text" wire:model="docTitle"
                               class="form-input @error('docTitle') border-red-500 @enderror"
                               placeholder="ex: Contrato de Trabalho 2024">
                        @error('docTitle') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="form-label">Tipo *</label>
                            <select wire:model="docType" class="form-select">
                                <option value="contract">Contrato</option>
                                <option value="rg">RG</option>
                                <option value="cpf">CPF</option>
                                <option value="diploma">Diploma</option>
                                <option value="medical">Atestado Médico</option>
                                <option value="certification">Certificação</option>
                                <option value="other">Outro</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Validade</label>
                            <input type="date" wire:model="expires_at" class="form-input">
                        </div>
                    </div>

                    <div>
                        <label class="form-label">Arquivo {{ $isEditing ? '(opcional)' : '*' }}</label>
                        <input type="file" wire:model="file"
                               class="form-input text-sm @error('file') border-red-500 @enderror">
                        <p class="text-[10px] text-neutral-muted mt-1">PDF, JPG, PNG, DOC, DOCX — máx 10MB</p>
                        @error('file') <p class="form-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="btn-secondary">Cancelar</button>
                        <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                            <span wire:loading.remove>{{ $isEditing ? 'Salvar' : 'Enviar' }}</span>
                            <span wire:loading>Enviando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
