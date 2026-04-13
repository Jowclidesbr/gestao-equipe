<x-layouts.auth title="Esqueci minha senha">

    <div class="mb-8">
        <h2 class="text-2xl font-bold text-neutral-text tracking-tight">Redefinir senha</h2>
        <p class="text-neutral-muted text-sm mt-1">Informe seu e-mail para receber o link de redefinição</p>
    </div>

    @if($errors->any())
        <div class="alert-danger mb-6">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
            </svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    @if(session('status'))
        <div class="alert-success mb-6">
            <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <label for="email" class="block text-xs font-semibold text-neutral-muted uppercase tracking-wider mb-1.5">
                E-mail corporativo
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-neutral-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <input type="email" id="email" name="email" value="{{ old('email') }}"
                       autocomplete="email" autofocus required
                       placeholder="nome@empresa.com.br"
                       class="w-full pl-10 pr-4 py-2.5 text-sm border rounded-lg bg-white text-neutral-text
                              placeholder-neutral-muted transition
                              focus:outline-none focus:ring-2 focus:ring-santander-red focus:border-transparent
                              border-neutral-border">
            </div>
        </div>

        <button type="submit"
                class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-lg text-sm font-semibold
                       text-white bg-santander-red hover:bg-santander-red-dark
                       transition-all focus:outline-none focus:ring-2 focus:ring-santander-red focus:ring-offset-2
                       shadow-sm hover:shadow-md active:scale-[0.99]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Enviar link de redefinição
        </button>
    </form>

    <div class="mt-6 text-center">
        <a href="{{ route('login') }}" class="text-sm text-santander-red hover:text-santander-red-dark font-medium">
            ← Voltar ao login
        </a>
    </div>

    <div class="mt-8 pt-6 border-t border-neutral-border">
        <p class="text-center text-xs text-neutral-muted">
            © {{ date('Y') }} Gestão de Equipe · Uso interno e confidencial
        </p>
    </div>

</x-layouts.auth>
