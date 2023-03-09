<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Common\Country;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\State;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierCategory;
use App\Models\Common\SupplierNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class SupplierController extends Controller
{
    public function index()
    {
    	$countries = Country::orderby('name', 'ASC')->pluck('name', 'id');
    	$pcategory = ProductCategory::where('parent_id',0)->get();
      $allcategory = ProductCategory::all();
      $product = ProductType::all()->pluck('title','id');
    	return view('sales.suppliers.index',compact('pcategory','allcategory','countries','product'));
    }

    public function getData()
    {
        $query = Supplier::with('getcountry', 'getstate','productcategory','producttype')->get();
        // dd($query);
        return Datatables::of($query)
            ->addColumn('name', function ($item) {

                return $item->first_name !== null ? $item->first_name . ' ' . $item->last_name : 'N.A';
            })
            ->addColumn('address', function ($item) {

                return $item->address_line_1 !== null ? $item->address_line_1 . ' ' . $item->address_line_2 : 'N.A';
            })
            ->addColumn('reference_name', function ($item) {

                return $item->reference_name !== null ? $item->reference_name : 'N.A';
            })
            ->addColumn('company', function ($item) {

                return $item->company !== null ? $item->company : 'N.A';
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
            ->addColumn('email', function ($item) {

                return $item->email !== null ? $item->email : 'N.A';
            })
            ->addColumn('city', function ($item) {

                return $item->city !== null ? $item->city : 'N.A';
            })
            ->addColumn('postalcode', function ($item) {

                return $item->postalcode !== null ? $item->postalcode : 'N.A';
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
              $html_string = '
              <a href="'.url('sales/get-supplier-details/'.$item->id).'" class="actionicon" title="View Detail"><i class="fa fa-eye"></i></a>        
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
              $html_string = 'N.A';
              return $html_string;
            }
            })
            ->addColumn('created_at', function ($item) {

              return $item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : 'N.A';
            })
            ->addColumn('created_at', function ($item) {

            return $item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : 'N.A';
            })
            ->addColumn('open_pos', function ($item) {
                $html_string = '$0';

                return $html_string;
            })
            ->addColumn('total_pos', function ($item) {
              $countPos = PurchaseOrder::where('supplier_id',$item->id)->get()->count();
              $html_string = $countPos;
              return $html_string;
            })
            ->addColumn('last_order_date', function ($item) {
            $html_string = 'Nov-21-2019';

            return $html_string;
            })
            ->addColumn('notes', function ($item) {
              // check already uploaded images //
              $notes = SupplierNote::where('supplier_id', $item->id)->count('id');

              $html_string = '<div class="d-flex justify-content-center text-center">';
              if($notes > 0){  
              $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="'.$item->id.'" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a></div>';
              }
              else{
                $html_string .='N.A';
              }

              
              return $html_string; 
            })
            ->rawColumns(['action', 'status', 'country', 'state', 'email', 'city', 'postalcode','detail','product_type','created_at','open_pos','total_pos','last_order_date','notes','reference_name'])
            ->make(true);

    }

    public function getProductCategoryChilds(Request $request)
    {
      $subCategories = ProductCategory::where('parent_id', $request->category_id)->get();
      $html_string = '';
      $html_string .= '<option value="">Select Sub-Category</option>';
      foreach ($subCategories as $value) 
      {
        $html_string .= '<option value="'.$value->id.'">'.$value->title.'</option>';
      }
      return response()->json(['html_string' => $html_string]);
    }

    public function deleteSupplier(Request $request)
    {
        $delete = Supplier::where('id',$request->id)->delete();

        return response()->json(['success' => true]);
    }

    public function updateSupplierProfile(Request $request, $id){
      $supplier = Supplier::where('id',$id)->first();
      // dd($customer);
      if($request->hasFile('logo') && $request->logo->isValid())
      {         
          $fileNameWithExt = $request->file('logo')->getClientOriginalName();
          $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
          $extension = $request->file('logo')->getClientOriginalExtension();
          $fileNameToStore = $fileName.'_'.time().'.'.$extension;
          $path = $request->file('logo')->move('public/uploads/sales/customer/logos/',$fileNameToStore);
          $supplier->logo = $fileNameToStore;
      }

      $supplier->save();
      return redirect()->back();
    }

    public function getSupplierDetailByID($id)
    {
      $supplier = Supplier::with('getcountry','getstate')->where('id',$id)->first();
      $categories = ProductCategory::where('parent_id',0)->get();
      $SupplierCat_count = SupplierCategory::with('supplierCategories')->where('supplier_id',$id)->count();
      return view('sales.suppliers.supplier-detail',compact('supplier','categories','id','SupplierCat_count'));
    }

    public function add(Request $request)
    {
    	// dd($request->all());
      $validator = $request->validate([
            'company' => 'required',
            'email' => 'required',
            'first_name' => 'required',          
            'last_name' => 'required',
            'phone' => 'required',
            'country' => 'required',
            'state' => 'required',
            'credit_term' => 'required',
        ]);

    	if ($request->reference_number == null) 
        {
            $system_gen_no = 'SC-' . $this->generateRandomString(4);
            $request->reference_number = $system_gen_no;
        }
        
        $supplier = new Supplier;
        $supplier->user_id	 		    = $this->user->id;
        $supplier->reference_number	= $request->reference_number;
        $supplier->company 		      = $request->company;
        // $supplier->category_id	    = $request->category_id;
        $supplier->credit_term      = $request->credit_term;
        $supplier->first_name	      = $request->first_name;
        $supplier->last_name	      = $request->last_name;
        $supplier->email 		        = $request->email;
        $supplier->phone 		        = $request->phone;
        $supplier->secondary_phone  = $request->secondary_phone;
        $supplier->address_line_1   = $request->address_line_1;
        $supplier->address_line_2   = $request->address_line_2;
        $supplier->country 		      = $request->country;
        $supplier->state 		        = $request->state;
        $supplier->city 		        = $request->city;
        $supplier->postalcode 	    = $request->postalcode;
        if($request->hasFile('logo') && $request->logo->isValid())
        {         
            $fileNameWithExt = $request->file('logo')->getClientOriginalName();
            $fileName = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
            $extension = $request->file('logo')->getClientOriginalExtension();
            $fileNameToStore = $fileName.'_'.time().'.'.$extension;
            $path = $request->file('logo')->move('public/uploads/sales/customer/logos/',$fileNameToStore);
            $supplier->logo = $fileNameToStore;
        }

        $supplier->save();
          // dd('hi');
        if($request->category_id[0] != null)
        {
          for($i=0;$i<sizeof($request->category_id);$i++)
          {
              $supplierCategories              = new SupplierCategory;
              $supplierCategories->supplier_id = $supplier->id;
              $supplierCategories->category_id = $request->category_id[$i];
              $supplierCategories->save();
          }
        }

        $newsupplier = Supplier::where('id',$supplier->id)->first();
        return response()->json(['success' => true, 'supplier' => $newsupplier]);

    }

    protected function generateRandomString($length)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function edit(Request $request)
    {
        $supplier = Supplier::find($request->id);
        $html = '';
        $html = $html . '
        <h3 class="text-capitalize fontmed">Edit Supplier</h3>
        <form method="post" action="" class="edit_cus_form">
            ' . csrf_field() . '
            <input type="hidden" name="supplier_id" value="'.$supplier->id.'">
          <div class="form-row">
          <div class="form-group col-6">
            <input type="text" name="company" placeholder="Company (Required)" required class="font-weight-bold form-control-lg form-control" value="' . $supplier->company . '">
          </div>
          <div class="form-group col-6">
            <input type="text" name="email" placeholder="Email" class="font-weight-bold form-control-lg form-control" value="' . @$supplier->email. '">
          </div>
          </div>
          
          <div class="form-row">
          <div class="form-group col-12">
            <input type="text" name="reference_number" placeholder="Reference Number | Leave Blank For System Generated" class="font-weight-bold form-control-lg form-control" value="' . @$supplier->reference_number . '">
          </div>
          </div>
          
          <div class="form-row">
          <div class="form-group col-6">
          <input type="text" name="first_name" placeholder="First Name" class="font-weight-bold form-control-lg form-control" value="' . @$supplier->first_name . '">
          </div>
          <div class="form-group col-6">
            <input type="text" name="last_name" placeholder="Last Name" class="font-weight-bold form-control-lg form-control" value="' . @$supplier->last_name . '">
          </div>
          </div>
          
          <div class="form-row">
          <div class="form-group col-6">
            <input type="text" name="phone" placeholder="Phone" class="font-weight-bold form-control-lg form-control" value="' . @$supplier->phone. '">          
          </div>
          <div class="form-group col-6">
            <input type="text" name="secondary_phone" placeholder="Additional Phone (Optional)" class="font-weight-bold form-control-lg form-control" value="' . @$supplier->secondary_phone. '">          
          </div>
          </div>          
          
          <div class="form-row">
          <div class="form-group col-6">
          <select class="font-weight-bold form-control-lg form-control" name="country" id="edit_cus_country">
          <option selected disabled>Select Country</option>
           ';
            $countries = Country::all();
            foreach ($countries as $country)
            {
                if($supplier->country != null)
                {
                    if($supplier->country == $country->id)
                    {
                        $html = $html.'<option selected value="'.$country->id.'">'.$country->name.'</option>';
                    }
                    else
                    {
                        $html = $html.'<option value="'.$country->id.'">'.$country->name.'</option>';
                    }
                }
                else
                {
                    $html = $html.'<option value="'.$country->id.'">'.$country->name.'</option>';
                }
            }

          $html = $html.'
          </select>
          </div>
          <div class="form-group col-6">
           <select class="font-weight-bold form-control-lg form-control" name="state" id="edit_cus_state">
          <option selected disabled>Select District</option>
          ';
            if($supplier->country != null)
            {
                $states = State::where('country_id',$supplier->country)->get();
                foreach ($states as $state)
                {
                    if($supplier->state != null)
                    {
                        if($supplier->state == $state->id)
                        {
                            $html = $html.'<option selected value="'.$state->id.'">'.$state->name.'</option>';
                        }
                        else
                        {
                            $html = $html.'<option value="'.$state->id.'">'.$state->name.'</option>';
                        }
                    }
                }
            }
          $html= $html.'
          </select>
          </div>
          </div>
          
          <div class="form-row">
          <div class="form-group col-6">
            <input type="text" name="city" placeholder="City" class="font-weight-bold form-control-lg form-control" value="' . @$supplier->city. '">
          </div>
          <div class="form-group col-6">
            <input type="text" name="postalcode" placeholder="Postal Code" class="font-weight-bold form-control-lg form-control" value="' . @$supplier->postalcode. '">
          </div>
          </div>

          <div class="form-row">
          <div class="form-group col-6">
            <input type="text" name="address_line_1" placeholder="Address Line 1" class="font-weight-bold form-control-lg form-control" value="' . @$supplier->address_line_1. '">
          </div>
          <div class="form-group col-6">
            <input type="text" name="address_line_2" placeholder="Address Line 2" class="font-weight-bold form-control-lg form-control" value="' . @$supplier->address_line_2. '">          
          </div>
          </div>';
          $html =$html.'<div class="form-row">
          		<div class="form-group col-6">
          		<select class="font-weight-bold form-control-lg form-control" name="category_id" id="category">
          		<option selected disabled>Select a category</option>';
          		$categories = ProductCategory::all();
          		foreach ($categories as $category)
                {
                	if($supplier->category_id == $category->id)
                	{
                	 $html = $html.'<option selected value="'.$category->id.'">'.$category->title.'</option>';
                	}
                	else
                	{
                	 $html = $html.'<option value="'.$category->id.'">'.$category->title.'</option>';
                	}
                }	

          $html =$html.'
          		</select>
          		</div>

          		<div class="form-group col-6">
          		<select class="font-weight-bold form-control-lg form-control" name="credit_term" id="credit_term">
      					<option disabled>Choose a Credit Term</option>';
                if($supplier->credit_term == 'Net 30')
                {
                $html =$html.' <option selected value="Net 30">Net 30</option>
                              <option value="Net 60">Net 60</option>
                              <option value="Net 90">Net 90</option>';
                }

                if($supplier->credit_term == 'Net 60')
                {
                $html =$html.' <option  value="Net 30">Net 30</option>
                              <option selected value="Net 60">Net 60</option>
                              <option value="Net 90">Net 90</option>';
                }

                if($supplier->credit_term == 'Net 90')
                {
                $html =$html.' <option  value="Net 30">Net 30</option>
                              <option value="Net 60">Net 60</option>
                              <option selected value="Net 90">Net 90</option>';
                }
      					
      				$html = $html.'</select>          		
          		</div>
          		</div> ';

              $html =$html.'<div class="form-row">
              <div class="form-group col-6">
              <select class="font-weight-bold form-control-lg form-control" name="product_type_id" id="category">
              <option selected disabled>Select a category</option>';
              $products = ProductType::all();
              foreach ($products as $product)
                {
                  if($supplier->product_type_id == $product->id)
                  {
                   $html = $html.'<option selected value="'.$product->id.'">'.$product->title.'</option>';
                  }
                  else
                  {
                   $html = $html.'<option value="'.$product->id.'">'.$product->title.'</option>';
                  }
                } 

          $html =$html.'
              </select>
              </div></div>';

          $html = $html.'
          <div class="form-row">
          <div class="form-group col-12">
          <div class="form-submit">
              <input type="submit" value="update" class="btn btn-bg save-btn" id="edit_cus_btn">
              <input type="reset" value="close" data-dismiss="modal" class="btn btn-danger close-btn">
          </div>
          </div>
          </div>
        </form>
        ';

        return $html;
    }

    public function update(Request $request)
    {
    	// dd($request->all());
    	$supplier_id = $request->supplier_id;

    	if ($request->reference_number == null) {
            $system_gen_no = 'SC-' . $this->generateRandomString(7);
            $request->reference_number = $system_gen_no;
        }

        $supplier = Supplier::find($supplier_id)->update([
            'reference_number' => $request->reference_number,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'company' => $request->company,
            'email' => $request->email,
            'phone' => $request->phone,
            'secondary_phone' => $request->secondary_phone,
            'address_line_1' => $request->address_line_1,
            'address_line_2' => $request->address_line_2,
            'country' => $request->country,
            'state' => $request->state,
            'city' => $request->city,
            'postalcode' => $request->postalcode,
            'category_id' => $request->category_id,
            'category_id' => $request->category_id,
        	'credit_term'  => $request->credit_term,
        ]);

        return response()->json(['success' => true]);
    }  

    public function getSupplierNote(Request $request)
    {
        $supplier_notes = SupplierNote::where('supplier_id', $request->supplier_id)->get();

        $html_string ='<div class="table-responsive">
                        <table class="table table-bordered text-center">
                        <thead class="table-bordered">
                        <tr>
                            <th>S.no</th>
                            <th>Title</th>
                            <th>Description</th>
                        </tr>
                        </thead><tbody>';
                        if($supplier_notes->count() > 0){
                        $i = 0;
                        foreach($supplier_notes as $note){
                        $i++;   
        $html_string .= '<tr id="gem-note-'.$note->id.'">
                            <td>'.$i.'</td>
                            <td>'.$note->note_title.'</td>
                            <td>'.$note->note_description.'</td>
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
}
