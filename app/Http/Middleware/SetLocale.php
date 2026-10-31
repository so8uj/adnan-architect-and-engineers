<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** The visitor's language lives in a plain "lang" cookie (set by the language button). Anything else = Bangla. */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($request->cookie('lang') === 'en' ? 'en' : 'bn');

        return $next($request);
    }
}
