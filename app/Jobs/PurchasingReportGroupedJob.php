<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\Product;
use App\Models\Common\WarehouseProduct;
use App\Exports\purchasingReportGroupExport;
use App\ExportStatus;
use App\FailedJobException;
use App\User;
use App\Variable;
use Carbon\Carbon;
use Exception;
use Illuminate\Queue\MaxAttemptsExceededException as QueueMaxAttemptsExceededException;
use MaxAttemptsExceededException;

class PurchasingReportGroupedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;
    protected $user_id;
    public $tries=1;
    public $timeout=500;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$user_id)
    {
        $this->request = $data;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = $this->request;
        $user_id = $this->user_id;

        try{
            $vairables=Variable::select('slug','standard_name','terminology')->get();
            $global_terminologies=[];
            foreach($vairables as $variable)
            {
                if($variable->terminology != null)
                {
                    $global_terminologies[$variable->slug] = $variable->terminology;
                }
                else
                {
                    $global_terminologies[$variable->slug] = $variable->standard_name;
                }
            }

            $query = PurchaseOrderDetail::select(\DB::raw('SUM(purchase_order_details.quantity) AS TotalQuantity,
            SUM(purchase_order_details.pod_total_unit_price) AS GrandTotalUnitPrice'),'purchase_order_details.*')->whereIn('po.status', [13,14,15])->whereNotNull('po.supplier_id')->where('purchase_order_details.is_billed','=', 'Product')->groupBy('purchase_order_details.product_id');
            $query->join('purchase_orders AS po','po.id','=','purchase_order_details.po_id');

            if($request['product_category_exp'] != null)
            {
                $id_split = explode('-', $request['product_category_exp']);
                // $id_split = (int)$id_split[1];
                if ($id_split[0] == 'pri') {
                    $product_ids = Product::where('primary_category', $id_split)->where('status',1)->pluck('id');
                    $query->whereIn('product_id',$product_ids);
                }
                else {
                    $product_ids = Product::where('category_id', $id_split)->where('status',1)->pluck('id');
                    $query->whereIn('product_id',$product_ids);
                }
                // else{
                //     $query->where('purchase_order_details.product_id', $id_split);
                // }
            }
            // if($request['product_category_exp'] != null)
            // {
            //     $product_ids = Product::where('category_id', $request['product_category_exp'])->where('status',1)->pluck('id');
            //     $query->whereIn('product_id',$product_ids);
            // }

            if($request['product_id_filter_exp'] != null)
            {
                $query->where('purchase_order_details.product_id', $request['product_id_filter_exp']);
            }

            if($request['filter_dropdown_exp'] != null)
            {
                if($request['filter_dropdown_exp'] == 'stock')
                {
                    $query = $query->whereIn('product_id',WarehouseProduct::select('product_id')->where('current_quantity','>',0.005)->pluck('product_id'));
                }
                elseif($request['filter_dropdown_exp'] == 'reorder')
                {
                    $product_ids = Product::where('min_stock','>',0)->where('status',1)->pluck('id');
                    $query->whereIn('product_id',$product_ids);
                }
            }

            if($request['from_date_exp'] != null)
            {
                $from_date = str_replace("/","-",$request['from_date_exp']);
                $from_date =  date('Y-m-d',strtotime($from_date));
                $query->where('po.confirm_date', '>=', $from_date);
            }

            if($request['to_date_exp'] != null)
            {
                $to_date = str_replace("/","-",$request['to_date_exp']);
                $to_date =  date('Y-m-d',strtotime($to_date));
                $query->where('po.confirm_date', '<=', $to_date);
            }
            
            if($request['supplier_filter_exp'] != null)
            {
                $id = $request['supplier_filter_exp'];
                $query->whereHas('PurchaseOrder', function($q) use($id){
                    $q->where('purchase_orders.supplier_id',$id);
                });
            }

            $current_date = date("Y-m-d");
            $query = PurchaseOrderDetail::PurchasingReportGroupedSorting($request, $query);
            // $query = $query->get();
            // $data = [];

            // foreach ($query as $item) {
                
            //     if($item->product_id != null)
            //     {
            //         $pf_no = $item->product->refrence_code;
            //     }
            //     else
            //     {
            //         $pf_no = 'N.A';
            //     }

            //     if($item->product_id !== null)
            //     {
            //         $desc = $item->product->short_desc;
            //     }
            //     else
            //     {
            //         $desc = 'N.A';
            //     }

            //     if($item->product_id !== null)
            //     {
            //         $billing_unit = $item->product->units->title;
            //     }
            //     else
            //     {
            //         $billing_unit = 'N.A';
            //     }

            //     if($item->product_id !== null)
            //     {
            //         $unit_mes_code = $item->product->sellingUnits->title;
            //     }
            //     else
            //     {
            //         $unit_mes_code = 'N.A';
            //     }

            //     if($item->TotalQuantity !== null)
            //     {
            //         $sum_qty = $item->TotalQuantity;
            //     }
            //     else
            //     {
            //         $sum_qty = 'N.A';
            //     }

            //     if($item->pod_unit_price !== null)
            //     {
            //         $cost_unit = number_format($item->pod_unit_price,3,'.','');
            //     }
            //     else
            //     {
            //         $cost_unit = 'N.A';
            //     }
                
            //     if($item->GrandTotalUnitPrice !== null)
            //     {
            //         $pod_total_unit = number_format($item->GrandTotalUnitPrice,3,'.','');
            //     }
            //     else
            //     {
            //         $pod_total_unit = 'N.A';
            //     }


            //     $data[] = [
            //         'pf_no'            => $pf_no, 
            //         'description'      => $desc, 
            //         'billing_unit'     => $billing_unit, 
            //         'selling_unit'     => $unit_mes_code, 
            //         'sum_of_qty'       => $sum_qty,
            //         'unit_eur'         => $cost_unit,
            //         'total_amount_eur' => $pod_total_unit,
            //     ];
            // }

            $return = \Excel::store(new purchasingReportGroupExport($query,$global_terminologies),'purchasing-report.xlsx');
            if($return)
            {
                ExportStatus::where('type','purchasing_report_detail_grouped')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
                return response()->json(['msg'=>'File Saved']);
            }

        }
        catch(Exception $e) {
            $this->failed($e);
        }
        catch(QueueMaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }

    public function failed( $exception)
    {
        ExportStatus::where('type','purchasing_report_detail_grouped')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException            = new FailedJobException();
        $failedJobException->type      = "purchasing_report_detail_grouped";
        $failedJobException->exception = $exception->getMessage();
        $failedJobException->save();
    }
}
