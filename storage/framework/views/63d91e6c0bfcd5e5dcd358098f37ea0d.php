<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? $attributes->get('title', 'Acesso')); ?> — Gestão de Equipe</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::styles(); ?>

</head>
<body class="h-full bg-white">
<div class="min-h-screen flex">

    
    <div class="hidden lg:flex lg:w-[48%] xl:w-[45%] relative overflow-hidden flex-col"
         style="background: #1C1C1E;">

        
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-40 -right-40 w-[500px] h-[500px] rounded-full"
                 style="background: radial-gradient(circle, rgba(236,0,0,0.18) 0%, transparent 70%);"></div>
            <div class="absolute -bottom-32 -left-32 w-[420px] h-[420px] rounded-full"
                 style="background: radial-gradient(circle, rgba(236,0,0,0.12) 0%, transparent 70%);"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full opacity-5"
                 style="background: radial-gradient(circle, #EC0000 0%, transparent 65%);"></div>
        </div>

        
        <div class="absolute top-0 left-0 right-0 h-1 bg-santander-red"></div>

        
        <div class="relative flex flex-col h-full justify-between p-10 xl:p-14">

            
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-santander-red shadow-lg">
                    <svg viewBox="0 0 24 24" fill="none" class="w-5 h-5" stroke="white" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-white font-semibold text-base tracking-tight">Gestão de Equipe</span>
            </div>

            
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold mb-6"
                      style="background: rgba(236,0,0,0.18); color: #FF7070;">
                    <span class="w-1.5 h-1.5 rounded-full bg-santander-red-light animate-pulse"></span>
                    Plataforma Corporativa de RH
                </span>

                <h1 class="text-4xl xl:text-5xl font-bold text-white leading-[1.15] mb-5">
                    Gerencie<br>
                    sua <span class="text-santander-red">equipe</span><br>
                    com inteligência.
                </h1>

                <p class="text-white/50 text-sm leading-relaxed max-w-xs">
                    Controle férias, cargos, documentos e colaboradores em uma plataforma unificada e segura.
                </p>

                
                <div class="mt-10 space-y-3">
                    <?php
                    $features = [
                        ['path' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => 'Gestão de férias e ausências'],
                        ['path' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'label' => 'Documentos e histórico'],
                        ['path' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'label' => 'Relatórios e métricas em tempo real'],
                    ];
                    ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex-shrink-0 flex items-center justify-center"
                             style="background: rgba(236,0,0,0.15);">
                            <svg class="w-4 h-4 text-santander-red" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="<?php echo e($f['path']); ?>"/>
                            </svg>
                        </div>
                        <span class="text-white/60 text-sm"><?php echo e($f['label']); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </div>

            
            <div class="flex items-center gap-2">
                <div class="w-5 h-5 rounded flex items-center justify-center bg-santander-red">
                    <span class="text-white font-black text-[9px] leading-none">S</span>
                </div>
                <span class="text-white/25 text-xs">Santander · Uso interno corporativo</span>
            </div>
        </div>
    </div>

    
    <div class="flex-1 flex items-center justify-center bg-white px-6 py-10 sm:px-10">
        <div class="w-full max-w-[400px]">

            
            <div class="flex items-center gap-2 mb-8 lg:hidden">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-santander-red">
                    <span class="text-white font-black text-xs">S</span>
                </div>
                <span class="font-bold text-neutral-text text-sm">Gestão de Equipe</span>
            </div>

            <?php echo e($slot); ?>

        </div>
    </div>

</div>
<?php echo \Livewire\Mechanisms\FrontendAssets\FrontendAssets::scripts(); ?>

<script>
(function(){
    var uri = '<?php echo e(url("livewire/update")); ?>';
    var tag = document.querySelector('script[data-update-uri]');
    if (tag) tag.setAttribute('data-update-uri', uri);
    if (window.livewireScriptConfig) window.livewireScriptConfig.uri = uri;
    document.addEventListener('livewire:init', function(){
        if (window.Livewire && window.Livewire.config) window.Livewire.config.uri = uri;
    });
})();
</script>
</body>
</html>
<?php /**PATH /Applications/XAMPP/xamppfiles/htdocs/gestao-equipe/resources/views/components/layouts/auth.blade.php ENDPATH**/ ?>