{{-- Profile Edit --}}
<div class="max-w-2xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h2 class="text-lg font-bold text-neutral-text">Meu Perfil</h2>
        <p class="text-sm text-neutral-muted">Gerencie suas informações pessoais e senha</p>
    </div>

    {{-- Profile Info --}}
    <div class="card">
        <h3 class="font-semibold text-neutral-text mb-4">Informações Pessoais</h3>

        <form wire:submit="updateProfile" class="space-y-4">
            {{-- Avatar --}}
            <div class="flex items-center gap-4">
                <img src="{{ auth()->user()->avatar_url }}" alt=""
                     class="w-16 h-16 rounded-full object-cover border-2 border-gray-200">
                <div>
                    <input type="file" wire:model="avatar" accept="image/*"
                           class="text-sm text-neutral-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-lg
                                  file:border-0 file:text-xs file:font-semibold file:bg-neutral-bg
                                  file:text-neutral-text hover:file:bg-gray-200 cursor-pointer">
                    @error('avatar') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    <p class="text-[11px] text-neutral-muted mt-1">JPG, PNG. Máximo 2MB.</p>
                </div>
            </div>

            {{-- Name --}}
            <div>
                <label class="block text-xs font-semibold text-neutral-muted uppercase tracking-wider mb-1.5">Nome</label>
                <input type="text" wire:model="name" class="form-input w-full">
                @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block text-xs font-semibold text-neutral-muted uppercase tracking-wider mb-1.5">E-mail</label>
                <input type="email" wire:model="email" class="form-input w-full">
                @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                    </svg>
                    Salvar Perfil
                </button>
            </div>
        </form>
    </div>

    {{-- Password Change --}}
    <div class="card">
        <h3 class="font-semibold text-neutral-text mb-4">Alterar Senha</h3>

        <form wire:submit="updatePassword" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-muted uppercase tracking-wider mb-1.5">Senha Atual</label>
                <input type="password" wire:model="current_password" class="form-input w-full" autocomplete="current-password">
                @error('current_password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-muted uppercase tracking-wider mb-1.5">Nova Senha</label>
                <input type="password" wire:model="password" class="form-input w-full" autocomplete="new-password" placeholder="Mínimo 8 caracteres">
                @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-neutral-muted uppercase tracking-wider mb-1.5">Confirmar Nova Senha</label>
                <input type="password" wire:model="password_confirmation" class="form-input w-full" autocomplete="new-password">
            </div>

            <div class="flex justify-end">
                <button type="submit" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Alterar Senha
                </button>
            </div>
        </form>
    </div>

    {{-- Account Info --}}
    <div class="card">
        <h3 class="font-semibold text-neutral-text mb-4">Informações da Conta</h3>
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <p class="text-xs text-neutral-muted uppercase tracking-wider">Papel</p>
                <p class="font-medium mt-0.5">{{ str_replace('_', ' ', ucfirst(auth()->user()->getRoleNames()->first() ?? '')) }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-muted uppercase tracking-wider">Tenant</p>
                <p class="font-medium mt-0.5">{{ auth()->user()->tenant?->name ?? 'Global' }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-muted uppercase tracking-wider">Membro desde</p>
                <p class="font-medium mt-0.5">{{ auth()->user()->created_at->format('d/m/Y') }}</p>
            </div>
            <div>
                <p class="text-xs text-neutral-muted uppercase tracking-wider">Último acesso</p>
                <p class="font-medium mt-0.5">{{ auth()->user()->updated_at->diffForHumans() }}</p>
            </div>
        </div>
    </div>

</div>
