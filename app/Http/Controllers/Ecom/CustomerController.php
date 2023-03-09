<?php

namespace App\Http\Controllers\Ecom;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Country;
use App\Models\Common\TableHideColumn;
use App\Models\Common\CustomerCategory;
use App\Models\Common\PaymentTerm;
use App\Models\Common\PaymentType;
use App\Models\Common\State;
use App\Models\Sales\Customer;
use App\Models\Common\Order\Order;
use App\User;
use Carbon\Carbon;
use Auth;
use Yajra\Datatables\Datatables;

class CustomerController extends Controller
{
    public function index(){

    	  $countries    = Country::orderby('name', 'ASC')->pluck('name', 'id');
      $table_hide_columns = TableHideColumn::where('user_id', Auth::user()->id)->where('type', 'customer_list')->first();
      $category     = CustomerCategory::all()->pluck('title','id');
      $payment_term = PaymentTerm::all()->pluck('title','id');
      $payment_types= PaymentType::all();
      $states       = State::select('id','name')->orderby('name', 'ASC')->where('country_id',217)->get();
      $users        = User::where('status',1)->where('role_id',3)->whereNull('parent_id')->get();
      $customer_categories = CustomerCategory::all();
      // $states = State::where('country_id',217)->get();
      return view('ecom.home.customer',compact('category','countries','table_hide_columns','payment_term','payment_types','states','users','customer_categories'));
    }
    public function getEcomData(Request $request){

    	
    	 //$query = Customer::where('ecommerce_customer','1')->get();
       $query = Customer::with('getcountry', 'getstate','getpayment_term')->where('ecommerce_customer', 1);
 
        return Datatables::of($query)
        ->addColumn('checkbox', function ($item) {

            $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                            <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="stone_check_'.$item->id.'">
                            <label class="custom-control-label" for="stone_check_'.$item->id.'"></label>
                        </div>';
            return $html_string;
        })
        ->addColumn('address', function ($item){
            return $item->address_line_1 !== null ? $item->address_line_1 . ' ' . $item->address_line_2 : 'N.A';
        })
        ->addColumn('category', function ($item) {
          if($item->category_id == null)
          {
            return 'N.A';
          }
          else{
          return $item->CustomerCategory->title;
          }
        })
        ->editColumn('company', function ($item) {

            return $item->company !== null ? @$item->company : 'N.A';
        })
        ->editColumn('reference_number', function ($item) {
            if($item->reference_number !== null)
            {
              $html_string = '<a href="'.url('sales/get-customer-detail/'.$item->id).'"  ><b>'.$item->reference_number.'</b></a>';
            }
            else
            {
              $html_string = '--';
            }
            return $html_string;
        })
        ->editColumn('reference_name', function ($item) {
            if($item->reference_name !== null)
            {
              $html_string = '<a href="'.url('sales/get-customer-detail/'.$item->id).'"><b>'.$item->reference_name.'</b></a>';
            }
            else
            {
              $html_string = '--';
            }
            return $html_string;
        })
        ->addColumn('user_id', function ($item) {
          $html_string = '<span class="m-l-15 inputDoubleClick" id="primary_salesperson_id" data-fieldvalue="'.@$item->primary_sale_id.'" data-id="salesperson '.@$item->primary_sale_id.' '.@$item->id.'"> ';
            $html_string .= ($item->primary_sale_id != null) ? $item->primary_sale_person->name: "--";
            $html_string .= '</span>';

            $html_string .= '<div class="incomplete-filter d-none inc-fil-salesperson">  
            <select data-row_id="'.@$item->id.'" class=" font-weight-bold form-control-lg form-control js-states state-tags select-common primary_salesperson_id primary_salespersons_select'.@$item->id.'" name="primary_salesperson_id" required="true">';
                // <option value="">Choose Category</option>';

            // $product_parent_category = ProductCategory::select('id','title')->where('parent_id',0)->orderBy('title')->get();
                
            // if($product_parent_category->count() > 0){
            // foreach($product_parent_category as $pcat){

            // $html_string .= '<optgroup label='.$pcat->title.'>';
            //         $subCat = ProductCategory::select('id','title')->where('parent_id',$pcat->id)->orderBy('title')->get();
            //       foreach($subCat as $scat){
            // $html_string .= '<option '.($scat->id == $item->category_id ? 'selected' : '' ).' value="'.$scat->id.'">'.$scat->title.'</option>';
            //       }
            // $html_string .= '</optgroup>';
            
            // }
            // }
            $html_string .= '</select></div>';
            return $html_string;
          //$item->primary_sale_id !== null ? @$item->primary_sale_person->name : 'N.A';
        })
        ->addColumn('secondary_sp', function ($item) {
          $html_string = '<span class="m-l-15 inputDoubleClick" id="secondary_salesperson_id" data-fieldvalue="'.@$item->secondary_sale_id.'" data-id="secondary_salesperosn '.@$item->secondary_sale_id.' '.@$item->id.'"> ';
            $html_string .= ($item->secondary_sale_id != null) ? $item->secondary_sale_person->name: "--";
            $html_string .= '</span>';

            $html_string .= '<div class="incomplete-filter d-none inc-fil-salesperson">  
            <select data-row_id="'.@$item->id.'" class="font-weight-bold form-control-lg form-control js-states state-tags select-common secondary_salesperson_id secondary_salespersons_select'.@$item->id.'" name="secondary_salesperson_id" required="true">';
                // <option value="">Choose Category</option>';

            // $product_parent_category = ProductCategory::select('id','title')->where('parent_id',0)->orderBy('title')->get();
                
            // if($product_parent_category->count() > 0){
            // foreach($product_parent_category as $pcat){

            // $html_string .= '<optgroup label='.$pcat->title.'>';
            //         $subCat = ProductCategory::select('id','title')->where('parent_id',$pcat->id)->orderBy('title')->get();
            //       foreach($subCat as $scat){
            // $html_string .= '<option '.($scat->id == $item->category_id ? 'selected' : '' ).' value="'.$scat->id.'">'.$scat->title.'</option>';
            //       }
            // $html_string .= '</optgroup>';
            
            // }
            // }
            $html_string .= '</select></div>';
            return $html_string;
          return $item->secondary_sale_id !== null ? @$item->secondary_sale_person->name : 'N.A';
        })
        ->addColumn('country', function ($item) {

            return $item->country !== null ? @$item->getcountry->name : 'N.A';
        })
        ->addColumn('state', function ($item) {
          // $customerAddress = CustomerBillingDetail::select('billing_state')->where('customer_id',$item->id)->where('is_default',1)->first();

          $customerAddress = $item->getbilling()->select('billing_state')->where('is_default',1)->first();
            if($customerAddress)
            {
              return $customerAddress->billing_state !== null ? @$customerAddress->getstate->name : 'N.A';
            }
            else
            {
              return 'N.A';
            }
        })
        ->addColumn('phone', function ($item) {

            return $item->phone !== null ? @$item->phone : 'N.A';
        })
        ->addColumn('credit_term', function ($item) {

            return $item->getpayment_term !== null ? @$item->getpayment_term->title : 'N.A';
        })
        ->addColumn('email', function ($item) {

            return $item->email !== null ? @$item->email : 'N.A';
        })
        ->addColumn('city', function ($item) {
          // $customerAddress = CustomerBillingDetail::select('billing_city')->where('customer_id',$item->id)->where('is_default',1)->first();

          $customerAddress = $item->getbilling()->select('billing_city')->where('is_default',1)->first();
          if($customerAddress != null)
          {
            return $customerAddress->billing_city !== null ? @$customerAddress->billing_city : 'N.A';
          }
          else
          {
            return 'N.A';
          }
        })
        ->addColumn('postalcode', function ($item) {

            return $item->postalcode !== null ? @$item->postalcode : 'N.A';
        })
        ->addColumn('created_at', function ($item) {

          return $item->created_at !== null ? Carbon::parse(@$item->created_at)->format('d/m/Y') : 'N.A';
        })

        ->addColumn('draft_orders', function ($item) {
          return $item->get_total_draft_orders($item->id);
        })

        ->addColumn('total_orders', function ($item) {
          // return $item->get_total_price_with_vat($item->id);
          $total = $item->customer_orders()->whereIn('primary_status',[2,3])->sum('total_amount');
          return number_format($total,2,'.',',');
        })

        ->addColumn('last_order_date', function ($item) {
          // $orders = Order::where('customer_id',$item->id)->select('created_at')->whereIn('primary_status',[2,3])->orderby('id','desc')->first();
          $orders = $item->customer_orders()->whereIn('primary_status',[2,3])->orderBy('id','desc')->first();
          if($orders == null)
          {
            return 'N.A';
          }
          else
          {
            return Carbon::parse($orders->created_at)->format('d/m/Y');
          }
        })

        ->addColumn('action', function ($item) {
          $html_string = '';
          if($item->status != 0 && Auth::user()->role_id != 7)
          {
            if($item->status == 1)
            {
              if(Auth::user()->role_id != 4){
              $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon suspend-customer" data-id="'.$item->id.'" title="Suspend"><i class="fa fa-ban"></i></a>';
            }
                //<a href="javascript:void(0);" class="actionicon deleteIcon custDeleteIcon" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                //<a href="javascript:void(0);" class="actionicon editIcon" data-id="' . $item->id . '" title="Edit"><i class="fa fa-edit"></i></a> ';

            }
            else
            {
              if(Auth::user()->role_id != 4)
              {
                // $html_string .= ' <a href="javascript:void(0);" class="actionicon viewIcon activateIcon" data-id="'.$item->id.'" title="Activate"><i class="fa fa-check"></i></a>';
                  $html_string .= ' <a href="javascript:void(0);" class="actionicon deleteIcon delete-customer" data-id="'.$item->id.'" title="Delete"><i class="fa fa-trash"></i></a>';
              }
            }
          }
          if($item->status == 0 && Auth::user()->role_id != 7)
          {
            if(Auth::user()->role_id != 4)
            {
              $html_string .= ' <a href="javascript:void(0);" class="actionicon deleteIcon delete-customer" data-id="'.$item->id.'" title="Delete"><i class="fa fa-trash"></i></a>';
            }
            $html_string .= '<a href="'.url('sales/get-customer-detail/'.$item->id).'" class="actionicon" title="View Detail"><i class="fa fa-eye"></i></a>';
          }

          return @$html_string;
        })

        ->rawColumns(['action', 'country', 'state', 'email', 'city', 'postalcode','created_at','draft_orders','total_orders','last_order_date','notess','reference_name','checkbox','reference_number','user_id','secondary_sp'])
        ->make(true);


    }
    public function EcomCancelledOrders(){
      return view('ecom.home.ecom-cancelled-orders');
    }

