<?php

namespace App\Http\Controllers\Backend;

use App\ExportStatus;
use App\Exports\BulkCategoryImport;
use App\Http\Controllers\Controller;
use App\ImportFileHistory;
use App\Imports\CategoryBulkImport;
use App\Jobs\UpdateProductMarginJob;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Flag;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\SupplierProducts;
use App\Models\Common\Deployment;
use App\QuotationConfig;
use Carbon\Carbon;
use Config;
use Excel;
use File;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Auth;
use App\Models\Common\Configuration;
use App\SubCategoryHistory;

class ProductCategoryController extends Controller
{
    public function index()
    {
      $categories = ProductCategory::where('parent_id',0)->get();
      $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();

      if($ecommerceconfig)
      {
        $check_status = unserialize($ecommerceconfig->print_prefrences);
        $ecommerceconfig_status = $check_status['status'][0];
      }
      else
      {
        $ecommerceconfig_status = '';
      }

      return $this->render('backend.productCategories.index',compact('categories','ecommerceconfig_status'));
    }

    public function getData()
    {
      $deployment = Deployment::where('status', 1)->first();
      $query = ProductCategory::where('parent_id',0)->get();
      return Datatables::of($query)

      ->addColumn('action', function ($item) use ($deployment) {
        $ecom = '';
        if ($deployment != null && $deployment->status == 1 && (Auth::user()->role_id == 1 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)) {
          $ecom = '<a href="javascript:void(0);" data-id="'.$item->id.'" class="actionicon snyc_with_ecom" title="Enable to Ecom"><i class="fa fa-check"></i></a>';
        }
        $html_string = '
          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon tickIcon edit-icon" title="Edit"><i class="fa fa-pencil"></i></a>
          <a href="'.route('product-sub-categories-list',$item->id).'" data-id="'.$item->id.'" class="actionicon editIcon" title="View Detail" title="View Detail"><i class="fa fa-eye"></i></a>
          '.$ecom.'
          <a href="javascript:void(0);" data-id="'.$item->id.'"  class="actionicon deleteIcon delete-icon"  title="Delete"><i class="fa fa-trash"></i></a>
          ';
        return $html_string;
      })

      ->addColumn('title', function ($item) {
        return $item->title;
      })

      ->addColumn('sub_categories', function ($item) {
        $countSubCategories = ProductCategory::where('parent_id',$item->id)->count();
        return $countSubCategories;
      })
      ->addColumn('created_at', function ($item) {
        $html_string = Carbon::parse(@$item->created_at)->format('d/m/Y');
        return $html_string;
      })
      ->setRowId(function ($item) {
        return $item->id;
      })
      ->rawColumns(['action','title','sub_categories','created_at'])
      ->make(true);
    }

    public function uploadBulkCategories(Request $request)
    {
      $validator = $request->validate([
        'excel' => 'required|mimes:csv,xlsx,xls'
      ]);
      Excel::import(new CategoryBulkImport,$request->file('excel'));
      if(!(Session('errorMsg'))){
          ImportFileHistory::insertRecordIntoDb(Auth::user()->id,'Categories Bulk Import',$request->file('excel'));
          return redirect()->back()->with('message', 'Bulk Categories Uploaded Successfully');
      }else{
        return redirect()->back();
      }
    }

    public function exportCatData(Request $request)
    {
      $customerCategory = CustomerCategory::where('is_deleted',0)->get();
      return \Excel::download(new BulkCategoryImport($customerCategory), 'BulkCategoryImport.xlsx');
    }

    public function add(Request $request)
    {
      if($request->ecom_enable_status == 1)
      {
        $validator = $request->validate([
          'title'          => 'required',
          'category_image' => 'mimes:jpeg,png,jpg,svg',
        ]);
      }
      else
      {
        $validator = $request->validate([
          'title'         => 'required',
        ]);
      }

      $check_category = ProductCategory::where('title', $request->title)->where('parent_id', '0')->first();
      if($check_category){
        return response()->json(['success' => false]);
      }
      $ProductCategory = new ProductCategory;
      $ProductCategory->title = $request->title;

      if($request->hasfile('category_image'))
      {
        $fileNameWithExt =$request->file('category_image')->getClientOriginalName();
        $fileName        = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        $extension       = $request->file('category_image')->getClientOriginalExtension();

        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $fileNameToStore = str_replace(' ', '', $fileNameToStore );
        $path            = $request->file('category_image')->move('public/uploads/category_image/',$fileNameToStore);
        $ProductCategory->image = $fileNameToStore;
      }
    	$ProductCategory->save();
    	return response()->json(['success' => true]);
    }

