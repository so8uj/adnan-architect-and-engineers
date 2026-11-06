<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/** Route guard for custom screens: ->middleware('perm:hr.shifts.manage'). Super Admin passes everything. */
class RequirePermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        abort_unless($request->user()?->hasPermission($permission), 403, 'Your role does not allow this.');

        return $next($request);
    }
}
