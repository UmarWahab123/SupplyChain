<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\AdminRequestChangePassword;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Mail;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function sendRequestToAdmin(Request $request)
    {
        $user = User::select('id', 'user_name')->where('user_name', $request->username)->first();
        $admins = User::select('email')->where('role_id', 1)->get();
        if ($user) {
            foreach ($admins as $admin) {
                // $from_email = config('mail.from.address');
                // $name = config('mail.from.name');
                // $html = 'Hi,<br>You bare request to change the password of ' . $user->user_name . '. Please click on the link below to change the users password.<br><a href="{{route("user_detail", ' . $user->id . ')}}" class="btn btn-primary">Change Password</a>';
                $admin->notify(new AdminRequestChangePassword($user));
                // Mail::send(array(), array(), function ($message) use ($html, $user, $from_email, $name, $admin) {
                //     $message->to($admin->email)
                //         ->subject('Change Password of ' . $user->user_name)
                //         ->from($from_email, $name)
                //         ->setBody($html, 'text/html');
                // });
            }
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }
}
