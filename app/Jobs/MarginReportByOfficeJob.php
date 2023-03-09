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
use App\FailedJobException;
use App\Models\Common\StockManagementOut;
use Exception;
use Illuminate\Queue\MaxAttemptsExceededException;

class MarginReportByOfficeJob implements ShouldQueue
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
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','margin_report_by_office')->where('user_id',$user_id)->first();

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

            $products = Warehouse::select(DB::raw('SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
            END) AS products_total_cost,
            CASE
            WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,CASE
            WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
            WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
            WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'),'warehouses.warehouse_title','warehouses.id as wid','op.user_warehouse_id','op.product_id','o.customer_id','o.dont_show','warehouses.id')->groupBy('warehouses.id');
            $products->join('order_products AS op','op.user_warehouse_id','=','warehouses.id');
            $products->join('orders AS o','o.id','=','op.order_id');
            $products->where('o.dont_show', 0);
            $products = $products->where('o.primary_status',3);
            $products = $products->whereNotNull('op.product_id');

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

            // $stock = (new StockManagementOut)->get_manual_adjustments_for_export($request);

            $to_get_totals = (clone $products)->get();
            $total_vat_out = $to_get_totals->sum('vat_amount_total');
            $total_items_sales = $to_get_totals->sum('sales');
            $total_sale_percent = 0;
            $total_vat_in = $to_get_totals->sum('import_vat_amount');
            $total_items_cogs  = $to_get_totals->sum('products_total_cost');
            $total_man = 0;
            $total_items_gp    = $total_items_sales - $total_items_cogs - abs($total_man);

            $products = Warehouse::doSortby($request, $products, $total_items_sales, $total_items_gp);
            $current_date = date("Y-m-d");
            // $this->productsArr=[];
            // MarginReportRecords::truncate();
            // $products->chunk(1500, function ($rows) use ($total_items_sales, $total_items_gp)
            // {
            // foreach ($rows as $row) {
            //     $adjustment_out = $row->manual_adjustment != null ? $row->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
            //     $sales = $row->sales;
            //     $cogs  = $row->products_total_cost;
            //     $total = $sales - $cogs - abs($adjustment_out);
            //     $formated = $total;
            //     if($total_items_gp !== 0)
            //     {
            //       $formated = ($formated/$total_items_gp)*100;
            //     }

            //     if($sales != 0)
            //     {
            //       $margin = $total/$sales;
            //     }
            //     else
            //     {
            //       $margin = 0;
            //     }
            //     if($margin == 0)
            //     {
            //       $margin = "-100.00";
            //     }
            //     else
            //     {
            //       $margin = $margin * 100;
            //     }
            //     $this->dataArray[] = [
            //         'product_id' => $row->wid,
            //         'office' => $row->warehouse_title,
            //         'vat_out' => $row->vat_amount_total != null ? $row->vat_amount_total : '--',
            //         'sales' => $row->sales,
            //         'percent_sales' => ($total_items_sales != 0) ? $sales / $total_items_sales * 100 : 0,
            //         'vat_in' => $row->import_vat_amount != null ? $row->import_vat_amount : '--',
            //         'cogs' => $cogs + abs($adjustment_out),
            //         'gp' => $total,
            //         'percent_gp' => $formated,
            //         'margins' => $margin
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
            $return=\Excel::store(new MarginReportByOfficeExport($products,$not_visible_arr, $global_terminologies, $request), 'Margin-Report-By-Office.xlsx');

            if($return)
            {
                ExportStatus::where('type','margin_report_by_office')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
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
      ExportStatus::where('type','margin_report_by_office')->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="margin_report_by_office";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}
