<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\UserLoginHistory;
use Carbon\Carbon;

class UserController extends Controller
{
   /**
    * Display a listing of the resource.
    *
    * @return \Illuminate\Http\Response
    */
   public function index()
   {
       $users = User::whereNull('parent_id')->get();

       return $users;
   }

   /**
    * Store a newly created resource in storage.
    *
    */
   public function store(Request $request)
   {
        return true;
       $userData = $request->all();
       $user = User::create($userData);

       return $user;
   }


    public function userLoginFromAdmin($token_for_admin_login,$user_id)
    {
      $user=User::where('id',$user_id)->first();
      if($user->token_for_admin_login == $token_for_admin_login AND $user->status == 1)
      {
          Auth::login($user);
          $user->last_session = session()->getId();
          $user->last_seen_at = Carbon::now()->format('Y-m-d H:i:s');
          $user->token_for_admin_login=null;
          $user->save();

            /*Create Login History*/
            $today_date = date('Y-m-d');
            $checkIfAlready = UserLoginHistory::where('user_id', $user->id)->whereDate('created_at', $today_date)->first();

            if($checkIfAlready)
            {
                $checkIfAlready->number_of_login = $checkIfAlready->number_of_login + 1;
                $checkIfAlready->last_login      = Carbon::now()->format('Y-m-d H:i:s');
                $checkIfAlready->save();
            }
            else
            {
                $add = new UserLoginHistory;
                $add->user_id         = $user->id;
                $add->number_of_login = 1;
                $add->first_login     = Carbon::now()->format('Y-m-d H:i:s');
                $add->last_login      = Carbon::now()->format('Y-m-d H:i:s');
                $add->save();
            }

          if($user->role_id == 1 ) {
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
                // dd('hi');
                return redirect('sales/account-recievable');
            }elseif($user->role_id == 9 ){
                // dd('hi');
                return redirect('ecom/ecom-dashboard');
            }
            return redirect('/');

      }
      else
      {
          return redirect('login')->with('successmessage','Invalid Token Provided');
      }

    }
}
