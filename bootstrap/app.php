<?php

use App\Http\Middleware\EnsureTenantScope;
use App\Http\Middleware\FixLivewireUpdateUri;
use App\Http\Middleware\TenantMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register named middleware aliases
        $middleware->alias([
            'tenant'          => TenantMiddleware::class,
            'tenant.scope'    => EnsureTenantScope::class,
            'role'            => RoleMiddleware::class,
            'permission'      => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

        // Fix Livewire data-update-uri for XAMPP subdirectory install
        $middleware->web(append: [
            \Illuminate\Http\Middleware\HandleCors::class,
            FixLivewireUpdateUri::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    ->create();
