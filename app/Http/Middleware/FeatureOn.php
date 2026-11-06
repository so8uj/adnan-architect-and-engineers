<?php

namespace App\Http\Middleware;

use App\Support\Features;
use Closure;
use Illuminate\Http\Request;

/** ->middleware('feature:attendance') : the screen answers 404 while its module is switched off. */
class FeatureOn
{
    public function handle(Request $request, Closure $next, string $module)
    {
        abort_unless(Features::adminCan($module), 404);

        return $next($request);
    }
}
