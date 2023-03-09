<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Notification;
use Auth;
use Carbon\Carbon;
use App\Http\Controllers\Controller;

class NotificationController extends Controller
{
    public function clearNotification(Request $request)
    {
    	$notification = Notification::where('id',$request->id)->first();
    	$notification->read_at = Carbon::now();
    	$notification->save();
        $count = Notification::where('notifiable_id', Auth::user()->id)->where('read_at', null)->count();
        return \response()->json(['count' => $count]);
    }
    public function cleaAllrNotifications(Request $request)
    {
    	$notifications = Notification::where('notifiable_id', Auth::user()->id)->get();
    	foreach ($notifications as $notification) {
	    	$notification->read_at = Carbon::now();
	    	$notification->save();
    	}
    }
}
