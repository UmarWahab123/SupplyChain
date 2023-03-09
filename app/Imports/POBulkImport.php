<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\Status;
use App\Models\Common\Supplier;
use App\Models\Common\Warehouse;
use Carbon\Carbon;
use App\ExportStatus;
use App\PoPaymentRef;
use App\PurchaseOrderTransaction;
use App\FailedJobException;

class POBulkImport implements ToCollection, WithStartRow
{
	protected $errors = [];
	protected $user_id;
	public $tries = 2;
	public function __construct($user_id)
	{
		$this->user_id = $user_id;
	}
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
    	try {

    		$row1 = $rows->toArray();
    		$user_id = $this->user_id;
	    	$status=ExportStatus::where('type','bulk_upload_po')->where('user_id', $user_id)->first();
	    	if ($row1[0][0] !== 'Supplier Name') {
	    		$status->error_msgs = 'Invalid File. Please Upload a Valid File';
	    		$status->status = 2;
	    	}
	        elseif ($rows->count() > 1)
	        {
	            $remove = array_shift($row1);
	            $i = 2;

	            foreach ($row1 as $row)
	            {
	            	$po_status = Status::where('id',4)->first();
		            $counter_formula = $po_status->counter_formula;
		            $counter_formula = explode('-',$counter_formula);
		            $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

		            $year = Carbon::now()->year;
		            $month = Carbon::now()->month;

		            $year = substr($year, -2);
		            $month = sprintf("%02d", $month);
		            $date = $year.$month;

		            $c_p_ref = PurchaseOrder::where('ref_id','LIKE',"$date%")->orderby('id','DESC')->first();
		            $str = @$c_p_ref->ref_id;
		            $onlyIncrementGet = substr($str, 4);
		            if($str == NULL)
		            {
		                $onlyIncrementGet = 0;
		            }
		            $system_gen_no = $date.str_pad(@$onlyIncrementGet + 1, $counter_length,0, STR_PAD_LEFT);

		            if ($row[0] == null) {
		            	array_push($this->errors, 'Supplier Name in row ' . $i . ' is required');
		            }
		            if ($row[1] == null) {
		            	array_push($this->errors, 'Supplier No in row ' . $i . ' is required');
		            }
		            if ($row[2] == null) {
		            	array_push($this->errors, 'To Warehouse in row ' . $i . ' is required');
		            }
		            if ($row[9] == null) {
		            	array_push($this->errors, 'PO Amount in row ' . $i . ' is required');
		            }
		            if ($row[10] == null) {
		            	array_push($this->errors, 'Paid Amount in row ' . $i . ' is required');
		            }

		            if ($row[0] != null && $row[1] != null && $row[2] != null && $row[9] != null && $row[10] != null)
		            {
		            	$supplier = Supplier::where('reference_number', $row[1])->first();
		            	$warehouse = Warehouse::where('warehouse_title', $row[2])->first();
		            	if ($supplier != null && $warehouse != null) {
		            		$supplier_id = $supplier->id;
		            		$warehouse_id = $warehouse->id;
		            		$exchange_rate = $supplier->getCurrency->conversion_rate;

				            $po = new PurchaseOrder();
				            $po->ref_id               = $system_gen_no;
			                $po->status               = 15;
			                $po->total				  = $row[9];
			                $po->total_in_thb         = $row[9];
			                $po->total_paid		   	  = $row[10];
			                $po->supplier_id          = $supplier_id;
			                $po->created_by           = $this->user_id;
			                $po->invoice_number	      = $row[7];
			                $po->memo                 = $row[8];
			                $po->payment_terms_id     = $row[6] != null ? $row[6] : $supplier->credit_term;
			                $po->target_receive_date  = $row[3] != null ? $row[3] : date("Y-m-d H:i:s");
			                $po->to_warehouse_id      = $warehouse_id;
			                $po->invoice_date         = $row[4] != null ? $row[4] : date("Y-m-d H:i:s");
			                $po->exchange_rate        = $row[5] != null ? $row[5] : $exchange_rate;
			                $po->save();

			                $addBilledItem = new PurchaseOrderDetail;
							        $addBilledItem->po_id = $po->id;
							        $addBilledItem->is_billed = "Billed";
							        $addBilledItem->pod_unit_price = $row[9];
							        $addBilledItem->pod_unit_price_with_vat = $row[9];
							        $addBilledItem->pod_total_unit_price = $row[9];
							        $addBilledItem->pod_total_unit_price_with_vat = $row[9];
							        $addBilledItem->created_by = $this->user_id;
							        $addBilledItem->save();


				            $po_payment_ref = new PoPaymentRef();
					        $po_payment_ref->payment_reference_no = 'Ref' . $system_gen_no;
					        $po_payment_ref->supplier_id = $supplier_id;
					        $po_payment_ref->payment_method = 3;
					        $po_payment_ref->received_date = $row[3] != null ? $row[3] : date("Y-m-d H:i:s");
					        $po_payment_ref->save();

				        	$po_transaction = new PurchaseOrderTransaction();
				        	$po_transaction->po_id 			      = $po->id;
			        		$po_transaction->supplier_id		  = $supplier_id;
			        		$po_transaction->po_order_ref_no	  = $system_gen_no;
			        		$po_transaction->user_id			  = $this->user_id;
			        		$po_transaction->payment_method_id	  = $row[6] != null ? $row[6] : $supplier->credit_term;
			        		$po_transaction->payment_reference_no = $po_payment_ref->id;
			        		$po_transaction->received_date		  = $row[3] != null ? $row[3] : date("Y-m-d H:i:s");
			        		$po_transaction->total_received		  = $row[10];
			        		$po_transaction->save();
		            	}
		            	else
		            	{
		            		if ($supplier == null) {
		            			array_push($this->errors, 'Supplier in row ' . $i . ' dosent exists in system. Please Add this Supplier first.');
		            		}
		            		if ($warehouse == null) {
		            			array_push($this->errors, 'Warehouse in row ' . $i . ' dosent exists in system. Please Add this Warehouse first.');
		            		}
		            	}
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
	            }
	            $status->status = 0;
	        }
	        else
	        {
	        	$status->error_msgs = 'Please Dont Upload Empty File';
	        	$status->status = 2;
	        }
	        $status->save();
    		
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
      ExportStatus::where('type','bulk_upload_po')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException            = new FailedJobException();
      $failedJobException->type      = "bulk_upload_po";
      $failedJobException->exception = $exception->getMessage();
      $failedJobException->save();
    }

    public function startRow():int
    {
      return 1;
    }
}
