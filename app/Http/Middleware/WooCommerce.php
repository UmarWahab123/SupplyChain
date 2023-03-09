<?php

namespace App\Http\Middleware;

use Closure;

class WooCommerce
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
        $token = $request->bearerToken();
        if($token != config('app.external_token'))
            return response()->json(['status' => 'Token is Invalid']);

        return $next($request);
    }
}
