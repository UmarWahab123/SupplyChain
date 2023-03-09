<?php

namespace App\Http\Controllers\Common;

use App\CustomerSecondaryUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Country;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerPaymentType;
use App\Models\Common\Configuration;
use App\Models\Common\CustomerProductFixedPrice;
use App\Models\Common\Order\CustomerNote;
use App\Models\Common\Order\CustomerShippingDetail;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\Order;
use App\Models\Sales\CustomerGeneralDocument;
use App\Models\Common\PaymentTerm;
use App\Models\Common\Order\DraftQuotation;
use App\Models\Common\EmailTemplate;
use App\Models\Common\PaymentType;
use App\Models\Common\Product;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\State;
use App\Models\Sales\Customer;
use App\Models\Common\CustomerContact;
use App\Events\LoggingStatus;
use App\Events\ProductCreated;

#Mails
use App\Mail\Backend\CustomerSuspensionEmail;
use App\Mail\Backend\CustomerActivationEmail;

use Auth;
use Mail;
use File;
use Carbon\Carbon;
use Yajra\Datatables\Datatables;
use App\User;
use Validator;
use App\Models\Common\UserDetail;

class CustomerController extends Controller
{
  public function index()
    {
      $user = Auth::user();
      $layout = '';
      if($user->role_id == 1)
      {
        $layout = 'backend';
      }
      elseif ($user->role_id == 2)
      {
        $layout = 'users';
      }
      elseif ($user->role_id == 3)
      {
        $layout = 'sales';
      }
        elseif ($user->role_id == 4)
        {
            $layout = 'sales';
        }
      elseif ($user->role_id == 5)
      {
        $layout = 'importing';
      }
      elseif ($user->role_id == 6)
      {
        $layout = 'warehouse';
      }
      if(Auth::user()->role_id == 3){
        $users = User::where('role_id',3)->get();
      }else{
         $warehouse_id = Auth::user()->warehouse_id;
                    $users = User::where('warehouse_id',$warehouse_id)->where('role_id',3)->get();
      }
      return view('common.customers.index',compact('user','layout','users'));
    }

