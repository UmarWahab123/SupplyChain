<?php

namespace App\Http\Controllers\Common;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Imports\SupplierBulkImport;
use App\Mail\Backend\SupplierActivationEmail;
use App\Mail\Backend\SupplierSuspensionEmail;
use App\Models\Common\Country;
use App\Models\Common\Currency;
use App\Models\Common\EmailTemplate;
use App\Models\Common\PaymentTerm;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\State;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierCategory;
use App\Models\Common\SupplierContacts;
use App\Models\Common\SupplierGeneralDocument;
use App\Models\Common\SupplierNote;
use Auth;
use Carbon\Carbon;
use Excel;
use Mail;
use Yajra\Datatables\Datatables;

class SupplierController extends Controller
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
      $countries = Country::orderby('name', 'ASC')->pluck('name', 'id');
   	  $pcategory = ProductCategory::where('parent_id',0)->get();
      $allcategory = ProductCategory::all();
      $currencies = Currency::all();
      $product = ProductType::all()->pluck('title','id');
      $payment_terms = PaymentTerm::all();
    	return view('common.suppliers.index',compact('pcategory','allcategory','countries','product','currencies','payment_terms','layout'));
    }

    public function getData()
    {
        $query = Supplier::with('getcountry', 'getstate','productcategory','producttype')->orderby('id','DESC')->get();
        // dd($query);
        return Datatables::of($query)
            ->addColumn('name', function ($item) {

                return $item->first_name !== null ? $item->first_name . ' ' . $item->last_name : '--';
            })
            ->addColumn('address', function ($item) {

                return $item->address_line_1 !== null ? $item->address_line_1 . ' ' . $item->address_line_2 : '--';
            })
            ->addColumn('company', function ($item) {

                return $item->company !== null ? $item->company : '--';
            })
            ->addColumn('country', function ($item) {

                return $item->country !== null ? $item->getcountry->name : '--';
            })
            ->addColumn('state', function ($item) {

                return $item->state !== null ? $item->getstate->name : '--';
            })
            ->addColumn('phone', function ($item) {

                return $item->phone !== null ? $item->phone : '--';
            })
            ->addColumn('email', function ($item) {

                return $item->email !== null ? $item->email : '--';
            })
            ->addColumn('city', function ($item) {

                return $item->city !== null ? $item->city : '--';
            })
            ->addColumn('postalcode', function ($item) {

                return $item->postalcode !== null ? $item->postalcode : '--';
            })
            ->addColumn('status', function ($item) {
                $status = '';
                if ($item->status == 1) {
                    $status = '<span class="badge badge-pill badge-success font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Completed</span>';
                } elseif ($item->status == 2) {
                    $status = '<span class="badge badge-pill badge-danger font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Deleted</span>';
                }
                elseif ($item->status == 0) {
                    $status = '<span class="badge badge-pill badge-warning font-weight-normal pb-2 pl-3 pr-3 pt-2" style="font-size:1rem;">Incompleted</span>';
                }
                return $status;
            })
            ->addColumn('action', function ($item) {
              
              $html_string = '
              <a href="'.url('/common/get-common-supplier-detail/'.$item->id).'" class="actionicon editIcon" title="View Detail"><i class="fa fa-eye"></i></a>        
              ';
                // $html_string .= '
                //  <a href="javascript:void(0);" class="actionicon deleteIcon deleteSupplier" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>
                                 
                //  ';
                
                 // <a href="javascript:void(0);" class="actionicon editIcon" data-id="' . $item->id . '" title="Edit"><i class="fa fa-edit"></i></a> 

                return $html_string;
            })
            ->addColumn('product_type', function ($item) {
              $html_string = '';
              if($item->main_tags != null){
              $multi_tags = explode(',', $item->main_tags);
              foreach($multi_tags as $tag){
                $html_string .= ' <span class="abc">'.$tag.'</span>';
              }
                return $html_string;
            }
            else{
              $html_string = '--';
              return $html_string;
            }
            })
            ->addColumn('created_at', function ($item) {

              return $item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : '--';
            })
            ->addColumn('created_at', function ($item) {

            return $item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : '--';
            })
            ->addColumn('open_pos', function ($item) {
                $countPos = PurchaseOrder::where('supplier_id',$item->id)->where('status',12)->get()->count();
                $html_string = $countPos;
                return $html_string;
            })
            ->addColumn('total_pos', function ($item) {
              $countPos = PurchaseOrder::where('supplier_id',$item->id)->get()->count();
              $html_string = $countPos;
              return $html_string;
            })
            ->addColumn('last_order_date', function ($item) {
              $last_order = PurchaseOrder::where('supplier_id',$item->id)->orderby('id','DESC')->first();
              if($last_order)
              {
                return (@$last_order->confirm_date != null) ? Carbon::parse(@$last_order->confirm_date)->format('d/m/Y'):'--';
              }
              else
              {
                return "--";
              }
              
            })
            ->addColumn('notes', function ($item) {
              // check already uploaded images //
              $notes = SupplierNote::where('supplier_id', $item->id)->count('id');

              $html_string = '<div class="d-flex justify-content-center text-center">';
              if($notes > 0){  
              $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';
              }

              
              return $html_string; 
            })
            ->rawColumns(['action', 'status', 'country', 'state', 'email', 'city', 'postalcode','detail','product_type','created_at','open_pos','total_pos','last_order_date','notes'])
            ->make(true);

    }

    public function getSupplierDetailByID($id)
    {
    	$user = Auth::user();
    	// dd($user);
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
      $supplier = Supplier::with('getcountry','getstate','supplierMultipleCat')->where('id',$id)->first();
      $countries = Country::select('id','name')->get();

      $country_id = $supplier->country;
      $states = State::select('id','name')->where('country_id',$country_id)->get();
      $supplierNotes = SupplierNote::with('getuser')->where('supplier_id',$id)->get();
      $paymentTerms = PaymentTerm::select('id','title')->get();
      $categories = ProductCategory::where('parent_id',0)->get();
      $SupplierCat_count = SupplierCategory::with('supplierCategories')->where('supplier_id',$id)->count();
      $currencies = Currency::select('id','currency_name')->get();
      $supplierCats = SupplierCategory::where('supplier_id',$id)->pluck('category_id')->toArray();
      
      // $supplierCat = SupplierCategory::with('supplierCategories')->where('supplier_id',$id)->get();
      return view('common.suppliers.supplier-detail',compact('supplier','categories','id','SupplierCat_count','countries','states','paymentTerms','supplierCats','currencies','supplierNotes','layout'));
    }

    public function getSupplierContact(Request $request)
    {
      $query = SupplierContacts::where('supplier_id',$request->id)->get();
        
        return Datatables::of($query)
                    
          ->addColumn('name',function($item){
              $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="name"  data-fieldvalue="'.@$item->name.'">'.(@$item->name != NULL ? @$item->name : "--").'</span>
                <input type="text" style="width:100%;" name="name" class="fieldFocusContact d-none" value="'.@$item->name.'">';
              return $html_string;
          })
          
          ->addColumn('sur_name',function($item){
              $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="sur_name"  data-fieldvalue="'.@$item->sur_name.'">'.(@$item->sur_name != NULL ? @$item->sur_name : "--").'</span>
                <input type="text" style="width:100%;" name="sur_name" class="fieldFocusContact d-none" value="'.@$item->sur_name.'">';
              return $html_string;
          })
          
          ->addColumn('email',function($item){
              $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="email"  data-fieldvalue="'.@$item->email.'">'.(@$item->email != NULL ? @$item->email : "--").'</span>
                <input type="email" style="width:100%;" name="email" class="fieldFocusContact d-none" value="'.@$item->email.'">';
              return $html_string;        
          })

          ->addColumn('telehone_number',function($item){
              $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="telehone_number"  data-fieldvalue="'.@$item->telehone_number.'">'.(@$item->telehone_number != NULL ? @$item->telehone_number : "--").'</span>
                <input type="number" style="width:100%;" name="telehone_number" class="fieldFocusContact d-none" value="'.@$item->telehone_number.'">';
              return $html_string;                
          })

          ->addColumn('postion',function($item){
              $html_string = '<span class="m-l-15 inputDoubleClickContacts" id="postion"  data-fieldvalue="'.@$item->postion.'">'.(@$item->postion != NULL ? @$item->postion : "--").'</span>
                <input type="text" style="width:100%;" name="postion" class="fieldFocusContact d-none" value="'.@$item->postion.'">';
              return $html_string;               
          })
           ->addColumn('action', function ($item) {
                $html_string = '
                 <a href="javascript:void(0);" class="actionicon deleteIcon deleteSupplierContact" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>                
                 ';
                 
                return $html_string;
            })
          
          ->setRowId(function ($item) {
            return $item->id;
          })

          ->rawColumns(['name','sur_name','email','telehone_number','postion','action'])
          ->make(true);     
    }

    public function getSupplierGeneralDocuments(Request $request)
    {

      $query = SupplierGeneralDocument::where('supplier_id',$request->id)->get();
      return Datatables::of($query)
  
      ->addColumn('file_name',function($item){
          return $item->file_name != null ? $item->file_name: '--';
      })
       ->addColumn('description',function($item){
          return $item->description != null ? $item->description : "--";
      })
      ->addColumn('action', function ($item) {
        $html_string = '
         <a href="javascript:void(0);" class="actionicon deleteIcon deleteGeneralDocument" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>                
         ';
          $html_string .= '<a href="'.asset('public/uploads/documents/'.$item->file_name).'" class="actionicon download" data-id="' . @$item->file_name . '" title="Download"><i class="fa fa-download"></i></a>';
        return $html_string;
      })
      ->setRowId(function ($item) {
        return $item->id;
      })
      ->rawColumns(['file_name','description','action'])
      ->make(true);            
    }
}
