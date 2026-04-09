
<div class="space-y-6">

    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($loading): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php for($i = 0; $i < 4; $i++): ?>
                <div class="skeleton-card"></div>
            <?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="metric-label">Total de Colaboradores</p>
                        <p class="metric-value"><?php echo e(number_format($metrics['headcount'])); ?></p>
                    </div>
                    <div class="metric-icon bg-blue-50">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs text-neutral-muted mt-2">
                    <span class="text-green-600 font-semibold"><?php echo e($metrics['active_count']); ?> ativos</span>
                    <span>·</span>
                    <span><?php echo e($metrics['inactive_count']); ?> inativos</span>
                </div>
            </div>

            
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="metric-label">Colaboradores Ativos</p>
                        <p class="metric-value text-green-600"><?php echo e(number_format($metrics['active_count'])); ?></p>
                    </div>
                    <div class="metric-icon bg-green-50">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex items-center gap-1 text-xs text-neutral-muted mt-2">
                    <span><?php echo e($metrics['headcount'] > 0 ? round(($metrics['active_count'] / $metrics['headcount']) * 100) : 0); ?>% do quadro</span>
                </div>
            </div>

            
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="metric-label">Turnover (12 meses)</p>
                        <p class="metric-value <?php echo e($metrics['turnover_rate'] > 10 ? 'text-santander-red' : 'text-neutral-text'); ?>">
                            <?php echo e($metrics['turnover_rate']); ?>%
                        </p>
                    </div>
                    <div class="metric-icon <?php echo e($metrics['turnover_rate'] > 10 ? 'bg-red-50' : 'bg-neutral-bg'); ?>">
                        <svg class="w-5 h-5 <?php echo e($metrics['turnover_rate'] > 10 ? 'text-santander-red' : 'text-neutral-muted'); ?>"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                    </div>
                </div>
                <p class="text-xs text-neutral-muted mt-2">Referência: &lt; 10% = saudável</p>
            </div>

            
            <div class="metric-card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="metric-label">Férias Pendentes</p>
                        <p class="metric-value <?php echo e($metrics['pending_vacations'] > 0 ? 'text-yellow-600' : ''); ?>">
                            <?php echo e($metrics['pending_vacations']); ?>

                        </p>
                    </div>
                    <div class="metric-icon <?php echo e($metrics['pending_vacations'] > 0 ? 'bg-yellow-50' : 'bg-neutral-bg'); ?>">
                        <svg class="w-5 h-5 <?php echo e($metrics['pending_vacations'] > 0 ? 'text-yellow-500' : 'text-neutral-muted'); ?>"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($metrics['pending_vacations'] > 0): ?>
                    <a href="<?php echo e(route('admin.vacations.index')); ?>" class="text-xs text-santander-red hover:underline mt-2 inline-block">
                        Ver solicitações →
                    </a>
                <?php else: ?>
                    <p class="text-xs text-neutral-muted mt-2">Nenhuma pendência</p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-neutral-text">🎂 Aniversariantes do Mês</h2>
                    <span class="text-xs text-neutral-muted"><?php echo e(now()->translatedFormat('F')); ?></span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($metrics['birthdays_this_month']->isEmpty()): ?>
                    <p class="text-sm text-neutral-muted">Nenhum aniversariante este mês.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $metrics['birthdays_this_month']->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center gap-3">
                                <img src="<?php echo e($emp->user->avatar_url); ?>" alt="<?php echo e($emp->user->name); ?>"
                                     class="w-8 h-8 rounded-full object-cover flex-shrink-0">
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium truncate"><?php echo e($emp->user->name); ?></p>
                                    <p class="text-xs text-neutral-muted"><?php echo e($emp->birth_date->format('d/m')); ?></p>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($emp->birth_date->day === now()->day && $emp->birth_date->month === now()->month): ?>
                                    <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full font-medium">Hoje!</span>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-neutral-text">🏖 Próximas Férias</h2>
                    <span class="text-xs text-neutral-muted">Próximos 30 dias</span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($metrics['upcoming_vacations']->isEmpty()): ?>
                    <p class="text-sm text-neutral-muted">Nenhuma férias programada.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $metrics['upcoming_vacations']->take(6); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vr): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium truncate"><?php echo e($vr->employee->user->name); ?></p>
                                    <p class="text-xs text-neutral-muted">
                                        <?php echo e($vr->start_date->format('d/m')); ?> – <?php echo e($vr->end_date->format('d/m/Y')); ?>

                                    </p>
                                </div>
                                <span class="badge-approved text-xs flex-shrink-0"><?php echo e($vr->days_requested); ?>d</span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>

            
            <div class="card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-neutral-text">🏥 Próximos Afastamentos</h2>
                    <span class="text-xs text-neutral-muted">Próximos 14 dias</span>
                </div>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($metrics['upcoming_absences']->isEmpty()): ?>
                    <p class="text-sm text-neutral-muted">Nenhum afastamento previsto.</p>
                <?php else: ?>
                    <ul class="space-y-3">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $metrics['upcoming_absences']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $abs): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-sm font-medium truncate"><?php echo e($abs->name); ?></p>
                                    <p class="text-xs text-neutral-muted">
                                        <?php echo e(\Carbon\Carbon::parse($abs->start_date)->format('d/m')); ?>

                                        – <?php echo e(\Carbon\Carbon::parse($abs->end_date)->format('d/m/Y')); ?>

                                    </p>
                                </div>
                                <span class="badge-pending text-xs flex-shrink-0"><?php echo e($abs->type); ?></span>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </ul>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </div>

        
        <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->any(['create', 'viewAny'], \App\Models\Employee::class)): ?>
        <div class="card">
            <p class="text-sm font-semibold text-neutral-text mb-3">Ações Rápidas</p>
            <div class="flex flex-wrap gap-3">
                <a href="<?php echo e(route('admin.employees.create')); ?>" class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Novo Colaborador
                </a>
                <a href="<?php echo e(route('admin.vacations.index')); ?>" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Gerenciar Férias
                </a>
                <a href="<?php echo e(route('admin.job-openings.index')); ?>" class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Vagas Abertas
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($metrics['open_positions'] > 0): ?>
                        <span class="bg-santander-red text-white text-xs px-1.5 py-0.5 rounded-full"><?php echo e($metrics['open_positions']); ?></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </a>
            </div>
        </div>
        <?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gestao-equipe/resources/views/livewire/admin/admin-dashboard.blade.php ENDPATH**/ ?>