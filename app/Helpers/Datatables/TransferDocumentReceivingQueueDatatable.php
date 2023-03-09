<?php

namespace App\Helpers\Datatables;

use Auth;
use Carbon\Carbon;
use App\Models\Common\Courier;
use App\Models\Common\PoGroup;
use Illuminate\Support\Facades\DB;
use App\Models\Common\SupplierProducts;
use App\Models\Common\StockManagementOut;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;


class TransferDocumentReceivingQueueDatatable {

    public static function returnFilterColumn($column, $item, $keyword) {
        switch ($column) {
            case 'warehouse':
                $item->whereHas('ToWarehouse', function($q) use($keyword){
                    $q->where('warehouse_title', 'LIKE', "%$keyword%");
                });
                break;

            case 'issue_date':
                $item->where('created_at', 'LIKE', "%$keyword%");
                break;

            case 'net_weight':
                $item->whereHas('po_group_detail', function ($q1) use($keyword) {
                    $q1->whereHas('purchase_order', function ($q2) use ($keyword) {
                        $q2->where('total_gross_weight' , 'LIKE', '%' . $keyword . '%');
                    });
                });
                break;

            case 'quantity':
                $item->whereHas('po_group_detail', function ($q1) use($keyword) {
                    $q1->whereHas('purchase_order', function ($q2) use($keyword) {
                        $q2->where('total_quantity', 'LIKE', '%' . $keyword . '%');
                    });
                });
                break;

            case 'supplier_ref_no':
                $item->whereHas('po_group_detail', function ($q) use($keyword) {
                    $q->whereHas('purchase_order', function ($q) use($keyword) {
                        $q->whereHas('PoWarehouse',function ($q) use($keyword) {
                            $q->where('warehouse_title', 'LIKE', "%$keyword%");
                        });
                    });
                });
                break;
                // $po_group_detail = $item->po_group_detail;
                // return $item->po_group_detail[0]->purchase_order->PoWarehouse->warehouse_title;

            case 'po_number':
                $item->whereHas('po_group_detail', function ($q) use($keyword) {
                    $q->whereHas('purchase_order', function ($q) use($keyword) {
                        $q->where('ref_id', 'LIKE', "%$keyword%");
                    });
                });
                break;

            case 'id':
                $item->where('ref_id', 'LIKE', "%$keyword%");
                break;
        }
    }
    public static function returnAddColumn($column, $item) {
        switch ($column) {
            case 'warehouse':
                return $item->ToWarehouse !== null ? $item->ToWarehouse->warehouse_title: "--" ;
                break;

            case 'target_receive_date':
                return $item->target_receive_date !== null ? $item->target_receive_date: "--" ;
                break;

            case 'po_total':
                $po_group_detail = $item->po_group_detail;
			    	$total = null;
			    	foreach ($po_group_detail as $p_g_d) {
			    		$total += $p_g_d->purchase_order->total_in_thb;
			    	}
			        return number_format($total,2,'.',',') ;
                break;

            case 'issue_date':
                $created_at = Carbon::parse($item->created_at)->format('d/m/Y');
		    	return $created_at;
                break;

            case 'net_weight':
                $po_group_detail = $item->po_group_detail;
		    	$weight = null;
		    	foreach ($po_group_detail as $p_g_d) {
		    		$weight += $p_g_d->purchase_order->total_gross_weight;
		    	}
		        return $weight ;
                break;

            case 'quantity':
                $po_group_detail = $item->po_group_detail;
			    $total_quantity = null;
			    foreach ($po_group_detail as $p_g_d) {
			    	$total_quantity += $p_g_d->purchase_order->total_quantity;
			    }
			    return $total_quantity ;
                break;

            case 'supplier_ref_no':
                $i = 1;
                $po_group_detail = $item->po_group_detail;
                if($po_group_detail->count() > 1 )
                {

                    $html_string = '
                        <a href="javascript:void(0)" data-toggle="modal" data-target="#Supplier'.$item->id.'">
                          <i class="fa fa-user-plus"></i>
                        </a>
                    ';

                    $html_string .= '
                    <div class="modal fade" id="Supplier'.@$item->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                      <div class="modal-dialog" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Supplier(s)</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                          <table class="bordered" style="width:100%;">
                                  <thead style="border:1px solid #eee;text-align:center;">
                                      <tr><th>S.No</th><th>Supplier #</th><th>Supplier Reference Name</th></tr>
                                  </thead>
                                  <tbody>';
                                  foreach ($po_group_detail as $p_g_d)
                                  {
                                      if($p_g_d->purchase_order->supplier_id != null)
                                      {
                                          $ref_no = $p_g_d->purchase_order->PoSupplier->reference_number;
                                          $name = $p_g_d->purchase_order->PoSupplier->reference_name;
                                      }
                                      else
                                      {
                                          $ref_no = $p_g_d->purchase_order->PoWarehouse->location_code;
                                          $name = $p_g_d->purchase_order->PoWarehouse->warehouse_title;
                                      }
                                    $html_string .= '<tr><td>'.$i.'</td><td>'.@$ref_no.'</td><td>'.@$name.'</td></tr>';
                                    $i++;
                                }
                                  $html_string .= '
                                  </tbody>
                          </table>

                          </div>
                        </div>
                      </div>
                    </div>
                    ';
                    return $html_string;
                }
                else
                {
                    if($item->po_group_detail[0]->purchase_order->supplier_id != null)
                      {
                          return $item->po_group_detail[0]->purchase_order->PoSupplier->reference_name;
                      }
                      else
                      {
                          return $item->po_group_detail[0]->purchase_order->PoWarehouse->warehouse_title;
                      }
                }
                break;

            case 'po_number':
                $i = 1;
		    	$po_group_detail = $item->po_group_detail;
		    	$po_id = [];
		    	foreach ($po_group_detail as $p_g_d)
		    	{
		    		array_push($po_id, $p_g_d->purchase_order->ref_id);
		    	}
		    	sort($po_id);
		        // return $item->po_group_detail !== null ? $po_id : "--" ;
		    	if(sizeof($po_id) > 1)
		    	{
			        $html_string = '
			        	<a href="javascript:void(0)" data-toggle="modal" data-target="#poNumberModal'.$item->id.'">
						  <i class="fa fa-tty"></i>
						</a>
			        ';

			        $html_string .= '
					<div class="modal fade" id="poNumberModal'.$item->id.'" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
					  <div class="modal-dialog" role="document">
					    <div class="modal-content">
					      <div class="modal-header">
					        <h5 class="modal-title" id="exampleModalLabel">PO Numbers</h5>
					        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
					          <span aria-hidden="true">&times;</span>
					        </button>
					      </div>
					      <div class="modal-body">
					      <table class="bordered" style="width:100%;">
					      		<thead style="border:1px solid #eee;text-align:center;">
					      			<tr><th>S.No</th><th>PO No.</th></tr>
					      		</thead>
					      		<tbody>';
					      		foreach ($po_group_detail as $p_g_d) {
			    		$html_string .= '<tr><td>'.$i.'</td><td>'.@ $p_g_d->purchase_order->ref_id.'</td></tr>';
			    		$i++;
			    	}
					  $html_string .= '
					      		</tbody>
					      </table>

					      </div>
					    </div>
					  </div>
					</div>
			        ';
			        return $html_string;
		        }
		        else
		        {
		        	return $item->po_group_detail !== null ? $po_id : "--" ;
		        }
                break;

            case 'id':
                if($item->ref_id != NULL){
                    if ($item->is_confirm == 0) {
                        $html_string = '<a href="'.url('warehouse/transfer-warehouse-products-receiving-queue', $item->id).'" data-id="' . $item->id . '"><b>'.$item->ref_id.'</b></a>';
                        return $html_string;
                    } else {
                        $html_string = '<a href="'.url('warehouse/warehouse-complete-transfer-products-receiving-queue', $item->id).'" data-id="' . $item->id . '"><b>'.$item->ref_id .'</b></a>';
                        return $html_string;
                    }
				}
				else{
					return "N.A";
				}
                break;
        }
    }

}
