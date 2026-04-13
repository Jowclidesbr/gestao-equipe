<x-layouts.auth title="Redefinir Senha">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-neutral-text tracking-tight">Nova senha</h2>
        <p class="text-neutral-muted text-sm mt-1">Defina sua nova senha de acesso</p>
    </div>

    @if($errors->any())
        <div class="alert-danger mb-6">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div>
            <label for="email" class="block text-xs font-semibold text-neutral-muted uppercase tracking-wider mb-1.5">
                E-mail
            </label>
            <input type="email" id="email" name="email" value="{{ old('email', $email) }}"
                   autocomplete="email" required
                   class="w-full px-4 py-2.5 text-sm border rounded-lg bg-white text-neutral-text
                          focus:outline-none focus:ring-2 focus:ring-santander-red focus:border-transparent
                          border-neutral-border">
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-neutral-muted uppercase tracking-wider mb-1.5">
                Nova Senha
            </label>
            <input type="password" id="password" name="password" required
                   autocomplete="new-password" placeholder="Mínimo 8 caracteres"
                   class="w-full px-4 py-2.5 text-sm border rounded-lg bg-white text-neutral-text
                          focus:outline-none focus:ring-2 focus:ring-santander-red focus:border-transparent
                          border-neutral-border">
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-neutral-muted uppercase tracking-wider mb-1.5">
                Confirmar Senha
            </label>
            <input type="password" id="password_confirmation" name="password_confirmation" required
                   autocomplete="new-password"
                   class="w-full px-4 py-2.5 text-sm border rounded-lg bg-white text-neutral-text
                          focus:outline-none focus:ring-2 focus:ring-santander-red focus:border-transparent
                          border-neutral-border">
        </div>

        <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold
                       text-white bg-santander-red hover:bg-santander-red-dark
                       transition-all focus:outline-none focus:ring-2 focus:ring-santander-red focus:ring-offset-2
                       shadow-sm hover:shadow-md active:scale-[0.99]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Redefinir Senha
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-sm text-santander-red hover:text-santander-red-dark font-medium">
            ← Voltar ao login
        </a>
    </div>

</x-layouts.auth>
