<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? $attributes->get('title', 'Acesso') }} — Gestão de Equipe</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full flex items-center justify-center bg-neutral-bg">
    <div class="w-full max-w-sm">
        {{ $slot }}
    </div>
    @livewireScripts
    <script>
    (function(){
        var full = '{{ url("livewire/update") }}';
        var tag  = document.querySelector('script[data-update-uri]');
        if (tag) tag.setAttribute('data-update-uri', full);
        if (window.livewireScriptConfig) window.livewireScriptConfig.uri = full;
        document.addEventListener('livewire:init', function(){
            if (window.Livewire && window.Livewire.config) window.Livewire.config.uri = full;
        });
    })();
    </script>
</body>
</html>
