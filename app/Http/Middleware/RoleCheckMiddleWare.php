<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleCheckMiddleWare
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = Auth::user();
        if ($user->role !== 'admin') {
            Auth::guard('web')->logout();
            // If the user is not authorized, redirect or abort
            // return abort(404, 'Unauthorized action.');
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect('/');
        }
        if (Auth::check()) {
            // Update 'last_seen' timestamp every time a user makes a request
            Auth::user()->update(['last_seen' => Carbon::now()]);
        }
        return $next($request);
    }
}
