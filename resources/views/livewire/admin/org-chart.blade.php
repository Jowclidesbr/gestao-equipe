<div class="p-6 space-y-6" x-data="{ expanded: {} }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">Organograma</h1>
            <p class="text-sm text-neutral-muted mt-0.5">Visualize a estrutura hierárquica da organização</p>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="$set('viewMode', 'department')"
                    class="{{ $viewMode === 'department' ? 'btn-primary' : 'btn-secondary' }} text-sm py-1.5 px-4">
                Por Departamento
            </button>
            <button type="button" wire:click="$set('viewMode', 'manager')"
                    class="{{ $viewMode === 'manager' ? 'btn-primary' : 'btn-secondary' }} text-sm py-1.5 px-4">
                Por Gestor
            </button>
        </div>
    </div>

    {{-- Tree --}}
    <div class="overflow-x-auto pb-8">
        @if(count($tree) === 0)
            <div class="card p-12 text-center text-neutral-muted">
                <svg class="w-10 h-10 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/>
                </svg>
                Nenhum dado encontrado para exibir o organograma.
            </div>
        @else
            <div class="org-tree">
                @foreach($tree as $node)
                    @include('livewire.admin.partials.org-node', ['node' => $node, 'depth' => 0])
                @endforeach
            </div>
        @endif
    </div>

</div>
