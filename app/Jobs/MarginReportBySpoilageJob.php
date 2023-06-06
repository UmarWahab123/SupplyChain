<?php

namespace App\Jobs;
use Carbon\Carbon;
use App\Models\Common\StockManagementOut;
use App\Exports\MarginReportBySpoilageExport;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\MaxAttemptsExceededException;
use Exception;
use DB;
use App\ExportStatus;
use App\FailedJobException;

class MarginReportBySpoilageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request;
    protected $user_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $user_id)
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
            $from_date = $request['from_date_exp'];
            $to_date = $request['to_date_exp'];
            if (!empty($from_date) && !empty($to_date)) {
            try {
                $from_date = Carbon::createFromFormat('d/m/Y', $from_date)->format('Y-m-d');
                $to_date = Carbon::createFromFormat('d/m/Y', $to_date)->format('Y-m-d');
                } catch (InvalidArgumentException $e) {
                    // Handle the exception, log the error, or display a meaningful message
                    return response()->json(['error' => 'Invalid date format'], 400);
                }
                $spoilageStockQuery = DB::table('stock_management_outs')
                ->join('customers', 'stock_management_outs.customer_id', '=', 'customers.id')
                ->join('suppliers', 'stock_management_outs.supplier_id', '=', 'suppliers.id')
                ->join('products', 'stock_management_outs.product_id', '=', 'products.id')
                ->select(
                    'products.refrence_code as reference_code',
                    'suppliers.reference_name as default_supplier',
                    'customers.reference_name as customer',
                    DB::raw('SUM(ABS(stock_management_outs.quantity_out)) as quantity'),
                    DB::raw('SUM(ABS(stock_management_outs.quantity_out) * stock_management_outs.cost) as cogs_total'),
                    DB::raw('MIN(stock_management_outs.cost) as unit_cogs')
                )
                ->where('customers.manual_customer', 2)
                ->whereDate('stock_management_outs.created_at', '>=', $from_date)
                ->whereDate('stock_management_outs.created_at', '<=', $to_date)
                ->groupBy('suppliers.reference_name', 'products.refrence_code')
                ->orderBy('reference_code');
              } else {
                $spoilageStockQuery = DB::table('stock_management_outs')
                ->join('customers', 'stock_management_outs.customer_id', '=', 'customers.id')
                ->join('suppliers', 'stock_management_outs.supplier_id', '=', 'suppliers.id')
                ->join('products', 'stock_management_outs.product_id', '=', 'products.id')
                ->select(
                    'products.refrence_code as reference_code',
                    'suppliers.reference_name as default_supplier',
                    'customers.reference_name as customer',
                    DB::raw('SUM(ABS(stock_management_outs.quantity_out)) as quantity'),
                    DB::raw('SUM(ABS(stock_management_outs.quantity_out) * stock_management_outs.cost) as cogs_total'),
                    DB::raw('MIN(stock_management_outs.cost) as unit_cogs')
                )
                ->where('customers.manual_customer', 2)
                ->groupBy('suppliers.reference_name', 'products.refrence_code')
                ->orderBy('reference_code');
            }
            $return=\Excel::store(new MarginReportBySpoilageExport($spoilageStockQuery, $request), 'Margin-Report-By-Spoilage.xlsx');
            if($return)
            {
                ExportStatus::where('type','margin_report_by_spoilage')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
                return response()->json(['msg'=>'File Saved']);
            }
        }catch(Exception $e) {
            $this->failed($e);
        }catch(MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }
    public function failed($exception)
    {
      ExportStatus::where('type','margin_report_by_spoilage')->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="margin_report_by_spoilage";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}