    public function addProductSubCategory(Request $request)
    {
      $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();

      if($ecommerceconfig)
      {
        $check_status = unserialize($ecommerceconfig->print_prefrences);
        $ecommerceconfig_status = $check_status['status'][0];
      }
      else
      {
        $ecommerceconfig_status = '';
      }

      if($ecommerceconfig_status == 1)
      {
        $validator = $request->validate([
          'default_value'  => 'required',
          'default_margin' => 'required',
          'title'          => 'required',
          'new_sub_cat_img_to_be_uploaded' => 'mimes:jpeg,png,jpg,svg',
        ]);
      }
      else
      {
        $validator = $request->validate([
          'default_value'  => 'required',
          'default_margin' => 'required',
          'title'          => 'required',
        ]);
      }

      $gettingParentCat = ProductCategory::where('id',$request->product_cat_id)->first();
      $prefix = $gettingParentCat->title[0].$request['title'][0];
      $already_exist = ProductCategory::where('prefix',$prefix)->first();
      if($already_exist)
      {
        $prefix .= $request['title'][1];
        $already_exist2 = ProductCategory::where('prefix',$prefix)->first();
        if($already_exist2)
        {
          $prefix .= $request['title'][2];
        }
      }
      $ProductCategory            = new ProductCategory;
      $ProductCategory->title     = $request['title'];
      $ProductCategory->hs_code   = $request['hs_code'];
      $ProductCategory->parent_id = $request['product_cat_id'];
      $ProductCategory->prefix    = strtoupper($prefix);
      $ProductCategory->import_tax_book    = $request['import_tax_book'];
      $ProductCategory->vat       = $request['vat'] != '' ? $request['vat'] : 0;

      if($request->hasfile('new_sub_cat_img_to_be_uploaded'))
      {
        $fileNameWithExt          =$request->file('new_sub_cat_img_to_be_uploaded')->getClientOriginalName();
        $fileName                 = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        $extension                = $request->file('new_sub_cat_img_to_be_uploaded')->getClientOriginalExtension();
        $fileNameToStore          = $fileName.'_'.time().'.'.$extension;
        $fileNameToStore = str_replace(' ', '',$fileNameToStore);
        $path                     = $request->file('new_sub_cat_img_to_be_uploaded')->move('public/uploads/category_image/',$fileNameToStore);
        $ProductCategory->image = $fileNameToStore;
        if($ProductCategory->image != Null)
        {
        }

      }
      $ProductCategory->save();

      if($request->customer_category_id[0] != null)
      {
        for($i=0;$i<sizeof($request->customer_category_id);$i++)
        {
          $customerTypeMargins = new CustomerTypeCategoryMargin;
          $customerTypeMargins->category_id      = $ProductCategory->id;
          $customerTypeMargins->customer_type_id = $request->customer_category_id[$i];
          $customerTypeMargins->default_margin   = $request->default_margin[$i];
          $customerTypeMargins->default_value    = $request->default_value[$i];
          $customerTypeMargins->save();
        }
      }

      return response()->json(['success' => true]);
    }

