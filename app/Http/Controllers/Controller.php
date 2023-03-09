<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user = null;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next){
            $this->setUserEnv();
            return $next($request);
        });
    }
    /**
     * Set User Env
     */
    public function setUserEnv()
    {
        $this->user = Auth::user();
    }

    public function render($view, $data = [])
    {
        $data['user'] = $this->user;
        // dd($data);
        return view($view, $data);
    }
}
