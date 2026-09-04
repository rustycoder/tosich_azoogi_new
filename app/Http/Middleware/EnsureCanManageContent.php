<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanManageContent
{
    public function handle(Request $request, Closure $next, string $resource = ''): Response
    {
        $user = $request->user();

        if ($resource === '') {
            $page = $request->route('page');
            $resource = is_object($page) ? $page->slug : (string) $request->route('resource');
        }

        if ($resource === 'enquiries') {
            if ($user && $user->canManageEnquiries()) {
                return $next($request);
            }

            abort(403);
        }

        if ($user && $user->canManage($resource)) {
            return $next($request);
        }

        abort(403);
    }
}
