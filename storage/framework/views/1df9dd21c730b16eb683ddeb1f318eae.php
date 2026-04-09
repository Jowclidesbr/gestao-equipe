<?php
    $isActive = request()->routeIs($match);
    $count    = $badge ?? $pendingCount ?? 0;
?>

<div class="relative group">
    <a href="<?php echo e(route($route)); ?>"
       class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg my-0.5 transition-all duration-150
              <?php echo e($isActive ? 'nav-item-active bg-white/[0.08] text-white font-medium' : 'text-white/50 hover:text-white hover:bg-white/[0.05]'); ?>">

        <svg class="w-4 h-4 flex-shrink-0 <?php echo e($isActive ? 'text-white' : 'text-white/50 group-hover:text-white/80'); ?>"
             fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.75">
            <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($icon); ?>"/>
        </svg>

        <span x-show="!open" x-cloak class="flex-1 truncate text-[13px]"><?php echo e($label); ?></span>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
            <span x-show="!open" x-cloak
                  class="ml-auto text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-red-600 text-white leading-none">
                <?php echo e($count); ?>

            </span>
            <span x-show="open" x-cloak
                  class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-red-600 ring-2 ring-neutral-sidebar">
            </span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </a>

    <div x-show="open" x-cloak
         class="nav-tooltip absolute left-full top-1/2 -translate-y-1/2 ml-3 z-50
                bg-gray-900 text-white text-xs font-medium px-2.5 py-1.5 rounded-lg shadow-lg whitespace-nowrap">
        <?php echo e($label); ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($count > 0): ?>
            <span class="ml-1.5 bg-red-600 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?php echo e($count); ?></span>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gestao-equipe/resources/views/layouts/partials/nav-item.blade.php ENDPATH**/ ?>