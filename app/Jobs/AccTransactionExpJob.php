<?php

namespace App\Jobs;

use App\OrderTransaction;
use App\OrdersPaymentRef;
use App\Models\Common\Order\Order;
use App\Exports\AccTransactionExport;
use App\ExportStatus;
use DB;
use Carbon\Carbon;
use App\Variable;
use App\TransactionRecord;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\FailedJobException;
use Auth;

class AccTransactionExpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1500;
    public $tries = 1;
    protected $user_id;
    protected $role_id;
    protected $from;
    protected $to;
    protected $cust;
    protected $invoice;
    protected $reference;
    // protected $productsArr=[];


    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($from,$to,$cust,$invoice,$reference, $user_id, $role_id)
    {
        $this->user_id = $user_id;
        $this->role_id = $role_id;
        $this->from = $from;
        $this->to = $to;
        $this->cust = $cust;
        $this->invoice = $invoice;
        $this->reference = $reference;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

         try{
           $user_id=$this->user_id;
           $role_id=$this->role_id;
           $from=$this->from;
           $to=$this->to;
           $cust=$this->cust;
           $invoice=$this->invoice;
           $reference=$this->reference;

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

         if($from != null || $to != null || $cust != null || $invoice != null || $reference != null)
        {
          // $query = OrderTransaction::latest();
           $query = OrderTransaction::select('order_transactions.id','order_transactions.payment_reference_no','order_transactions.received_date','order_transactions.order_id','order_transactions.customer_id','order_transactions.vat_total_paid','order_transactions.non_vat_total_paid','order_transactions.total_received','order_transactions.payment_method_id', 'order_transactions.remarks')->with('order','get_payment_type','get_payment_ref')->where('payment_reference_no','!=', NULL);
        }
        else
        {

          $query = OrderTransaction::orderBy('id', 'desc')->limit(10);
        }

         if($from != null)
        {

          $date = str_replace("/","-",$from);
          $date =  date('Y-m-d',strtotime($date));
           $query->whereDate('received_date', '>=', $date);
        }
        if($to != null)
        {
          $date = str_replace("/","-",$to);
          $date =  date('Y-m-d',strtotime($date));
           $query->whereDate('received_date', '<=', $date);
        }


        if($cust != null)
        {
          $query = $query->whereIn('order_id',Order::where('customer_id' , $cust)->pluck('id')->toArray());

        }

        if($invoice != null)
        {

          $result = $invoice;
              if (strstr($result,'-'))
              {
                $order = Order::where(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%".$result."%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_id`)"), 'LIKE', "%".$result."%");
              }
              else
              {
                $resultt = preg_replace("/[^0-9]/", "", $result );
                $order = Order::where('ref_id',$resultt)->orWhere('in_ref_id',$resultt);
              }

              // dd($order->toSql());
              $order_id = $order->pluck('id');
              // $order_id = Order::where('ref_id','LIKE','%'.$data->order_no.'%')->pluck('id');
          $query = @$query->where('order_id',$order_id[0]);
        }

        if($reference != null)

        {
        $payment_ref = OrdersPaymentRef::where('payment_reference_no',$reference)->orWhere('auto_payment_ref_no',$reference)->first();
          $query = @$query->where('payment_reference_no',$payment_ref->id);

        }
        if($role_id == 9){
          $query = $query->whereIn('order_id',Order::where('ecommerce_order' , 1)->pluck('id')->toArray());
         }

            $current_date = date("Y-m-d");
            $productsArr=[];
            $query=$query->get();
            TransactionRecord::truncate();
            // $productObj=new OrderTransaction;
            // $query->chunk(1000,function ($rows) use($productObj) {

            foreach($query as $item)
            {
              $order = $item->order;
              $payment_r = null;
              $rec_date = null;
              $ref_no = null;
              $refer_name = null;
              $c_name = null;
              $delivery_date=null;
              $total_amount=null;
              $vat_total_paid=null;
              $non_vat_total_paid=null;
              $total_paid=null;
              $diff=null;
              $sale_p=null;


            //   if($item->get_payment_ref->payment_reference_no != null){
            //        $payment_r = $item->get_payment_ref->payment_reference_no;
            //      }else{
            //        $payment_r = 'N.A';
            //      }
                $payment_r = $item->get_payment_ref->auto_payment_ref_no != null ? $item->get_payment_ref->auto_payment_ref_no : $item->get_payment_ref->payment_reference_no;

                 if($item->received_date != null){
                   $rec_date = Carbon::parse(@$item->received_date)->format('d/m/Y');
                 }else{
                   $rec_date = 'N.A';
                 }

                if($order->primary_status == 3)
                {
                  if($order->in_status_prefix !== null || $order->in_ref_prefix !== null){
                   $ref_no = @$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
                  }
                  else{
                   $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id;
                    }
                }else if($order->primary_status == 25){
                      $ref_no = @$order->status_prefix.$order->ref_id;
                  } else
                    {
                      if($order->status_prefix !== null || $order->ref_prefix !== null){
                       $ref_no = @$order->in_status_prefix.'-'.$order->in_ref_prefix.$order->in_ref_id;
                      }
                      else{
                        $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code.@$order->customer->CustomerCategory->short_code.@$order->ref_id;
                     }
                   }

                 if($item->order->customer !== null){
                   $refer_name = $item->order->customer->reference_name;

                 } else{
                     $refer_name = "N.A";

                 }

                  if($item->order->customer !== null){
                   $c_name = $item->order->customer->company;

                 } else{
                     $c_name = "N.A";

                 }

               if(@$item->order->primary_status == 25)
                {
                  if(@$item->order->credit_note_date != null){
                  $delivery_date = Carbon::parse(@$item->order->credit_note_date)->format('d/m/Y');
                }else
                {
                $delivery_date = 'N.A';
                }
                }else
                {
                  if(@$item->order->delivery_request_date != null)
                  {
                    $delivery_date = Carbon::parse(@$item->order->delivery_request_date)->format('d/m/Y');
                  }
                  else
                  {
                    $delivery_date = 'N.A';
                  }
                }

                if($item->order->total_amount != null){

                  $total_amount = number_format(preg_replace('/(\.\d\d).*/', '$1', round(@$item->order->total_amount,4)),2,'.',',');

                }else
                {
                   $total_amount = 'N.A';

                }

                if($item->vat_total_paid != null){

                  $vat_total_paid = number_format(preg_replace('/(\.\d\d).*/', '$1', round(@$item->vat_total_paid,4)),2,'.',',');

                }else
                {
                   $vat_total_paid = 'N.A';

                }

                 if($item->non_vat_total_paid != null){

                   $non_vat_total_paid = preg_replace('/(\.\d\d).*/', '$1', round(@$item->non_vat_total_paid,4));
                    $non_vat_total_paid = number_format($non_vat_total_paid,2,'.',',');

                }else
                {
                   $non_vat_total_paid = 'N.A';

                }

                if($item->total_received != null){

                  $total_paid = number_format(preg_replace('/(\.\d\d).*/', '$1', round(@$item->total_received,4)),2,'.',',');

                }else
                {
                   $total_paid = 'N.A';

                }

                if($item->order->customer->primary_sale_person != null){

                   $sale_p = $item->order->customer->primary_sale_person->name;

                }else{
                    $sale_p = 'N.A';

                }

              $diff = $item->order->total_amount - ($item->order->vat_total_paid + $item->order->non_vat_total_paid);
              $diff = number_format(preg_replace('/(\.\d\d).*/', '$1',number_format(@$diff,4,'.','')),2,'.',',');

                $productsArr[] = [
                'payment_reference'=> $payment_r,
                'received_date'=>$rec_date,
                'invoice_number'=>$ref_no,
                'reference_name'=>$refer_name,
                'billing_name'=>$c_name,
                'delivery_date'=>$delivery_date,
                'invoice_total'=>$total_amount,
                'total_paid_vat'=>$vat_total_paid,
                'total_paid_non_vat'=>$non_vat_total_paid,
                'total_paid'=>$total_paid,
                'difference'=>$diff,
                'payment_method'=>$item->payment_method_id != null ? $item->get_payment_type->title : 'N.A',
                'sale_person'=> $sale_p,
                'remarks'=> $item->remarks != null ? $item->remarks : '--'
                ];
                // $count++;
            }
            // ProductsRecord::insert($this->productsArr);
            foreach (array_chunk($productsArr,1000) as $t)
            {
                // TransactionRecord::insert($t);
                DB::table('transaction_records')->insert($t);
            }
            // $this->productsArr=[];

        // });

          // $query = $query->get();
          // $current_date = date("Y-m-d");s


           $data=TransactionRecord::orderBy('id','asc')->get();
           $return = \Excel::store(new AccTransactionExport($data,$global_terminologies), 'Account-Transaction-Export.xlsx');

           // $return = \Excel::store(new AccTransactionExport($query), 'Account Transaction'.$current_date.'.xlsx', 'csv2s');

         if($return)
           {
              ExportStatus::where('user_id',$user_id)->where('type','acc_transaction_exp')->update(['status' => 0]);
              //return response()->json(['msg'=>'File Saved']);
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

        ExportStatus::where('type','acc_transaction_exp')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Account Transaction Export";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();

    }

        //
}
