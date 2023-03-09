<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\TableHideColumn;
use DB;
use File;
use App\Exports\AccountPayableTableExport;
use App\ExportStatus;
use App\Models\Common\PaymentType;
use App\FailedJobException;
use Illuminate\Http\Request;

class AccountPayableExpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $auth_user;
    protected $dosortby;
    protected $select_supplier;
    protected $select_po;
    protected $customer_id_select;
    protected $from_date;
    protected $to_date;
    protected $select_by_value;
    protected $type;
    protected $sortbyvalue;
    protected $sortbyparam;
    protected $search_value;
    public $tries=1;
    public $timeout=1500;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($select_supplier, $select_po, $from_date, $to_date, $select_by_value, $type,$dosortby, $auth_user,$sortbyvalue, $sortbyparam, $search_value) {


        $this->select_supplier = $select_supplier;
        $this->select_po = $select_po;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->select_by_value = $select_by_value;
        $this->type = $type;
        $this->dosortby = $dosortby;
        $this->auth_user = $auth_user;
        $this->sortbyvalue = $sortbyvalue;
        $this->sortbyparam = $sortbyparam;
        $this->search_value = $search_value;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $select_supplier = $this->select_supplier;
        $select_po = $this->select_po;
        $from_date = $this->from_date;
        $to_date = $this->to_date;
        $select_by_value = $this->select_by_value;
        $type = $this->type;
        $dosortby = $this->dosortby;
        $auth_user = $this->auth_user;
        $sortbyvalue = $this->sortbyvalue;
        $sortbyparam = $this->sortbyparam;
        $search_value = $this->search_value;

        try {

          $query = PurchaseOrder::with('PurchaseOrderDetail','createdBy','purchaseOrderTransaction','pOpaymentTerm','PoSupplier.getCurrency','po_notes');
          $payment_types = PaymentType::all();

          $request_data = new Request();
          $request_data->replace(['sortbyvalue'=>$sortbyvalue,'sortbyparam'=>$sortbyparam]);


          PurchaseOrder::doSort($request_data, $query);


          $query->where(function($q){
            $q->where('status', 15)->whereNotNull('supplier_id');
          });

          if($from_date != null)
          {
            $from_date = str_replace("/","-",$from_date);
            $from_date =  date('Y-m-d',strtotime($from_date));
           $query = $query->whereDate('invoice_date', '>=', $from_date);
          }
          if($to_date != null)
          {
            $to_date = str_replace("/","-",$to_date);
            $to_date =  date('Y-m-d',strtotime($to_date));
            $query = $query->whereDate('invoice_date', '<=', $to_date);
          }

          if($select_supplier == null && $select_po == null)
          {
            // $query = $query->where('payment_due_date','<',$date);
          }
          if($select_supplier != null)
          {
            $query->where('supplier_id', $select_supplier);
          }

          if($select_po != null)
          {
            $query = $query->where('ref_id', $select_po);
          }

          $query = $query->whereRaw('total_in_thb > total_paid');


            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','account_payable')->where('user_id', $auth_user->id)->first();
            if($not_visible_columns)
            {
              $not_visible_arr = explode(',',$not_visible_columns->hide_columns);
            }
            else
            {
              $not_visible_arr = [];
            }

            $query = $query;
            $file = storage_path('app/account-payable-export.xlsx');
            if(File::exists($file))
            {
                File::delete($file);
            }

            if($search_value != null) {
                $query->where(function($p) use($search_value) {
                    $p->where('ref_id','LIKE', '%' . $search_value . '%')
                    ->orWhere('invoice_number', 'LIKE', '%' . $search_value . '%')
                    ->orWhere('memo', 'LIKE', '%' . $search_value . '%')
                    ->orWhereHas('PoSupplier', function ($r) use($search_value) {
                        $r->whereHas('getCurrency',function($rr) use ($search_value) {
                            $rr->where('currency_code','LIKE', '%' . $search_value . '%');
                        });
                    })
                    ->orWhereHas('PoSupplier', function ($q) use($search_value){
                        $q->where('reference_name','LIKE','%'.$search_value.'%');
                    })
                    ->orWhereHas('pOpaymentTerm', function($s) use($search_value) {
                        $s->where('title', 'LIKE','%'.$search_value.'%');
                    });
                });
            }

            $return = \Excel::store(new AccountPayableTableExport($query,$not_visible_arr),'account-payable-export.xlsx');

            if($return)
            {
              ExportStatus::where('user_id',$auth_user->id)->where('type','account_payable_report')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s'),'exception' => $query->count()]);
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

    public function failed( $exception)
    {
      ExportStatus::where('type','account_payable_report')->where('user_id',$this->auth_user->id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException            = new FailedJobException();
      $failedJobException->type      = "Account Payable Export";
      $failedJobException->exception = $exception->getMessage();
      $failedJobException->save();
    }
}
