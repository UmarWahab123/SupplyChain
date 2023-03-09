<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Warehouse;
use App\MarginReportRecords;
use App\Exports\MarginReportByOfficeExport;
use App\Variable;
use DB;
use App\ExportStatus;
use App\Models\Common\Product;
use App\Models\Common\StockManagementOut;
use App\Models\Common\StockOutHistory;
use App\FailedJobException;
use Exception;
use Illuminate\Queue\MaxAttemptsExceededException;

class MarginReportByProductNameJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1500;
    public $tries = 2;
    protected $request;
    protected $user_id;
    protected $dataArray=[];
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request,$user_id)
    {
        $this->request=$request;
        $this->user_id=$user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try{
            $request = $this->request;
            $user_id = $this->user_id;
            // $from_date           = $request->from_date;
            // $to_date             = $request->to_date;
            // $customer_id         = $request->customer_id;
            // $sales_person        = $request->sales_person;
            // $customer_orders_ids = NULL;
            $not_visible_arr=[];
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','margin_report_by_product_name')->where('user_id',$user_id)->first();

            if($not_visible_columns!=null)
            {
                $not_visible_arr = explode(',',$not_visible_columns->hide_columns);
            }
            $vairables=Variable::select('slug','standard_name','terminology')->get();
            $global_terminologies=[];
            foreach($vairables as $variable)
            {
              if($variable->terminology != null)
              {
                $global_terminologies[$variable->slug]=$variable->terminology;
              }
              else
              {
                $global_terminologies[$variable->slug]=$variable->standard_name;
              }
            }
            if($request['supplier_id'] != null && $request['supplier_id'] != "")
            {
                $sup_stock_out = StockManagementOut::whereNotNull('supplier_id')->where('supplier_id',$request['supplier_id'])->pluck('id')->toArray();
                $sup_order = StockOutHistory::whereIn('stock_out_from_id',$sup_stock_out)->pluck('stock_out_id')->toArray();

                $final_order_ids = StockManagementOut::whereIn('id',$sup_order)->whereNotNull('order_id')->pluck('order_id')->toArray();
            }
            else
            {
                $final_order_ids = null;
            }

            $products = Product::select(DB::raw('SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
            END) AS products_total_cost,
            CASE
            WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
            CASE
            WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
            CASE
            WHEN o.primary_status="3" THEN SUM(op.quantity) END AS totalQuantity,
            CASE
            WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
            WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
            WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount, SUM(CASE
            WHEN o.primary_status="3" THEN op.qty_shipped
            END) AS qty'),'products.refrence_code','products.short_desc','op.product_id','products.brand','o.dont_show','products.id','o.customer_id','products.supplier_id')->groupBy('op.product_id');
            $products->join('order_products AS op','op.product_id','=','products.id');
            $products->join('orders AS o','o.id','=','op.order_id');
            // $products->leftJoin('stock_out_histories AS soh','soh.order_id','=','o.id');
            //   $products->join('stock_management_outs AS smo','products.id','=','smo.product_id');
            $products = $products->where('o.primary_status',3);

            if($request['from_date_exp'] != null)
            {
                $from_date = str_replace("/","-",$request['from_date_exp']);
                $from_date =  date('Y-m-d',strtotime($from_date));
                $products = $products->where('o.converted_to_invoice_on','>=',$from_date.' 00:00:00');
            }

            if($request['to_date_exp'] != null)
            {
                $to_date = str_replace("/","-",$request['to_date_exp']);
                $to_date =  date('Y-m-d',strtotime($to_date));
                $products = $products->where('o.converted_to_invoice_on','<=',$to_date.' 23:59:59');
            }
            if($request['sale_id'] != null)
            {
                $products = $products->where('o.user_id',$request['sale_id']);
            }
            else
            {
                $products = $products->where('o.dont_show',0);
            }
            if($final_order_ids != null)
            {
                $products = $products->whereIn('o.id',$final_order_ids);
            }

            if($request['category_id'] != null)
            {
                $products = $products->where('products.primary_category',$request['category_id']);
            }

            if($request['customer_selected'] != null && $request['customer_selected'] !== '')
            {
                $products->where('o.customer_id',$request['customer_selected']);
            }

            $to_get_totals = (clone $products)->get();
            $total_items_sales = $to_get_totals->sum('sales');
            $total_items_cogs  = $to_get_totals->sum('products_total_cost');

            //to find cogs of manual adjustments
            $stock = (new StockManagementOut)->get_manual_adjustments_for_export($request);
            $total = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
            $total_items_gp    = $total_items_sales - $total_items_cogs - abs($total);

            // $this->productsArr=[];
            // MarginReportRecords::truncate();
            // $products->chunk(1500, function ($rows) use ($total_items_sales, $total_items_gp)
            // {
            // foreach ($rows as $item) {
            //     $total_vat_in  = $item->vat_in != null ? round($item->vat_in,2) : '';
            //     $brand = @$item->brand != null ? @$item->brand : '';
            //     $adjustment_out = $item->manual_adjustment != null ? $item->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
            //     $total_pos_count = $item->purchaseOrderDetailVatIn->sum('quantity');

            //     $total_pos_vat = $item->purchaseOrderDetailVatIn->sum('pod_vat_actual_total_price');
            //     if($total_pos_count > 0)
            //     {
            //         $total_pos_vat = $total_pos_vat / $total_pos_count;
            //     }

            //     if($total_pos_count > $item->totalQuantity)
            //     {
            //         $quantity_to_multiply = $item->totalQuantity;
            //     }
            //     else
            //     {
            //         $quantity_to_multiply = $total_pos_count;
            //     }
            //     $sales = $item->sales;
            //     $cogs = $item->products_total_cost;
            //     if($sales != 0)
            //     {
            //       $total = ($sales - $cogs - abs($adjustment_out)) / $sales;
            //       // $total = $item->marg;
            //     }
            //     else
            //     {
            //       $total = 0;
            //     }
            //     if($total == 0)
            //     {
            //       $formated = "-100.00";
            //     }
            //     else
            //     {
            //       $formated = $total * 100;
            //     }

            //     $gp = $item->sales - $item->products_total_cost;
            //     $this->dataArray[] = [
            //         'product_id' => $item->id,
            //         'office' => $item->refrence_code.' - '.$item->short_desc.' - '.$brand,
            //         'vat_out' => $item->vat_amount_total != null ? $item->vat_amount_total : '--',
            //         'sales' => $item->sales,
            //         'percent_sales' => ($total_items_sales != 0) ? $item->sales / $total_items_sales * 100 : 0,
            //         'vat_in' => $total_vat_in,
            //         'cogs' => $item->products_total_cost + abs($adjustment_out),
            //         'gp' => $gp - abs($adjustment_out),
            //         'percent_gp' => $total_items_gp !==0 ? $gp/$total_items_gp *100 : 0,
            //         'margins' => $formated
            //     ];
            // }
            // foreach (array_chunk($this->dataArray,1500) as $t)
            // {
            //     MarginReportRecords::insert($t);
            // }
            // $this->dataArray=[];
            // });
            // // $records=MarginReportRecords::all();
            // $records=MarginReportRecords::query();
            $products = Product::MarginReportByProductNameSorting($request, $products, $total_items_sales, $total_items_gp);
            $products->with('def_or_last_supplier');
            $return=\Excel::store(new MarginReportByOfficeExport($products,$not_visible_arr, $global_terminologies, $request), 'Margin-Report-By-Product-Name.xlsx');

            if($return)
            {
                ExportStatus::where('type','margin_report_by_product_name')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
                return response()->json(['msg'=>'File Saved']);
            }
        }
        catch(Exception $e) {
            $this->failed($e);
        }
        catch(MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }

    public function failed($exception)
    {
      ExportStatus::where('type','margin_report_by_product_name')->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="margin_report_by_product_name";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}
