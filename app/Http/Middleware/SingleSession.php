<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;

class SingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if(Auth::check())
        {
        // If current session id is not same with last_session column
        if(Auth::user()->last_session != Session::getId())
        {
            // do logout
            Auth::logout();

            session()->flush();
            // Redirecto login page
            return redirect('login')->with('alert', 'You are logged out because someone logged in from another device with the same credentials !');;
        }
        }
        return $next($request);
    }
}
