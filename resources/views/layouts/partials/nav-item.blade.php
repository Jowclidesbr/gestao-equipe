@php
    $isActive = request()->routeIs($match);
    $count    = $badge ?? $pendingCount ?? 0;
@endphp

<div class="relative group">
    <a href="{{ route($route) }}"
       class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg my-0.5 transition-all duration-150
              {{ $isActive ? 'nav-item-active bg-white/[0.08] text-white font-medium' : 'text-white/50 hover:text-white hover:bg-white/[0.05]' }}">

        <svg class="w-4 h-4 flex-shrink-0 {{ $isActive ? 'text-white' : 'text-white/50 group-hover:text-white/80' }}"
             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
        </svg>

        <span x-show="!open" x-cloak class="flex-1 truncate text-[13px]">{{ $label }}</span>

        @if($count > 0)
            <span x-show="!open" x-cloak
                  class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-600 text-white leading-none">
                {{ $count }}
            </span>
            <span x-show="open" x-cloak
                  class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-red-600 ring-2 ring-neutral-sidebar">
            </span>
        @endif
    </a>

    <div x-show="open" x-cloak
         class="nav-tooltip absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50
                bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg shadow-lg whitespace-nowrap">
        {{ $label }}
        @if($count > 0)
            <span class="ml-1.5 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full">{{ $count }}</span>
        @endif
    </div>
</div>
