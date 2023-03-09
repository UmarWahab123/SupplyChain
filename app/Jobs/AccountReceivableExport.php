<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Common\Order\Order;
use App\Models\Sales\Customer;
use App\Exports\AccReceivableExp;
use App\FailedJobException;
use App\ExportStatus;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Http\Request;

use Exception;
use DB;

class AccountReceivableExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries=1;
    public $timeout = 500;
    protected $request;
    protected $user_id;
    protected $role_id;
    protected $sortbyparam;
    protected $sortbyvalue;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$user_id,$role_id,$sortbyparam,$sortbyvalue)
    {
        $this->request=$data;
        $this->user_id=$user_id;
        $this->role_id=$role_id;
        $this->sortbyparam = $sortbyparam;
        $this->sortbyvalue = $sortbyvalue;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try 
        {
            $request=$this->request;
            $user_id=$this->user_id;
            $role_id=$this->role_id;
            $sortbyparam=$this->sortbyparam;
            $sortbyvalue=$this->sortbyvalue;

           

             $date = date('Y-m-d H:i:s');
              if($role_id == 3)
              {
                $query = Order::where('orders.primary_status',3)->whereIn('orders.status',[11,32])->whereIn('customer_id',Customer::select('id')->where('user_id',$user_id)->pluck('id'))->where('orders.total_amount','!=',0);
              }
              else
              {
                // $query = Order::where('primary_status',3)->where('status',11)->where('total_amount','!=',0);
                $query = Order::where(function($q){
                  $q->where(function($p){
                    $p->where('orders.primary_status',3)->whereIn('orders.status',[11,32])->where('orders.total_amount','!=',0);
                  })->orWhere(function($r){
                    $r->where('orders.primary_status',25)->whereIn('orders.status',[27,33])->where('orders.total_amount','!=',0);
                  });
                });
              }

              $request_data = new Request();
              $request_data->replace(['sortbyvalue'=>$sortbyvalue,'sortbyparam'=>$sortbyparam]);
    
              Customer::doSort($request_data, $query);

              if($request['selecting_customerx'] == null && $request['order_nox'] == null)
              {
                // $query = $query->where('payment_due_date','<',$date)->orWhere(function($q){
                //   $q->where('primary_status',25)->whereIn('status',[27,33]);
                // });

                $query = $query->where(function($q) use ($date){
                  $q->where('payment_due_date','<',$date)->orWhere(function($q){
                  $q->where('primary_status',25)->whereIn('orders.status',[27,33]);
                  });
                });
              }
              if($request['selecting_customerx'] != null)
              {
                $cust_id = $request['selecting_customerx'];
                // $query->where('customer_id', $request['selecting_customerx'])->orWhere(function($q) use ($cust_id){
                //   $q->where('primary_status',25)->whereIn('status',[27,33])->where('customer_id',$cust_id);
                // })->where('total_amount','!=',0);

                $query->where(function($z) use ($cust_id) {
                  $z->where('customer_id', $cust_id)->orWhere(function($q) use ($cust_id){
                  $q->where('orders.primary_status',25)->whereIn('orders.status',[27,33])->where('customer_id',$cust_id);
                    });
                })->where('total_amount','!=',0);
              }

              if($request['from_datex'] != null)
              {
                $date = str_replace("/","-",$request['from_datex']);
                $date =  date('Y-m-d',strtotime($date));
                $query = $query->where('orders.converted_to_invoice_on', '>=', $date.' 00:00:00');
              }
              if($request['to_datex'] != null)
              {
                $date = str_replace("/","-",$request['to_datex']);
                $date =  date('Y-m-d',strtotime($date));
                $query = $query->where('orders.converted_to_invoice_on', '<=', $date.' 23:59:59');
              }
              // dd($query->toSql());
              

              if($request['selecting_salex'] != null)
              {
                // $query->where('user_id', $request->selecting_salex);        
                $query = $query->whereIn('customer_id',User::where('id',$request['selecting_salex'])->first()->customer->pluck('id'));       
              }

              if($request['order_nox'] != null)
              {
                // $query = $query->where('in_ref_id', $request['order_no']);

                $result = $request['order_nox'];
                // dd( $result[0]);
                  // dd($query->pluck('status'));

                    if (strstr($result,'-'))
                    {
                      $query = $query->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%");

                    }
                    else if(@$result[0] == 'C')
                    {
                      $query = $query->where(DB::raw("CONCAT(`status_prefix`,`ref_id`)"), 'LIKE', "%".$result."%");
                    }
                    else
                    {
                      $resultt = preg_replace("/[^0-9]/", "", $result );
                      $query = $query->where('in_ref_id',$resultt);
                    }
                    if($result[0] == 'C')
                    {
                      $query = $query->whereIn('orders.status',[27,33])->where('total_amount','!=',0);
                    } 
                    else
                    {
                      $query = $query->whereIn('orders.status',[11,32])->where('total_amount','!=',0);
                    } 

                    
              }
              $query = $query->orderBy('converted_to_invoice_on');
              $query = $query->with('customer.primary_sale_person.get_warehouse', 'customer.CustomerCategory', 'order_products', 'get_order_transactions', 'order_products_vat_2');
              $current_date = date("Y-m-d");

                // return \Excel::store(new AccReceivableExp($query), 'Account Receivable Export'.$current_date.'.xlsx');

                $filename='Account-Receivale-Report-'.$user_id.'-'.$current_date.'.xlsx';
                $return= \Excel::store(new AccReceivableExp($query), $filename);
                if($return)
                {
                    ExportStatus::where('user_id',$user_id)->where('type','account_receivable_export')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s'),'file_name'=>$filename]);
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
       
        ExportStatus::where('type','account_receivable_export')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Account Receivable Export";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
       
    }
}
