<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Safe default security headers for every page. (No Content-Security-Policy on purpose: the admin can add its own
 * scripts and tracking codes, and a strict policy would break them.)
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()');

        // only on real HTTPS in production: tells browsers to always use https for this site
        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        // the admin panel must never be kept in the browser cache (back button after logout)
        if ($request->is('admin', 'admin/*')) {
            $response->headers->set('Cache-Control', 'no-store, private');
        }

        header_remove('X-Powered-By');

        return $response;
    }
}
