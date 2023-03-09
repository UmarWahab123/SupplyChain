<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\Status;
use App\Models\Common\Supplier;
use App\Models\Common\Warehouse;
use Carbon\Carbon;
use App\PoPaymentRef;
use App\PurchaseOrderTransaction;


class POBulkImportTest extends TestCase
{
    /**
     * A basic unit test example.
     *
     * @return void
     */
    // public function testExample()
    // {
    //     $this->assertTrue(true);
    // }

    protected $user_id = 21;
    public function testBulkImport()
    {
    	$row1 = [
    		0=>[
				'Au Chapon',
				'SC19',
				'Bangkok',
				null,
				null,
				null,
				null,
				null,
				null,
				'2000',
				'100'
			]
    	];
    	foreach ($row1 as $row)
        {
        	$i = 2;
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
            	$this->assertTrue(false);
            }
            if ($row[1] == null) {
            	$this->assertTrue(false);
            }
            if ($row[2] == null) {
            	$this->assertTrue(false);
            }
            if ($row[9] == null) {
            	$this->assertTrue(false);
            }
            if ($row[10] == null) {
            	$this->assertTrue(false);
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

        			$this->assertTrue(true);
            	}
            	else
            	{
            		if ($supplier == null) {
            			$this->assertTrue(false);
            		}
            		if ($warehouse == null) {
            			$this->assertTrue(false);
            		}
            	}
            }
            $i++;
        }
    }
}
