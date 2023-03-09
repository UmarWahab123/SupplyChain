<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\ExportStatus;
use App\FailedJobException;
use App\Models\Sales\Customer;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Product;
use App\Models\Common\Status;
use App\User;
use App\Models\Common\PaymentTerm;
use App\Models\Common\Order\Order;
use Carbon\Carbon;


class CustomerPOBulkImport implements ToCollection
{

    protected $errors = [];
    protected $user_id;
    protected $user;
    public function __construct($user_id, $user)
  {
      $this->user_id=$user_id;
      $this->user = $user;
  }


    public function collection(Collection $rows)
    {
        // dd('here');
    		$row1 = $rows->toArray();
        $user = $this->user;
        $user_id = $this->user_id;
	    	$status=ExportStatus::where('type','customer_bulk_upload_po')->where('user_id',$user_id)->first();
	    	if ($row1[0][0] == 'Customer#') {
                // dd('here');
	    		if ($rows->count() > 1) {
                    $remove = array_shift($row1);
                    $i = 2;

                    foreach($row1 as $row) {
                        // dd($row[6]);
                        $customer = Customer::where('reference_number',$row[0])->first();

                        if($customer !== null)
                        {
                            // dd('here');
                          //to generate draft invoice number
                          $quot_status     = Status::where('id',1)->first();
                          $draf_status     = Status::where('id',2)->first();
                          $counter_formula = $quot_status->counter_formula;
                          $counter_formula = explode('-',$counter_formula);
                          $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

                          $date = Carbon::now();
                          $date = $date->format($counter_formula[0]);
                          $company_prefix          = $user->getCompany->prefix;
                          $draft_status_prefix     = $draf_status->prefix.$company_prefix;
                          $quot_status_prefix      = $quot_status->prefix.$company_prefix;

                          $draft_customer_category = $customer->CustomerCategory;
                          if($customer->category_id == 6)
                          {
                            $p_cat = CustomerCategory::where('id',4)->first();
                            $ref_prefix = $p_cat->short_code;
                          }
                          else
                          {
                            $ref_prefix              = @$draft_customer_category->short_code;
                          }
                          $c_p_ref = Order::whereIn('status_prefix',[$quot_status_prefix,$draft_status_prefix])->where('ref_id','LIKE',"$date%")->where('ref_prefix',$ref_prefix)->orderby('id','DESC')->first();
                          $str = @$c_p_ref->ref_id;
                          $onlyIncrementGet = substr($str, 4);
                          if($str == NULL)
                          {
                            $onlyIncrementGet = 0;
                          }
                          $system_gen_no = str_pad(@$onlyIncrementGet + 1,$counter_length,0, STR_PAD_LEFT);
                          $system_gen_no = $date.$system_gen_no;

                          $draft_status_prefix_draft = $draft_status_prefix;
                          $ref_prefix_draft = $ref_prefix;
                          $system_gen_no_draft = $system_gen_no;
                          //end to generate draft invoice number

                          //to generate invoice number
                          $inv_status = Status::where('id', 3)->first();
                          $counter_formula = $inv_status->counter_formula;
                          $counter_formula = explode('-', $counter_formula);
                          $counter_length = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

                          $date = Carbon::now();
                          $date = $date->format($counter_formula[0]);

                          $company_prefix          = $user->getCompany->prefix;

                          $draft_customer_category = $customer->CustomerCategory;
                          if($customer->category_id == 6)
                          {
                            $p_cat = CustomerCategory::where('id',4)->first();
                            $ref_prefix = $p_cat->short_code;
                          }
                          else
                          {
                            $ref_prefix              = @$draft_customer_category->short_code;
                          }
                          $status_prefix           = $inv_status->prefix.$company_prefix;
                          // dd($status_prefix,$ref_prefix,$date);
                          $c_p_ref = Order::where('in_status_prefix','=',$status_prefix)->where('in_ref_prefix',$ref_prefix)->where('in_ref_id','LIKE',"$date%")->orderBy('converted_to_invoice_on','DESC')->orderBy('id','DESC')->first();

                          $str = @$c_p_ref->in_ref_id;
                          $onlyIncrementGet = substr($str, 4);
                          if ($str == null)
                          {
                              $onlyIncrementGet = 0;
                          }
                          $system_gen_no = str_pad(@$onlyIncrementGet + 1, $counter_length, 0, STR_PAD_LEFT);
                          $system_gen_no = $date . $system_gen_no;
                          //end to generate invoice number

                          $current_date = Carbon::now();
                          $current_date = $current_date->toDateString();

                          if($row[4] == null) {
                            $target_ship_date = Carbon::now();
                            $target_ship_date = $target_ship_date->toDateString();
                          } else {
                            $target_ship_date = $row[4];
                          }

                          if($row[2] == null) {
                            $delivery_request_date = Carbon::now();
                            $delivery_request_date = $delivery_request_date->toDateString();
                          } else {
                            $delivery_request_date = $row[2];
                          }

                          if($row[6] != null) {
                            $userId = User::where('name', $row[6])->first();
                            if($userId == null) {
                                $status->error_msgs = 'Please enter a valid Sales Person';
                                $status->status = 2;
                            } else {
                                $userId = $userId->id;
                            }
                          } else {
                            $userId = $this->user_id;
                          }

                          if ($row[0] == null) {
                            array_push($this->errors, 'Customer# in row ' . $i . ' is required');
                          }

                          if ($row[1] == null) {
                            array_push($this->errors, 'Customer Name in row ' . $i . ' is required');
                          }

                          if ($row[9] == null) {
                            array_push($this->errors, 'Invoice Amount in row ' . $i . ' is required');
                          }

                          if($row[3] !== null) {
                            $payment_term_check = PaymentTerm::where('title', $row[3])->first();
                            if($payment_term_check !== null) {
                              $payment_term_id = $payment_term_check->id;
                            } else {
                              $payment_term = new PaymentTerm;
                              $payment_term->title = $row[3];
                              $payment_term->status = 1;
                              $payment_term->description = $row[3];
                              $payment_term->save();
                              $payment_term_id = $payment_term->id;
                            }
                          } else {
                            $payment_term_id = null;
                          }

                          if($row[0] !== null && $row[1] !== null && $row[9] !== null) {
                            $address  = CustomerBillingDetail::where('customer_id',$customer->id)->first();
                            $order                        = new Order;
                            $order->status_prefix         = $draft_status_prefix_draft;
                            $order->ref_prefix            = $ref_prefix_draft;
                            $order->ref_id                = $system_gen_no_draft;
                            $order->in_status_prefix      = $status_prefix;
                            $order->in_ref_prefix         = $ref_prefix;
                            $order->in_ref_id             = $system_gen_no;
                            $order->customer_id           = $customer->id;
                            $order->total_amount          = $row[9];
                            $order->target_ship_date      = $target_ship_date;
                            $order->memo                  = '';
                            $order->discount              = null;
                            $order->from_warehouse_id     = null;
                            $order->shipping              = null;
                            $order->payment_due_date      = $current_date;
                            $order->delivery_request_date = $delivery_request_date;
                            $order->billing_address_id    = $address != null ? $address->id : null;
                            $order->shipping_address_id   = $address != null ? $address->id : null;
                            $order->user_id               = $user_id;
                            $order->converted_to_invoice_on = Carbon::now();
                            $order->manual_ref_no         = null;
                            $order->is_vat                = 0;
                            $order->created_by            = $this->user_id;
                            $order->primary_status        = 3;
                            $order->status                = 11;
                            $order->memo                  = $row[5];
                            $order->save();
                            // $order->ref_id = $system_gen_no;
                            $order->full_inv_no = @$status_prefix.'-'.@$ref_prefix.@$system_gen_no;;
                            $order->save();
                            // $product = Product::find($stock->product_id);

                            // dd($order);

                            $new_order_product = OrderProduct::create([
                              'order_id'             => $order->id,
                              'product_id'           => null,
                              'category_id'          => null,
                              'short_desc'           => null,
                              'brand'                => null,
                              'type_id'              => null,
                              'number_of_pieces'     => null,
                              'quantity'             => 1,
                              'qty_shipped'          => 1,
                              'selling_unit'         => null,
                              'margin'               => null,
                              'vat'                  => null,
                              'vat_amount_total'     => null,
                              'unit_price'           => null,
                              'last_updated_price_on'=> null,
                              'unit_price_with_vat'  => null,
                              'unit_price_with_discount'  => null,
                              'is_mkt'               => null,
                              'total_price'          => null,
                              'total_price_with_vat' => null,
                              'supplier_id'          => null,
                              'from_warehouse_id'    => null,
                              'user_warehouse_id'    => null,
                              'warehouse_id'         => null,
                              'is_warehouse'         => null,
                              'status'               => 11,
                              'is_billed'            => 'Billed',
                              'default_supplier'     => null,
                              'remarks'     => $row[7],
                              'created_by'           => $this->user_id,
                              'discount'             => 0,
                              'is_retail'            => 'qty',
                              'import_vat_amount'    => null,
                              'actual_cost'          => null,
                              'locked_actual_cost'          => null,
                            ]);
                          }
                        } else {
                            $status->error_msgs = 'Please upload a valid customer';
                            $status->status = 2;
                        }
                        $i++;
                    }

                    if ($this->errors != null) {
                      $html = '<ul>';
                      foreach ($this->errors as $error) {
                        $html .= '<li>'.$error.'</li>';
                      }
                      $html .= '</ul>';
                      $status->error_msgs = $html;
                      $status->status = 2;
                    }


                } else {
                    $status->error_msgs = 'Please Dont Upload Empty File';
                    $status->status = 2;
                }
	    	} else {
	        	$status->error_msgs = 'Please Upload Valid File';
	        	$status->status = 2;
	        }

	        $status->save();
            ExportStatus::where('type', 'customer_bulk_upload_po')->where('user_id', $user_id)->update(['status' => 0, 'exception' => null, 'error_msgs' => $status->error_msgs]);
            return response()->json(true);

    }



    public function failed( $exception)
    {
      ExportStatus::where('type','customer_bulk_upload_po')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException            = new FailedJobException();
      $failedJobException->type      = "customer_bulk_upload_po";
      $failedJobException->exception = $exception->getMessage();
      $failedJobException->save();
    }

    public function startRow():int
    {
      return 1;
    }
}