    public function getData(Request $request)
    {
        // $query = Customer::with('getcountry', 'getstate','getpayment_term')->get();
        // dd($query);
      $user_id = $request->user_id;
      if(Auth::user()->role_id == 4){
        $warehouse_id = Auth::user()->warehouse_id;
        $users = User::select('id')->where('warehouse_id',$warehouse_id)->where('role_id',3)->get();
        $query = Customer::query();
         $query->with('getcountry', 'getstate','getpayment_term');
         $ids = array();
        foreach ($users as $user) {
          // $query = $query->where('status', 1)->where('user_id',$user->id)->orderBy('id', 'DESC');
          // dd($query->get());
          array_push($ids, $user->id);
        }
        if($request->customers_status != '')
        {
          if($request->user_id != ''){
            if($request->customers_type != ''){
                      if($request->customers_type == 0){
                          $query->where('status', $request->customers_status)->where(function($query) use ($user_id){
                          $query->where('secondary_sale_id', $user_id);
                        })->whereIn('user_id',$ids)->orderBy('id', 'DESC');
                    }else if($request->customers_type == 1){
                      $query->where('status', $request->customers_status)->where(function($query) use ($user_id){
                          $query->where('user_id', $user_id);
                        })->whereIn('user_id',$ids)->orderBy('id', 'DESC');
                    }
            }else{
            $query->where('status', $request->customers_status)->where(function($query) use ($user_id){
              $query->where('user_id',$user_id)->orWhere('secondary_sale_id',$user_id);
            })->whereIn('user_id',$ids)->orderBy('id', 'DESC');
          }
          }
          else{
            $query->where('status', $request->customers_status)->whereIn('user_id',$ids)->orderBy('id', 'DESC');
          }
        // $query->where('status',1)->whereIn('user_id',$ids)->orderBy('id','DESC');

      }
    }
      else{
         $query = Customer::query();
        $currency = Configuration::select('id','currency_id')->first();
        $query->with('getcountry', 'getstate','getpayment_term');

        if($request->customers_status != '')
        {
          if($request->user_id != ''){
            if($request->customers_type != ''){
                      if($request->customers_type == 0){
                          $query->where('status', $request->customers_status)->where(function($query) use ($user_id){
                          $query->where('secondary_sale_id', $user_id);
                        })->orderBy('id', 'DESC');
                    }else if($request->customers_type == 1){
                      $query->where('status', $request->customers_status)->where(function($query) use ($user_id){
                          $query->where('user_id', $user_id);
                        })->orderBy('id', 'DESC');
                    }
            }else{
            $query->where('status', $request->customers_status)->where(function($query) use ($user_id){
              $query->where('user_id',$user_id)->orWhere('secondary_sale_id',$user_id);
            })->orderBy('id', 'DESC');
          }
          }
          else{
            $query->where('status', $request->customers_status)->orderBy('id', 'DESC');
          }
        }
        else
        {
            $query->where('status', 1)->orderBy('id', 'DESC');
        }
    }
        return Datatables::of($query)
         ->addColumn('checkbox', function ($item) {

                    $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check" value="'.$item->id.'" id="stone_check_'.$item->id.'">
                                    <label class="custom-control-label" for="stone_check_'.$item->id.'"></label>
                                </div>';
                    return $html_string;
                })
            ->addColumn('name', function ($item) {

                return $item->first_name !== null ? $item->first_name . ' ' . $item->last_name : 'N.A';
            })
            ->addColumn('address', function ($item) {

                return $item->address_line_1 !== null ? $item->address_line_1 . ' ' . $item->address_line_2 : 'N.A';
            })
            ->editColumn('category_id', function ($item) {
              $category = CustomerCategory::where('id',$item->category_id)->first();
              if($category == null){
                return 'N.A';
              }else{

              return $category->title;
              }
            })
            ->editColumn('company', function ($item) {

                return $item->company !== null ? $item->company : 'N.A';
            })

            ->addColumn('user_id', function ($item) {

              return $item->user_id !== null ? $item->user->name : 'N.A';
          })
            ->editColumn('reference_name', function ($item) {
$html_string = '<a href="'.url('common/get-common-customer-detail/'.$item->id).'" class="" title="View Detail">'.($item->reference_name !== null ? $item->reference_name : "N.A").'</a>';
                return $html_string;
            })
            ->addColumn('country', function ($item) {

                return $item->country !== null ? $item->getcountry->name : 'N.A';
            })
            ->addColumn('state', function ($item) {

                return $item->state !== null ? $item->getstate->name : 'N.A';
            })
            ->addColumn('phone', function ($item) {

                return $item->phone !== null ? $item->phone : 'N.A';
            })
            ->addColumn('credit_term', function ($item) {

                return $item->getpayment_term !== null ? $item->getpayment_term->title : 'N.A';
            })
            ->editColumn('email', function ($item) {

                return $item->email !== null ? $item->email : 'N.A';
            })
            ->addColumn('city', function ($item) {

                return $item->city !== null ? $item->city : 'N.A';
            })
            ->addColumn('postalcode', function ($item) {

                return $item->postalcode !== null ? $item->postalcode : 'N.A';
            })
            ->addColumn('created_at', function ($item) {

              return $item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : 'N.A';
          })
          ->addColumn('draft_orders', function ($item) {
            $draft_orders = 0;
            $orders = Order::where('customer_id',$item->id)->where('primary_status',2)->get();
            foreach($orders as $order){
              $draft_orders += $order->order_products->sum('total_price_with_vat');
            }

            return number_format($draft_orders,2,'.',',');
        })
        ->addColumn('total_orders', function ($item) {
          $orders_total = 0;
            $orders = Order::where('customer_id',$item->id)->where('primary_status',2)->orWhere('primary_status',3)->get();
            foreach($orders as $order){
              $orders_total += $order->order_products->sum('total_price_with_vat');
            }

            return number_format($orders_total,2,'.',',');
      })
      ->addColumn('last_order_date', function ($item) {
        $orders = Order::where('customer_id',$item->id)->where('primary_status',2)->orWhere('primary_status',3)->orderby('id','desc')->first();
        if($orders == null){
          return 'N.A';
        }
        else{
        return Carbon::parse($orders->created_at)->format('d/m/Y');
        }
    })
    ->addColumn('notess', function ($item) {

      return 'No note found';
  })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    $status = '<span class="badge badge-pill badge-success font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Active</span>';
                } elseif ($item->status == 2) {
                    $status = '<span class="badge badge-pill badge-danger font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Suspended</span>';
                } else {
                    $status = '<span class="badge badge-pill badge-warning font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">InActive</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($item) {

              $html_string = '<a href="'.url('common/get-common-customer-detail/'.$item->id).'" class="actionicon" title="View Detail"><i class="fa fa-eye"></i></a>';

                return $html_string;
            })

            ->addColumn('shippingInfo', function ($item) {
                // check already uploaded images //
                $shippingInfo = CustomerShippingDetail::where('customer_id', $item->id)->count('id');

                $html_string = '<div class="d-flex justify-content-center text-center">';
                if($shippingInfo > 0){
                $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#shipping-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-shipping-info mr-2" title="View Shipping Info"></a>';
                }

                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_shipping_detail_modal" data-id="'.$item->id.'"  class="add-shipping-detail fa fa-plus" title="Add Shipping Info"></a>
                          </div>';
                return $html_string;
                })



                ->addColumn('notes', function ($item) {
                // check already uploaded images //
                $notes = CustomerNote::where('customer_id', $item->id)->count('id');

                $html_string = '<div class="d-flex justify-content-center text-center">';
                if($notes > 0){
                $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';
                }else{
                  if(Auth::user()->role_id == 5){
                    $html_string .= 'N.A';
                  }
                }
                if(Auth::user()->role_id == 1 || Auth::user()->role_id == 11){
                $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="'.$item->id.'"  class="add-notes fa fa-plus" title="Add Note"></a>
                          </div>';
                          }
                return $html_string;
                })

            ->rawColumns(['action', 'status', 'country', 'state', 'email', 'city', 'postalcode','notes','created_at','draft_orders','total_orders','last_order_date','notess','checkbox','reference_name','company','category_id'])
            ->make(true);

    }

    public function getCustomerDetail($id)
    {
      $user = Auth::user();
      $layout = '';
      if($user->role_id == 1)
      {
        $layout = 'backend';
      }
      elseif ($user->role_id == 2)
      {
        $layout = 'users';
      }
      elseif ($user->role_id == 3)
      {
        $layout = 'sales';
      }
         elseif ($user->role_id == 4)
        {
            $layout = 'sales';
        }
      elseif ($user->role_id == 5)
      {
        $layout = 'importing';
      }
      elseif ($user->role_id == 6)
      {
        $layout = 'warehouse';
      }

      $customer = Customer::with('getcountry','getstate')->where('id',$id)->first();
      $states = State::select('id','name')->orderby('name', 'ASC')->where('country_id',217)->get();
      $customerShipping = CustomerShippingDetail::with('getcountry','getstate')->where('customer_id',$id)->latest()->first(); //Here it will return only one result but we have many
      $customerBilling = CustomerBillingDetail::with('getcountry','getstate')->where('customer_id',$id)->where('is_default',1)->first(); //Here it will return only one result but we have many
      if(!$customerBilling){
        $customerBilling = CustomerBillingDetail::with('getcountry','getstate')->where('customer_id',$id)->latest()->first(); //Here it will return only one result but we have many
      }

      $customerNotes = CustomerNote::where('customer_id',$id)->get(); //Here it will return only one result but we have many
      $countries = Country::orderby('name', 'ASC')->get();
      $ProductCustomerFixedPrice = ProductCustomerFixedPrice::with('products','customers')->where('customer_id',$id)->get();
      $categories = CustomerCategory::where('is_deleted',0)->get();;
      $getCustShipping = CustomerShippingDetail::where('customer_id',$id)->latest()->get();
      $getCustBilling = CustomerBillingDetail::where('customer_id',$id)->latest()->get();
      $paymentTerms = PaymentTerm::select('id','title')->get();
      $paymentTypes = PaymentType::select('id','title')->where("visible_in_customer", 1)->get();
      $customer_contacts = CustomerContact::where('customer_id' , $id)->get();

      // dd($ProductCustomerFixedPrice->count());
      return view('common.customers.customer_detail',compact('customer','customerShipping','customerBilling','customerNotes','countries','ProductCustomerFixedPrice','categories','getCustShipping','getCustBilling','paymentTerms','paymentTypes','states','customer_contacts','layout'));
    }

