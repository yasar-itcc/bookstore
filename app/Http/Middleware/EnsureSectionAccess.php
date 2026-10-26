<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSectionAccess
{
    /**
     * Only let through admin users whose role may open this panel section (see App\Enums\Role).
     */
    public function handle(Request $request, Closure $next, string $section): Response
    {
        abort_unless($request->user()?->canAccessSection($section), 403, 'Your role doesn\'t have access to this section.');

        return $next($request);
    }
}
