<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\ExportStatus;
use App\FailedJobException;
use App\User;
use App\Variable;
use Carbon\Carbon;
use Exception;
use Illuminate\Queue\MaxAttemptsExceededException as QueueMaxAttemptsExceededException;
use MaxAttemptsExceededException;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\General;
use App\Exports\receivedIntoStockExport;

class ReceivedIntoStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $request;
    protected $user_id;
    public $tries=1;
    public $timeout=500;
    protected $targetShipDateConfig;
    protected $blade;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$user_id)
    {
        $this->request=$data;
        $this->user_id=$user_id;
        $general = new General();
        $targetShipDate = $general->getTargetShipDateConfig();
        $this->targetShipDateConfig = $targetShipDate;
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
        $targetShipDateConfig =  $this->targetShipDateConfig;
        $this->blade = $request['blade'];
        try {

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

            $currentMonth = date('m');
            $currentYear = date('Y');
            $query = PurchaseOrder::with('PurchaseOrderDetail','createdBy','PoSupplier','po_notes');

            $query->where(function($q) use ($request) {
                if ($request['blade'] == 'received_into_stock')
                {
                 $q->where('purchase_orders.status', 15);
                }
                else if ($request['blade'] == 'dispatch_from_supplier')
                {
                     $q->where('purchase_orders.status', 14);
                }
                else if ($request['blade'] == 'shipping')
                {
                     $q->where('purchase_orders.status', 13);
                }
                else if ($request['blade'] == 'waiting_confirmation')
                {
                     $q->where('purchase_orders.status', 12);
                }
            });

            if ($request['date_radio'] == '1') {
                $date_column = 'purchase_orders.target_receive_date';
            }
            else{
                $date_column = 'purchase_orders.invoice_date';
            }

            if($request['from_date'] != null)
            {
                $date = str_replace("/","-",$request['from_date']);
                $date =  date('Y-m-d',strtotime($date));
                // $query->where('purchase_orders.target_receive_date', '>=', $date);
                $query->where($date_column, '>=', $date);
            }
            else
            {
                if ($request['blade'] == 'received_into_stock') {
                    if($this->targetShipDateConfig['target_ship_date'] == 1)
                    {
                        $query->whereRaw('MONTH(purchase_orders.target_receive_date) = ?',[$currentMonth]);
                        $query->whereRaw('YEAR(purchase_orders.target_receive_date) = ?',[$currentYear]);
                    }
                }
            }

            if($request['to_date'] != null)
            {
                $date = str_replace("/","-",$request['to_date']);
                $date =  date('Y-m-d',strtotime($date));
                // $query->where('purchase_orders.target_receive_date', '<=', $date);
                $query->where($date_column, '<=', $date);
            }
            else
            {
                if ($request['blade'] == 'received_into_stock') {
                    if($this->targetShipDateConfig['target_ship_date'] == 1)
                    {
                        $query->whereRaw('MONTH(purchase_orders.target_receive_date) = ?',[$currentMonth]);
                        $query->whereRaw('YEAR(purchase_orders.target_receive_date) = ?',[$currentYear]);
                    }
                }
            }

            if($request['selecting_suppliers'] != null)
            {
                $query->where('supplier_id', $request['selecting_suppliers']);
            }

            if($request['search_value'] != null) {
                $search_value = $request['search_value'];

                $query = $query->where(function($p) use ($search_value) {
                    $p->where('invoice_number','LIKE','%'.$search_value.'%')
                    ->orWhere('ref_id','LIKE','%'.$search_value.'%')
                    ->orWhereHas('PoSupplier', function($q) use($search_value) {
                        $q->where('reference_name','LIKE','%'.$search_value.'%');
                    })
                    ->orWhereHas('PurchaseOrderDetail', function($r) use ($search_value) {
                        $r->whereHas('customer', function($rr) use ($search_value) {
                            $rr->where('reference_name','LIKE','%'.$search_value.'%');
                        });
                    })
                    ->orWhereHas('ToWarehouse', function($s) use($search_value) {
                        $s->where('warehouse_title', 'LIKE','%'.$search_value.'%');
                    })
                    ->orWhereHas('po_notes', function($t) use($search_value) {
                        $t->where('note', 'LIKE','%'.$search_value.'%');
                    });
                });
            }

            PurchaseOrder::doSortBy($request, $query);
            $filename = $request['blade'].'.xlsx';
            $return=\Excel::store(new receivedIntoStockExport($query, $global_terminologies, $request['blade']), $filename);
            if($return)
            {
                ExportStatus::where('type',$request['blade'])->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
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



    public function failed($exception)
    {
        ExportStatus::where('type', $this->blade)->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type=$this->blade;
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
    }
}
