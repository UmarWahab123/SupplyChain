<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\FailedJobException;
use Auth;
use App\Exports\cancelOrdersExport;
use App\ExportStatus;
use App\Models\Common\Order\Order;
use App\Variable;

class CancelledOrderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $role_id;
    protected $filter_dropdown_exp;
    protected $to_date_exp;
    protected $from_date_exp;
    protected $date_radio_exp;
    public $tries=1;
    public $timeout=1500;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($filter_dropdown_exp,$to_date_exp,$from_date_exp,$user_id,$role_id,$date_radio_exp)
    {
        $this->user_id = $user_id;
        $this->role_id = $role_id;
        $this->filter_dropdown_exp = $filter_dropdown_exp;
        $this->from_date_exp = $from_date_exp;
        $this->to_date_exp = $to_date_exp;
        $this->date_radio_exp = $date_radio_exp;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
       $user_id = $this->user_id;
       $role_id = $this->role_id;
       $filter_dropdown_exp = $this->filter_dropdown_exp;
       $from_date_exp = $this->from_date_exp;
       $to_date_exp = $this->to_date_exp;
       $date_radio_exp = $this->date_radio_exp;

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

            if($role_id == 4)
              {
                $query = Order::with('customer')->orderBy('id','DESC');
              }
              else if($role_id == 2)
              {
                $query = Order::with('customer')->orderBy('id','DESC');
              }
              else if($role_id == 1 || $role_id == 7 || $role_id == 11)
              {
                $query = Order::with('customer')->orderBy('id','DESC');

              }
              else if($role_id == 3)
              {
                $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(),Auth::user()->secondary_customer->pluck('id')->toArray());

                $query = Order::with('customer')->whereIn('customer_id', $all_customer_ids)->orderBy('id','DESC');
              }
              else if(Auth::user()->role_id == 9)
                {
                    $query = Order::where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->orderBy('orders.id', 'DESC');
                }
              else
              {
                $query = Order::with('customer')->where('user_id', $this->user->id)->orderBy('id','DESC');
              }
              if($filter_dropdown_exp == "draft"){
                $query = $query->where('in_status_prefix',null);
              }
              if($filter_dropdown_exp == "invoice"){
                $query = $query->where('in_status_prefix','!=',null);
              }
              if($from_date_exp != null)
              {
                if($date_radio_exp == '1')
                {
                  $date = str_replace("/","-",$from_date_exp);
                  $date =  date('Y-m-d',strtotime($date));
                  $query = $query->where('target_ship_date', '>=', $date);
                }
                else
                {
                  $date = str_replace("/","-",$from_date_exp);
                  $date =  date('Y-m-d',strtotime($date));
                  $query = $query->whereDate('cancelled_date', '>=', $date);
                }

              }
              if($to_date_exp != null)
              {
                if($date_radio_exp == '1')
                {
                  $date_to = str_replace("/","-",$to_date_exp);
                  $date_to =  date('Y-m-d',strtotime($date_to));
                  $query = $query->where('target_ship_date', '<=', $date_to);
                }
                else
                {
                  $date_to = str_replace("/","-",$to_date_exp);
                  $date_to =  date('Y-m-d',strtotime($date_to));
                  $query = $query->whereDate('cancelled_date', '<=', $date_to);
                }

              }
              $query->where(function($q){
                $q->where('primary_status', 17)->orderBy('orders.id', 'DESC');
              });
              $query = $query->get();
              $current_date = date("Y-m-d");

              // return \Excel::download(new cancelOrdersExport($query), 'Cancel Orders Export'.$current_date.'.xlsx');

               $return = \Excel::store(new cancelOrdersExport($query,$global_terminologies,$role_id), 'cancelled-order-export.xlsx');

            if($return)
            {
             ExportStatus::where('user_id',$user_id)->where('type','cancel_order_export')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s')]);
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
         ExportStatus::where('type','cancel_order_export')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Complete Products Export";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
    }
}