    public function edit(Request $request)
    {
        // dd($request->all());

      $sub_title = null;
      $sub_prefix = null;

      $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();

      if($ecommerceconfig)
      {
        $check_status = unserialize($ecommerceconfig->print_prefrences);
        $ecommerceconfig_status = $check_status['status'][0];
      }
      else
      {
        $ecommerceconfig_status = '';
      }

      if($ecommerceconfig_status == 1)
      {
        $validator = $request->validate([
          'title'  => 'required',
          'prefix' => 'required',
          'sub_cat_img' => 'mimes:jpeg,png,jpg,svg',
        ]);
      }
      else
      {
        $validator = $request->validate([
          'title'  => 'required',
          'prefix' => 'required',
        ]);
      }

      $cuurent_prefix = ProductCategory::where('id' , $request->editid)->pluck('prefix')->toArray();
      if($cuurent_prefix[0] != null)
      {
        $checkPrefix = ProductCategory::where('prefix' , $request->prefix)->whereNotIn('prefix', [$cuurent_prefix])->first();
      }
      else if($cuurent_prefix[0] == null)
      {
        $checkPrefix = ProductCategory::where('prefix' , $request->prefix)->first();
      }


      if($checkPrefix != null)
      {
        $sub_prefix = 1;
        return response()->json(['success' => false , 'sub_prefix' => $sub_prefix ]);
      }

      $ProductCategory = ProductCategory::find($request->editid);

      $column_name = '';
      $old_value = '';
      $new_value = '';

      if($request->hs_code != $ProductCategory->hs_code) {
        $column_name = 'HS Code';
        $new_value = $request->hs_code;
        $old_value = $ProductCategory->hs_code;
      }

      if($request->title != $ProductCategory->title) {
        $column_name = 'SubCategory';
        $new_value = $request->title;
        $old_value = $ProductCategory->title;
      }

      if($request->prefix != $ProductCategory->prefix) {
        $column_name = 'Prefix';
        $new_value = $request->prefix;
        $old_value = $ProductCategory->prefix;
      }

      if($request->import_tax_book != $ProductCategory->import_tax_book) {
        $column_name = 'Import Tax Book';
        $new_value = $request->import_tax_book;
        $old_value = $ProductCategory->import_tax_book;
      }

      if($request->vat != $ProductCategory->vat) {
        $column_name = 'Vat';
        $new_value = $request->vat;
        $old_value = $ProductCategory->vat;
      }

        SubCategoryHistory::create([
            'user_id' => Auth::user()->id,
            'sub_category_id' => $request->editid,
            'column_name' => $column_name,
            'old_value' => $old_value,
            'new_value' => $new_value,
          ]);

      $ProductCategory->title = $request['title'];
      $ProductCategory->hs_code = $request['hs_code'];
      $ProductCategory->import_tax_book = $request['import_tax_book'];
      $ProductCategory->vat = $request['vat'];

      if($request->hasfile('sub_cat_img'))
      {
        $fileNameWithExt =$request->file('sub_cat_img')->getClientOriginalName();
        $fileName        = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        $extension       = $request->file('sub_cat_img')->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $fileNameToStore = str_replace(' ', '', $fileNameToStore);
        $path            = $request->file('sub_cat_img')->move('public/uploads/category_image/',$fileNameToStore);
        $ProductCategory->image = $fileNameToStore;
      }
      if($checkPrefix != null)
      {
        $sub_prefix = 1;
      }
      else
      {
        $ProductCategory->prefix = $request['prefix'];
      }

      $ProductCategory = ProductCategory::find($request->editid);
      $ProductCategory->title = $request['title'];
      $ProductCategory->hs_code = $request['hs_code'];
      // $ProductCategory->expiry = $request['expiry'];
      $ProductCategory->import_tax_book = $request['import_tax_book'];
      $ProductCategory->vat = $request['vat'] != null && $request['vat'] != '' ? $request['vat'] : '0';

      if($checkPrefix != null)
      {
        $sub_prefix = 1;
      }
      else
      {
        $ProductCategory->prefix = $request['prefix'];
      }

      $ProductCategory->save();

      if($request->customer_category_id[0] != null)
      {
        for($i=0;$i<sizeof($request->customer_category_id);$i++)
        {
          $customerTypeMargins = CustomerTypeCategoryMargin::where('category_id',$request->editid)->where('customer_type_id',$request->customer_category_id[$i])->first();

            //saving data into categoryhistory table
          // if($customerTypeMargins->default_value != $request->default_value[$i])
          // {

          //   $category_history = new CategoryHistory;
          //   $category_history->user_id = Auth::user()->id;
          //   $category_history->category_margin_id = $request->newid;
          //   $category_history->sub_category = $request['title'];
          //   $category_history->column_name = $request->customer_category_id[$i];
          //   $category_history->old_value = $customerTypeMargins->default_value;
          //   $category_history->new_value = $request->default_value[$i];
          //   $category_history->save();
          // }
            $customerTypeMargins->default_margin   = $request->default_margin[$i];
            $customerTypeMargins->default_value    = $request->default_value[$i];
            $customerTypeMargins->save();
        }
      }

      return response()->json(['success' => true , 'sub_title' => $sub_title , 'sub_prefix' => $sub_prefix]);
    }

    public function getSubCategoryHistory() {
        $query = SubCategoryHistory::where('sub_category_id', '!=', null)->orderBy('id', 'DESC')->get();


        return Datatables::of($query)
            ->addColumn('user_id', function ($item) {
                return @$item->user_id != null ? $item->user->name : '--';
            })

            ->addColumn('column_name', function ($item) {
                return @$item->column_name != null ? ucwords(str_replace('_', ' ', $item->column_name)) : '--';
            })

            ->addColumn('sub_category_id', function ($item) {
                $item_id = $item->sub_category_id !== null && $item->productCategory != null ? $item->productCategory->title : '--';
                return $item_id;
            })

            ->addColumn('old_value', function ($item) {
                return @$item->old_value != null ? $item->old_value : '--';
            })

            ->addColumn('new_value', function ($item) {
                return @$item->new_value != null ? $item->new_value : '--';
            })
            ->addColumn('created_at', function ($item) {
                return @$item->created_at != null ? $item->created_at->format('d/m/Y') : '--';
            })

            ->rawColumns(['user_name', 'sub_category_id', 'column_name', 'old_value', 'new_value', 'created_at'])
            ->make(true);
    }

