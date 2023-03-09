<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\User;

class IncompleteProfile
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
            //if null, this means profile is incomplete
            if(Auth::user()->getUserDetail)
            {
                //return redirect('complete-profile');
                return $next($request);
            }
            else
            {
                if(Auth::user()->role_id == 1 || Auth::user()->role_id == 11)
                {
                    return redirect()->to('admin/admin-complete-profile');
                }
                if(Auth::user()->role_id == 2)
                {
                    return redirect()->to('complete-profile');
                }
                if(Auth::user()->role_id == 3)
                {
                    return redirect()->to('sales/complete-profile');
                }
                if(Auth::user()->role_id == 5)
                {
                    return redirect()->to('importing/complete-profile');
                }
                if(Auth::user()->role_id == 6)
                {
                    return redirect()->to('warehouse/complete-profile');
                }
            }

        }

        return $next($request);

    }
}
