<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Models\Sales\Customer;
use App\Models\Common\TableHideColumn;
use App\ExportStatus;
use App\Exports\CustomerExport;
use App\Variable;
use App\FailedJobException;
use App\Models\Common\CustomerCategory;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\PaymentTerm;
use App\Models\Common\State;
use Illuminate\Http\Request;
use App\User;

class CustomerExpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user_id;
    protected $userid;
    public $tries = 1;
    public $timeout = 1500;
    protected $status_exp;
    protected $person_exp;
    protected $type_exp;
    protected $group_exp;
    protected $role_id;
    protected $sortbyparam;
    protected $sortbyvalue;
    protected $search_value;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($status_exp, $person_exp, $type_exp, $group_exp, $user_id, $role_id, $sortbyparam, $sortbyvalue, $search_value)
    {
        $this->user_id = $user_id;
        $this->status_exp = $status_exp;
        $this->person_exp = $person_exp;
        $this->type_exp = $type_exp;
        $this->group_exp = $group_exp;
        $this->role_id = $role_id;
        $this->sortbyparam = $sortbyparam;
        $this->sortbyvalue = $sortbyvalue;
        $this->search_value = $search_value;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user_id = $this->user_id;
        $status_exp = $this->status_exp;
        $person_exp = $this->person_exp;
        $type_exp = $this->type_exp;
        $group_exp = $this->group_exp;
        $role_id = $this->role_id;
        $sortbyparam = $this->sortbyparam;
        $sortbyvalue = $this->sortbyvalue;
        $search_value = $this->search_value;

        try {
            $vairables = Variable::select('slug', 'standard_name', 'terminology')->get();
            $global_terminologies = [];
            foreach ($vairables as $variable) {
                if ($variable->terminology != null) {
                    $global_terminologies[$variable->slug] = $variable->terminology;
                } else {
                    $global_terminologies[$variable->slug] = $variable->standard_name;
                }
            }

            $query = Customer::query();

            $query->with('CustomerCategory', 'primary_sale_person:id,name', 'CustomerSecondaryUser.secondarySalesPersons', 'getcountry:id,name', 'getstate', 'getpayment_term:id,title', 'getbilling.getstate', 'getbilling.getcountry', 'getnotes')->select('customers.*');

            $userid = $person_exp;
            if ($status_exp !== null) {
                if ($person_exp !== null) {
                    if ($type_exp != '') {
                        if ($type_exp == 0) {
                            // $query->where('status', $status_exp)->where(function($query) use ($userid){
                            //     $query->where('secondary_sale_id', $userid);
                            //   });

                            $query->where('customers.status', $status_exp)->whereHas('CustomerSecondaryUser', function ($query) use ($userid) {
                                $query->where('user_id', $userid);
                            });
                        } else if ($type_exp == 1) {
                            $query->where('customers.status', $status_exp)->where(function ($query) use ($userid) {
                                $query->where('primary_sale_id', $userid);
                            });
                        }
                    } else {

                        // $query->where('primary_sale_id',$userid)->orWhere('secondary_sale_id',$userid);

                        $query->where('customers.status', $status_exp)->where(function ($query) use ($userid) {
                            $query->where('primary_sale_id', $userid)->orWhereHas('CustomerSecondaryUser', function ($query) use ($userid) {
                                $query->where('user_id', $userid);
                            });
                        });
                    }
                } else {
                    $query->where('customers.status', $status_exp);
                }
            }

            if ($group_exp != null) {
                $query->where('customers.category_id', @$group_exp);
            }
            if ($role_id == 9) {
                $query->where('customers.ecommerce_customer', 1);
            }

            if ($search_value) {
                $query = $query->where('reference_number', 'LIKE', "%$search_value%")
                    ->orWhere('reference_name', 'LIKE', "%$search_value%")
                    ->orWhere('company', 'LIKE', "%$search_value%")
                    ->orWhere('phone', 'LIKE', "%$search_value%")
                    ->orWhereIn('customers.id', CustomerBillingDetail::select('customer_id')->where('billing_email', 'LIKE', "%$search_value%")->pluck('customer_id'))
                    ->orWhereIn('primary_sale_id', User::select('id')->where('name', 'LIKE', "%$search_value%")->pluck('id'))
                    ->orWhereIn('customers.id', CustomerBillingDetail::select('customer_id')->where('billing_city', 'LIKE', "%$search_value%")->pluck('customer_id'))
                    ->orWhereIn('customers.id', CustomerBillingDetail::select('customer_id')->whereIn('billing_state', State::select('id')->where('name', 'LIKE', "%$search_value%")->pluck('id'))->pluck('customer_id'))
                    ->orWhereIn('category_id', CustomerCategory::select('id')->where('title', 'LIKE', "%$search_value%")->pluck('id'))
                    ->orWhereIn('credit_term', PaymentTerm::select('id')->where('title', 'LIKE', "%$search_value%")->pluck('id'));
            }

            /*********************  Sorting code ************************/
            $request_data = new Request();
            $request_data->replace(['sortbyparam' => $sortbyparam, 'sortbyvalue' => $sortbyvalue]);
            $query = Customer::CustomerlIstSorting($request_data, $query);
            /*********************************************/

            // $query = $query->get();
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', 'customer_list')->where('user_id', $user_id)->first();
            if ($not_visible_columns != null) {
                $not_visible_arr = explode(',', $not_visible_columns->hide_columns);
            } else {
                $not_visible_arr = [];
            }

            $current_date = date("Y-m-d");
            // dd($not_visible_arr);

            // $return = \Excel::store(new CustomerExport($query,$not_visible_arr,$global_terminologies),'Customer-List-Report.xlsx');


            $return = \Excel::store(new CustomerExport($query, $not_visible_arr, $global_terminologies), 'customer-list-export.xlsx');

            if ($return) {
                ExportStatus::where('user_id', $user_id)->where('type', 'customer_list_report')->update(['status' => 0, 'last_downloaded' => date('Y-m-d H:i:s')]);
                return response()->json(['msg' => 'File Saved']);
            }
        } catch (Exception $e) {
            $this->failed($e);
        } catch (MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }


    public function failed($exception)
    {
        // ExportStatus::where('type','customer_list_report')->update(['status'=>2,'exception'=>$exception->getMessage()]);
        ExportStatus::where('type', 'customer_list_report')->where('user_id', $this->user_id)->update(['status' => 2, 'exception' => $exception->getMessage()]);
        $failedJobException = new FailedJobException();
        $failedJobException->type = "Complete Products Export";
        $failedJobException->exception = $exception->getMessage();
        $failedJobException->save();
    }
}