    public function EcomCancelledOrdersData(Request $request){

    
        $query = Order::with('customer')->orderBy('id','DESC');

      if($request->status_filter == "draft"){
        $query = $query->where('in_status_prefix',null);
      }
      if($request->status_filter == "invoice"){
        $query = $query->where('in_status_prefix','!=',null);
      }
      if($request->from_date != null)
      {
        $date = str_replace("/","-",$request->from_date);
        $date =  date('Y-m-d',strtotime($date));
        $query = $query->where('target_ship_date', '>=', $date);
      }
      if($request->to_date != null)
      {
        $date_to = str_replace("/","-",$request->to_date);
        $date_to =  date('Y-m-d',strtotime($date_to));
        $query = $query->where('target_ship_date', '<=', $date_to);
      }
        $query->where(function($q){
         $q->where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->orderBy('orders.id', 'DESC');
        });
      
        // dd($query->get());
        return Datatables::of($query)

            ->addColumn('checkbox', function ($item) { 
                    
                    $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check1" value="'.$item->id.'" id="quot_'.$item->id.'">
                                    <label class="custom-control-label" for="quot_'.$item->id.'"></label>
                                </div>';
                    return $html_string;         
                })

            ->addColumn('customer', function ($item) { 
                   if($item->customer_id != null){
                    if(Auth::user()->role_id == 3){
                        if($item->customer['reference_name'] != null)
                        {
                            // $html_string = $item->customer['reference_name'];
                            $html_string = '
                  <a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.$item->customer['reference_name'].'</b></a>';
                        }
                        else
                        {
                          $html_string = '
                  <a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'. $item->customer['first_name'].' '.$item->customer['last_name'].'</b></a>';
                            // $html_string = $item->customer['first_name'].' '.$item->customer['last_name'];
                        }
                      }else{
                        if($item->customer['reference_name'] != null)
                        {
                            // $html_string = $item->customer['reference_name'];
                            $html_string = '
                  <a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.$item->customer['reference_name'].'</b></a>';
                        }
                        else
                        {
                          $html_string = '
                  <a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'. $item->customer['first_name'].' '.$item->customer['last_name'].'</b></a>';
                            // $html_string = $item->customer['first_name'].' '.$item->customer['last_name'];
                        }
                      }
                    }else{
                        $html_string = 'N.A';
                    }
                  
                 
                  return $html_string;         
              })

            ->addColumn('customer_ref_no',function($item){
              if(Auth::user()->role_id == 3){
               return '
              <a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.@$item->customer->reference_number.'</b></a>';
            }else{
              return '
              <a href="'.url('sales/get-customer-detail/'.@$item->customer_id).'"><b>'.@$item->customer->reference_number.'</b></a>';
            }
                // return $item->customer->reference_number;
            })

            ->addColumn('target_ship_date',function($item){
                return Carbon::parse(@$item->target_ship_date)->format('d/m/Y');
            })

             ->addColumn('memo',function($item){
                return @$item->memo != null ? @$item->memo : '--';
            })

            ->addColumn('status',function($item){
                    $html = '<span class="sentverification">'.@$item->statuses->title.'</span>';
                    return $html;
                })

            ->addColumn('number_of_products', function($item) {
                  $html_string = $item->order_products->count();
                  return $html_string;  
              })

            ->addColumn('sales_person', function($item) { 
                // return ($item->user !== null ? $item->user->name : '--');
                return ($item->customer !== null ? @$item->customer->primary_sale_person->name : '--');
              })

            ->addColumn('ref_id', function($item) { 
              if($item->status_prefix !== null)
              {
                $ref_no = $item->status_prefix.'-'.$item->ref_prefix.$item->ref_id;
                $html_string = '<a href="'.route('get-cancelled-order-detail', ['id' => $item->id]).'"><b>'.$ref_no.'</b></a>';
              }
              else
              { 
                $ref_no = '--';
                $html_string = '--';
              }
              return $html_string;              
            })

            ->addColumn('in_ref_id', function($item) { 
              if($item->in_status_prefix !== null)
              {
                $ref_no = $item->in_status_prefix.'-'.$item->in_ref_prefix.$item->in_ref_id;
                $html_string = '<a href="'.route('get-cancelled-order-detail', ['id' => $item->id]).'"><b>'.$ref_no.'</b></a>';
              }
              else
              { 
                $ref_no = '--';
                $html_string = '--';
              }
              return $html_string;              
            })

            ->addColumn('payment_term', function($item) { 
              return (@$item->customer->getpayment_term !== null ? @$item->customer->getpayment_term->title : '');
            })

            ->addColumn('invoice_date', function($item) { 
          
              return Carbon::parse(@$item->updated_at)->format('d/m/Y');

            })

            ->addColumn('total_amount', function($item) { 
          
              return number_format($item->total_amount,2,'.',',');
            })

            ->addColumn('action', function ($item) { 
              // $html_string = '<a href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon text-center"><i class="fa fa-eye"></i></a>';
              $html_string = '';
              if($item->ecommerce_order == 1 && $item->primary_status == 17 && $item->status == 18)
              {
                $html_string .= '<a href="'.route('get-cancelled-order-detail', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon text-center"><i class="fa fa-eye"></i></a>';
              }
             
              return $html_string;         
              })
              ->rawColumns(['action','ref_id','in_ref_id','sales_person', 'customer', 'number_of_products','status','customer_ref_no','checkbox'])
              ->make(true);

    }
    public function EcomPinGenerate(Request $request){
      $link = 'api/createnewpin';
      $response =  curl_call($link, $request);
    }

    public function curl_call($link, $data){
        $url =  config('app.ecom_url').$link;
        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "content-type: application/json",
            "postman-token: 08f91779-330f-bf8f-1a64-d425e13710f9"
          ),
        ));

        $response = curl_exec($curl);
        
        $err = curl_error($curl);
        curl_close($curl);
        // if ($err) {
        //     return "cURL Error #:" . $err;
        // } else {
        //     return $response;
        // }
    }


}