public function assignCustomersToSale(Request $request){
    // dd('here');
  // dd($request->all());
    foreach($request->customers as $customer){
        $cust = customer::find($customer);
        if($request->user_as == 1)
        {
          $cust->primary_sale_id = $request->user_id;
          $cust->save();
        }
        else
        {
            $customer_id=$customer;

            $CustomerSecondaryUser =new CustomerSecondaryUser();
            $isSecSaleExists=$CustomerSecondaryUser::where('user_id',$request->user_id)->where('customer_id',$customer_id)->count();

            if($isSecSaleExists==0){
                $CustomerSecondaryUser->user_id=$request->user_id;
                $CustomerSecondaryUser->customer_id=$customer_id;
                $CustomerSecondaryUser->status=1;
                $CustomerSecondaryUser->save();
            }
        }
      }
        return response()->json(['success' => true]);
}
 public function getCustomerContact(Request $request){
      // dd($request->id);

      // dd("helllo");
        $query = CustomerContact::where('customer_id',$request->id)->get();
        // dd($query->get());
         return Datatables::of($query)
         ->addColumn('name',function($item){
              $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="name"  data-fieldvalue="'.@$item->name.'">'.(@$item->name != NULL ? @$item->name : "--").'</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="name" class="fieldFocusContact d-none" value="'.@$item->name.'">';
              return $html_string;
          })
           ->addColumn('sur_name',function($item){
              $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="sur_name"  data-fieldvalue="'.@$item->sur_name.'">'.(@$item->sur_name != NULL ? @$item->sur_name : "--").'</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="sur_name" class="fieldFocusContact d-none" value="'.@$item->sur_name.'">';
              return $html_string;
          })

          ->addColumn('email',function($item){
              $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="email"  data-fieldvalue="'.@$item->email.'">'.(@$item->email != NULL ? @$item->email : "--").'</span>
                <input type="email" autocomplete="nope" style="width:100%;" name="email" class="fieldFocusContact d-none" value="'.@$item->email.'">';
              return $html_string;
          })

          ->addColumn('telehone_number',function($item){
              $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="telehone_number"  data-fieldvalue="'.@$item->telehone_number.'">'.(@$item->telehone_number != NULL ? @$item->telehone_number : "--").'</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="telehone_number" class="fieldFocusContact d-none" value="'.@$item->telehone_number.'">';
              return $html_string;
          })

          ->addColumn('postion',function($item){
              $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="postion"  data-fieldvalue="'.@$item->postion.'">'.(@$item->postion != NULL ? @$item->postion : "--").'</span>
                <input type="text" autocomplete="nope" style="width:100%;" name="postion" class="fieldFocusContact d-none" value="'.@$item->postion.'">';
              return $html_string;
          })
           ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon deleteCustomerContact" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                 ';

                return $html_string;
            })

          ->setRowId(function ($item) {
            return $item->id;
          })

            ->rawColumns(['name','sur_name','email','telehone_number','postion','action'])
            ->make(true);

    }

    public function getCustomerGeneralDocuments(Request $request){
      // dd($request->id);

      // dd("helllo");
      if(@$request->al == true){
        $query = CustomerGeneralDocument::where('customer_id',$request->id)->get();
      }else{

        $query = CustomerGeneralDocument::where('customer_id',$request->id)->limit(5);
      }
        // dd($query->get());
         return Datatables::of($query)

            ->addColumn('date',function($item){
                return $item->created_at !== null ? $item->created_at->format('d/m/Y'): 'N.A';
            })
            ->addColumn('file_name',function($item){
                return $item->file_name !== null ? $item->file_name: 'N.A';
            })
             ->addColumn('description',function($item){
                return $item->description ? $item->description : "N.A";
            })
              ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon deleteGeneralDocument" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                 ';

                 $html_string .= '<a href="'.asset('public/uploads/documents/'.$item->file_name).'" class="actionicon download" data-id="' . @$item->file_name . '" title="Download"><i class="fa fa-download"></i></a>';

                return $html_string;
            })
            ->rawColumns(['file_name','description','date','action'])
            ->make(true);



        //    ->setRowId(function ($item) {
        //           return $item->id;
        //       })
        //    // yellowRow is a custom style in style.css file
        //    ->setRowClass(function ($item) {
        //         if($item->product == null){
        //     return  'yellowRow';
        //           }

        // })

    }

    public function showSingleBilling(Request $request){
      // dd($request->all());
      $customer_id = $request->cust_detail_id;
      $billing_id = $request->billing_id;

      $customerBillingDetails = CustomerBillingDetail::where('customer_id',$customer_id)->where('id',$billing_id)->first();
      // dd($customerBillingDetails->getstate->name);
      $userState = @$customerBillingDetails->getstate->name;
      return response()->json([
        "error" => false,
        "billingCustomer" => $customerBillingDetails,
        "userState" => $userState,
       //  "new_value" => $new_value
     ]);
    }

     public function getCustomerDocuments(Request $request){
      // dd($request->id);
        $user = Auth::user();
        $layout = '';
        if($user->role_id == 1)
        {
            $layout = 'backend';
        }
        elseif ($user->role_id == 2)
        {
            $layout = 'users';
        }
        elseif ($user->role_id == 3)
        {
            $layout = 'sales';
        }
         elseif ($user->role_id == 4)
        {
            $layout = 'sales';
        }
        elseif ($user->role_id == 5)
        {
            $layout = 'importing';
        }
        elseif ($user->role_id == 6)
        {
            $layout = 'warehouse';
        }
      $id = $request->id;
      $customer = Customer::where('id',$id)->first();
      // dd($customer);
      return view('common.customers.customer-documents',compact('id','customer','layout'));
    }

      public function getCustomerProductFixedPrices(Request $request){
      // dd($request->id);
         $user = Auth::user();
        $layout = '';
        if($user->role_id == 1)
        {
            $layout = 'backend';
        }
        elseif ($user->role_id == 2)
        {
            $layout = 'users';
        }
        elseif ($user->role_id == 3)
        {
            $layout = 'sales';
        }
         elseif ($user->role_id == 4)
        {
            $layout = 'sales';
        }
        elseif ($user->role_id == 5)
        {
            $layout = 'importing';
        }
        elseif ($user->role_id == 6)
        {
            $layout = 'warehouse';
        }
      $id = $request->id;
      $customer = Customer::where('id',$id)->first();
      $ProductCustomerFixedPrice = ProductCustomerFixedPrice::where('customer_id',$id)->get();
      return view('common.customers.customer-product-fixedprices',compact('id','customer','ProductCustomerFixedPrice','layout'));
    }

     public function addCustomerNote(Request $request)
    {
        $request->validate([
            'note_description' => 'required|max:255',
        ]);

        $customer = Customer::find($request->customer_id);

        $customer->getnotes()->create([
            'note_title' => 'note',
            'note_description' => $request->note_description,
            'user_id' => Auth::user()->id,
        ]);

        return json_encode(['success' => true]);

    }

    public function getCustomerNote(Request $request)
    {
        $customer_notes = CustomerNote::where('customer_id', $request->customer_id)->get();


                $html_string ='<div class="table-responsive">
                                <table class="table table-bordered text-center">
                                <thead class="table-bordered">
                                <tr>
                                    <th>S.no</th>

                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                                </thead><tbody>';
                                if($customer_notes->count() > 0){
                                $i = 0;
                                foreach($customer_notes as $note){
                                $i++;
                $html_string .= '<tr id="cust-note-'.$note->id.'">
                                    <td>'.$i.'</td>

                                    <td>'.$note->note_description.'</td>
                                    <td><a href="javascript:void(0);" data-id="'.$note->id.'" class="delete-note actionicon deleteIcon" title="Delete Note"><i class="fa fa-trash"></i></a></td>
                                 </tr>';
                                }
                                }else{
                $html_string .= '<tr>
                                    <td colspan="4">No Note Found</td>
                                 </tr>';
                                }


                $html_string .= '</tbody></table></div>';
        return $html_string;

    }

    public function updateImg(Request $request){

      $user = UserDetail::where('user_id',Auth::user()->id)->first();

       $validator = Validator::make($request->all(), [
           'profileimg' => 'mimes:jpeg,png,jpg,svg|max:2048'
        ]);

        if ($validator->fails()) {

            //return response()->json(['error' => 'wrong image selected']);
          return response()->json(['error' => $validator->errors()->all()]);

          }else{
             if($request->hasFile('profileimg'))
        {
            $fileNameWithExt = $request->file('profileimg')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $extension = $request->file('profileimg')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$extension;
            if(Auth::user()->role_id == 3){
            $path = $request->file('profileimg')->move('public/uploads/sales/images/',$fileNameToStore);
              if($user != null){
                     $image_path = 'public/uploads/sales/images/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }else if(Auth::user()->role_id == 2){
            $path = $request->file('profileimg')->move('public/uploads/purchase/',$fileNameToStore);
              if($user != null){
                     $image_path = 'public/uploads/purchase/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }else if(Auth::user()->role_id == 5){
            $path = $request->file('profileimg')->move('public/uploads/importing/images/',$fileNameToStore);
                  if($user != null){
                     $image_path = 'public/uploads/importing/images/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }
            else if(Auth::user()->role_id == 6){
            $path = $request->file('profileimg')->move('public/uploads/warehouse/images/',$fileNameToStore);
                  if($user != null){
                     $image_path = 'public/uploads/warehouse/images/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }
            else if(Auth::user()->role_id == 7){
            $path = $request->file('profileimg')->move('public/uploads/accounting/images/',$fileNameToStore);
                  if($user != null){
                     $image_path = 'public/uploads/accounting/images/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }

             else if(Auth::user()->role_id == 1 || Auth::user()->role_id == 11){
            $path = $request->file('profileimg')->move('public/uploads/admin/images/',$fileNameToStore);
                  if($user != null){
                     $image_path = 'public/uploads/admin/images/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }

        }

         if($user != null){

        $user->image = $fileNameToStore;
        $user->save();
      }else{
        $user = new UserDetail;
        $user->user_id = Auth::user()->id;
        $user->image = $fileNameToStore;
        $user->country_id = '217';
        $user->state_id = '3032';
        $user->address = 'Thailand';
        $user->save();
      }

      return response()->json(['success'=>1]);



          }
      // dd($request->all());



    }

    public function updateImguser(Request $request){
       $user = UserDetail::where('user_id',$request->userid)->first();
       $userdetail = User::where('id',$request->userid)->first();
       //dd($userdetail->id);

       $validator = Validator::make($request->all(), [
           'profileimg' => 'mimes:jpeg,png,jpg,svg|max:2048'
        ]);

        if ($validator->fails()) {

            //return response()->json(['error' => 'wrong image selected']);
          return response()->json(['error' => $validator->errors()->all()]);

          }else{
             if($request->hasFile('profileimg'))
        {
            $fileNameWithExt = $request->file('profileimg')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $extension = $request->file('profileimg')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$extension;
            if($userdetail->role_id == 3 && $userdetail->id==$request->userid){

            $path = $request->file('profileimg')->move('public/uploads/sales/images/',$fileNameToStore);
              if($user != null){
                     $image_path = 'public/uploads/sales/images/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }else if($userdetail->role_id == 2 && $userdetail->id==$request->userid){
            $path = $request->file('profileimg')->move('public/uploads/purchase/',$fileNameToStore);
              if($user != null){
                     $image_path = 'public/uploads/purchase/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }else if($userdetail->role_id == 5 && $userdetail->id==$request->userid){
            $path = $request->file('profileimg')->move('public/uploads/importing/images/',$fileNameToStore);
                  if($user != null){
                     $image_path = 'public/uploads/importing/images/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }
            else if($userdetail->role_id == 6 && $userdetail->id==$request->userid){
            $path = $request->file('profileimg')->move('public/uploads/warehouse/images/',$fileNameToStore);
                  if($user != null){
                     $image_path = 'public/uploads/warehouse/images/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }
    else if($userdetail->role_id == 7 && $userdetail->id==$request->userid){
      //dd('acc');
            $path = $request->file('profileimg')->move('public/uploads/accounting/images/',$fileNameToStore);
                  if($user != null){
                     $image_path = 'public/uploads/accounting/images/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }

             else if($userdetail->role_id == 1 && $userdetail->id==$request->userid){
              //dd('admin');
            $path = $request->file('profileimg')->move('public/uploads/admin/images/',$fileNameToStore);
                  if($user != null){
                     $image_path = 'public/uploads/admin/images/'.@$user->image;
                        if(file_exists($image_path)) {
                            File::delete($image_path);
                        }
                  }
            }

        }

         if($user != null){

        $user->image = $fileNameToStore;
        $user->save();
      }else{
        $user = new UserDetail;
        $user->user_id = $request->userid;
        $user->image = $fileNameToStore;
        $user->country_id = '217';
        $user->state_id = '3032';
        $user->address = 'Thailand';
        $user->save();
      }

      return response()->json(['success'=>1]);



          }
      // dd($request->all());



    }

    public function checkActive(){
      // dd(Auth::user());
      $check = Auth::user()->id;
    // event(new LoggingStatus('Farooq'));

      // // dd($check);
      // if($check == null){
        return response()->json(['active'=>false]);
      // }
    }

    public function removeProfileImage(Request $request){
      $user = UserDetail::where('user_id',$request->id)->first();
      $image_path = 'public/uploads/admin/images/'.@$user->image;
      if(file_exists($image_path)) {
          File::delete($image_path);
      }
      return redirect()->back();
    }

}
