<?php

namespace App\Console\Commands;

use App\Exports\completeProductExport;
use App\FiltersForCompleteProduct;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\ProductsRecord;
use App\Models\Common\ProductCategory;
use App\ExportStatus;
use App\Variable;
use Auth;
use App\QuotationConfig;
use DB;
use Illuminate\Console\Command;

class CheckStatusForCompleteProductCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CSVExport:CheckStatusForCompleteProductCommand {user_id} {request}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Status Check For Complete Products';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $user_id=$this->argument('user_id');
        $request=$this->argument('request');
        $getWarehouses = Warehouse::where('status',1)->get();
        $fileName='All';
           $hide_hs_description=null;
           $globalAccessConfig2 = QuotationConfig::where('section','products_management_page')->first();
           // $globalaccessForConfig=[];

           if($globalAccessConfig2)
           {
               $globalaccessForConfig = unserialize($globalAccessConfig2->print_prefrences);
               foreach ($globalaccessForConfig as $val)
               {
                   if($val['slug'] === "hide_hs_description")
                   {
                       $hide_hs_description = $val['status'];
                   }           
               }
           }
           else
           {
               $hide_hs_description = '';
           }

           $vairables=Variable::select('slug','standard_name','terminology')->get();
               $global_terminologies=[];
               foreach($vairables as $variable)
               {
                   if($variable->terminology != null)
                   {
                       $global_terminologies[$variable->slug]=$variable->terminology;
                   }else{
                       $global_terminologies[$variable->slug]=$variable->standard_name;
                   }
               }

           $query = Product::select('products.refrence_code','products.primary_category','products.short_desc','products.buying_unit','products.selling_unit','products.stock_unit','products.type_id','products.brand','products.product_temprature_c','products.supplier_id','products.id','products.total_buy_unit_cost_price','products.weight','products.unit_conversion_rate','products.selling_price','products.vat','products.import_tax_book','products.hs_code','products.category_id','products.hs_description');
           $query->with('def_or_last_supplier', 'units','prouctImages','productType','productBrand','productSubCategory','supplier_products','productCategory')->where('status',1)->orderBy('refrence_no', 'DESC');

       if($request['default_supplier_exp'] != '')
       {
           $fileName="Filtered";
           $supplier_query = $request['default_supplier_exp'];
           $query = $query->whereIn('id', SupplierProducts::select('product_id')->where('supplier_id',$supplier_query)->pluck('product_id'));
       }

       if($request['prod_type_exp'] != '')
       {
            $fileName="Filtered";
           $query->where('type_id', $request['prod_type_exp'])->where('status',1)->orderBy('refrence_no', 'DESC');
       }

       if($request['prod_category_exp'] != '')
       {
            $fileName="Filtered";
           $query->whereIn('products.category_id', ProductCategory::select('id')->where('title',$request['prod_category_exp'])->where('parent_id','!=',0)->pluck('id'))->where('products.status',1);
       }
   
        if($request['prod_category_primary_exp'] != '')
       {

            $fileName="Filtered";
           $query->where('primary_category', $request['prod_category_primary_exp'])->where('status',1)->orderBy('refrence_no', 'DESC');
       }

       if($request['filter-dropdown_exp']!= '')
       {


            $fileName="Filtered";
           if($request['filter-dropdown_exp'] == 'stock')
           {
               $query = $query->whereIn('id',WarehouseProduct::select('product_id')->where('current_quantity','>',0.005)->pluck('product_id'));
           }
           elseif($request['filter-dropdown_exp'] == 'reorder')
           {
               $query->where('min_stock','>',0);
           }
       }

       if($request['search_value']!='')
       {

        $fileName="Filtered";
           $rc = (clone $query)->where('refrence_code','LIKE','%$request["search_value"]%')->orWhere('short_desc','LIKE','%$request["search_value"]%')->orWhere('short_desc','LIKE','%$request["search_value"]%')->orWhereIn('id', SupplierProducts::select('product_id') ->where('supplier_description','LIKE','%$request["search_value"]%')->pluck('product_id'));
           //if(!$rc->get()->isEmpty()) ->where('supplier_description','LIKE',"%$request['search_value']%")->pluck('product_id'))
           $query=$rc;
 
           // $sd1 = (clone $query)->where('short_desc','LIKE',"%$request['search_value%")
           // if(!$sd1->get()->isEmpty())
           // $query=$sd1;

           // $sd = (clone $query)->whereIn('id', SupplierProducts::select('product_id')->where('supplier_description','LIKE',"%$request['search_value%")->pluck('product_id'));
           // if(!$sd->get()->isEmpty())
           // $query=$sd;
           
         
          
           // $query = $query->whereHas('supplier_products',function($q) use($keyword) {
           //     $q->where('supplier_description','LIKE',"%$keyword%")->pluck('product_id');
           // });//SupplierProducts::select('product_id')->where('supplier_description','LIKE',"%$keyword%")->pluck('product_id'));
         

       }
       $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','completed_products')->where('user_id',$user_id)->first();
       $not_visible_arr = explode(',',$not_visible_columns->hide_columns);
       /***********/   
        $current_date = date("Y-m-d");
       $query = $query->get();
       //StatusCheckForCompleteProductsExport::where('id',1)->update(['status'=>1]);

       // ****************************************************************** //
               // dynamic code for a warehouse reserved and current Qty
       // ****************************************************************** //
         
       DB::table('products_records')->truncate();
       $data=[];

       foreach($query as $q)
       {
           $getProductDefaultSupplier = $q->supplier_products->where('supplier_id',$q->supplier_id)->first();

           if($getWarehouses->count() > 0)
           {
               foreach ($getWarehouses as $warehouse) 
               {
                   $ids =  Order::where('primary_status',2)->whereHas('user',function($qq) use($warehouse){
                     $qq->where('warehouse_id',$warehouse->id);
                   })->pluck('id')->toArray();

                   $warehouse_product = $q->warehouse_products->where('warehouse_id',$warehouse->id)->first();
               
                   $current_qty  =  (@$warehouse_product->current_quantity != null) ? @$warehouse_product->current_quantity:'0';
                   $reservd_qty  = OrderProduct::whereIn('order_id',$ids)->where('product_id',$q->id)->sum('quantity');

                   $warehosue_c_r_array[] = [
                       "".substr($warehouse->warehouse_title, 0, 3).'_current_qty'.""  => $current_qty,
                       "".substr($warehouse->warehouse_title, 0, 3).'_reserved_qty'."" => $reservd_qty
                   ];
               }
           }

           $data[] = ['id'=>$q->id, 
           'refrence_code'=> $q->refrence_code,
           'short_desc'=>$q->short_desc,
           'primary_category_title'=>$q->productCategory->title,
           'category_title'=>$q->productSubCategory->title,
           'buying_unit_title'=>$q->units->title,
           'selling_unit_title'=>$q->sellingUnits->title,
           'type_title'=>$q->productType->title,
           'brand'=>$q->brand,
           'product_temprature_c'=>$q->product_temprature_c,
           'total_buy_unit_cost_price'=>$q->total_buy_unit_cost_price,
           'weight'=>$q->weight,
           'unit_conversion_rate'=>$q->unit_conversion_rate,
           'selling_price'=>$q->selling_price,
           'vat'=>$q->vat,
           'import_tax_book'=>$q->import_tax_book,
           'hs_code'=>$q->hs_code,
           'hs_description'=>$q->hs_description,
           'supplier_description'=>@$getProductDefaultSupplier->supplier_description,
           'product_supplier_reference_no'=>@$getProductDefaultSupplier->product_supplier_reference_no,
           'purchasing_price_eur'=>number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', ''),
           'purchasing_price_thb'=>number_format((float)@$getProductDefaultSupplier->buying_price_in_thb, 3, '.', ''),
           'freight'=>@$getProductDefaultSupplier->freight,
           'landing'=>@$getProductDefaultSupplier->landing,
           'leading_time'=>@$getProductDefaultSupplier->leading_time,
           'default_supplier'=>$q->def_or_last_supplier->reference_name,
           'currency_symbol'=>$q->def_or_last_supplier->getCurrency->currency_symbol,
           'warehosue_c_r_array'=>serialize($warehosue_c_r_array)
           ];
           
           $warehosue_c_r_array = [];
       }
       foreach (array_chunk($data,1000) as $t)  
       {
           DB::table('products_records')->insert($t); 
       }        
       //return reponse()->json(['msg'=>'Table Done'])      
       $records=ProductsRecord::orderBy('refrence_code','desc')->get();
    //    $path=public_path('uploads/completed_products_exports');
       
    
       $return=\Excel::store(new completeProductExport($records,$not_visible_arr,$global_terminologies,$hide_hs_description,$getWarehouses), 'Completed-Product-Report.xlsx','testt');

       if($return)
       {
            ExportStatus::where('type','complete_products')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
           $this->info('Job Ended');
           $this->info('CSV Status Changed at '.date('Y-m-d H:i:s'));
           $this->info('******************************************');
           return response()->json(['msg'=>'File Saved']);
       } 

        //Artisan::call('CSVExport:CheckStatusForCompleteProductCommand',['request'=>$request,'user_id',$this->user_id]);
    }
}
