{{-- Vacation Request Form (Employee) --}}
<div class="max-w-xl">
    <div class="card space-y-5">

        {{-- Balance Info --}}
        <div class="flex items-center justify-between bg-neutral-bg rounded-lg px-4 py-3">
            <div>
                <p class="text-xs text-neutral-muted">Saldo disponível</p>
                <p class="text-2xl font-bold text-neutral-text">{{ $balance }} <span class="text-base font-normal">dias</span></p>
            </div>
            <div class="w-12 h-12 bg-santander-red/10 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-santander-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        </div>

        <form wire:submit="submit" class="space-y-4">

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Data de início <span class="text-santander-red">*</span></label>
                    <input type="date" wire:model.live="start_date"
                           min="{{ now()->addDays(30)->format('Y-m-d') }}"
                           class="form-input @error('start_date') border-red-500 @enderror">
                    @error('start_date') <p class="form-error">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="form-label">Data de retorno <span class="text-santander-red">*</span></label>
                    <input type="date" wire:model.live="end_date"
                           min="{{ $start_date ?: now()->addDays(30)->format('Y-m-d') }}"
                           class="form-input @error('end_date') border-red-500 @enderror">
                    @error('end_date') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Days preview --}}
            @if($daysRequested > 0)
                <div class="alert-info">
                    <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>{{ $daysRequested }} dias</strong> de férias selecionados.
                    Saldo restante após aprovação: <strong>{{ max(0, $balance - $daysRequested - $sell_days) }} dias</strong>.</span>
                </div>
            @endif

            <div>
                <label class="form-label">
                    Abono pecuniário (dias a converter em dinheiro)
                    <span class="text-xs text-neutral-muted font-normal">— máx. 10 dias</span>
                </label>
                <input type="number" wire:model.live="sell_days" min="0" max="10"
                       class="form-input w-32 @error('sell_days') border-red-500 @enderror">
                @error('sell_days') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="form-label">Observações (opcional)</label>
                <textarea wire:model="notes" rows="3" placeholder="Alguma informação adicional..."
                          class="form-input resize-none @error('notes') border-red-500 @enderror"></textarea>
                @error('notes') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            @error('balance') <div class="alert-danger">{{ $message }}</div> @enderror

            <div class="flex items-center gap-3 pt-2">
                <button type="submit" class="btn-primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>Enviar Solicitação</span>
                    <span wire:loading>Enviando...</span>
                </button>
                <a href="{{ route('employee.vacation.index') }}" class="btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    {{-- CLT rules hint --}}
    <div class="mt-4 alert-info text-xs">
        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
        </svg>
        <span>
            <strong>Regras CLT:</strong> Período mínimo de 14 dias corridos. Solicitação com antecedência mínima de 30 dias.
            O abono pecuniário converte até 10 dias em remuneração em dinheiro.
        </span>
    </div>
</div>
