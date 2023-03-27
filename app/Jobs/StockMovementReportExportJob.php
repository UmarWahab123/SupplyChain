<?php

namespace App\Jobs;

use DB;
use App\Variable;
use App\ExportStatus;
use App\FailedJobException;
use Illuminate\Bus\Queueable;
use App\Models\Common\Product;
use MaxAttemptsExceededException;
use App\Exports\soldProductExport;
use App\Models\Common\TableHideColumn;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Common\StockManagementOut;
use App\Exports\stockMovementReportExport;
use App\Helpers\ProductConfigurationHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\StockMovementReportRecord;
use Illuminate\Queue\MaxAttemptsExceededException as QueueMaxAttemptsExceededException;

class StockMovementReportExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request;
    protected $user_id;
    protected $role_id;
    public $tries=1;
    public $timeout=500;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$user_id,$role_id)
    {
        $this->request=$data;
        $this->user_id=$user_id;
        $this->role_id=$role_id;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $request=$this->request;
        $user_id=$this->user_id;
        $role_id=$this->role_id;
        try
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

          $warehouse_id = $request['warehouse_id_exp'];

          $supplier_id  = $request['supplier_id_exp'];
          $all_movement  = $request['all_movement_exp'];

          $from_date = $request['from_date_exp'];
          $unit_id = $request['unit_id_exp'];
          if($from_date !== null)
          {
            $from_date = str_replace("/","-",$from_date);
            $from_date =  date('Y-m-d',strtotime($from_date));
          }


          $to_date = $request['to_date_exp'];
          if($to_date !== null)
          {
            $to_date = str_replace("/","-",$to_date);
            $to_date =  date('Y-m-d',strtotime($to_date));
          }
          $p_id = array_key_exists('p_id', $request) && $request['p_id'] != null ? $request['p_id'] : null;
          $product_id   = array_key_exists('product_id', $request) && $request['product_id'] != null ? $request['product_id'] : $p_id;

          // if($product_id != null)
          // {
          //   $ids = array();
          //   array_push($ids, $product_id);
          // }
          // else{
          //   $ids = StockManagementOut::distinct('product_id');
          //   if($from_date !== null)
          //   {
          //       $ids->where('created_at','>=',$from_date);
          //   }
          //   if($to_date !== null)
          //   {
          //       $ids->where('created_at','<=',$to_date.' 23:59:59');
          //   }
          //   if($warehouse_id != null)
          //   {
          //       $ids = $ids->where('warehouse_id',$warehouse_id)->pluck('product_id')->toArray();
          //   }
          //   else
          //   {
          //       $ids = $ids->pluck('product_id')->toArray();
          //   }

          // }

          // $products = Product::select('products.id','products.short_desc','products.brand','products.refrence_code','products.min_stock','products.selling_unit','products.type_id','products.type_id_2', 'products.type_id_3','products.supplier_id', 'products.unit_conversion_rate', 'products.total_buy_unit_cost_price')->whereIn('products.id',$ids)->with([
          //   'sellingUnits' => function($u){
          //       $u->select('id','title', 'decimal_places');
          //   },
          //   'stock_out' => function($q) use ($from_date, $to_date){
          //       // dd($from_date, $to_date);
          //   $q->select('id','product_id','quantity_in','quantity_out','created_at','po_group_id','title','order_id')->whereDate('created_at','>=',$from_date)->whereDate('created_at','<=',$to_date);
          //   },'productType','productType2'
          // ]);

          $products = Product::select('products.id', 'products.short_desc', 'products.brand', 'products.refrence_code', 'products.min_stock', 'products.selling_unit', 'products.type_id', 'products.type_id_2', 'products.type_id_3', 'products.supplier_id', 'products.unit_conversion_rate', 'products.total_buy_unit_cost_price')->where('status', 1);
            if($product_id != null){
                $products = $products->where('id', $product_id);
            }
            $products = $products->join('warehouse_products', 'products.id', '=', 'warehouse_products.product_id')
                ->groupBy('products.id')
                ->havingRaw('SUM(warehouse_products.current_quantity) < products.min_stock')
                ->with([
                    'sellingUnits' => function($u) {
                        $u->select('id', 'title', 'decimal_places');
                    },
                    'stock_out' => function($q) use ($from_date, $to_date, $warehouse_id) {
                        $q->select('id', 'product_id', 'quantity_in', 'quantity_out', 'created_at', 'po_group_id', 'title', 'order_id')
                          ->whereDate('created_at', '>=', $from_date)
                          ->whereDate('created_at', '<=', $to_date);
                        if ($warehouse_id != null && $warehouse_id != '') {
                            $q->where('warehouse_id', $warehouse_id);
                        }
                    },
                    'productType',
                    'productType2'
          ]);

          if($warehouse_id != null)
          {
            if($role_id == 9 && $warehouse_id == 1){
                $products = $products->where('products.ecommerce_enabled',1);
            }

          }

          if($supplier_id != null)
          {
            $products = $products->where('products.supplier_id',$request['supplier_id_exp']);
          }

          if($unit_id != null)
          {
            $products->where('products.selling_unit',$unit_id);
          }

          if($request['prod_category_exp'] != '' && $request['prod_category_exp'] != null)
          {
            $products->where('products.category_id', $request['prod_category_exp'])->orderBy('refrence_no', 'ASC');
          }

          if($product_id != null)
          {
            $products->where('products.products.id',$product_id);
          }

          if($request['product_type_exp'] != null)
          {
            $products = $products->where('products.type_id',$request['product_type_exp']);
          }
          if($request['product_type_2_exp'] != null)
          {
            $products = $products->where('products.type_id_2',$request['product_type_2_exp']);
          }
          if($request['product_type_3_exp'] != null)
          {
            $products = $products->where('products.type_id_3',$request['product_type_3_exp']);
          }

          if($p_id != null)
          {
            $products->where('products.id',$p_id);
          }

          if($all_movement != null && $all_movement == 1)
          {
            if($warehouse_id != null)
            {
                $products->whereHas('warehouse_products',function($q) use($warehouse_id){
                    $q->where('warehouse_id',$warehouse_id);
                    $q->where('current_quantity','>',0);
                });
            }
            else
            {
                $products->whereHas('warehouse_products',function($q){
                    $q->where('current_quantity','>',0);
                });
            }

            $stock_items = true;
          }
          else
          {
            $stock_items = false;
          }
          if($all_movement != null && $all_movement == 2)
          {
            $products->where('products.min_stock','>',0);
          }

          if($all_movement != null && $all_movement == 3)
          {
            if($warehouse_id != null)
            {
                // $products->whereHas('warehouse_products',function($q) use($warehouse_id){
                //     $q->where('warehouse_id',$warehouse_id);
                //     $q->where('current_quantity','>',0);
                // });

                if(Auth::user()->role_id == 9 && $warehouse_id == 1){

                  $products->where('products.min_stock', '!=', 0)->whereHas('warehouse_products',function($q)use($warehouse_id){
                  $q->where('warehouse_id',$warehouse_id)->groupBy('warehouse_products.product_id')->havingRaw('SUM(current_quantity) < products.min_stock');
                });
              }
              else{
                  $products->where('products.min_stock', '!=', 0)->whereHas('warehouse_products',function($q)use($warehouse_id){
                  $q->where('warehouse_id',$warehouse_id)->groupBy('warehouse_products.product_id')->havingRaw('SUM(current_quantity) < products.min_stock');
                  });
              }
            }
            else
            {
                $products->where('products.min_stock', '!=', 0)->whereHas('warehouse_products',function($q){
                    $q->groupBy('warehouse_products.product_id')->havingRaw('SUM(floor(current_quantity)) < products.min_stock');
                });
            }

            $stock_min_current = true;
            $stock_items = true;

          }
          else
          {
            $stock_min_current = false;
            $stock_items = false;

          }

          //Sorting Code Starts Here
      $column_name = null;
      $sort_order = $request['sort_order'];
      if ($request['column_name'] == 'pf')
      {
        $column_name = 'refrence_code';
      }
      if ($request['column_name'] == 'product_description')
      {
        $column_name = 'short_desc';
      }
      if ($request['column_name'] == 'brand')
      {
        $column_name = 'brand';
      }
      if ($request['column_name'] == 'min_stock')
      {
        $column_name = 'min_stock';
      }

      if ($request['column_name'] == 'type')
      {
        $products->leftjoin('types as pt', 'pt.id', '=', 'products.type_id')->orderBy('pt.title', $sort_order);
      }
      else if ($request['column_name'] == 'type_2')
      {
        $products->leftjoin('product_secondary_types as pt', 'pt.id', '=', 'products.type_id_2')->orderBy('pt.title', $sort_order);
      }
      else if ($request['column_name'] == 'unit')
      {
        $products->leftjoin('units as u', 'u.id', '=', 'products.selling_unit')->orderBy('u.title', $sort_order);
      }
      if($column_name != null)
      {
        $products->orderBy($column_name, $sort_order);
      }

      //sorting ends here

          // $products = $products->get();
          // DB::table('stock_movement_report_records')->truncate();
          // $data=[];
          // foreach ($products as $prod) {
          //     $unit_conversion_rate = ($prod->unit_conversion_rate != null) ? $prod->unit_conversion_rate : 1;
          //     $cogs = $prod->total_buy_unit_cost_price * $unit_conversion_rate;
          //        $data[] = [
          //           'reference_code'=>$prod->refrence_code != null ? $prod->refrence_code : 'N.A',
          //           'short_desc'=>$prod->short_desc !== null ? $prod->short_desc : 'N.A',
          //           'brand' => $prod->brand != null ? $prod->brand : '--',
          //           'type' => $prod->productType != null ? $prod->productType->title : '--',
          //           'type_2' => $prod->productType2 != null ? $prod->productType2->title : '--',
          //           'selling_unit' => $prod->selling_unit != null ? $prod->sellingUnits->title : '--',
          //           'start_count' => round($prod->Start_count_out+$prod->Start_count_in,2),
          //           'min_stock' => round($prod->min_stock,2),
          //           'in_from_purchase'=>round(($prod->in_purchase!=0 || $prod->in_purchase !=null) ? number_format($prod->in_purchase,2,'.',',')  :0),
          //           'in_order_update'=>round(($prod->in_orderUpdate!=0 || $prod->in_orderUpdate !=null) ? number_format($prod->in_orderUpdate,2,'.',',')  :0),
          //           'in_transfer_document'=>round(($prod->in_transferDocument!=0 || $prod->in_transferDocument !=null) ? number_format($prod->in_transferDocument,2,'.',',')  :0),
          //           'in_manual_adjustment'=>round(($prod->in_manualAdjusment!=0 || $prod->in_manualAdjusment !=null) ? number_format($prod->in_manualAdjusment,2,'.',',')  :0),
          //           'stock_in' => round($prod->INS,2),
          //           'out_manual_adjustment'=>round(($prod->out_manual_adjustment!=0 || $prod->out_manual_adjustment !=null) ? number_format($prod->out_manual_adjustment,2,'.',',')  :0),
          //           'out_transfer_document'=>round(($prod->out_transfer_document!=0 || $prod->out_transfer_document !=null) ? number_format($prod->out_transfer_document,2,'.',',')  :0),
          //           'out_order'=>round(($prod->out_order!=0 || $prod->out_order !=null) ? number_format($prod->out_order,2,'.',',')  :0),

          //           'stock_out' => round($prod->OUTs,2),
          //           'stock_balance' => round($prod->Start_count_out+$prod->Start_count_in+$prod->INS+$prod->OUTs,2),
          //           'cogs' => round($cogs,3)

          //       ];
          //     }

          //   foreach (array_chunk($data,1500) as $t)
          //   {
          //       DB::table('stock_movement_report_records')->insert($t);
          //   }
            $column_visiblity = [];
            $visible_columns = TableHideColumn::where('type', 'stock_movement_report')->where('user_id', $user_id)->select('hide_columns')->first();
            if($visible_columns!=null)
            {
                $column_visiblity = explode(',',$visible_columns->hide_columns);
            }
            // $records=StockMovementReportRecord::get();
            $product_detail_section = ProductConfigurationHelper::getProductConfigurations();
            $return=\Excel::store(new stockMovementReportExport($products,$global_terminologies,$column_visiblity,$role_id, $from_date, $product_detail_section, $warehouse_id),'Stock-Movement-Report.xlsx');
            if($return)
            {
                ExportStatus::where('type','stock_movement_report')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
                return response()->json(['msg'=>'File Saved']);
            }
        }
        catch (Exception $e)
        {
            $this->failed($e);
        }
        catch(QueueMaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }
    public function failed( $exception)
    {
        ExportStatus::where('type','stock_movement_report')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Stock Movement Report";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
    }
}
