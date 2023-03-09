<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class SalesAuthenticated
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
            $url =  \Request::path();
            $user = Auth::user()->role_id;


            if(Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 1 && Auth::user()->role_id != 2 && Auth::user()->role_id != 5 && Auth::user()->role_id != 6 && Auth::user()->role_id != 7 && Auth::user()->role_id != 8 && Auth::user()->role_id != 9 && Auth::user()->role_id != 10 && Auth::user()->role_id != 11)
            {
              return redirect()->back();
            }

            if(Auth::user()->role_id == 9 && $url == 'sales'){
                return redirect()->back();
            }
            //  if($user == 2 && $url == 'sales'){
            //     return redirect('/');
            // }

        }
        return $next($request);
    }
}
