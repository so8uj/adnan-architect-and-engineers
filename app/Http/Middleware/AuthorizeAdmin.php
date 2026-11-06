<?php

namespace App\Http\Middleware;

use App\Support\Permissions;
use Closure;
use Illuminate\Http\Request;

/** Checks the signed-in admin's role against the screen being opened (see App\Support\Permissions::required). */
class AuthorizeAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $need = Permissions::required($request);
        $user = $request->user();

        if ($need !== null && $user) {
            $ok = match (true) {
                $need === '!' => $user->isSuper(),
                $need === 'website.any' => collect(array_keys(config('admin.website', [])))->contains(fn ($k) => $user->hasPermission("website.$k.view")),
                default => $user->hasPermission($need),
            };
            abort_unless($ok, 403, 'Your role does not allow this.');
        }

        return $next($request);
    }
}