    public function editParent(Request $request)
    {
        // dd($request->all());
      if($request->ecom_enable_status == 1)
      {
        $validator = $request->validate([
          'title' => 'required',
          'category_image'=> 'mimes:jpeg,png,jpg,svg',
        ]);
      }
      else
      {
        $validator = $request->validate([
          'title' => 'required',
        ]);
      }
      $check_category = ProductCategory::where('title', $request->title)->where('parent_id', '0')->first();
      if($check_category){
        return response()->json(['success' => false]);
      }
      $ProductCategory = ProductCategory::find($request->editid);
      $ProductCategory->title = $request['title'];

      if($request->hasfile('category_image'))
      {
        $fileNameWithExt =$request->file('category_image')->getClientOriginalName();
        $fileName        = pathinfo($fileNameWithExt,PATHINFO_FILENAME);
        $extension       = $request->file('category_image')->getClientOriginalExtension();
        $fileNameToStore = $fileName.'_'.time().'.'.$extension;
        $fileNameToStore = str_replace(' ', '', $fileNameToStore);
        $path            = $request->file('category_image')->move('public/uploads/category_image/',$fileNameToStore);
        $ProductCategory->image = $fileNameToStore;
      }

      $ProductCategory->save();

      return response()->json(['success' => true]);
    }

    public function subCategories($id)
    {
      $parent = ProductCategory::select('title')->where('id',$id)->first();
      $parentCategory = $parent->title;
      $customerCategory = CustomerCategory::where('is_deleted',0)->get();
      $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();

      if($ecommerceconfig)
      {
        $check_status = unserialize($ecommerceconfig->print_prefrences);
        $ecommerceconfig_status = $check_status['status'][0];
      }
      else
      {
        $ecommerceconfig_status = '';
      }

      return $this->render('backend.productCategories.sub-categories',compact('id','customerCategory','parentCategory','ecommerceconfig_status'));
    }

    public function getDynamicCategoryFields()
    {
      $html_string = '';
      $customerCategory = CustomerCategory::where('is_deleted',0)->get();

      if($customerCategory)
      {
        foreach($customerCategory as $categories)
        {
          $html_string .= '
          <tr>
            <td style="width: 135px;">
              <div class="form-group">
                <input class="font-weight-bold form-control-lg form-control" value="'.$categories->id.'" name="customer_category_id[]" type="hidden">
                <input class="font-weight-bold form-control-lg form-control" value="'.$categories->title.'" name="customer_category_name[]" type="text" readonly="">
              </div>
              <input type="hidden" name="default_margin[]" value="Percentage">
            </td>
            <td style="width: 150px;">
              <div class="form-group">
                <input class="font-weight-bold form-control-lg form-control" placeholder="Default Value" name="default_value[]" type="number" required="" autocomplete="off">
              </div>
            </td>
          </tr>';
        }
      }
      else
      {
        $html_string .= '<div style="float:center;"><p>No Categories Found...</p></div>';
      }

      return response()->json([ 'success' => true, 'html_string' => $html_string ]);
    }

