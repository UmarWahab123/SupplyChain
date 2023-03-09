<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\TableHideColumn;
use App\Models\Common\StockManagementOut;
use App\MarginReportRecords;
use App\Exports\MarginReportByOfficeExport;
use App\Variable;
use DB;
use App\ExportStatus;
use App\FailedJobException;
use Exception;
use Illuminate\Queue\MaxAttemptsExceededException;

class MarginReportBySupplierJob implements ShouldQueue
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
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','margin_report_by_supplier')->where('user_id',$user_id)->first();

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

            $products = StockManagementOut::whereNotNull('stock_management_outs.supplier_id')->with('supplier')
            ->leftJoin('purchase_order_details','stock_management_outs.p_o_d_id','=','purchase_order_details.id')
            ->leftJoin('stock_out_histories','stock_management_outs.id','=','stock_out_histories.stock_out_from_id')
            ->groupBy('stock_management_outs.supplier_id')->selectRaw('stock_management_outs.*, sum(cost*(quantity_in - available_stock)) as total_cogs, sum(stock_management_outs.quantity_in) as total_quantity, sum(stock_out_histories.sales) as sales, sum(stock_out_histories.vat_in) as import_vat_amount,sum(stock_out_histories.total_cost) as products_total_cost, sum(stock_out_histories.vat_out) as vat_amount_total, (sum(stock_out_histories.sales) - sum(stock_out_histories.total_cost)) / sum(stock_out_histories.sales) as marg');

            if($request['from_date_exp'] != null)
            {
                $from_date = str_replace("/","-",$request['from_date_exp']);
                $from_date =  date('Y-m-d',strtotime($from_date));
                $products = $products->where('stock_management_outs.created_at','>=',$from_date.' 00:00:00');
            }

            if($request['to_date_exp'] != null)
            {
                $to_date = str_replace("/","-",$request['to_date_exp']);
                $to_date =  date('Y-m-d',strtotime($to_date));
                $products = $products->where('stock_management_outs.created_at','<=',$to_date.' 23:59:59');
            }

            $query = (clone $products)->get();
            $total_items_sales = $query->sum('sales');
            $total_items_cogs  = $query->sum('products_total_cost');
            $total_items_gp    = $total_items_sales - $total_items_cogs;

            $current_date = date("Y-m-d");
            // $this->productsArr=[];
            // MarginReportRecords::truncate();
            // $products->chunk(1500, function ($rows) use ($total_items_sales, $total_items_gp)
            // {
            // foreach ($rows as $row) {
            //     $sales = $row->sales;
            //     $cogs  = $row->total_cost;
            //     $total = $sales - $cogs;
            //     $formated = $total;
            //     if($total_items_gp !== 0)
            //     {
            //       $formated = ($formated/$total_items_gp)*100;
            //     }
            //     // $adjustment_out = $item->manual_adjustment != null ? $item->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
            //     $adjustment_out = 0;
            //     $margin = 0;
            //     if($sales != 0)
            //     {
            //       $margin = ($sales - $cogs - abs($adjustment_out)) / $sales;
            //       // $total = $item->marg;
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
            //       $margin = number_format($margin * 100,2);
            //     }

            //     $this->dataArray[] = [
            //         'product_id' => 0,
            //         'office' => $row->supplier != null ? $row->supplier->reference_name : '--',
            //         'vat_out' => $row->vat_out,
            //         'sales' => $row->sales,
            //         'percent_sales' => ($total_items_sales != 0) ? $sales / $total_items_sales * 100 : 0,
            //         'vat_in' => $row->vat_in,
            //         'cogs' => $row->total_cost,
            //         'gp' => $sales - $cogs,
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
            // $records=MarginReportRecords::all();
            // $records=MarginReportRecords::query();
            $products = StockManagementOut::doSortby($request, $products, $total_items_sales, $total_items_gp);
            $return=\Excel::store(new MarginReportByOfficeExport($products, $not_visible_arr, $global_terminologies, $request), 'Margin-Report-By-Supplier.xlsx');

            if($return)
            {
                ExportStatus::where('type','margin_report_by_supplier')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
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
      ExportStatus::where('type','margin_report_by_supplier')->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="margin_report_by_supplier";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}
