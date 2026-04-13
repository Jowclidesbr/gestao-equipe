@php
    $hasChildren = !empty($node['children']);
    $nodeId = ($node['type'] ?? 'node') . '-' . $node['id'];
    $isDept = ($node['type'] ?? '') === 'department';
@endphp

<div class="org-node ml-{{ $depth > 0 ? '6' : '0' }}">
    {{-- Connector line --}}
    @if($depth > 0)
        <div class="org-connector"></div>
    @endif

    {{-- Card --}}
    <div class="flex items-start gap-2 mb-1">
        @if($hasChildren)
            <button type="button"
                    @click="expanded['{{ $nodeId }}'] = !expanded['{{ $nodeId }}']"
                    class="mt-2.5 w-5 h-5 flex-shrink-0 flex items-center justify-center rounded border border-neutral-border bg-white text-neutral-muted hover:text-santander-red hover:border-santander-red transition-colors text-xs">
                <span x-text="expanded['{{ $nodeId }}'] === false ? '+' : '−'">−</span>
            </button>
        @else
            <div class="w-5 flex-shrink-0"></div>
        @endif

        @if($isDept)
            {{-- Department card --}}
            <div class="card p-3 min-w-[220px] border-l-4 border-l-santander-red">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-santander-red" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-neutral-text truncate">{{ $node['name'] }}</p>
                        <p class="text-xs text-neutral-muted">
                            @if($node['code'])<span class="font-mono">{{ $node['code'] }}</span> · @endif
                            {{ $node['count'] }} colaborador{{ $node['count'] !== 1 ? 'es' : '' }}
                        </p>
                    </div>
                </div>

                {{-- Employee chips --}}
                @if(!empty($node['employees']))
                    <div class="mt-2 pt-2 border-t border-neutral-border space-y-1">
                        @foreach($node['employees'] as $emp)
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full flex-shrink-0 flex items-center justify-center text-[9px] font-bold text-white"
                                     style="background:linear-gradient(135deg,#EC0000,#7B0000);">{{ $emp['initials'] }}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-neutral-text truncate">{{ $emp['name'] }}</p>
                                    <p class="text-[10px] text-neutral-muted truncate">{{ $emp['position'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @else
            {{-- Employee card --}}
            <div class="card p-3 min-w-[220px] border-l-4 border-l-blue-500">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center text-xs font-bold text-white"
                         style="background:linear-gradient(135deg,#EC0000,#7B0000);">{{ $node['initials'] }}</div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-sm text-neutral-text truncate">{{ $node['name'] }}</p>
                        <p class="text-xs text-neutral-muted truncate">{{ $node['position'] }}</p>
                        <p class="text-[10px] text-neutral-muted truncate">{{ $node['department'] }}</p>
                    </div>
                </div>
                @if(!empty($node['children']))
                    <p class="text-[10px] text-neutral-muted mt-1.5 pt-1.5 border-t border-neutral-border">
                        {{ count($node['children']) }} subordinado{{ count($node['children']) !== 1 ? 's' : '' }}
                    </p>
                @endif
            </div>
        @endif
    </div>

    {{-- Children --}}
    @if($hasChildren)
        <div x-show="expanded['{{ $nodeId }}'] !== false"
             x-collapse
             class="pl-2 border-l-2 border-neutral-border/50 ml-2.5">
            @foreach($node['children'] as $child)
                @include('livewire.admin.partials.org-node', ['node' => $child, 'depth' => $depth + 1])
            @endforeach
        </div>
    @endif
</div>