    public function getSubData($id)
    {
      $deployment = Deployment::where('status', 1)->first();
      $query = ProductCategory::where('parent_id',$id)->get();

      $dt = Datatables::of($query);

      $dt->addColumn('action', function ($item) use ($deployment) {
        $ecom = '';
        if ($deployment != null && $deployment->status == 1 && (Auth::user()->role_id == 1 || Auth::user()->role_id == 10 || Auth::user()->role_id == 11)) {
          $ecom = '<a href="javascript:void(0);" data-id="'.$item->id.'" class="actionicon snyc_with_ecom" title="Enable to Ecom"><i class="fa fa-check"></i></a>';
        }

        $html_string = '<div class="icons">'.'
            <a href="javascript:void(0);" data-id="'.$item->id.'" title="Edit" class="actionicon tickIcon edit-icon"><i class="fa fa-pencil"></i></a>
            <a href="javascript:void(0);" data-id="'.$item->id.'" title="Delete" class="actionicon deleteIcon delete-icon"><i class="fa fa-trash"></i></a>
            <a href="javascript:void(0);" data-id="'.$item->id.'" title="Update on a Product level" class="actionicon updateIcon update-prod-by-cat"><i class="fa fa-refresh"></i></a>
            '.$ecom.'
          </div>';
        return $html_string;
      });

      $dt->addColumn('title', function ($item) {
        if($item->parent_id == 0)
        {
          $html_string = '<div>'.'
            <a href="'.route('product-sub-categories-list').'" data-id="'.$item->id.'"  class="parent" style="color:blue">'.$item->title.'</a>
          </div>';
        }
        else
        {
          $html_string = '<div>'.$item->title.'</div>';
        }

        return $html_string;
      });

      $dt->addColumn('hs_code', function ($item) {
        return $item->hs_code !== null ? $item->hs_code : '--';
      });

      $dt->addColumn('prefix', function ($item) {
        return $item->prefix !== null ? $item->prefix : '--';
      });

      $dt->addColumn('import_tax_book', function ($item) {
        return $item->import_tax_book !== null ? $item->import_tax_book.' %': '--';
      });

      $dt->addColumn('vat', function ($item) {
        return $item->vat !== null ? $item->vat.' %': '0 %';
      });

      // ->addColumn('expiry', function ($item) {
      //     return $item->expiry !== null ? $item->expiry : '--';
      //     })

      $customerCategory = CustomerCategory::where('is_deleted',0)->get();
      $i=0;
      foreach ($customerCategory as $customerCat) {
        $dt->addColumn($customerCat->title, function ($item) use($i) {
            return $item->get_markups[$i]->default_value !== null ? $item->get_markups[$i]->default_value.' %': '--';
          });
        $i++;
      }

      // ->addColumn('HOTEL', function ($item) {
      //     return $item->get_markups[1]->default_value !== null ? $item->get_markups[1]->default_value.' %': '--';
      //     })
      // ->addColumn('RETAIL', function ($item) {
      //     return $item->get_markups[2]->default_value !== null ? $item->get_markups[2]->default_value.' %': '--';
      //     })
      // ->addColumn('PRIVATE', function ($item) {
      //     return $item->get_markups[3]->default_value !== null ? $item->get_markups[3]->default_value.' %': '--';
      //     })
      // ->addColumn('catering_markup', function ($item) {
      //     if($item->get_markups[4])
      //     {
      //       return $item->get_markups[4]->default_value !== null ? $item->get_markups[4]->default_value.' %': '--';
      //     }
      //     else
      //     {
      //       return "N.A";
      //     }
      //     })

      $dt->addColumn('created_at', function ($item) {
        $html_string = Carbon::parse(@$item->created_at)->format('d/m/Y');
        return $html_string;
      });

      $dt->setRowId(function ($item) {
        return $item->id;
      });

      $dt->rawColumns(['action','title','expiry','created_at','vat','import_tax_book','hs_code']);

      return $dt->make(true);

    }

    public function deleteSubCat(Request $request)
    {
      $sub_cat_product = Product::where('category_id',$request->id)->get();
      if($sub_cat_product->count() > 0)
      {
        $message = "Product(s) against this Sub Category exists. First delete its Products";
        return response()->json(['error' => true, 'successmsg' => $message]);
      }
      else
      {
        $product_categories = ProductCategory::find($request->id);
        if($product_categories)
        {
          $cust_type_cat_margins = CustomerTypeCategoryMargin::where('category_id' , $product_categories->id)->get();
          if($cust_type_cat_margins->count() > 0)
          {
            foreach ($cust_type_cat_margins as $ctcm)
            {
              $ctcm->delete();
            }
          }
          $product_categories->delete();
        }
        $message = "Sub Category has been deleted";
        return response()->json(['error' => false, 'successmsg' => $message]);
      }
    }

    public function deleteCategory(Request $request)
    {
      $sub_cat_product = Product::where('primary_category',$request->id)->get();
      if($sub_cat_product->count() > 0)
      {
        $message = "Product(s) against this Category exists. First delete its Products";
        return response()->json(['error' => true, 'successmsg' =>$message]);
      }
      else
      {
        $product_category = ProductCategory::find($request->id);
        $its_sub_categories = ProductCategory::where('parent_id',$request->id)->get();
        if($product_category)
        {
          foreach ($its_sub_categories as $sub_cat) {
            $cust_type_cat_margins = CustomerTypeCategoryMargin::where('category_id' , $sub_cat->id)->get();
            if($cust_type_cat_margins->count() > 0)
            {
              foreach ($cust_type_cat_margins as $ctcm)
              {
                $ctcm->delete();
              }
            }
            $sub_cat->delete();
          }
          $product_category->delete();
        }
        $message = "Category and its Sub Categories has been deleted";
        return response()->json(['error' => false, 'successmsg' => $message]);
      }
    }

