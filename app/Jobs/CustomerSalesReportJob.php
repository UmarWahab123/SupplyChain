<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\MaxAttemptsExceededException;
use Exception;
use DB;
use App\FailedJobException;
use App\ExportStatus;
use App\Models\Common\TableHideColumn;
use App\Models\Sales\Customer;
use App\Exports\CustomerSaleReportExport;
use App\User;

class CustomerSalesReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $tries=1;
    public $timeout = 500;
    protected $request;
    protected $user_id;
    protected $role_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data,$user_id,$role_id)
    {
        $this->request=$data;
        $this->user_id=$user_id;
        $this->role_id=$role_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $request=$this->request;
            $user_id=$this->user_id;
            $role_id=$this->role_id;
            $months = explode(" ", $request['months']);
            // $sale_year = '2020';
            $sale_year = $request['sale_year'];
            // $customers = Customer::whereIn('id',Order::where('primary_status' , 3)->distinct()->pluck('customer_id')->toArray())->where('status' , 1)->with('getcountry', 'getstate','getpayment_term','customer_orders')->get();
      
            $customers = Customer::where('o.primary_status',3)->where('customers.status',1)
            ->select(DB::raw('sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=1 THEN o.total_amount
            END) AS Jan,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=2 THEN o.total_amount
            END) AS Feb,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=3 THEN o.total_amount
            END) AS Mar,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=4 THEN o.total_amount
            END) AS Apr,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=5 THEN o.total_amount
            END) AS May,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=6 THEN o.total_amount
            END) AS Jun,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=7 THEN o.total_amount
            END) AS Jul,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=8 THEN o.total_amount
            END) AS Aug,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=9 THEN o.total_amount
            END) AS Sep,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=10 THEN o.total_amount
            END) AS Oct,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=11 THEN o.total_amount
            END) AS Nov,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" AND MONTH(`converted_to_invoice_on`)=12 THEN o.total_amount
            END) AS Dece,
            sum(CASE 
            WHEN o.primary_status=3 AND YEAR(`converted_to_invoice_on`)="'.$sale_year.'" THEN o.total_amount
            END) AS customer_orders_total'),'customers.reference_name','customers.id','customers.primary_sale_id','customers.credit_term','o.dont_show','o.user_id','o.customer_id')->whereYear('o.converted_to_invoice_on',$sale_year)->groupBy('o.customer_id');
            $customers->join('orders AS o','customers.id','=','o.customer_id');

            if($request['sale_person'] != null )
            {
              $customers = $customers->where('o.user_id',$request['sale_person']);
            }
            else
            {
              $customers = $customers->where('o.dont_show',0);
            }
      
            if($request['customer_categories'] != null)
            {
              $customers = $customers->where('category_id',$request['customer_categories']);
            }


            if($request['sale_person_filter'] != null )
            {
              if($user_id != $request['sale_person_filter'])
              {
                // dd($request['sale_person_filter']);
                $customers = $customers->where('o.user_id',$request['sale_person_filter']);
              }
              else
              {
                $customers = $customers->where(function($q) use ($user_id){
                  $q->where('o.user_id',$user_id);
                });
              }
            }
            elseif($role_id == 3)
            {
              // $customers = $customers->where(function($q) use($user_id){
              //   $q->where('o.user_id',$user_id);
              // });
              $user_r = User::find($user_id);
              if($user_r != null)
              {
                $customers = $customers->where(function($or) use ($user_id, $user_r){
                  $or->where('o.user_id', $user_id)->orWhereIn('o.customer_id',@$user_r->customer->pluck('id')->toArray())->orWhereIn('o.customer_id', @$user_r->user_customers_secondary->pluck('customer_id')->toArray());
                });
              }
            }
      
            $current_date = date("Y-m-d");
      
            $selected_year = $request['sale_year'];
      
            // dd($customers->get());
             /*********************  Sorting code ************************/
             $sort_variable = '';
             $sort_order = '';
            if($request['sortbyparam']== 3 && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'reference_name';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 3 && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'reference_name';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'Jan' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Jan';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Jan' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Jan';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'Feb' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Feb';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Feb' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Feb';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'Mar' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Mar';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Mar' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Mar';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'Apr' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Apr';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Apr' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Apr';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'May' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'May';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'May' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'May';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'Jun' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Jun';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Jun' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Jun';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'Jul' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Jul';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Jul' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Jul';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'Aug' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Aug';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Aug' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Aug';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'Sep' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Sep';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Sep' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Sep';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'Oct' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Oct';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Oct' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Oct';
                $sort_order     = 'ASC';
              }
      
              if($request['sortbyparam']== 'Nov' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Nov';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Nov' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Nov';
                $sort_order     = 'ASC';
              }


      
              if($request['sortbyparam']== 'Dec' && $request['sortbyvalue'] == 1)
              {
                $sort_variable  = 'Dec';
                $sort_order     = 'DESC';
              }
              elseif($request['sortbyparam']== 'Dec' && $request['sortbyvalue'] == 2)
              {
                $sort_variable  = 'Dec';
                $sort_order     = 'ASC';
              }
      
              // if($request['sortbyparam']!== null)
              // {
              //   $customers->orderBy($sort_variable, $sort_order);
              // }
              // else
              // {
              //   $customers->orderBy('reference_name','ASC');
              // }

              if($request['sortbyparam'] == 'location_code')
              {
                $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
                $customers->leftJoin('users as u', 'u.id', '=', 'customers.primary_sale_id')->join('warehouses as w', 'w.id', '=', 'u.warehouse_id')->orderBy('location_code', $sort_order);
              }

              if($request['sortbyparam'] == 'sales_person_code')
              {
                $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
                $customers->leftJoin('users as u', 'u.id', '=', 'customers.primary_sale_id')->orderBy('name', $sort_order);
              }

              if($request['sortbyparam'] == 'payment_term_code')
              {
                $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
                $customers->leftJoin('payment_terms as pt', 'pt.id', '=', 'customers.credit_term')->orderBy('title', $sort_order);
              }
              elseif($sort_variable != '')
              {
                $customers->orderBy($sort_variable, $sort_order);
              }
              else
              {
                $customers->orderBy('reference_name','ASC');
              }
            /*********************************************/

            $customers = $customers->with('primary_sale_person','getpayment_term','primary_sale_person.get_warehouse');
            $filename='Customer-Sale-Report-'.$user_id.'-'.$current_date.'.xlsx';
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type','customer_sale_report')->where('user_id',$user_id)->first();
                if($not_visible_columns!=null)
                {
                  $not_visible_arr = explode(',',$not_visible_columns->hide_columns);
                }
                else
                {
                  $not_visible_arr=[];
                }

            $return= \Excel::store(new CustomerSaleReportExport($customers , $selected_year, $months,$not_visible_arr), $filename);
            if($return)
            {
                ExportStatus::where('user_id',$user_id)->where('type','customer_sales_report')->update(['status'=>0,'last_downloaded'=>date('Y-m-d H:i:s'),'file_name'=>$filename]);
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
       
        ExportStatus::where('type','customer_sales_report')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        $failedJobException=new FailedJobException();
        $failedJobException->type="Customer Sales Report";
        $failedJobException->exception=$exception->getMessage();
        $failedJobException->save();
       
    }
}
