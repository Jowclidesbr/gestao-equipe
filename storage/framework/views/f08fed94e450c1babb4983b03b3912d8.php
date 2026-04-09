
<div class="space-y-4">

    
    <div class="card">
        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search"
                       placeholder="Buscar por colaborador..."
                       class="form-input max-w-xs">
            </div>
            <div class="flex items-center gap-2">
                <select wire:model.live="filterStatus" class="form-select text-sm">
                    <option value="">Todos os status</option>
                    <option value="pending">Aguardando</option>
                    <option value="approved">Aprovadas</option>
                    <option value="rejected">Rejeitadas</option>
                    <option value="cancelled">Canceladas</option>
                </select>
            </div>
        </div>
    </div>

    
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Colaborador</th>
                    <th>Período</th>
                    <th>Dias</th>
                    <th>Abono</th>
                    <th>Status</th>
                    <th>Solicitado em</th>
                    <th class="text-right">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr wire:key="<?php echo e($req->id); ?>">
                        <td>
                            <div class="flex items-center gap-2">
                                <img src="<?php echo e($req->employee->user->avatar_url); ?>" alt=""
                                     class="w-7 h-7 rounded-full object-cover flex-shrink-0">
                                <div>
                                    <p class="font-medium"><?php echo e($req->employee->user->name); ?></p>
                                    <p class="text-xs text-neutral-muted"><?php echo e($req->employee->department->name ?? '—'); ?></p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap">
                            <?php echo e($req->start_date->format('d/m/Y')); ?> – <?php echo e($req->end_date->format('d/m/Y')); ?>

                        </td>
                        <td><?php echo e($req->days_requested); ?></td>
                        <td><?php echo e($req->sell_days > 0 ? $req->sell_days . ' dias' : '—'); ?></td>
                        <td>
                            <span class="<?php echo e($req->status_badge_class); ?>"><?php echo e($req->status_label); ?></span>
                        </td>
                        <td class="text-neutral-muted whitespace-nowrap">
                            <?php echo e($req->submitted_at?->format('d/m/Y H:i') ?? '—'); ?>

                        </td>
                        <td class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($req->isPending()): ?>
                                    <?php if (app(\Illuminate\Contracts\Auth\Access\Gate::class)->check('approve', $req)): ?>
                                        <button type="button" wire:click="approve(<?php echo e($req->id); ?>)"
                                                wire:confirm="Confirmar aprovação das férias?"
                                                class="btn-success py-1 px-2 text-xs">
                                            Aprovar
                                        </button>
                                        <button type="button" wire:click="openRejectModal(<?php echo e($req->id); ?>)"
                                                class="btn-danger py-1 px-2 text-xs">
                                            Rejeitar
                                        </button>
                                    <?php endif; ?>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <span class="text-neutral-muted text-xs px-2">
                                    <?php echo e($req->approver?->name ? 'por ' . $req->approver->name : ''); ?>

                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="py-12 text-center text-neutral-muted">
                            <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Nenhuma solicitação encontrada.
                        </td>
                    </tr>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo e($requests->links()); ?>


    
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showRejectModal): ?>
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" wire:click.self="$set('showRejectModal', false)">
            <div class="bg-white rounded-card shadow-card-lg w-full max-w-md p-6"
                 x-transition>
                <h3 class="text-base font-semibold text-neutral-text mb-4">Rejeitar Solicitação</h3>

                <div>
                    <label class="form-label">Motivo da rejeição <span class="text-santander-red">*</span></label>
                    <textarea wire:model="rejectNotes" rows="4" placeholder="Informe o motivo detalhado..."
                              class="form-input resize-none"></textarea>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['rejectNotes'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="form-error"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button type="button" wire:click="$set('showRejectModal', false)" class="btn-secondary">Cancelar</button>
                    <button type="button" wire:click="confirmReject" class="btn-danger">Confirmar Rejeição</button>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gestao-equipe/resources/views/livewire/vacation/vacation-request-list.blade.php ENDPATH**/ ?>