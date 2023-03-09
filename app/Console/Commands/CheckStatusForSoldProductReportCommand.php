<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\StatusCheckForSoldProductsExport;
use App\FiltersForSoldProductReport;
use App\SoldProductsReportRecord;
use App\Models\Common\Order\OrderProduct;
use App\User;
use App\Models\Common\Order\Order;
use App\Models\Common\Product;
use App\Models\Common\WarehouseProduct;
use Carbon\Carbon;
use DB;
use App\Exports\soldProductExport;
use App\Variable;


class CheckStatusForSoldProductReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CSVExport:CheckStatusForSoldProductReportCommand';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $status=StatusCheckForSoldProductsExport::first();
        if($status->status==1)
        {
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
          $this->info('*****************************************');
          $this->info('*****************************************');
          $this->info('CSV Scheduler Started at '.date('Y-m-d H:i:s'));
          $this->info('******************************************');
          $this->info('******************************************');
         // dd($status);
          $request=FiltersForSoldProductReport::first();
          $query = OrderProduct::with('product','get_order')->select('product_id','order_id','supplier_id','from_warehouse_id','id','vat','total_price','unit_price','qty_shipped','quantity','created_at')->whereNotNull('product_id')->whereHas('get_order',function($q){
            $q->whereIn('primary_status',[2,3]);
          });


      if($request->warehouse_id_exp != null)
      {
        $users =  User::where('warehouse_id' , $request->warehouse_id_exp)->whereNull('parent_id')->pluck('id');
        $orders = Order::whereIn('user_id' , $users)->pluck('id');
        $query = $query->whereIn('order_id',$orders);
      }
      
      if($request->customer_id_exp != null)
      {
        $query = $query->whereIn('order_id', Order::where('customer_id',$request->customer_id_exp)->pluck('id') );
      }

      if($request->order_type_exp != null)
      {
        $query = $query->whereIn('order_id', Order::where('primary_status',$request->order_type_exp)->pluck('id') );
      }

      if($request->product_id_exp != '')
      {
        $query = $query->where('product_id' , $request->product_id_exp);
      }
      if($request->prod_category_exp != null) 
        {
            $product_ids = Product::where('category_id', $request->prod_category_exp)->where('status',1)->pluck('id');
            // dd($product_ids);
        $query = $query->whereIn('product_id',$product_ids);
        }

      if($request->filter_exp != null)
        {
            if($request->filter_exp == 'stock')
            {
                $query = $query->whereIn('product_id',WarehouseProduct::select('product_id')->where('current_quantity','>',0.005)->pluck('product_id'));
            }
            elseif($request->filter_exp == 'reorder')
            {
                //$query->where('min_stock','>',0);
                $product_ids = Product::where('min_stock','>',0)->where('status',1)->pluck('id');
                // dd($product_ids);
                $query = $query->whereIn('product_id',$product_ids);
            }
        }

        if($request->from_date_exp != null)
        {
          $date = str_replace("/","-",$request->from_date_exp);
          $date =  date('Y-m-d',strtotime($date));
          // $query = $query->whereDate('created_at', '>=', $date);
          $query->whereHas('get_order' ,function($q) use ($date){
            $q->where('created_at', '>=', $date);
          });
        }
        if($request->to_date_exp != null)
        {
          $date = str_replace("/","-",$request->to_date_exp);
          $date =  date('Y-m-d',strtotime($date));
          // $query->whereIn('order_id' , Order::where('created_at', '<=', $date.' 23:59:59')->pluck('id')->toArray());
          $query->whereHas('get_order' ,function($q) use ($date){
            $q->where('created_at', '<=', $date.' 23:59:59');
          });
          // $query->whereHas('get_order', function($q) use($date){
          //         $q->where('orders.created_at', '<=', $date);
          //     });
          // $query = $query->whereDate('created_at', '<=', $date);
        }

        /***********/
        $current_date = date("Y-m-d");
        $query = $query->get(); 
        $data=[]; 
        StatusCheckForSoldProductsExport::where('id',1)->update(['status'=>1]);
          
        DB::table('sold_products_report_records')->truncate();
        $data=[];
         foreach($query as $item)
         {
            $order = $item->get_order;
            $order_idd = null;
            $supply_from = null;
            $qty = null;
            $brand=null;

              if ($item->order_id != null)
              {
                    if ($order->in_status_prefix !== null && $order->in_ref_prefix !== null && $order->in_ref_id !== null)
                    {
                        $order_idd = $order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
                    }
                    elseif($order->status_prefix !== null && $order->ref_prefix !== null && $order->ref_id !== null)
                    {
                        $order_idd = $order->status_prefix.'-'.$order->ref_prefix.$order->ref_id;
                    }
                    else
                    {
                        $order_idd = $order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id;
                    }
              }
              else
              {
                $order_idd = 'N.A';
              }

              if($item->supplier_id != NULL && $item->from_warehouse_id == NULL)
              {
                     
                 $supply_from = $item->from_supplier->reference_name;
              }
              
              elseif($item->from_warehouse_id != NULL && $item->supplier_id == NULL)
              {
              
                 $supply_from = $item->from_warehouse->warehouse_title;
              }
              else
              {
                $supply_from = 'N.A';
              }
              

              if($order->primary_status == 2)
              {
              $qty = ($item->quantity !== null ? round($item->quantity,2) : 'N.A');
              }
              else
              {
              $qty = ($item->qty_shipped !== null ? round($item->qty_shipped,2) : 'N.A');
              }
              if($item->brand!=null)
              {
                $brand=$item->brand;
              }
              elseif($item->product_id!=null)
              {
                if($item->product->brand!=null)
                {
                  $brand=$item->product->brand;
                }
                else
                {
                  $brand='--';
                }
                
              }
              else
              {
                $brand='--';
              }

                $data[] = [
                    'order_no'=>$order_idd, 
                    
                    'customer'=>$order->customer !== null ? $order->customer->reference_name : 'N.A', 
                    
                    'delivery_date'=>$order->delivery_request_date !== null ? Carbon::parse($order->delivery_request_date)->format('d/m/Y') : 'N.A', 
                    
                    'created_date'=>$item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : 'N.A', 
                    
                    'supply_from'=>$supply_from, 
                    
                    'warehouse'=>$order->user->get_warehouse !== null ? $order->user->get_warehouse->warehouse_title : 'N.A',

                    'p_ref'=>$item->product->refrence_code, 

                    'item_description'=>$item->product->short_desc, 

                    'selling_unit'=>$item->product->sellingUnits->title , 
                    
                    'qty'=>$qty,

                    'unit_price'=>($item->unit_price !== null ? round($item->unit_price,2) : 'N.A'), 

                    'total_amount'=>$item->total_price_with_vat !== null ? round($item->total_price_with_vat,2) : 'N.A', 
                    
                    'vat'=>$item->vat !== null ? $item->vat.' %' : 'N.A', 

                    'brand'=>$brand, 
             
                ];
         }
         foreach (array_chunk($data,1500) as $t)  
        {
            DB::table('sold_products_report_records')->insert($t); 
        }        
        //return reponse()->json(['msg'=>'Table Done'])      
        $records=SoldProductsReportRecord::get();
        $return=\Excel::store(new soldProductExport($records,$global_terminologies),'Sold-Products-Report.xlsx');
        if($return)
        {
            StatusCheckForSoldProductsExport::where('id',1)->update(['status'=>0,'last_download'=>date('Y-m-d')]);
            $this->info('*****************************************************************************************');
            $this->info('*****************************************************************************************');
            $this->info('CSV Exporting Ended at '.date('Y-m-d H:i:s'));
            $this->info('*****************************************************************************************');
            $this->info('******************************************************************************************');
            return response()->json(['msg'=>'File Saved']);
        } 
        
  
        }
    }
}
