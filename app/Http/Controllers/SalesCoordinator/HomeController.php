<?php

namespace App\Http\Controllers\SalesCoordinator;

use App\Http\Controllers\Controller;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Common\UserDetail;
use App\Models\Sales\Customer;
use App\User;
use Auth;
use Hash;
use Illuminate\Http\Request;

class HomeController extends Controller
{
  public function index(){
    return view('salesCoordinator.home.dashboard');
  }
}
