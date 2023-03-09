<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Mail\Backend\AddSalesEmail;
use App\Models\Common\EmailTemplate;
use App\Models\Common\Role;
use App\Models\Sales\SalesWarehouse;
use App\User;
use Mail;
use Yajra\Datatables\Datatables;
use App\Helpers\MyHelper;


class SalesController extends Controller
{
    public function index(){
        $warehouse = User::where('role_id',6)->whereNull('parent_id')->orderBy('name','DESC')->get();    	
    	// dd('hello');
    	return $this->render('backend.sales.index',['warehouse'=>$warehouse]);
    }

    public function getData()
    {
        $query = User::with('roles','get_warehouse')
                ->whereNull('parent_id')
                ->whereHas('roles', function($query){
                    $query->where('role_id', '=','3');})
                ->whereHas('get_warehouse', function($query){
                    $query->where('sales_id', '!=','');})
                ->select('users.*')->get();
                // dd($query->get()[0]->getWarehouse->users->email);

        return Datatables::of($query)
        
            ->addColumn('status', function ($item) {
                if($item->status == 1){
                    $status = '<span class="badge badge-pill badge-success font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Active</span>';
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
                    $html_string.='  <a href="javascript:void(0);" class="actionicon viewWarehouse" data-id="'.$item->id.'" title="View warehouse"><i class="fa fa-eye"></i></a>';                
                $html_string .= '</div>';
                return $html_string;         
                })
            ->addColumn('warehouse',function($item){
                return $item->get_warehouse->users->name;
            })

                ->rawColumns(['action', 'status','warehouse'])
                    ->make(true);
    }

    public function add(Request $request){
     	// dd($request->all());
    	$validator = $request->validate([
    		'first_name' => 'required',
    		'last_name' => 'required',
    		'email' => 'required|email|unique:users'
    	]);

    	// generate random password //
    	$password = uniqid();
    	$role = Role::where('id', '=', '3')->first();
    	// save user //
    	$user = new User;
    	$user->name = $request->first_name.' '.$request->last_name;
    	$user->email = $request->email;
    	$user->password =  bcrypt($password);
    	$user->email_verified_at =  now();
        $user->status =  true;

    	$sales = $role->user()->save($user);

        //Add to Relation table
        $sales_warehouse               = new SalesWarehouse;
        $sales_warehouse->warehouse_id = $request->warehouse;
        $sales_warehouse->sales_id     = $sales->id;
        $sales_warehouse->save();

    	$template= EmailTemplate::where('type','create-sales')->first();
    	// send email //
        $my_helper =  new MyHelper;
        $result_helper=$my_helper->getApiCredentials();
        if($result_helper['email_notification'] == 1){
    	Mail::to($sales->email, $sales->name)->send(new AddSalesEmail($sales, $password,$template));
        }

    	return response()->json(['success' => true]);

    }

    public function getSalesWarehouse(Request $request){

            $sales_warehouse = SalesWarehouse::where('sales_id',$request['id'])->with('getWarehouse')->get();
            return response()->json(['success' => true,'sales_warehouse'=>$sales_warehouse]);

        }
}
