<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the admin surface out of every index. Paired with the /admin
 * disallow in public/robots.txt (SEO.md §9, ADMIN_PANEL.md §2).
 */
class NoIndexAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