    public function getSubCatMarginDetail(Request $request)
    {
      $getCatMargins = CustomerTypeCategoryMargin::where('category_id',$request->cat_id)->get();

      $html_string = '';

      $html_string = '
        <div class="table-responsive">
          <table class="table table-bordered">
          <thead class="thead-light">
            <tr>
              <th>Customer Type</th>
              <th>Default Markup Value %</th>
            </tr>
          </thead>';
        if($getCatMargins):
        foreach($getCatMargins as $margins):
        $html_string .= '<tr>
              <td style="width: 135px;">
                <div class="form-group">
                  <input class="font-weight-bold form-control-lg form-control"  name="default_value" type="text" value="'.$margins->categoryMargins->title.'" readonly="">
                </div>
              </td>
              <td style="width: 150px;">
                <div class="form-group">
                  <input class="font-weight-bold form-control-lg form-control" name="default_value" type="text" value="'.$margins->default_value.'" readonly="">
                </div>
              </td>
            </tr>';
        endforeach;
        endif;
        $html_string .= ' <tbody>
          </tbody>
          </table>
        </div>';

      return response()->json(['success' => true, 'html_string' => $html_string]);
    }

    public function getSubCatDetailForEdit(Request $request)
    {
      $getCatMargins = CustomerTypeCategoryMargin::where('category_id',$request->cat_id)->get();
      $categoryTable = ProductCategory::where('id',$request->cat_id)->first();
      $customerCategory = CustomerCategory::where('is_deleted',0)->get();

      $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();
      if($ecommerceconfig)
      {
        $check_status = unserialize($ecommerceconfig->print_prefrences);
        $ecommerceconfig_status = $check_status['status'][0];
      }
      else
      {
        $ecommerceconfig_status = '';
      }

        $html_string = '';

        $html_string .= '
        <div class="form-group">
          <label class="pull-left">HS Code</label>
          <input type="text" name="hs_code" class="font-weight-bold form-control-lg form-control e-cat-hs_code" value="'.$categoryTable->hs_code.'" placeholder="Enter HS Code">
        </div>

        <div class="form-group">
          <input type="hidden" name="editid" id="editid" value="'.$request->cat_id.'">
          <input type="hidden" name="newid" id="newid" value="'.$categoryTable->parent_id.'">
          <label class="pull-left">Sub Category</label>
          <input type="text" name="title" class="font-weight-bold form-control-lg form-control e-prod-cat" placeholder="Enter Product Category" value="'.$categoryTable->title.'" required="">
        </div>
          <span class="d-none bold" id="tit" style="color:red;font-size:12px;" role="alert">Title Already Exists</span>

        <div class="form-group">
          <label class="pull-left">Prefix</label>
          <input class="font-weight-bold form-control-lg form-control e-prod-cat"  type="text" name="prefix" class="font-weight-bold form-control-lg form-control e-prod-cat-prefix" placeholder="Enter Product Prefix" value="'.$categoryTable->prefix.'" required="">
        </div>
        <span class="d-none bold" id="pre" style="color:red;font-size:12px;" role="alert">Prefix Already Exists</span>';

        if($ecommerceconfig_status == 1)
        {
          $html_string .=  '<div class="form-group">';
          if($categoryTable->image)
          {

           $html_string .= '
            <img height="140px" id="uploaded_sub_cat_image" src="'.asset('/public/uploads/category_image/'.$categoryTable->image).'">';
          }
          else
          {
            $html_string .= '
           <img id="uploaded_icategoryTable->imagemage" height="140px" src="'.asset('public/uploads/logo/file-upload.jpg').'">
                  ';
          }
          $html_string .= '</div>

          <div class="form-group">
          <label class="pull-left">Upload Image</label>
          <input type="file" name="sub_cat_img" id="upload_sub_cat_image_file_field" class="font-weight-bold form-control-lg form-control e-sub-prod-cat-image" placeholder=""
          ';
          if($categoryTable->image){ }
          else
          {
            $html_string .= '';
          }
          $html_string .= ' ></div>';
        }

        $html_string .=  '<div class="form-group">
          <label class="pull-left">Import Tax Book (%)</label>
          <input type="number" name="import_tax_book" class="font-weight-bold form-control-lg form-control e-cat-import_tax_book" value="'.$categoryTable->import_tax_book.'" placeholder="Enter Import Tax Book">

        </div>
        <div class="form-group">
          <label class="pull-left">Vat (%)</label>
          <input type="number" name="vat" class="font-weight-bold form-control-lg form-control e-cat-vat" value="'.$categoryTable->vat.'" placeholder="Enter Vat">

        </div>';

        $html_string .= '
        <div class="table-responsive">
          <table class="table table-bordered">
          <thead class="thead-light">
            <tr>
              <th>Customer Type</th>
              <th>Default Markup Value %</th>
            </tr>
          </thead>';
        if($getCatMargins):
        foreach($getCatMargins as $margins):
        $html_string .= '<tr>
          <td style="width: 135px;">
            <div class="form-group">
              <input class="font-weight-bold form-control-lg form-control" value="'.$margins->customer_type_id.'" name="customer_category_id[]" type="hidden">
              <input class="font-weight-bold form-control-lg form-control"  name="customer_category_name[]" type="text" value="'.$margins->categoryMargins->title.'" readonly="">

            </div>
            <input value="Percentage" name="default_margin[]" type="hidden">
          </td>
          <td style="width: 150px;">
            <div class="form-group">
              <input class="font-weight-bold form-control-lg form-control" name="default_value[]" type="text" value="'.$margins->default_value.'">

            </div>
          </td>
        </tr>';
        endforeach;
        endif;
        $html_string .= ' <tbody>
          </tbody>
          </table>
        </div>';
        $html_string .= '<div class="form-submit">
        <input type="submit" value="Update" class="btn btn-bg w-25 save-edit-cat-btn"></div>';

      return response()->json(['success' => true, 'html_string' => $html_string]);
    }

