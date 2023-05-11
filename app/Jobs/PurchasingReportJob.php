<?php

namespace App\Jobs;

use App\User;
use Exception;
use App\Variable;
use Carbon\Carbon;
use App\ExportStatus;
use App\FailedJobException;
use Illuminate\Bus\Queueable;
use App\Models\Common\Product;
use MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use App\Exports\purchasingReportExport;
use App\Models\Common\WarehouseProduct;
use Illuminate\Queue\InteractsWithQueue;
use App\Helpers\ProductConfigurationHelper;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use Illuminate\Queue\MaxAttemptsExceededException as QueueMaxAttemptsExceededException;

class PurchasingReportJob implements ShouldQueue
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
        $this->request=$data;
        $this->user_id=$user_id;
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
        try{
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

            if($request['check_the_hit_exp'] != null && $request['check_the_hit_exp'] != '')
            {
                $statuses = [14];
            }
            else
            {
                $statuses = [13,14,15];
            }
            if($request['status_dropdown_exp'] == 'all')
            {
                array_push($statuses, 40);
            }
            $query = PurchaseOrderDetail::select('purchase_order_details.*');
            $query->with('PurchaseOrder:id,ref_id,confirm_date,supplier_id,invoice_number,invoice_date,po_group_id','PurchaseOrder.PoSupplier:id,reference_name,country',
                'product:id,refrence_code,short_desc,selling_unit,buying_unit,total_buy_unit_cost_price,vat,type_id,type_id_2,type_id_3,min_stock,unit_conversion_rate,primary_category,weight',
                'product.sellingUnits:id,title',
                'product.supplier_products:id,landing,freight,product_id,import_tax_actual',
                'product.units:id,title',
                'PurchaseOrder.po_group.po_group_product_details',
                'product.productType',
                'product.productType2',
                'product.productType3',
                'PurchaseOrder.PoSupplier.getcountry:id,name',
                'product.productCategory:id,title')
                ->where('purchase_order_details.is_billed','=', 'Product');

            if($request['status_dropdown_exp'] == 40)
            {
                $query->whereHas('PurchaseOrder',function($q) {
                    $q->where('purchase_orders.status', 40);
                });
            }
            else
            {
                $query->whereHas('PurchaseOrder', function($q) use($statuses) {
                    $q->whereIn('purchase_orders.status', $statuses);
                    $q->whereNotNull('purchase_orders.supplier_id');
                });
            }
            if($request['product_category_exp'] != null)
            {
                $product_ids = Product::where('category_id', $request['product_category_exp'])->where('status',1)->pluck('id');
                $query->whereIn('product_id',$product_ids);
            }

            if($request['product_id_filter_exp'] != null)
            {
                $query->where('purchase_order_details.product_id', $request['product_id_filter_exp']);
            }

            if($request['product_type_exp'] != null)
            {
                $query->whereHas('product',function($p) use ($request){
                    $p->where('type_id',$request['product_type_exp']);
                });
            }
            if($request['product_type_2_exp'] != null)
            {
                $query->whereHas('product',function($p) use ($request){
                    $p->where('type_id_2',$request['product_type_2_exp']);
                });
            }
            if($request['product_type_3_exp'] != null)
            {
                $query->whereHas('product',function($p) use ($request){
                    $p->where('type_id_3',$request['product_type_3_exp']);
                });
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
            if($request['supplier_filter_exp'] != null)
            {
                $id = $request['supplier_filter_exp'];
                $query->whereHas('PurchaseOrder', function($q) use($id){
                    $q->where('purchase_orders.supplier_id',$id);
                });
            }

            if($request['from_date_exp'] != null)
            {
                $date = str_replace("/","-",$request['from_date_exp']);
                $date =  date('Y-m-d',strtotime($date));
                $query->whereHas('PurchaseOrder', function($q) use($date){
                    $q->where('purchase_orders.confirm_date', '>=', $date);
                });
            }
            if($request['to_date_exp'] != null)
            {
                $date = str_replace("/","-",$request['to_date_exp']);
                $date =  date('Y-m-d',strtotime($date));
                $query->whereHas('PurchaseOrder', function($q) use($date){
                    $q->where('purchase_orders.confirm_date', '<=', $date);
                });
            }

            // $query->select(\DB::raw('purchase_order_details.*'))
            //     ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            //     ->orderBy('purchase_orders.confirm_date', 'desc');

            $query = PurchaseOrderDetail::doSortby($request, $query);

            /***********/
            $current_date = date("Y-m-d");
            // $query = $query->get();
            // $data = [];
            // foreach ($query as $item) {
            //     if($item->PurchaseOrder->confirm_date !== null)
            //     {
            //         $confirm_date = Carbon::parse($item->PurchaseOrder->confirm_date)->format('d/m/Y');
            //     }
            //     else
            //     {
            //         $confirm_date = 'N.A';
            //     }
            //     $supplier = $item->PurchaseOrder->PoSupplier->reference_name;
            //     if($item->po_id !== null)
            //     {
            //         $po_no = $item->PurchaseOrder->ref_id;
            //     }
            //     else
            //     {
            //         $po_no = 'N.A';
            //     }
            //     if($item->product_id != null)
            //     {
            //         $pf_no = $item->product->refrence_code;
            //         $desc = $item->product->short_desc;
            //         $product_type = @$item->product->productType != null ? @$item->product->productType->title : 'N.A';
            //         $product_type_2 = @$item->product->productType2 != null ? @$item->product->productType2->title : 'N.A';
            //         $billing_unit = $item->product->units->title;
            //         $unit_mes_code = $item->product->sellingUnits->title;
            //         if($item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->freight !== null)
            //         {
            //             $fright = number_format((float) $item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->freight, 3, '.', ',');
            //         }
            //         else
            //         {
            //             $fright = '--';
            //         }

            //         if($item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->landing !== null)
            //         {
            //             $landing = number_format((float) $item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->landing, 3, '.', ',');
            //         }
            //         else
            //         {
            //             $landing = '--';
            //         }
            //         if($item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->import_tax_actual !== null)
            //         {
            //             $tax_actual = number_format((float) $item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->import_tax_actual, 3, '.', ',');
            //         }
            //         else
            //         {
            //             $tax_actual = '--';
            //         }
            //         if($item->product->total_buy_unit_cost_price !== null)
            //         {
            //             $cost_price = number_format((float) $item->product->total_buy_unit_cost_price, 3, '.', '');
            //         }
            //         else
            //         {
            //             $cost_price = '--';
            //         }
            //     }
            //     else
            //     {
            //         $pf_no = 'N.A';
            //         $desc = 'N.A';
            //         $billing_unit = 'N.A';
            //         $unit_mes_code = 'N.A';
            //         $fright = 'N.A';
            //         $landing = 'N.A';
            //         $tax_actual = 'N.A';
            //         $cost_price = 'N.A';
            //     }

            //     if($item->quantity !== null)
            //     {
            //         $sum_qty = $item->quantity;
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
            //     if($item->pod_total_unit_price !== null)
            //     {
            //         $pod_total_unit = number_format($item->pod_total_unit_price,3,'.','');
            //     }
            //     else
            //     {
            //         $pod_total_unit = 'N.A';
            //     }
            //     $cost_unit_thb = $item->unit_price_in_thb + ($item->pod_freight + $item->pod_landing + $item->pod_total_extra_cost);
            //     $sum_cost_amt = ($item->unit_price_in_thb * $item->quantity);
            //     $cst_unt_thb = number_format($cost_unit_thb,3,'.','');
            //     $sm_cst_amt = number_format($sum_cost_amt,3,'.','');
            //     if($item->product->vat !== null)
            //     {
            //         $vat = $item->product->vat.' %';
            //     }
            //     else
            //     {
            //         $vat = 'N.A';
            //     }

            //     $data[] = [
            //         'confirm_date'=> $confirm_date,

            //         'supplier'=> $supplier,

            //         'po_no'=> $po_no,

            //         'pf_no'=> $pf_no,

            //         'description'=> $desc,
            //         'product_type'=> $product_type,
            //         'product_type_2'=> $product_type_2,

            //         'billing_unit'=> $billing_unit,

            //         'selling_unit'=> $unit_mes_code,

            //         'sum_of_qty'=> $sum_qty,

            //         'freight'=> $fright ,

            //         'landing'=> $landing,

            //         'tax_allocation'=>$tax_actual,
            //         'total_unit_cost'=> $cost_price,

            //         'unit_eur'=> $cost_unit,
            //         'total_amount_eur' => $pod_total_unit,

            //         'unit_cost'=> $cst_unt_thb,

            //         'total_amount'=>$sm_cst_amt,

            //         'vat'=>$vat,
            //     ];
            // }

            $product_detail_section = ProductConfigurationHelper::getProductConfigurations();
            $return=\Excel::store(new purchasingReportExport($query,$global_terminologies,$product_detail_section),'purchasing-report.xlsx');
            if($return)
            {
                ExportStatus::where('type','purchasing_report_detail')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
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
        ExportStatus::where('type','purchasing_report_detail')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Purchasing Report Export";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
    }
}
