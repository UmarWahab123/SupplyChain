<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\TableHideColumn;
use App\Models\Common\ProductType;
use App\MarginReportRecords;
use App\Exports\MarginReportByOfficeExport;
use App\Variable;
use DB;
use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\StockManagementOut;
use Exception;
use Illuminate\Queue\MaxAttemptsExceededException;

class MarginReportByProductTypeJob implements ShouldQueue
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
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','margin_report_by_product_type')->where('user_id',$user_id)->first();

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

            $products = ProductType::select(DB::raw('SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped)
            END) AS products_total_cost,
            CASE
            WHEN o.primary_status="3" THEN SUM(op.total_price) END AS sales,
            CASE
            WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END AS vat_in,
            CASE
            WHEN o.primary_status="3" THEN ((SUM(op.total_price) - SUM(op.actual_cost * op.qty_shipped)) / SUM(op.total_price)) END AS marg,CASE
            WHEN o.primary_status="3" THEN SUM(op.vat_amount_total) END AS vat_amount_total,CASE
            WHEN o.primary_status="3" THEN SUM(op.import_vat_amount) END AS import_vat_amount'),'types.title','products.short_desc','products.brand','op.product_id','types.id AS category_id')->groupBy('products.type_id');
            $products->join('products','products.type_id','=','types.id');
            $products->join('order_products AS op','op.product_id','=','products.id');
            $products->join('orders AS o','o.id','=','op.order_id');
            $products = $products->where('o.primary_status',3);
            $products = $products->where('o.dont_show',0);

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

            if($request['product_id'] != null)
            {
                $products = $products->where('products.id',$request['product_id']);
            }

            $stock = (new StockManagementOut)->get_manual_adjustments_for_export($request);
            $total_man_ov = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
            $to_get_totals = (clone $products)->get();
            $total_items_sales = $to_get_totals->sum('sales');
            $total_items_cogs  = $to_get_totals->sum('products_total_cost');
            $total_items_gp    = $total_items_sales - $total_items_cogs - abs($total_man_ov);

            $current_date = date("Y-m-d");
            // $this->productsArr=[];
            // MarginReportRecords::truncate();
            // $products->chunk(1500, function ($rows) use ($total_items_sales, $total_items_gp, $request)
            // {
            // foreach ($rows as $row) {
            //     $stock = (new ProductType)->get_manual_adjustments_for_export($request,$row->category_id);
            //     $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
            //     $sales = $row->sales;
            //     $cogs  = $row->products_total_cost;
            //     $total = $sales - $cogs - abs($total_man);
            //     $formated = $total;
            //     if($total_items_gp !== 0)
            //     {
            //       $formated = ($formated/$total_items_gp)*100;
            //     }

            //     if($sales != 0)
            //     {
            //         $margin = $total/$sales;
            //       // $margin = $row->marg;
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
            //         'product_id' => $row->category_id,
            //         'office' => $row->title,
            //         'vat_out' => $row->vat_amount_total != null ? $row->vat_amount_total : '--',
            //         'sales' => $row->sales,
            //         'percent_sales' => ($total_items_sales != 0) ? $sales / $total_items_sales * 100 : 0,
            //         'vat_in' => $row->vat_in != null ? round($row->vat_in,2) : '--',
            //         'cogs' => $row->products_total_cost + abs($total_man),
            //         'gp' => $sales - $cogs - abs($total_man),
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
            $products = ProductType::doSortby($request, $products, $total_items_sales, $total_items_gp);
            $return=\Excel::store(new MarginReportByOfficeExport($products,$not_visible_arr, $global_terminologies, $request), 'Margin-Report-By-Product-Type.xlsx');

            if($return)
            {
                ExportStatus::where('type','margin_report_by_product_type')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
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
      ExportStatus::where('type','margin_report_by_product_type')->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="margin_report_by_product_type";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}
