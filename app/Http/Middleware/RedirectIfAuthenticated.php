<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user = Auth::user();
        if (Auth::guard($guard)->check()) {
            switch (true) {
            case $user->user_role == 'divisional_manager':
                return redirect('/division');
            case $user->user_role == 'admin':
                return redirect('/admin');
            case $user->user_role == 'factory':
                return redirect('/factory');
            case $user->user_role == 'warehouse':
                return redirect('/warehouse');
            default:
                return '/';
            }
        }

        return $next($request);
    }
}