    public function getPrdCatNameForEdit(Request $request)
    {
      $ecommerceconfig = QuotationConfig::where('section','ecommerce_configuration')->first();

      if($ecommerceconfig)
      {
        $check_status = unserialize($ecommerceconfig->print_prefrences);
        $ecommerceconfig_status = $check_status['status'][0];
      }
      else
      {
        $ecommerceconfig_status = '';
      }
      $categoryTable = ProductCategory::where('id',$request->cat_id)->first();
      $html_string = '';

      $html_string = '
          <div class="form-group">
            <input type="hidden" name="editid" id="editid" value="'.$request->cat_id.'">
            <label class="pull-left"></label>
            <input type="text" name="title" class="font-weight-bold form-control-lg form-control e-prod-cat" placeholder="Enter Product Category" value="'.$categoryTable->title.'" required="true">
          </div>';

      $html_string .= '<input type="hidden" name="ecom_enable_status" value="'.$ecommerceconfig_status.'">';
      if($ecommerceconfig_status == 1)
      {
        $path = config('app.ecom_public_path');
        $html_string .= '
        <div class="form-group">';
          // <label class="pull-left">Category Image</label>';
        if($categoryTable->image)
        {
          $html_string .= '
          <img id="uploaded_image" height="140px" src="'.asset('public/uploads/category_image/'.$categoryTable->image).'">';
        }
        else
        {
          $html_string .= '
          <img id="uploaded_image" height="140px" src="'.asset('public/uploads/logo/file-upload.jpg').'"';
        }

        if($categoryTable->image)
        {
          // do nothing
        }
        else
        {
          $html_string .= 'required="true">';
        }

        $html_string .= '<input id="upload_image_file_field" type="file" name="category_image" class="mt-2 font-weight-bold form-control-lg form-control e-p@elserod-cat" placeholder="" ></div>';
      }

      return response()->json(['success' => true, 'html_string' => $html_string]);
    }

    public function validateUniquePrefix(Request $request)
    {
      $prefix = $request->get('prefix');
      $already_exist = ProductCategory::where('prefix',$prefix)->first();
      if($already_exist != null )
      {
        return response()->json(['error' => true, 'successmsg' => 'Prefix already exist.']);
      }
      else
      {
        return response()->json(['error' => false, 'successmsg' => 'Prefix is unique']);
      }
    }

