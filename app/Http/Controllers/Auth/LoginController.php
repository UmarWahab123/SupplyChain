<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use App\UserLoginHistory;
use Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    // protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    protected function authenticated(Request $request, $user)
    {
        // \Auth::logoutOtherDevices(request('password'));
        $user->last_session = session()->getId();
        $user->last_seen_at = Carbon::now()->format('Y-m-d H:i:s');
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

        if($user->role_id == 1 || $user->role_id == 8 || $user->role_id == 11) {
            return auth()->user()->last_seen_url != null ? redirect(auth()->user()->last_seen_url) : redirect('/sales');
        }
        elseif($user->role_id == 2 ){
            return auth()->user()->last_seen_url != null ? redirect(auth()->user()->last_seen_url) : redirect('/');
        }
        elseif($user->role_id == 3){
            return auth()->user()->last_seen_url != null ? redirect(auth()->user()->last_seen_url) : redirect('/sales');
        }
        elseif($user->role_id == 4 ){
            return auth()->user()->last_seen_url != null ? redirect(auth()->user()->last_seen_url) : redirect('/sales');
        }
        elseif($user->role_id == 5 ){
            return auth()->user()->last_seen_url != null ? redirect(auth()->user()->last_seen_url) : redirect('importing/importing-receiving-queue');
        }
        elseif($user->role_id == 6 ){
            return auth()->user()->last_seen_url != null ? redirect(auth()->user()->last_seen_url) : redirect('/warehouse');
        }
        elseif($user->role_id ==  7){
            return auth()->user()->last_seen_url != null ? redirect(auth()->user()->last_seen_url) : redirect('sales/account-recievable');
        }
        elseif($user->role_id == 9){
            return auth()->user()->last_seen_url != null ? redirect(auth()->user()->last_seen_url) : redirect('ecom/ecom-dashboard');
        }
        elseif($user->role_id == 10){
            return auth()->user()->last_seen_url != null ? redirect(auth()->user()->last_seen_url) : redirect('admin/roles');
        }
        return redirect('login');
    }

    public function logout(Request $request) {
        $user = auth()->user();
        // if($request->if_session_expire != "true") {
        //     if($user != null)
        //     {
        //         $user->last_seen_url = url()->previous();
        //         $user->save();
        //     }
        // } else {
        //     $user->last_seen_url = NULL;
        //     $user->save();
        // }

        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->loggedOut($request) ?: redirect('/login');
    }

    public function __construct()
    {
        // dd('here',auth()->user());
        $this->middleware('guest')->except('logout');
    }

    public function postLogin(Request $request)
    {
            //dd($request->email);
            $this->validateLogin($request);

            if ($this->hasTooManyLoginAttempts($request)) {
                $this->fireLockoutEvent($request);

                return $this->sendLockoutResponse($request);
            }

            if (Auth::attempt(['user_name' => $request->email, 'password' => $request->password, 'status' => 1], $request->remember)) {
                return $this->sendLoginResponse($request);
            }

            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
          $this->username() => 'required|string',
          'password' => 'required|string'
          //,'g-recaptcha-response' => 'required|captcha'
        ]);
    }
}
