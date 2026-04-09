<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Rewrite the root-relative data-update-uri="/livewire/update"
 * embedded in Livewire's <script> tag to an absolute URL.
 *
 * Needed when Laravel runs in a subdirectory (e.g. XAMPP at
 * /gestao-equipe/public/) because the browser would otherwise
 * POST to http://localhost/livewire/update → Apache 404.
 */
class FixLivewireUpdateUri
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only rewrite HTML responses
        $contentType = $response->headers->get('Content-Type', '');
        if (! str_contains($contentType, 'text/html')) {
            return $response;
        }

        $content = $response->getContent();
        if ($content === false || $content === '') {
            return $response;
        }

        // Replace root-relative URI with absolute URL
        $absolute = url('livewire/update');
        $content = str_replace(
            'data-update-uri="/livewire/update"',
            'data-update-uri="' . $absolute . '"',
            $content
        );

        $response->setContent($content);

        return $response;
    }
}
