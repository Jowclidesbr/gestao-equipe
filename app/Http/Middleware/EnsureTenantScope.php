<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureTenantScope
 *
 * After authentication, ensures the authed user belongs to the current tenant.
 * Prevents horizontal privilege escalation between tenants.
 */
class EnsureTenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // SuperAdmins are not bound to any tenant
        if ($user->hasRole('super_admin')) {
            return $next($request);
        }

        $tenant = app('tenant');

        if (! $tenant || (int) $user->tenant_id !== (int) $tenant->id) {
            auth()->logout();
            return redirect()->route('login')->withErrors([
                'email' => 'Acesso não autorizado para esta empresa.',
            ]);
        }

        return $next($request);
    }
}