    public function updateProductMargins(Request $request)
    {
      $product_categories = ProductCategory::where('parent_id','!=',0)->get();  // getting all product categories
      foreach ($product_categories as $category)
      {
        $Products = Product::all();                          // getting all products
        foreach ($Products as $product)
        {
          $customer_categories = CustomerCategory::where('is_deleted','!=',1)->get();    // getting all Customer Categories
          foreach ($customer_categories as $cust_cat)
          {
            $productMargins = CustomerTypeProductMargin::where('customer_type_id',$cust_cat->id)->where('product_id', $product->id)->first();
            $getCatMargins = CustomerTypeCategoryMargin::where('category_id',$category->id)->where('customer_type_id',$cust_cat->id)->first();

            if($productMargins) // if exist then update the product margins
            {
              $productMargins->default_value  =  $getCatMargins->default_value;
              $productMargins->save();
            }
            else               // create new product margin
            {
              $categoryMarginsNew = new CustomerTypeProductMargin;
              $categoryMarginsNew->product_id       = $product->id;
              $categoryMarginsNew->customer_type_id = $cust_cat->id;
              $categoryMarginsNew->default_margin   = $getCatMargins->default_margin;
              $categoryMarginsNew->default_value    = $getCatMargins->default_value;
              $categoryMarginsNew->save();
            }

            $productFixedPrice = ProductFixedPrice::where('customer_type_id',$cust_cat->id)->where('product_id', $product->id)->first();
            if($productFixedPrice)  // if exist then update the ProductFixedPrice
            {
              // do nothing
            }
            else                    // create new ProductFixedPrice
            {
              $productFixedPrices = new ProductFixedPrice;
              $productFixedPrices->product_id       = $product->id;
              $productFixedPrices->customer_type_id = $cust_cat->id;
              $productFixedPrices->fixed_price      = 0;
              $productFixedPrices->expiration_date  = NULL;
              $productFixedPrices->save();
            }
          }

          // Now updating product pricing according to the categories
          $getProduct = Product::find($product->id);
          $getProduct->hs_code          = $category->hs_code;
          $getProduct->import_tax_book  = $category->import_tax_book;
          $getProduct->vat              = $category->vat;

          if($getProduct->supplier_id != 0)  // if product default/last supplier exist
          {
            $getProductDefaultSupplier = SupplierProducts::where('product_id',$getProduct->id)->where('supplier_id',$getProduct->supplier_id)->first();
            if($getProductDefaultSupplier->import_tax_actual == null) // if import tax actual is not defined then this condition will execute
            {
              $importTax = $getProduct->import_tax_book;
              $newTotalBuyingPrice = ((($importTax)/100) * $getProductDefaultSupplier->buying_price_in_thb) + $getProductDefaultSupplier->buying_price_in_thb;

              $total_buying_price = ($getProductDefaultSupplier->freight)+($getProductDefaultSupplier->landing)+($getProductDefaultSupplier->extra_cost)+($getProductDefaultSupplier->extra_tax)+($getProductDefaultSupplier->buying_price_in_thb);

              $getProduct->total_buy_unit_cost_price = $total_buying_price;
              // this is supplier buying unit cost price
              $supplier_conv_rate_thb = @$getProductDefaultSupplier->supplier->getCurrency->conversion_rate;
              $getProduct->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;
              // this is selling price
              $total_selling_price = $getProduct->total_buy_unit_cost_price * $getProduct->unit_conversion_rate;
              $getProduct->selling_price = $total_selling_price;
            }
          }

          $getProduct->save();
        }
      }
      $successmsg = "Products Updated Successfully";
      return response()->json(['success' => true, 'successmsg' => $successmsg]);
    }

    public function updateProductMarginsByCat(Request $request)
    {
      $flag_table = Flag::where('type','product_margin_update')->first();
      if($flag_table == null)
      {
        $flag_table = Flag::firstOrNew(['type'=>'product_margin_update']);
        $flag_table->currency_id = @$request->id;
        $flag_table->save();
        return response()->json(['percent' => '2']);
      }
      elseif($flag_table->status == 0)
      {
        $flag_table->currency_id = $request->id;
        $flag_table->save();
        if($flag_table->total_rows != 0)
        {
          $percent = round(($flag_table->updated_rows/$flag_table->total_rows)*100);
        }
        else
        {
          $percent = '2';
        }
        return response()->json(['percent' => $percent]);
      }
      else
      {
        $flag_table->delete();
        return response()->json(['success' => true]);
      }
    }

    public function marginUpdateJobStatus(Request $request)
    {
      $id = $request->id;
      $status = ExportStatus::where('type','margins_update_on_products')->first();

      if($status == null)
      {
        $new           = new ExportStatus();
        $new->user_id  = Auth::user()->id;
        $new->type     = 'margins_update_on_products';
        $new->status   = 1;
        $new->save();
        UpdateProductMarginJob::dispatch($id,Auth::user()->id);
        return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'recursive' => true]);
      }
      elseif($status->status==1)
      {
        return response()->json(['msg' => "File is already being prepared", 'status' => 2]);
      }
      elseif($status->status==0 || $status->status==2)
      {
        ExportStatus::where('type','margins_update_on_products')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
        UpdateProductMarginJob::dispatch($id,Auth::user()->id);
        return response()->json(['msg' => "File is now getting prepared", 'status' => 1, 'exception' => null]);
      }
    }

    public function checkStatusForFirstTimeMargins()
    {
      $status = ExportStatus::where('type','margins_update_on_products')->where('user_id',Auth::user()->id)->first();
      if($status != null)
      {
        return response()->json(['status' => $status->status]);
      }
      else
      {
        return response()->json(['status' => 0]);
      }
    }

    public function recursiveJobStatusMarginsUpdate()
    {
      $status = ExportStatus::where('type','margins_update_on_products')->first();
      return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception]);
    }

    public function syncProductCategory(Request $request)
    {
      $result = (new ProductCategory)->syncProductCategoryEcom($request->id);
      if($result['success'] == true)
      {
        return response()->json(['success' => true]);
      }
      else
      {
        return response()->json(['success' => false]);
      }
    }
}
