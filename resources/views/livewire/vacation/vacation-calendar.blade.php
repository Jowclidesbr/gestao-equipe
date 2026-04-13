<div class="p-6 space-y-6"
     x-data="vacationCalendar()"
     x-init="init()">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-neutral-text">Calendário de Férias</h1>
            <p class="text-sm text-neutral-muted mt-0.5">Visualização da escala de férias por período</p>
        </div>
        <div class="flex items-center gap-2">
            <select wire:model.live="filterDept" class="input-field text-sm !py-1.5 !w-auto">
                <option value="">Todos os departamentos</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus" class="input-field text-sm !py-1.5 !w-auto">
                <option value="">Aprovadas + Pendentes</option>
                <option value="approved">Aprovadas</option>
                <option value="pending">Pendentes</option>
                <option value="rejected">Rejeitadas</option>
                <option value="cancelled">Canceladas</option>
            </select>
        </div>
    </div>

    {{-- Legend --}}
    <div class="flex flex-wrap gap-4 text-xs">
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-green-600"></span> Aprovada</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-amber-500"></span> Pendente</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-red-500"></span> Rejeitada</span>
        <span class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-sm bg-gray-400"></span> Cancelada</span>
    </div>

    {{-- Calendar --}}
    <div class="card p-4">
        <div id="vacation-calendar" style="min-height: 600px;"></div>
    </div>

    {{-- Event Detail Popover --}}
    <template x-teleport="body">
        <div x-show="showDetail" x-transition.opacity
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40"
             @click.self="showDetail = false">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-sm p-6 space-y-3" x-transition>
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-neutral-text text-lg" x-text="detail.title"></h3>
                    <button type="button" @click="showDetail = false"
                            class="text-neutral-muted hover:text-neutral-text p-1 rounded">✕</button>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">Departamento</span>
                        <span class="font-medium text-neutral-text" x-text="detail.department"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">Período</span>
                        <span class="font-medium text-neutral-text" x-text="detail.period"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">Dias</span>
                        <span class="font-medium text-neutral-text" x-text="detail.days"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-muted">Status</span>
                        <span class="font-semibold" :class="detail.statusClass" x-text="detail.status"></span>
                    </div>
                    <template x-if="detail.notes">
                        <div>
                            <span class="text-neutral-muted block mb-1">Observação</span>
                            <p class="text-neutral-text bg-neutral-50 rounded p-2 text-xs" x-text="detail.notes"></p>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </template>

    {{-- FullCalendar CDN --}}
    @assets
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
    @endassets

    <script>
        function vacationCalendar() {
            return {
                calendar: null,
                showDetail: false,
                detail: { title: '', department: '', period: '', days: '', status: '', statusClass: '', notes: '' },

                init() {
                    this.$nextTick(() => {
                        const calendarEl = document.getElementById('vacation-calendar');
                        if (!calendarEl) return;

                        this.calendar = new FullCalendar.Calendar(calendarEl, {
                            locale: 'pt-br',
                            initialView: 'dayGridMonth',
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,dayGridWeek,listMonth'
                            },
                            buttonText: {
                                today: 'Hoje',
                                month: 'Mês',
                                week: 'Semana',
                                list: 'Lista'
                            },
                            height: 'auto',
                            events: @json($events),
                            eventDisplay: 'block',
                            eventClick: (info) => {
                                info.jsEvent.preventDefault();
                                const props = info.event.extendedProps;
                                const start = info.event.start;
                                const end = info.event.end ? new Date(info.event.end.getTime() - 86400000) : start;
                                this.detail = {
                                    title: info.event.title,
                                    department: props.department,
                                    period: start.toLocaleDateString('pt-BR') + ' — ' + end.toLocaleDateString('pt-BR'),
                                    days: props.days + ' dias',
                                    status: props.status,
                                    statusClass: this.getStatusClass(info.event.backgroundColor),
                                    notes: props.notes || '',
                                };
                                this.showDetail = true;
                            },
                        });
                        this.calendar.render();

                        Livewire.hook('morph.updated', () => {
                            this.calendar.removeAllEvents();
                            this.calendar.addEventSource(@json($events));
                        });
                    });
                },

                getStatusClass(color) {
                    if (color === '#16a34a') return 'text-green-600';
                    if (color === '#f59e0b') return 'text-amber-600';
                    if (color === '#ef4444') return 'text-red-600';
                    return 'text-gray-500';
                }
            };
        }
    </script>
</div>
