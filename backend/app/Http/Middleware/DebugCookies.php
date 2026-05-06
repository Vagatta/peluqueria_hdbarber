<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugCookies
{
    public function handle(Request $request, Closure $next): Response
    {
        // Log incoming cookies
        if (str_contains($request->path(), 'csrf') || str_contains($request->path(), 'register')) {
            error_log('[DEBUG] Incoming cookies: ' . json_encode($request->cookies->all()));
            error_log('[DEBUG] X-XSRF-TOKEN header: ' . ($request->header('X-XSRF-TOKEN') ? 'present' : 'missing'));
        }

        $response = $next($request);

        // Log outgoing cookies
        if (str_contains($request->path(), 'csrf')) {
            $cookies = [];
            foreach ($response->headers->getCookies() as $cookie) {
                $cookies[] = [
                    'name' => $cookie->getName(),
                    'domain' => $cookie->getDomain(),
                    'path' => $cookie->getPath(),
                    'secure' => $cookie->isSecure(),
                    'samesite' => $cookie->getSameSite(),
                    'httponly' => $cookie->isHttpOnly(),
                ];
            }
            error_log('[DEBUG] Outgoing cookies: ' . json_encode($cookies));
        }

        return $response;
    }
}
