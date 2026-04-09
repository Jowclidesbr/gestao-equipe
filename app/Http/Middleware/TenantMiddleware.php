<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

/**
 * TenantMiddleware
 *
 * Resolves the current tenant from either:
 *   1. The request subdomain  → acme.app.com
 *   2. A custom domain header → X-Tenant-Slug
 *   3. The authenticated user's tenant_id (fallback for non-subdomain setups)
 *
 * Sets app('tenant') binding and scopes the database connection.
 */
class TenantMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Super admins are not scoped to any tenant
        if ($request->user() && $request->user()->isSuperAdmin()) {
            return $next($request);
        }

        $tenant = $this->resolveTenant($request);

        if (! $tenant) {
            abort(404, 'Tenant não encontrado ou inativo.');
        }

        // Bind tenant to the service container (available everywhere via app('tenant'))
        App::instance('tenant', $tenant);

        // Make tenant_id available to all Eloquent global scopes
        Config::set('app.tenant_id', $tenant->id);

        // Optionally set custom DB prefix per tenant (if needed in the future)
        // DB::statement("SET @tenant_id = {$tenant->id}");

        return $next($request);
    }

    private function resolveTenant(Request $request): ?Tenant
    {
        // ── Strategy 1: Subdomain ─────────────────────────────────────────────
        $host = $request->getHost(); // e.g. "acme.gestaoequipe.com"
        $appHost = config('app.base_domain', 'gestaoequipe.test');

        if (str_ends_with($host, '.' . $appHost)) {
            $slug = str_replace('.' . $appHost, '', $host);
            if ($slug && $slug !== $appHost) {
                return Tenant::where('slug', $slug)->where('is_active', true)->first();
            }
        }

        // ── Strategy 2: X-Tenant-Slug header (useful for APIs / Postman) ─────
        if ($slug = $request->header('X-Tenant-Slug')) {
            return Tenant::where('slug', $slug)->where('is_active', true)->first();
        }

        // ── Strategy 3: Authenticated user's tenant ───────────────────────────
        $user = $request->user();
        if ($user && $user->tenant_id) {
            return Tenant::where('id', $user->tenant_id)->where('is_active', true)->first();
        }

        return null;
    }
}
