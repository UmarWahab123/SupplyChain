<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class PurchasingAuthenticated
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
            if(Auth::user()->role_id != 2 && Auth::user()->role_id != 1 && Auth::user()->role_id != 5 && Auth::user()->role_id != 6 && Auth::user()->role_id != 3 && Auth::user()->role_id != 4 && Auth::user()->role_id != 7 && Auth::user()->role_id != 8 && Auth::user()->role_id != 9 && Auth::user()->role_id != 10 && Auth::user()->role_id != 11)
            {
                return redirect()->back();
            }
        }
        $url =  \Request::path();
        $route = \Request::route()->getName();
        if($url == '/')
        {

            $user = Auth::user();
            if($user->role_id == 1 || $user->role_id == 8 ) {
                if($route == 'purchasing-dashboard')
                {
                    return $next($request);
                }
                else
                {
                    return redirect('/sales');
                }
            }
            elseif($user->role_id == 2 ){
                return $next($request);
            }
            elseif($user->role_id == 3 ){
                return redirect('/sales');
            }
            elseif($user->role_id == 4 ){
                return redirect('/sales');
            }
            elseif($user->role_id == 5 ){
                return redirect('importing/importing-receiving-queue');
            }
            elseif($user->role_id == 6 ){
                return redirect('/warehouse');
            }
            elseif($user->role_id == 7 ){
                if($route == 'purchasing-dashboard')
                {
                    return $next($request);
                }
                else
                {
                    return redirect('sales/account-recievable');
                }
            }
            elseif($user->role_id == 9 ){
                return redirect('/ecom/ecom-dashboard');
            }
        }
        return $next($request);
    }
}
