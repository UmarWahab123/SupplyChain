<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Mail\Backend\AddWarehouseEmail;
use App\Models\Common\EmailTemplate;
use Yajra\Datatables\Datatables;
use Illuminate\Http\Request;
use App\Models\Common\Role;
use App\Models\Common\Warehouse;
use Carbon\Carbon;
use App\User;
use App\Models\Common\Product;
use Mail;
use App\Helpers\MyHelper;
use App\Models\Common\WarehouseProduct;
use App\QuotationConfig;
use App\Models\Common\WarehouseZipCode;


class WarehouseController extends Controller
{
    public function index()
    {	
    	return $this->render('backend.warehouse.index');
    }

    public function getData()
    {
        $query = User::with('roles')->whereHas('roles', function($query){
            $query->where('role_id', '=','6');
        })->select('users.*')->get();

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
            $html_string .= '</div>';
            return $html_string;         
        })
        ->addColumn('default',function ($item){
            $html = '';
            $defaultUser = User::where('is_default','=',1);
            if($defaultUser->count())
            {
                if($item->is_default)
                {
                    $html .= ' <a href="javascript:void(0);" class="actionicon viewIcon ResetdefaultIcon" data-id="'.$item->id.'" title="Reset Default"><i class="fa fa-times"></i></a>';                     
                }
                else
                {

                }
            }
            else
            {
                $html .= ' <a href="javascript:void(0);" class="actionicon viewIcon MakedefaultIcon" data-id="'.$item->id.'" title="Make Default"><i class="fa fa-check"></i></a>';                     
            }
            return $html;
        })
            
        ->rawColumns(['action', 'status','default'])
        ->make(true);
    }

    public function getAll()
    {
        $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();
        $check_status =  unserialize($ecommerceconfig->print_prefrences);
        $ecommerceconfig_status = $check_status['status'][0];
        $query = Warehouse::all();
        return Datatables::of($query)
        
        ->addColumn('action', function ($item) {

            // $html = '<a class="btn button-st" data-toggle="modal" data-target="#addWarehouseModal'.$item->id.'"><i class="fa fa-edit"></i></a>';
            if($item->status == 1)
            {
                $html_string = ' <a style="text-align: center;" href="javascript:void(0);" class="actionicon deleteIcon suspend-warehouse" data-id="'.$item->id.'" title="Suspend"><i class="fa fa-ban"></i></a>';
            }
            else
            {
                $html_string = ' <a style="text-align: center;" href="javascript:void(0);" class="actionicon tickIcon enable-warehouse" data-id="'.$item->id.'" title="Activate"><i class="fa fa-check"></i></a>';
            }
            return $html_string;
        })
        ->addColumn('title', function ($item) {
            $html_string = '<span class="m-l-15 inputDoubleClick" id="warehouse_title"  data-fieldvalue="'.($item->warehouse_title != null ? $item->warehouse_title : '').'">'.($item->warehouse_title != null ? $item->warehouse_title : '--').'</span>
            <input type="text" autocomplete="nope" style="width:100%;" name="warehouse_title" class="fieldFocus d-none" value="'.($item->warehouse_title != null ? $item->warehouse_title : '').'" data-id="'.$item->id.'">';

            return $html_string;
        })
        ->addColumn('code',function($item){
            $html_string = '<span class="m-l-15 inputDoubleClick" id="language_code"  data-fieldvalue="'.@$item->location_code.'">'.(@$item->location_code != NULL ? @$item->location_code : "--").'</span>
            <input type="text" autocomplete="nope" style="width:100%;" name="location_code" class="fieldFocus d-none" value="'.@$item->location_code.'" data-id="'.$item->id.'">';
            return $html_string;
        })
        ->addColumn('default_zipcode', function ($item) { 
           
            $html_string = '<span class="m-l-15 inputDoubleClick" id="language_code"  data-fieldvalue="'.@$item->default_zipcode.'">'.(@$item->default_zipcode != NULL ? @$item->default_zipcode : "--").'</span>
            <input type="text" autocomplete="nope" style="width:100%;" name="default_zipcode" class="fieldFocus d-none" value="'.@$item->default_zipcode.'" data-id="'.$item->id.'">';
            return $html_string;      
        })
        ->addColumn('default_shipping', function ($item) { 
        
            $html_string = '<span class="m-l-15 inputDoubleClick" id="language_code"  data-fieldvalue="'.@$item->default_shipping.'">'.(@$item->default_shipping != NULL ? @$item->default_shipping : "--").'</span>
            <input type="text" autocomplete="nope" style="width:100%;" name="default_shipping" class="fieldFocus d-none" value="'.@$item->default_shipping.'" data-id="'.$item->id.'">';
            return $html_string;         
        })
        ->addColumn('associated_zip_codes', function ($item) { 
            $html_string = '<a href='.url("admin/warehouse-zipcodes/".$item->id).' class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>' ;
            return $html_string;         
        })
        ->addColumn('created_at', function ($item) { 
            return $item->created_at != null ?  Carbon::parse(@$item->created_at)->format('d/m/Y') : '--';         
        })
        ->addColumn('updated_at', function ($item) { 
            return $item->updated_at != null ?  Carbon::parse(@$item->updated_at)->format('d/m/Y') : '--';        
        })

        ->addColumn('is_bonded', function ($item) {
            $yes_no = $item->is_bonded == 1 ? 'Yes' : 'No';
            $html_string = '
            <span class="m-l-15 inputDoubleClick" id="is_bonded"  data-fieldvalue="'.@$item->is_bonded.'">';
            $html_string .= '<b>'.$yes_no.'</b>';
            $html_string .= '</span>';
            $no = $item->is_bonded == 0 ? 'selected' : '';
            $yes = $item->is_bonded == 1 ? 'selected' : '';

            $html_string .= '<select name="is_bonded" id="select_is_bonded" class="select-common form-control is_bonded d-none" data-id="'.$item->id.'" style="width:100%;"><option disabled value="">Select One Option</option>';

            $html_string .= '<option value="0" '.$no.'>No</option>';
            $html_string .= '<option value="1" '.$yes.'>Yes</option>';
            $html_string .= '</select>';
           return $html_string;
        })
        
        ->rawColumns(['title','code','default_zipcode','default_shipping','associated_zip_codes','created_at','updated_at','is_bonded','action','table_hide_columns'])
        ->make(true);
    }

    public function activateSelectedWarehouse(Request $request)
    {
        $getWarehouse = Warehouse::find($request->warehouse_id);
        if($getWarehouse)
        {
            $getWarehouse->status = 1;
            $getWarehouse->save();
        }
        return response()->json([ 'success' => true ]);
    }

    public function suspendSelectedWarehouse(Request $request)
    {
        $getWarehouse = Warehouse::find($request->warehouse_id);
        $w_id = $request->warehouse_id;
        if($getWarehouse)
        {
            $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();
            if($ecommerceconfig)
            {
                $check_status =  unserialize($ecommerceconfig->print_prefrences);
                $ecommerceconfig_status = $check_status['status'][5];

                if($ecommerceconfig_status == $request->warehouse_id)
                {
                    return response()->json([ 'ecom_enabled_warhouse' => true]);
                }
            }

            $find_users = User::where('warehouse_id', $request->warehouse_id)->get();
            if($find_users->count() > 0)
            {
                $users_array = array();
                $html_string = '';
                $html_string .= '<table id="example" class="table table-responsive headings-color dataTable const-font" style="width: 100%;">';
                $html_string .= '<tbody>';
                $i = 1;
                $break = 0;
                $html_string .= '<tr>';
                foreach ($find_users as $user) 
                {
                    array_push($users_array, $user->id);
                    $html_string .= '<td align="left">'.$i.".".'</td>';
                    $html_string .= '<td align="left">'.$user->name.'</td>';
                    
                    $break++;
                    if($break == 4)
                    {
                        $html_string .= '</tr>';
                        $html_string .= '<tr>';
                        $break = 0;
                    }
                    $i++;
                }
                $html_string .= '</tbody>';
                $html_string .= '</table>';

                $warehouses_str = '';
                $getAllWarehouses = Warehouse::where('id','!=',$request->warehouse_id)->where('status',1)->get();
                $warehouses_str .= '<option value="">Choose Warehouse</option>';
                if($getAllWarehouses->count() > 0)
                {
                    foreach ($getAllWarehouses as $value) 
                    {
                        $warehouses_str .= '<option value="'.$value->id.'">'.$value->warehouse_title.'</option>';
                    }
                }
                return response()->json([ 'success' => false, 'html_string' => $html_string, 'w_name' => $getWarehouse->warehouse_title, 'warehouses_str' => $warehouses_str, 'users_array' => $users_array, 'w_id' => $w_id]);
            }
            else
            {
                $getWarehouse->status = 2;
                $getWarehouse->save();
                return response()->json([ 'success' => true ]);
            }
        }
    }

    public function changeUsersWarehouse(Request $request)
    {
        $users_array = explode(',', $request->users_ids);
        for($i=0; $i<sizeof($users_array); $i++)
        {
            $getUser = User::find($users_array[$i]);
            if($getUser)
            {
                $getUser->warehouse_id = $request->new_warehouse;
                $getUser->save();
            }
        }

        $warehouse = Warehouse::find($request->old_w_id);
        if($warehouse)
        {
            // 2 is for suspended warehouse
            $warehouse->status = 2;
            $warehouse->save();
        }

        return response()->json([ 'success' => true ]);
    }

    public function add(Request $request)
    {
    	$validator = $request->validate([
    		'title' => 'required',
    		'location_code' => 'required',
    	]);
        
        $warehouse = new Warehouse();
        $warehouse->warehouse_title=$request->title;
        $warehouse->location_code=$request->location_code;
        $warehouse->company_id=1;
        $warehouse->is_bonded = $request->is_bonded_warehouse;
        $warehouse->save();
        $product=Product::get();
        $warehouse_products=[];
        foreach($product as $p)
        {
            $warehouse_products[] =[
            'warehouse_id' => $warehouse->id, 
            'product_id'=> $p->id
            ];
           
        }
        WarehouseProduct::insert($warehouse_products); 
    	return response()->json(['success' => true]);

    	// $template= EmailTemplate::where('type','create-warehouse')->first();
    	// // send email //
        // $my_helper =  new MyHelper;
        // $result_helper=$my_helper->getApiCredentials();
        // if($result_helper['email_notification'] == 1)
        // {
    	// Mail::to($warehouse->email, $warehouse->name)->send(new AddWarehouseEmail($warehouse, $password,$template));
        // }
    }

    public function resetDefault(Request $request)
    {
        $warehouse = User::find($request->id);
        $warehouse->is_default = 0;
        $warehouse->update();
        return response()->json(['error' => false,'successmsg' => ucfirst($request->type).' Default Reset.']);
    }

    public function setDefault(Request $request)
    {
        $warehouse = User::find($request->id);
        $warehouse->is_default = 1;
        $warehouse->update();
        return response()->json(['error' => false,'successmsg' => ucfirst($request->type).' Default Assign.']);
    }

    public function saveWarehouseData(Request $request)
    {
        $table_hide_columns = ['4,5'];

        $warehouse = Warehouse::where('id',$request->id)->first();

        foreach($request->except('id') as $key => $value)
        {
            if($value == '')
            {
              // $customer_contacts->$key = null;
            }
            else
            {
              $warehouse->$key = $value;
            }
        }
        $warehouse->save();
        return response()->json(['success' => true]);
    }

    public function updateWarehouse(Request $request)
    {
       $warehouse_id = $request->warehouse_id;
       $is_bonded = $request->is_bonded;

       $warehouse = Warehouse::find($warehouse_id);
       $warehouse->is_bonded = $is_bonded;
       $warehouse->save();

       return response()->json(['success'=>true]);
    }

    public function showzipcodes(Request $request)
    {
        $w_id = $request->id;
        $warehouse = Warehouse::find($request->id);
        $checked = $warehouse->free_shipment_enabled == 1 ? 'checked' : '';
        if($checked == 'checked')
        {
            $disabled = '';
        }
        else
        {
            $disabled = 'disabled';
        }
        if($checked == 'checked')
        {
            $value = $warehouse->free_shipment_enabled_value;
        }
        else
        {
            $value = null;
        }
        return view('backend.warehouse.warehouse_zipcodes', compact('warehouse','checked','disabled','value','w_id'));
    }

    public function showallzipcodes(Request $request)
    {
        $query = WarehouseZipCode::where('warehouse_id', $request->id);
        return Datatables::of($query)
        
        ->addColumn('action', function ($item) {
            $html_string = '<a href="javascript:void(0);" class="actionicon deleteIcon deleteWarehouseZipcode" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
            return $html_string;
        })

        ->editColumn('name', function ($item) { 
            $html_string = '<span class="m-l-15 inputDoubleClick" id="language_code"  data-fieldvalue="'.@$item->name.'">'.(@$item->name != NULL ? @$item->name : "--").'</span>
            <input type="text" autocomplete="nope" style="width:100%;" name="name" class="fieldFocus d-none" value="'.@$item->name.'" data-id="'.$item->id.'">';
            return $html_string;
        })
        ->editColumn('zipcode',function ($item){
            $html_string = '<span class="m-l-15 inputDoubleClick" id="language_code"  data-fieldvalue="'.@$item->zipcode.'">'.(@$item->zipcode != NULL ? @$item->zipcode : "--").'</span>
            <input type="text" autocomplete="nope" style="width:100%;" name="zipcode" class="fieldFocus d-none" value="'.@$item->zipcode.'" data-id="'.$item->id.'">';
            return $html_string;
        })
        ->editColumn('shipping_charges',function ($item){
            $html_string = '<span class="m-l-15 inputDoubleClick" id="language_code"  data-fieldvalue="'.@$item->shipping_charges.'">'.(@$item->shipping_charges != NULL ? @$item->shipping_charges : "--").'</span>
            <input type="text" autocomplete="nope" style="width:100%;" name="shipping_charges" class="fieldFocus d-none" value="'.@$item->shipping_charges.'" data-id="'.$item->id.'">';
            return $html_string;
        })
        ->addColumn('free_shipment', function ($item) {
            $check_war = Warehouse::where('id',$item->warehouse_id)->first();
            $maximum_amount = $item->maximum_amount;
            $checked = $item->free_shipment_enabled == 1 ? 'checked' : '';
            $disabled = ($item->free_shipment_enabled == 0 || $check_war->free_shipment_enabled == 1) ? 'disabled' : '';
            $checkbox_disabled = $check_war->free_shipment_enabled == 1 ? 'disabled' : '';
            $html_string = '<div class="d-flex pl-4">
                <div><input type="checkbox" class="enable_free_shipment" name="enable_free_shipment" data-id="'.$item->id.'" '.$checked.' '.$checkbox_disabled.' /></div>
                <div class="pl-4"><input type="number" class="maximum_amount fieldFocusShipment" id="maximum_amount_'.$item->id.'" value="'.$maximum_amount.'" data-id="'.$item->id.'" name="maximum_amount" '.$disabled.'/> </div
            </div>';
            return $html_string;
        })
        
        ->rawColumns(['action', 'name','zipcode', 'shipping_charges','free_shipment'])
        ->make(true);
    }

    public function addzipcodes(Request $request)
    {
        $error = false;
        $default_zipcode =  Warehouse::where('default_zipcode', $request->zipcode)->where('status',1)->first();
        if($default_zipcode)
        {
            $error = true;
            return response()->json(['success' => false, 'message'=> 'Zipcode already exists as Default Zipcode']);
        }
        $checkwh_zipcode = WarehouseZipCode::where('zipcode', $request->zipcode)->first();

        if($checkwh_zipcode)
        {
            $error = true;
            return response()->json(['success' => false, 'message'=> 'Zipcode already exists.']);
        }
        if($error == false)
        {
            $zipcodes = new WarehouseZipCode;
            $zipcodes->name = $request->name;
            $zipcodes->zipcode = $request->zipcode;
            $zipcodes->shipping_charges = $request->shipping_charges;
            $zipcodes->warehouse_id = $request->warehouse_id;
            $zipcodes->save();
            return response()->json(['success' => true]);    
        }
    }

    public function savewarehousezipcode(Request $request)
    {
        if($request->maximum_amount != null)
        {
            $checkwh_zipcode = WarehouseZipCode::where('id', $request->id)->first();
            $checkwh_zipcode->maximum_amount = $request->maximum_amount;
            $checkwh_zipcode->free_shipment_enabled = 1;
            $checkwh_zipcode->save();
            return response()->json(['success' => true]);
        }
        elseif($request->enable_free_shipment != null)
        {
            $checkwh_zipcode = WarehouseZipCode::where('id', $request->id)->first();
            $checkwh_zipcode->free_shipment_enabled = $request->enable_free_shipment;
            $checkwh_zipcode->save();
            return response()->json(['success' => true]);
        }
        else
        {
            $error = false;
            if($request->zipcode)
            {
                $default_zipcode =  Warehouse::where('default_zipcode', $request->zipcode)->first();
                if($default_zipcode)
                {
                    $error = true;
                    return response()->json(['success' => false, 'message'=> 'Zipcode already exists as Default Zipcode']);
                }
                $checkwh_zipcode = WarehouseZipCode::where('zipcode', $request->zipcode)->first();

                if($checkwh_zipcode)
                {
                    $error = true;
                    return response()->json(['success' => false, 'message'=> 'Zipcode already exists.']);
                }
            }
            if($error == false)
            {
                $warehouse = WarehouseZipCode::where('id',$request->id)->first();
                foreach($request->except('id') as $key => $value){
                    if($value != ''){
                       $warehouse->$key = $value;
                    }
                }
                $warehouse->save();
                return response()->json(['success' => true]);
            }
        }
        
    }

    public function savewarehousezipcodeForAllRegions(Request $request)
    {
        // dd($request->all());
        if($request->maximum_amount_for_all != null && $request->maximum_amount_for_all == 'clear')
        {
            $warehouse = Warehouse::find($request->id);
            $warehouse->free_shipment_enabled = 0;
            $warehouse->free_shipment_enabled_value = null;
            $warehouse->save();
            $checkwh_zipcodes = WarehouseZipCode::where('warehouse_id', $request->id)->get();
            foreach($checkwh_zipcodes as $checkwh_zipcode)
            {
                $checkwh_zipcode->maximum_amount = $checkwh_zipcode->old_maximum_amount;
                $checkwh_zipcode->old_maximum_amount = null;
                $checkwh_zipcode->free_shipment_enabled = 1;
                $checkwh_zipcode->save();
            }

            return response()->json(['success' => true]);
        }
        if($request->maximum_amount_for_all != null)
        {
            $warehouse = Warehouse::find($request->id);
            $warehouse->free_shipment_enabled = 1;
            $warehouse->free_shipment_enabled_value = $request->maximum_amount_for_all;
            $warehouse->save();
            $checkwh_zipcodes = WarehouseZipCode::where('warehouse_id', $request->id)->get();
            foreach($checkwh_zipcodes as $checkwh_zipcode)
            {
                $checkwh_zipcode->old_maximum_amount = $checkwh_zipcode->maximum_amount;
                $checkwh_zipcode->save();

                $checkwh_zipcode->maximum_amount = $request->maximum_amount_for_all;
                $checkwh_zipcode->free_shipment_enabled = 1;
                $checkwh_zipcode->save();
            }
            
            return response()->json(['success' => true]);
        }
        else
        {
            return response()->json(['success' => false, 'message' => 'Opps !! Something went wrong please try again.']);
        }
    }

    public function deletewarehouezipcode(Request $request)
    {
        WarehouseZipCode::where('id',$request->id)->delete();
        return response()->json(['success' => true]);
    }
}
