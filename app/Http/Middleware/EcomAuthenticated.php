<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EcomAuthenticated
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
        if(!Auth::check())
        {
            return redirect()->to('login');
        }
        else
        {
            if(Auth::user()->role_id != 9 && Auth::user()->role_id != 1 && Auth::user()->role_id != 3 && Auth::user()->role_id != 4  && Auth::user()->role_id != 10  && Auth::user()->role_id != 8 && Auth::user()->role_id != 11)
            {
                return redirect()->back();
            }
        }
        return $next($request);
        // return $next($request);
    }
}
