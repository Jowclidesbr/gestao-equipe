{{-- Placeholder view for modules not yet implemented --}}
<div class="card flex flex-col items-center justify-center py-20 text-center">
    <div class="w-16 h-16 bg-neutral-bg rounded-2xl flex items-center justify-center mb-4">
        <svg class="w-8 h-8 text-neutral-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
        </svg>
    </div>
    <h2 class="text-lg font-semibold text-neutral-text">{{ $title ?? 'Módulo em Desenvolvimento' }}</h2>
    <p class="text-sm text-neutral-muted mt-1 max-w-xs">
        Este módulo será implementado em breve. Retorne a área anterior no menu lateral.
    </p>
    <a href="{{ url()->previous() }}" class="btn-secondary mt-6">← Voltar</a>
</div>
