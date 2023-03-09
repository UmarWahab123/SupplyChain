<?php

namespace App\Http\Controllers\Backend;

use Auth;
use Carbon\Carbon;
use App\ExportStatus;
use Illuminate\Http\Request;
use App\Models\Common\Product;
use App\Models\Sales\Customer;
use Yajra\Datatables\Datatables;
use App\Http\Controllers\Controller;
use App\Jobs\AddCustomerCategoryJob;
use App\Models\Common\Configuration;
use App\Models\Common\ProductCategory;
use App\Models\Common\CustomerCategory;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\CustomerTypeCategoryMargin;

class CustomerCategoryController extends Controller
{
    public function index()
    {
        $config = Configuration::first();
    	return $this->render('backend.customerCategories.index', compact('config'));
    }

    public function getData()
    {
      $query = CustomerCategory::where('is_deleted','!=',2);
      return Datatables::of($query)

      ->addColumn('action', function ($item) {
        if($item->is_deleted == 1)
        {
          $showClass = '';
        }
        else
        {
          $showClass = 'd-none';
        }
        $html_string = '<div class="icons">'.'
          <a href="javascript:void(0);" data-id="'.$item->id.'" class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a>
          <a href="javascript:void(0);" data-id="'.$item->id.'" class="actionicon deleteIcon" title="Delete"><i class="fa fa-trash"></i></a>
          <a href="javascript:void(0);" data-id="'.$item->id.'" class="actionicon makeBinding '.$showClass.'" title="Make Binding"><i class="fa fa-refresh"></i></a>
          </div>';
        return $html_string;
      })
      ->addColumn('created_at', function ($item) {
        $html_string = $item->created_at != NULL ? Carbon::parse(@$item->created_at)->format('d/m/Y') : 'N.A';
        return $html_string;
      })
      // ->addColumn('ecommr_enabled', function($item) {
      //   $checker= ($item->ecommr_enabled == 1 ? 'checked' : '');
      //   $html_string = '<input type="checkbox"'.$checker.' name="ecommerce_enabled" class="ecommerce_enabled'.$item->id.'  ecommerce_enabled" data-id="'.$item->id.'" >';
      //   return $html_string;
      // })
    	->setRowId(function ($item) {
        return $item->id;
      })
      ->rawColumns(['action','created_at'])
      ->make(true);
    }

    public function add(Request $request)
    {
      // $specific_customer=CustomerCategory::find($request['customer_categ_id']);
      // $specific_customer->ecommr_enabled=$request['ecommerce_enabled'];
      // $specific_customer->save();

    	$validator = $request->validate([
    		'title' => 'required',
    	]);

      $CustomerCategory             = new CustomerCategory;
      $CustomerCategory->title      = strtoupper($request->title);
      //Get the first character using substr.
      $firstCharacter               = substr($request->title, 0, 1);
      $CustomerCategory->short_code = $request->has('short_code') ? $request->short_code : strtoupper($firstCharacter);
      $CustomerCategory->is_deleted = 1;
      $CustomerCategory->show                 = 0;
      $CustomerCategory->suggested_price_show = 0;
      $CustomerCategory->save();

      return response()->json(['success' => true]);
    }

    public function makeBinding(Request $request)
    {
      $status = ExportStatus::where('type','add_customer_type')->first();
      if($status == null)
      {
        $new          = new ExportStatus();
        $new->user_id = Auth::user()->id;
        $new->type    = 'add_customer_type';
        $new->status  = 1;
        $new->save();
        AddCustomerCategoryJob::dispatch($request->cat_id, Auth::user()->id);
        return response()->json(['msg'=>"Customer type category is binding.",'status'=>1,'recursive'=>true]);
      }

      elseif($status->status == 1)
      {
        AddCustomerCategoryJob::dispatch($request->cat_id, Auth::user()->id);
        return response()->json(['msg'=>"Customer type cateogry is binded",'status'=>2]);
      }
      elseif($status->status == 0 || $status->status == 2)
      {
        ExportStatus::where('type','add_customer_type')->update(['status'=>1,'exception'=>null,'user_id'=>Auth::user()->id]);
        AddCustomerCategoryJob::dispatch($request->cat_id, Auth::user()->id);
        return response()->json(['msg'=>"Category is binded Successfully.",'status'=>1,'exception'=>null]);
      }
    }

    public function recursiveCategoryStatusCheck()
    {
      $status=ExportStatus::where('type','add_customer_type')->first();
      return response()->json(['msg'=>"File is now getting prepared",'status'=>$status->status,'exception'=>$status->exception]);
    }

    public function checkStatusFirstTimeCategory()
    {
      $status=ExportStatus::where('type','add_customer_type')->where('user_id',Auth::user()->id)->first();
      if($status!=null)
      {
        return response()->json(['status'=>$status->status]);
      }
      else
      {
        return response()->json(['status'=>0]);
      }
    }

    public function getCustCatNameForEdit(Request $request)
    {
      $categoryTable = CustomerCategory::where('id',$request->cat_id)->first();
      $config = Configuration::first();
      $html_string = '';

      $html_string = '
        <div class="form-group">
          <input type="hidden" name="editid" id="editid" value="'.$request->cat_id.'">
          <label class="pull-left">Title</label>
          <input type="text" name="title" class="font-weight-bold form-control-lg form-control e-prod-cat" placeholder="Enter Customer Category" value="'.$categoryTable->title.'" autocomplete="off" required="true"></div>';
        if ($config->server == 'lucilla')
        {
            $html_string .= '
            <div class="form-group">
                <label class="mt-2 pull-left">Prefix</label>
                <input type="text" name="short_code" class="font-weight-bold form-control-lg form-control e-prod-cat" placeholder="Enter Prefix" value="'.$categoryTable->short_code .'" autocomplete="off"></div>';
        }
      return response()->json(['success' => true, 'html_string' => $html_string]);
    }

    public function edit(Request $request)
    {
      $CustomerCategory = CustomerCategory::find($request->editid);
      if(strtolower($CustomerCategory->title) != strtolower($request['title']))
      {
        $validator = $request->validate([
          'title' => 'required|unique:customer_categories',
        ]);
      }

      $CustomerCategory->title = $request['title'];
      if ($request->has('short_code')) {
        $CustomerCategory->short_code = $request->short_code;
      }
      $CustomerCategory->save();
      return response()->json(['success' => true]);
    }

    public function deleteCustomerCategory(Request $request)
    {
      $customerCategory = CustomerCategory::find($request->cat_id);
      if($customerCategory)
      {
        $getCTCMtotal = CustomerTypeCategoryMargin::where('customer_type_id',$customerCategory->id)->get();
        $getCTPMtotal = CustomerTypeProductMargin::where('customer_type_id',$customerCategory->id)->get();
        $getPFPtotal  = ProductFixedPrice::where('customer_type_id',$customerCategory->id)->get();
        $getCustTotal = Customer::where('category_id',$customerCategory->id)->get();

        if($getCustTotal->count() > 0)
        {
          return response()->json(['success' => false]);
        }
        else
        {
          # here are the three statuses of customer categories
          #0 completed
          #1 pending
          #2 disabled
          if($customerCategory->is_deleted == 1)
          {
            $customerCategory->delete();
          }
          else
          {
            $customerCategory->is_deleted           = 2;
            $customerCategory->show                 = 0;
            $customerCategory->suggested_price_show = 0;
            $customerCategory->save();
          }
          return response()->json(['success' => true]);
        }
      }
    }

}
