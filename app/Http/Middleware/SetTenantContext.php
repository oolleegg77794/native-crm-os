<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Core\Tenant\TenantContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetTenantContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->organization) {
            TenantContext::set($user->organization);
        }

        $response = $next($request);

        TenantContext::clear();

        return $response;
    }
}
