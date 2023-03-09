<?php

namespace App\Http\Controllers\Backend;
use App\Mail\Backend\AddImportingEmail;
use App\Http\Controllers\Controller;
use App\Models\Common\EmailTemplate;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use App\Models\Common\Role;
use App\User;
use Mail;
use App\Helpers\MyHelper;


class ImportingController extends Controller
{
    public function index(){    	
    	// dd('hello');
    	return $this->render('backend.importing.index');
    }

    public function getData()
    {
        $query = User::with('roles')->whereHas('roles', function($query){
                    $query->where('role_id', '=','5');
                })->select('users.*')->whereNull('parent_id')->get();

        return Datatables::of($query)
        
            ->addColumn('status', function ($item) {
                if($item->status == 1){
                    $status = '<span data-search="'.$item->status.'" class="badge badge-pill badge-success font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Active</span>';
                }elseif($item->status == 2){
                    $status = '<span class="badge badge-pill badge-danger font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Suspended</span>';
                }else{
                    $status = '<span class="badge badge-pill badge-warning font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">InActive</span>';
                }
                return $status;
            })

            ->addColumn('action', function ($item) { 
                $html_string = ''; 
                if($item->status == 1){
                    $html_string .= ' <a href="javascript:void(0);" class="actionicon deleteIcon suspend-user" data-id="'.$item->id.'" data-role_name="'.$item->roles->name.'" title="Suspend"><i class="fa fa-ban"></i></a>'; 
                }else{
                    $html_string .= ' <a href="javascript:void(0);" class="actionicon viewIcon activate-user activateIcon" data-id="'.$item->id.'" data-role_name="'.$item->roles->name.'" title="Activate"><i class="fa fa-check"></i></a>'; 
                }
                $html_string .= '</div>';
                return $html_string;         
                })
                ->rawColumns(['action', 'status'])
                    ->make(true);
    }

    public function add(Request $request){
     	// dd($request);
    	$validator = $request->validate([
    		'first_name' => 'required',
    		'last_name' => 'required',
    		'email' => 'required|email|unique:users'
    	]);

    	// generate random password //
    	$password = uniqid();
    	$role = Role::where('id', '=', '5')->first();
    	// save user //
    	$user = new User;
    	$user->name = $request->first_name.' '.$request->last_name;
    	$user->email = $request->email;
    	$user->password =  bcrypt($password);
    	$user->email_verified_at =  now();
        $user->status =  true;

    	$importing = $role->user()->save($user);
    	$template= EmailTemplate::where('type','create-importing')->first();
    	// send email //
        $my_helper =  new MyHelper;
        $result_helper=$my_helper->getApiCredentials();
        if($result_helper['email_notification'] == 1)
        {
    	Mail::to($importing->email, $importing->name)->send(new AddImportingEmail($importing, $password,$template));
        }

    	return response()->json(['success' => true]);

    }

}
