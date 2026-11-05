<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectWwwToApexHost
{
    /**
     * Permanently redirect www.{host} requests to the bare host configured in APP_URL,
     * so search engines index a single copy of each page.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apexHost = strtolower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        $requestHost = strtolower($request->getHost());

        if ($apexHost !== '' && ! str_starts_with($apexHost, 'www.') && $requestHost === 'www.'.$apexHost) {
            $scheme = (string) (parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: $request->getScheme());

            return redirect()->to($scheme.'://'.$apexHost.$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
