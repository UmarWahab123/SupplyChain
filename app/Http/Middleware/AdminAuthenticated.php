<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class AdminAuthenticated
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
        if(!Auth::check()){
            return redirect()->to('login');
        }else{
            if(Auth::user()->role_id != 1 && Auth::user()->role_id != 2 && Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 5 && Auth::user()->role_id != 6 && Auth::user()->role_id != 7 && Auth::user()->role_id != 8 && Auth::user()->role_id != 9 && Auth::user()->role_id != 10 && Auth::user()->role_id != 11){
                return redirect()->back();
            }
        }
        return $next($request);
    }
}
