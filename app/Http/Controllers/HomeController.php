<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        if($user->role_id == 1 || $user->role_id == 8) {
            return redirect('/sales');
        }
        elseif($user->role_id == 2 ){
            return redirect('/');
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
            return redirect('sales/account-recievable');
        }
        return redirect('login');
        return redirect()->back();
    }
}
