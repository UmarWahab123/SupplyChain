<?php

namespace App\Http\Controllers\Importing;

use App\Http\Controllers\Controller;
use App\Models\Common\Country;
use App\Models\Common\State;
use App\Models\Common\Status;
use App\Models\Common\Supplier;
use App\Models\Common\PaymentTerm;
use App\Models\Common\ProductCategory;
use App\Models\Common\SupplierCategory;
use App\Models\Common\Currency;

use App\Models\Common\UserDetail;
use App\Models\Sales\Customer;
use App\User;
use Auth;
use Hash;
use Illuminate\Http\Request;

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
    public function getHome()
    {
       
        return $this->render('importing.home.dashboard');
    }
 
    public function receivingQueue(){
        return $this->render('importing.products.receiving-queue');
    }

    public function completeProfile()
    {
        $check_profile_completed = UserDetail::where('user_id',Auth::user()->id)->count();
        if($check_profile_completed > 0){
            return redirect()->back();
        }
        $countries = Country::get();

        return $this->render('importing.home.profile-complete', compact('countries'));
    }

    public function completeProfileProcess(Request $request){
        // dd('here');
        $validator = $request->validate([
            'name' => 'required',
            'company' => 'required',
            'address' => 'required',
            'country' =>'required',
            'state' =>'required',
            'city' =>'required',
            'zip_code' =>'required',
            'phone_number' =>'required',
            //'image' =>'required|image|mimes:jpeg,png,jpg,gif,svg|max:1024',
        ]);        

        $user_detail = new UserDetail;
        $user_detail->user_id = Auth::user()->id;
        $user_detail->company_name = $request['company'];
        $user_detail->address = $request['address'];
        $user_detail->country_id = $request['country'];
        $user_detail->state_id = $request['state'];
        $user_detail->city_name = $request['city'];
        $user_detail->zip_code = $request['zip_code'];
        $user_detail->phone_no = $request['phone_number'];
        $user_detail->save();

        
        return response()->json([
            "success"=>true
        ]);
        
    }

    public function changePassword()
    {
       
        return view('importing.password-management.index');
    }
    
    public function checkOldPassword(Request $request)
    {
        
        $hashedPassword=Auth::user()->password;
        $old_password =  $request->old_password;
        if (Hash::check($old_password, $hashedPassword)) {
            $error = false;
        }
        else
        {
            $error = true;
        }
        
        return response()->json([
                "error"=>$error
            ]);
    }

    public function changePasswordProcess(Request $request)
    {
        // dd($request->all());
        $validator = $request->validate([
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_new_password'  => 'required',
           
        ]);


        $user= User::where('id',Auth::user()->id)->first();
        // dd($user);
        if($user)
        {
           
            $hashedPassword=Auth::user()->password;
            $old_password =  $request['old_password'];
            if (Hash::check($old_password, $hashedPassword)) 
            {
                if($request['new_password'] == $request['confirm_new_password'])
                {
                     $user->password=bcrypt($request['new_password']);
                }
                
            }
            $user->save();
        }

        return response()->json(['success'=>true]);
       
    }

    public function profile()
    {
    	$user_states=[];
    	$countries = Country::orderBy('name','ASC')->get();
        $user_detail = UserDetail::where('user_id',Auth::user()->id)->first();
        if($user_detail){
        	$user_states= State::where('country_id',$user_detail->country_id)->get();
    	}
    	return view('importing.profile-setting.index',['countries'=>$countries,'user_detail'=>$user_detail,'user_states'=>$user_states]);
    }

    public function updateProfile(Request $request)
    {
    	$validator = $request->validate([
    		'name' => 'required',
    		'company' => 'required',
    		'address' => 'required',
    		'country' =>'required',
    		'state' =>'required',
    		'city' =>'required',
    		'zip_code' =>'required',
    		'phone_number' =>'required',
    		'image' =>'image|mimes:jpeg,png,jpg,gif,svg|max:1024',
    	]);

        
        $error = false;
        $user = User::where('id',Auth::user()->id)->first();
        if($user)
		{
            // dd('here');
			$user->name=$request['name'];			
			$user->save();

			$user_detail = UserDetail::where('user_id',Auth::user()->id)->first();
			if($user_detail)
			{					
				$user_detail->address 		= $request['address'];					
				$user_detail->country_id	= $request['country'];
				$user_detail->state_id 		= $request['state'];
				$user_detail->city_name 	= $request['city'];
				$user_detail->zip_code 		= $request['zip_code'];
				$user_detail->phone_no 		= $request['phone_number'];
				$user_detail->company_name	= $request['company'];

								
				//image

				if($request->hasFile('image') && $request->image->isValid())
				{					
			      $fileNameWithExt = $request->file('image')->getClientOriginalName();
			      $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
			      $extension = $request->file('image')->getClientOriginalExtension();
			      $fileNameToStore = $fileName.'_'.time().'.'.$extension;
			      $path = $request->file('image')->move('public/uploads/importing/images/',$fileNameToStore);
			      $user_detail->image = $fileNameToStore;
				}			
				
				$user_detail->save();

				return response()->json([
					"error"=>$error
				]);
			}else{
                // dd('here');
                $user_detail = new UserDetail;
                $user_detail->user_id = Auth::user()->id;
                $user_detail->address 		= $request['address'];					
				$user_detail->country_id	= $request['country'];
				$user_detail->state_id 		= $request['state'];
				$user_detail->city_name 	= $request['city'];
				$user_detail->zip_code 		= $request['zip_code'];
				$user_detail->phone_no 		= $request['phone_number'];
				$user_detail->company_name	= $request['company'];

								
				//image

				if($request->hasFile('image') && $request->image->isValid())
				{					
			      $fileNameWithExt = $request->file('image')->getClientOriginalName();
			      $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
			      $extension = $request->file('image')->getClientOriginalExtension();
			      $fileNameToStore = $fileName.'_'.time().'.'.$extension;
			      $path = $request->file('image')->move('public/uploads/importing/images/',$fileNameToStore);
			      $user_detail->image = $fileNameToStore;
				}			
				
				$user_detail->save();

				return response()->json([
					"error"=>$error
				]);
            }

		}   
    
    }
   
}
