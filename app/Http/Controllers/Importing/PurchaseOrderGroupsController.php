<?php

namespace App\Http\Controllers\Importing;

use App\Http\Controllers\Controller;
use App\Models\Common\Brand;
use App\Models\Common\Courier;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\ProductFixedPrice;
use App\Models\Common\ProductImage;
use App\Models\Common\ProductReceivingHistory;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrderDocument;
use App\Models\Common\PurchaseOrders\PoGroupStatusHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrderNote;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\Warehouse;
use App\Models\Common\Order\Order;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDF;
use Yajra\Datatables\Datatables;

class PurchaseOrderGroupsController extends Controller
{
    public function runScript()
    {
        // dd('hree');
        $all_groups = PoGroup::get();
        foreach ($all_groups as $group) {
        $po_group_details = PoGroupProductDetail::where('status',1)->where('po_group_id',$group->id)->first();
        // dd($po_group_details);
        if($po_group_details == null)
        { 

        $po_ids = PoGroupDetail::where('po_group_id',$group->id)->pluck('purchase_order_id')->toArray();
        // dd($po_ids);  
        foreach ($po_ids as $po_id) {
            $purchase_order = PurchaseOrder::find($po_id);
            $purchase_order->po_group_id = $group->id;
            $purchase_order->save();

            $purchase_order_details = PurchaseOrderDetail::where('po_id',$purchase_order->id)->whereNotNull('product_id')->get();
            foreach ($purchase_order_details as $p_o_d) {

                $po_group_product =  PoGroupProductDetail::where('status',1)->where('product_id',$p_o_d->product_id)->where('po_group_id',$group->id)->where('supplier_id',$purchase_order->supplier_id)->first();
                if($po_group_product != null)
                {
                    $po_group_product->quantity_ordered          += @$p_o_d->desired_qty;
                    $po_group_product->quantity_inv              += $p_o_d->quantity;
                    $po_group_product->import_tax_book_price     += $p_o_d->pod_import_tax_book_price;
                    $po_group_product->total_gross_weight        += $p_o_d->pod_total_gross_weight;
                    $po_group_product->total_unit_price_in_thb   += $p_o_d->unit_price_in_thb* $p_o_d->quantity;
                    $po_group_product->quantity_received_1       += $p_o_d->quantity_received ;
                    $po_group_product->quantity_received_2       += $p_o_d->quantity_received_2;
                    $po_group_product->trasnfer_qty_shipped      += $p_o_d->trasnfer_qty_shipped;
                    $po_group_product->save();
                }
                else
                {
                    $po_group_product = new PoGroupProductDetail;
                    $po_group_product->po_group_id               = $group->id;
                    if($purchase_order->supplier_id != null)
                    {
                    $po_group_product->supplier_id               = $purchase_order->supplier_id;
                    }
                    else
                    {
                    $po_group_product->from_warehouse_id         = $purchase_order->from_warehouse_id;
                    }
                    $po_group_product->to_warehouse_id           = $purchase_order->to_warehouse_id;
                    $po_group_product->product_id                = $p_o_d->product_id;
                    $po_group_product->quantity_ordered          = @$p_o_d->desired_qty;
                    $po_group_product->quantity_inv              = $p_o_d->quantity;
                    $po_group_product->import_tax_book           = $p_o_d->pod_import_tax_book;
                    $po_group_product->import_tax_book_price     = $p_o_d->pod_import_tax_book_price;
                    $po_group_product->total_gross_weight        = $p_o_d->pod_total_gross_weight;
                    $po_group_product->unit_price                = $p_o_d->pod_unit_price;
                    $po_group_product->currency_conversion_rate  = $p_o_d->currency_conversion_rate;
                    $po_group_product->unit_price_in_thb         = $p_o_d->unit_price_in_thb;
                    $po_group_product->total_unit_price_in_thb   = $p_o_d->unit_price_in_thb* $p_o_d->quantity;
                    $po_group_product->quantity_received_1       = $p_o_d->quantity_received ;
                    $po_group_product->expiration_date_1         = $p_o_d->expiration_date ;
                    $po_group_product->quantity_received_2       = $p_o_d->quantity_received_2;
                    $po_group_product->expiration_date_2         = $p_o_d->expiration_date_2;
                    $po_group_product->trasnfer_qty_shipped      = $p_o_d->trasnfer_qty_shipped;
                    $po_group_product->good_condition            = $p_o_d->good_condition;
                    $po_group_product->result                    = $p_o_d->result;
                    $po_group_product->good_type                 = $p_o_d->good_type;
                    $po_group_product->temperature_c             = $p_o_d->temperature_c;
                    $po_group_product->checker                   = $p_o_d->checker;
                    $po_group_product->problem_found             = $p_o_d->problem_found;
                    $po_group_product->solution                  = $p_o_d->solution;
                    $po_group_product->authorized_changes        = $p_o_d->authorized_changes;
                    $po_group_product->save();
                }
            }
        }
        }   

        }
        
        return response()->json(['success' => true]);
    }

	public function getProductSuppliersRecord($id)
    {
        $query = SupplierProducts::with('supplier','product')->where('product_id',$id)->get();
        
         return Datatables::of($query)
                        
            ->addColumn('action',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= 'd-none';
                } 
                else 
                {
                  $class= '';
                }
                $html_string = '<button type="button" style="cursor: pointer;" class="btn-xs btn-danger '.$class.'" data-prodisupid="'.$item->supplier->id.'" data-prodid="'.$item->product_id.'" name="delete_sup" id="delete_sup"><i class="fa fa-trash"></i></button>';
                return $html_string;
            })
            ->addColumn('company',function($item){
                $ref_no = $item->supplier->company;
                return  $html_string = '<a target="_blank" href="'.url('get-supplier-detail/'.$item->supplier->id).'"  >'.$ref_no.'</a>';

            })
            ->addColumn('product_supplier_reference_no',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="product_supplier_reference_no"  data-fieldvalue="'.$item->product_supplier_reference_no.'">'.($item->product_supplier_reference_no != NULL ? $item->product_supplier_reference_no : "--").'</span>
                <input type="text" style="width:100%;" name="product_supplier_reference_no" class="prodSuppFieldFocus d-none" value="'.$item->product_supplier_reference_no.'">';
                return $html_string;
            })
            ->addColumn('import_tax_actual',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="import_tax_actual"  data-fieldvalue="'.$item->import_tax_actual.'">'.($item->import_tax_actual != NULL ? $item->import_tax_actual : "--").'</span>
                <input type="number" style="width:100%;" name="import_tax_actual" class="prodSuppFieldFocus d-none" value="'.$item->import_tax_actual.'">';
                return $html_string;
            })
            ->addColumn('gross_weight',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="gross_weight"  data-fieldvalue="'.$item->gross_weight.'">'.($item->gross_weight != NULL ? $item->gross_weight : "--").'</span>
                <input type="number" style="width:100%;" name="gross_weight" class="prodSuppFieldFocus d-none" value="'.$item->gross_weight.'">';
                return $html_string;              
            })
            ->addColumn('freight',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="freight"  data-fieldvalue="'.$item->freight.'">'.($item->freight != NULL ? $item->freight : "--").'</span>
                <input type="number" style="width:100%;" name="freight" class="prodSuppFieldFocus d-none" value="'.$item->freight.'">';
                return $html_string;              
            })
            ->addColumn('landing',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="landing"  data-fieldvalue="'.$item->landing.'">'.($item->landing != NULL ? $item->landing : "--").'</span>
                <input type="number" style="width:100%;" name="landing" class="prodSuppFieldFocus d-none" value="'.$item->landing.'">';
                 return $html_string;
            })
            ->addColumn('buying_price',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="buying_price"  data-fieldvalue="'.$item->buying_price.'">'.($item->buying_price != NULL ? $item->buying_price : "--").'</span>
                <input type="number" style="width:100%;" name="buying_price" class="prodSuppFieldFocus d-none" value="'.$item->buying_price.'">';
                return $html_string;   
            })
            ->addColumn('leading_time',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="leading_time"  data-fieldvalue="'.$item->leading_time.'">'.($item->leading_time != NULL ? $item->leading_time : "--").'</span>
                <input type="number" style="width:100%;" name="leading_time" class="prodSuppFieldFocus d-none" value="'.$item->leading_time.'">';
                return $html_string;
            })
            ->setRowId(function ($item) {
                    return $item->id;
            })
             // greyRow is a custom style in style.css file
            ->setRowClass(function ($item) {
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                    return $item->supplier_id == $checkLastSupp->supplier_id ? 'greyRow' : '';
                }
            })
            ->rawColumns(['action','company','product_supplier_reference_no','import_tax_actual','buying_price','freight','landing','leading_time','gross_weight'])
            ->make(true);
    
    }
    public function getPoGroups()
    {
        // dd("hello");
        $query = PoGroup::where('is_confirm',1)->orderBy('id', 'ASC')->get();
        return Datatables::of($query)

		    ->addColumn('po_number',function($item){
			    	$po_group_detail = $item->po_group_detail;
			    	$po_id = [];
			    	foreach ($po_group_detail as $p_g_d) {
			    		array_push($po_id, $p_g_d->purchase_order_id);
			    	}
			    	sort($po_id);
			        return $item->po_group_detail !== null ? $po_id : "--" ;
		        })

		    
			->addColumn('bl_awb',function($item){
		            return $item->bill_of_landing_or_airway_bill !== null ? $item->bill_of_landing_or_airway_bill: "--" ;
		        })

		    ->addColumn('courier',function($item){
		            return $item->po_courier->title !== null ? $item->po_courier->title: "--" ;
		        })

		    ->addColumn('supplier',function($item){
		            $po_group_detail = $item->po_group_detail;
					$supplier = [];
					$preSup = '';
			    	foreach ($po_group_detail as $p_g_d) {
						array_push($supplier, $p_g_d->purchase_order->PoSupplier->company);
			    	}
					$new_sup = array_unique($supplier);
			        return $item->po_group_detail !== null ? $new_sup : "--" ;
		        })

		    ->addColumn('supplier_ref_no',function($item){
		            $po_group_detail = $item->po_group_detail;
			    	$supplier = [];
			    	foreach ($po_group_detail as $p_g_d) {
			    		array_push($supplier, $p_g_d->purchase_order->PoSupplier->reference_number);
			    	}
			    	sort($supplier);
			        return $item->po_group_detail !== null ? $supplier : "--" ;
		        })

		    ->addColumn('quantity',function($item){
		    	$po_group_detail = $item->po_group_detail;
			    $total_quantity = null;
			    foreach ($po_group_detail as $p_g_d) {
			    	$total_quantity += $p_g_d->purchase_order->total_quantity;
			    }
			    return $total_quantity ;
			})
			
			->addColumn('tax',function($item){
				$tax = $item->tax != null ? $item->tax : 'N.A';
				$html_string = '<span class="m-l-15 inputDoubleClick" id="tax" data-fieldvalue="'.$tax.'">
				'.$tax.'
				</span>';

				$html_string .= '<input type="number"  name="tax" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$tax.'" style="width:70%">';
		    	return $html_string;
			})
			
			->addColumn('freight',function($item){
				$freight = $item->freight != null ? $item->freight : 'N.A';
				$html_string = '<span class="m-l-15 inputDoubleClick" id="freight" data-fieldvalue="'.$freight.'">
				'.$freight.'
				</span>';

				$html_string .= '<input type="number"  name="freight" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$freight.'" style="width:70%">';
		    	return $html_string;
			})
			
			->addColumn('landing',function($item){
				$landing = $item->landing != null ? $item->landing : 'N.A';
				$html_string = '<span class="m-l-15 inputDoubleClick" id="landing" data-fieldvalue="'.$landing.'" >
				'.$landing.'
				</span>';

				$html_string .= '<input type="number"  name="landing" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$landing.'" style="width:70%">';
		    	return $html_string;
		    })

		    ->addColumn('net_weight',function($item){
		    	$po_group_detail = $item->po_group_detail;
		    	$weight = null;
		    	foreach ($po_group_detail as $p_g_d) {
		    		$weight += $p_g_d->purchase_order->total_gross_weight;
		    	}
		        return $weight ;
		    })

		    ->addColumn('issue_date',function($item){
		    	return '10/10/2019';
		    })

		    ->addColumn('po_total',function($item){
		    	$po_group_detail = $item->po_group_detail;
			    	$total = null;
			    	foreach ($po_group_detail as $p_g_d) {
			    		$total += $p_g_d->purchase_order->total;
			    	}
			        return number_format($total,3,'.',',') ;
		    })

		    ->addColumn('target_receive_date',function($item){
		        return $item->target_receive_date !== null ? $item->target_receive_date: "--" ;
		    })

		    ->addColumn('action', function ($item) {
				
                $html_string = '
                 <a href="'.url('importing/products-received',$item->id).'" class="actionicon viewIcon" data-id="' . $item->id . '" title="View"><i class="fa fa-eye"></i></a>
                 ';
                return $html_string;
            })

            ->addColumn('warehouse',function($item){
		            return $item->ToWarehouse !== null ? $item->ToWarehouse->name: "--" ;
		        })

		    ->rawColumns(['po_number','bill_of_lading','airway_bill','tax','freight','landing','bl_awb','courier','vendor','vendor_ref_no','action'])

		    ->make(true);
    }

    public function inCompletedPoGroups()
    {       
        return $this->render('importing.home.incompleted-groups');
    }

    public function getInCompletedPoGroupsData(Request $request)
    {
        $is_con = $request->dosortby;
        // $query = PoGroup::where('is_review',$is_con)->orderBy('id', 'DESC');

        $query = PoGroup::where('is_review',$is_con)->whereHas('po_group_detail', function($q){
            $q->whereHas('purchase_order', function($q) {
                $q->where('purchase_orders.supplier_id','!=', NULL);                
            });
        })->orderBy('id', 'DESC');

        // dd($query->count());

        if($request->from_date != null)
        {
           $query->where('target_receive_date', '>=', $request->from_date);
        }
        if($request->to_date != null)
        {
           $query->where('target_receive_date', '<=', $request->to_date);
        }
        
        return Datatables::of($query)

            ->addColumn('id', function($item){
                return $item->ref_id != NULL ? $item->ref_id : "N.A" ;
            })

		    ->addColumn('po_number',function($item){
                $i = 1;
                $po_group_detail = $item->po_group_detail;
                $po_id = [];
                foreach ($po_group_detail as $p_g_d) {
                     if(!in_array($p_g_d->purchase_order->ref_id, $po_id, true)){
                        array_push($po_id, $p_g_d->purchase_order->ref_id);
                    }
                    // array_push($po_id, $p_g_d->purchase_order->ref_id);
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
                })
		    
			->editColumn('bill_of_landing_or_airway_bill',function($item){
                if($item->is_review == 0)
                {
                    $bill_of_landing_or_airway_bill = $item->bill_of_landing_or_airway_bill != null ? $item->bill_of_landing_or_airway_bill : 'N.A';
                    $html_string = '<span class="m-l-15 inputDoubleClick" id="bill_of_landing_or_airway_bill" data-fieldvalue="'.$bill_of_landing_or_airway_bill.'">
                    '.$bill_of_landing_or_airway_bill.'
                    </span>';

                    $html_string .= '<input type="text"  name="bill_of_landing_or_airway_bill" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$bill_of_landing_or_airway_bill.'" style="width:70%">';
                    return $html_string;
                }
                else{
                    return $item->bill_of_landing_or_airway_bill !== null ? $item->bill_of_landing_or_airway_bill: "N.A" ;
                }
            
		        })

		    ->addColumn('courier',function($item){
		    	if($item->is_review == 1){
		    		return $item->po_courier !== null ? $item->po_courier->title: "N.A" ;;
		    	}
		    	$couriers = Courier::select('id','title')->get();
	            $title = $item->po_courier !== null ? $item->po_courier->title: "N.A" ;
	            $html_string = '<span class="m-l-15 inputDoubleClick" data-fieldvalue="'.@$item->po_courier->title.'">';
                    $html_string .= $title;
                    $html_string .= '</span>';
                $html_string .= '<select name="courier" class="select-common form-control d-none" data-id="'.$item->id.'">
                    <option>Choose Courier</option>';
                     if($couriers){
                        foreach($couriers as $courier)
                        {
                            $html_string .= '<option value="'.$courier->id.'"> '.$courier->title.'</option>';
                        }
                        }
                    $html_string .= '</select>';    
                    return $html_string;    
		        })
            ->filterColumn('courier', function( $query, $keyword ) {
                $query = $query->whereIn('courier', Courier::select('id')->where('title','LIKE',"%$keyword%")->pluck('id'));
            },true )

		    ->addColumn('supplier_ref_no',function($item){
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
                })

		    ->addColumn('quantity',function($item){
		    	$po_group_detail = $item->po_group_detail;
			    $total_quantity = null;
			    foreach ($po_group_detail as $p_g_d) {
			    	$total_quantity += $p_g_d->purchase_order->total_quantity;
			    }

			    return round($total_quantity,3) ;

			})
			
			->addColumn('tax',function($item){
				$tax = $item->tax != null ? $item->tax : 'N.A';
				$html_string = '<span class="m-l-15" id="tax" data-fieldvalue="'.$tax.'">
				'.$tax.'
				</span>';

				$html_string .= '<input type="number"  name="tax" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$tax.'" style="width:70%">';
		    	return $html_string;
			})
			
			->addColumn('freight',function($item){
				$freight = $item->freight != null ? $item->freight : 'N.A';
				$html_string = '<span class="m-l-15" id="freight" data-fieldvalue="'.$freight.'">
				'.$freight.'
				</span>';

				$html_string .= '<input type="number"  name="freight" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$freight.'" style="width:70%">';
		    	return $html_string;
			})
			
			->addColumn('landing',function($item){
				$landing = $item->landing != null ? $item->landing : 'N.A';
				$html_string = '<span class="m-l-15" id="landing" data-fieldvalue="'.$landing.'" >
				'.$landing.'
				</span>';

				$html_string .= '<input type="number"  name="landing" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$landing.'" style="width:70%">';
		    	return $html_string;
		    })

		    ->addColumn('net_weight',function($item){
		    	$po_group_detail = $item->po_group_detail;
		    	$weight = null;
		    	foreach ($po_group_detail as $p_g_d) {
		    		$weight += $p_g_d->purchase_order->total_gross_weight;
		    	}
		        return number_format($weight,2,'.',',') ;
		    })

		    ->addColumn('issue_date',function($item){
				$created_at = Carbon::parse($item->created_at)->format('Y-m-d');
		    	return $created_at;
		    })

		    ->addColumn('po_total',function($item){
		    	$po_group_detail = $item->po_group_detail;
			    	$total = null;
			    	foreach ($po_group_detail as $p_g_d) {
			    		$total += $p_g_d->purchase_order->total_in_thb;
			    	}
			        return number_format($total,3,'.',',');
		    })

		    ->addColumn('target_receive_date',function($item){
		    	if($item->is_review == 0){
		    	$target_receive_date = $item->target_receive_date != null ? $item->target_receive_date : 'N.A';
				$html_string = '<span class="m-l-15 inputDoubleClick" id="target_receive_date" data-fieldvalue="'.$target_receive_date.'">
				'.$target_receive_date.'
				</span>';

				$today = Carbon::now();
				$today = date('Y-m-d');
				if($target_receive_date == 'N.A')
				{
					$target_receive_date = '';
				}
				$html_string .= '<input type="date"  name="target_receive_date" min="'.$today.'" data-id="'.$item->id.'" class="fieldFocus d-none" value="'.$target_receive_date.'" style="width:70%">';
		    	return $html_string;
		    	}
		    	else{
		        	return $item->target_receive_date !== null ? $item->target_receive_date: "--" ;
		    	}

		    })

		    ->addColumn('action', function ($item) use ($is_con) {
				if($is_con == 0){
                $html_string = '
                 <a href="'.url('importing/products-receiving-queue',$item->id).'" class="actionicon viewIcon" data-id="' . $item->id . '" title="View"><i class="fa fa-eye"></i></a>
                 ';		
				}else if($is_con == 1){
					$html_string = '
                 <a href="'.url('importing/products-received',$item->id).'" class="actionicon viewIcon" data-id="' . $item->id . '" title="View"><i class="fa fa-eye"></i></a>
                 ';
				}
                return $html_string;
            })

            ->addColumn('warehouse',function($item){
	            return $item->ToWarehouse !== null ? $item->ToWarehouse->warehouse_title: "--" ;
	        })

		    ->rawColumns(['po_number','bill_of_lading','airway_bill','target_receive_date','tax','freight','landing','bill_of_landing_or_airway_bill','courier','vendor','vendor_ref_no','action','supplier_ref_no','id'])

		    ->make(true);
    }

	public function getDetailsOfPo($id)
    {
		$po_group = PoGroup::find($id);
		$all_record = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*', 'po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$id)
            ->whereNotNull('purchase_order_details.product_id')
            ->get();

            // dd($all_record);
		$po_group_detail = $po_group->po_group_detail;
			    	$po_ids = [];
			    	foreach ($po_group_detail as $p_g_d) {
			    		array_push($po_ids, $p_g_d->purchase_order_id);
			    	}
					sort($po_ids);
					
					$allData = [];
					foreach ($po_ids as $po) {
						$sup = PurchaseOrder::where('id',$po)->first();
			    		array_push($allData, $sup);
					}
					sort($allData);
		
		return Datatables::of($all_record)

		    ->addColumn('po_number',function($item){
		    	// dd($item);
		    	return  $html_string = '<a target="_blank" href="'.url('importing/get-single-po-detail/'.$item->po_id).'"  >'.$item->ref_id.'</a>';
			        // return $item->po_id !== null ? $item->po_id : "--" ;
		    })

			->addColumn('supplier',function($item){
                if($item->supplier_id !== NULL)
                {                
    				$sup_name = Supplier::where('id',$item->supplier_id)->first();
    				return  $html_string = '<a target="_blank" href="'.url('common/get-common-supplier-detail/'.$item->supplier_id).'"  >'.$sup_name->reference_name.'</a>';
                }
                else
                {
                    $sup_name = Warehouse::where('id',$item->from_warehouse_id)->first();
                    return  $html_string = $sup_name->warehouse_title;
		            // return $sup_name->company != null ? $sup_name->company :"--" ;
                }
		    })

		    ->addColumn('reference_number',function($item){
                if($item->supplier_id !== NULL)
                {
                    $sup_name = SupplierProducts::where('supplier_id',$item->supplier_id)->where('product_id',$item->product_id)->first();
                    return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
                }
                else
                {
                    return "N.A";
                }
		    	
		    })

		    ->addColumn('prod_reference_number',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
                return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  >'.$product->refrence_code.'</a>';
		    })

		    ->addColumn('desc',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
				return $product->short_desc != null ? $product->short_desc : '' ;
		    })

		    ->addColumn('buying_price',function($item){
				return $item->pod_unit_price != null ?number_format($item->pod_unit_price,3,'.',','): '' ;
		    })

		    ->addColumn('buying_price_in_thb',function($item){
				return $item->unit_price_in_thb != null ? number_format($item->unit_price_in_thb,3,'.',','): '' ;
		    })

		    ->addColumn('total_buying_price',function($item){
				return $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb,3,'.',','): '' ;
		    })

		    ->addColumn('import_tax_book',function($item){
                $html_string = '<input type="number"  name="pod_import_tax_book" data-id="'.$item->id.'" data-fieldvalue="" class="fieldFocus" value="'.number_format($item->pod_import_tax_book,2,'.','').'" readonly disabled style="width:100%">';
                return $html_string;
		    })	    

		    ->addColumn('freight',function($item){		    	
		    	$freight = $item->pod_freight;
				return number_format($freight,2,'.',',');
		    })

		    ->addColumn('landing',function($item){
		    	$landing = $item->pod_landing;
				return number_format($landing,2,'.',',');
		    })

		    ->addColumn('book_tax',function($item){
		    	$import_tax = $item->pod_import_tax_book;
		    	$total_price = $item->total_unit_price_in_thb;

		    	$book_tax = (($import_tax*$total_price)/100);
                if($book_tax != 0)
                {                    
				    return number_format($book_tax,2,'.',',');
                }
                else
                {
                    $count = PurchaseOrderDetail::whereIn('po_id',PoGroupDetail::where('po_group_id',$item->po_group_id)->pluck('purchase_order_id'))->count();
                    $book_tax = (1/$count)* $item->total_unit_price_in_thb;
                    return number_format($book_tax,2,'.',',');
                }
		    })

		    ->addColumn('weighted',function($item){
		    	$total_import_tax = $item->po_group_import_tax_book;
                if($total_import_tax == 0){
                    return "--";
                }
                else
                {
		    	$import_tax = $item->pod_import_tax_book;
		    	$total_price = $item->total_unit_price_in_thb;

		    	$book_tax = (($import_tax*$total_price)/100);

		    	$weighted = (($book_tax/$total_import_tax)*100);

				return number_format($weighted,2,'.',',').'%';
                }
		    })

		    ->addColumn('actual_tax',function($item){
		    	$total_import_tax = $item->po_group_import_tax_book;
                if($total_import_tax == 0){
                    return "--";
                }
                else
                {
		    	$import_tax = $item->pod_import_tax_book;
		    	$total_price = $item->total_unit_price_in_thb;

		    	$book_tax = (($import_tax*$total_price)/100);

		    	$weighted = ($book_tax/$total_import_tax);
		    	$tax = $item->tax;
				return number_format(($weighted*$tax),2,'.',',');
                }
		    })

		    ->addColumn('actual_tax_percent',function($item){
		    	$actual_tax_percent = $item->pod_actual_tax_percent;
				return number_format($actual_tax_percent,2,'.',',').'%';
		    })

		    ->addColumn('unit',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
				return $product->units->title != null ? $product->units->title : '';			    
		    })

		    ->addColumn('buying_currency',function($item){
                if($item->supplier_id !== NULL)
                {
                    $supplier = Supplier::where('id',$item->supplier_id)->first();
                    return $supplier->getCurrency->currency_code != null ? $supplier->getCurrency->currency_code : '';    
                }
                else
                {
                    return "N.A";
                }
		    	
		    })

            ->addColumn('qty_ordered',function($item){
                if($item->order_product_id != null)
                {
                    $order_product = OrderProduct::find($item->order_product_id);
                    return number_format($order_product->quantity,3,'.',',');
                }
                else
                {
                    return '--';
                }
            })

		    ->addColumn('qty',function($item){
		    	return number_format($item->quantity,3,'.',','); 
		    })

		    ->addColumn('pod_total_gross_weight',function($item){		    	
		    	$pod_total_gross_weight = $item->pod_total_gross_weight != null ? $item->pod_total_gross_weight : 0 ;

				$html_string = '<input type="number"  name="pod_total_gross_weight" data-id="'.$item->id.'" data-fieldvalue="'.$pod_total_gross_weight.'" class="fieldFocus" value="'. number_format($pod_total_gross_weight,2,'.','').'" readonly disabled style="width:100%">';
		    	return $html_string;
		    })

		    ->addColumn('pod_total_extra_cost',function($item){		    	
		    	$pod_total_extra_cost = $item->pod_total_extra_cost != null ? $item->pod_total_extra_cost : 0 ;

				$html_string = '<input type="number"  name="pod_total_extra_cost" data-id="'.$item->id.'" data-fieldvalue="'.$pod_total_extra_cost.'" class="fieldFocus" value="'. number_format($pod_total_extra_cost,2,'.','').'" readonly disabled style="width:100%">';
		    	return $html_string;
		    })

		    ->addColumn('currency_conversion_rate',function($item){		    	
		    	$currency_conversion_rate = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0 ;

				$html_string = '<input type="number"  name="currency_conversion_rate" data-id="'.$item->id.'" data-fieldvalue="'.$currency_conversion_rate.'" class="fieldFocus" value="'. $currency_conversion_rate.'" readonly disabled style="width:100%">';
		    	return $html_string;
		    })
			
		    ->rawColumns(['po_number','supplier','reference_number','import_tax_book','desc','kg','pod_total_gross_weight','pod_total_extra_cost','currency_conversion_rate','qty','prod_reference_number'])

		    ->make(true);
    }

    public function getProductDetail($id)
    {
        $product_type = ProductType::all();
        $product_brand = Brand::all();
        $product = Product::with('def_or_last_supplier', 'units', 'productCategory','supplier_products')->where('id',$id)->first();

        $CustomerTypeProductMargin = CustomerTypeProductMargin::with('margins')->where('product_id',$id)->get();

        $hotelProductMargin = CustomerTypeProductMargin::with('margins')->where('product_id',$id)->where('customer_type_id',1)->get();

        $resturantProductMargin = CustomerTypeProductMargin::with('margins')->where('product_id',$id)->where('customer_type_id',2)->get();

        $CustomerTypeProductMarginCount = CustomerTypeProductMargin::with('margins')->where('product_id',$id)->count();

        $ProductCustomerFixedPrices = ProductCustomerFixedPrice::with('customers','products')->where('product_id',$id)->orderBy('id', 'ASC')->get();

        $productImages = ProductImage::where('product_id',$id)->orderBy('id', 'ASC')->get();
        $productImagesCount = ProductImage::select('image','product_id')->where('product_id',$id)->count();
        
        $primaryCategory = ProductCategory::where('parent_id',0)->get();

        $product_supplier = Product::where('id',$id)->first();
        $last_or_def_supp_id = @$product_supplier->supplier_id;

        if($last_or_def_supp_id != 0)
        {
            $default_or_last_supplier = SupplierProducts::where('product_id',$id)->where('supplier_id',$last_or_def_supp_id)->first();
            $supplier_name = Supplier::select('company')->where('id',$last_or_def_supp_id)->first();
            $supplier_company = @$supplier_name->company;
        }

        $checkLastOrDefaultSupplier = Product::where('id',$id)->where('supplier_id','!=',0)->count();
        $product_fixed_price = ProductFixedPrice::where("product_id",$id)->get();
        $warehouses = User::where('role_id', '=','6')->whereNull('parent_id')->orderBy('id','ASC')->get();

        $stock_card = PurchaseOrderDetail::where('product_id',$id)->get();
                $countOfProductSuppliers = SupplierProducts::where('product_id',$id)->count();

        $total_buy_unit_calculation = SupplierProducts::where('product_id',$id)->where('supplier_id',$last_or_def_supp_id)->pluck('import_tax_actual')->first();
        if($total_buy_unit_calculation != NULL)
        {
            $IMPcalculation = 'Import Tax Actual + Buying Price + Frieght + Landing';
        }
        elseif($product->import_tax_book != null)
        {
            $IMPcalculation = 'Import Tax Book + Buying Price + Frieght + Landing';
        }
        else
        {
            $IMPcalculation = 'Import Tax Book + Buying Price + Frieght + Landing';
        }
        //dd($stock_card);

        return view('importing.products.product-detail',compact('product_type','product','CustomerTypeProductMargin','ProductCustomerFixedPrices','default_or_last_supplier','supplier_company','productImages','productImagesCount','primaryCategory','checkLastOrDefaultSupplier','CustomerTypeProductMarginCount','hotelProductMargin','resturantProductMargin','product_fixed_price','id','product_brand','warehouses','stock_card','countOfProductSuppliers','IMPcalculation'));
    }

    public function getProductSuppliersData($id)
    {
    	// dd($id);
        $query = SupplierProducts::with('supplier','product')->where('product_id',$id)->get();
        
         return Datatables::of($query)
                        
            ->addColumn('action',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= 'd-none';
                } 
                else 
                {
                  $class= '';
                }
                $html_string = '<button type="button" style="cursor: pointer;" class="btn-xs btn-danger '.$class.'" data-prodisupid="'.$item->supplier->id.'" data-prodid="'.$item->product_id.'" name="delete_sup" id="delete_sup"><i class="fa fa-trash"></i></button>';
                return $html_string;
            })
            ->addColumn('company',function($item){
                return $item->supplier->company;
                // return  $html_string = '<a target="_blank" href="'.url('importing/get-supplier-detail/'.$item->supplier->id).'"  >'.$ref_no.'</a>';

            })
            ->addColumn('product_supplier_reference_no',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="product_supplier_reference_no"  data-fieldvalue="'.$item->product_supplier_reference_no.'">'.($item->product_supplier_reference_no != NULL ? $item->product_supplier_reference_no : "--").'</span>
                <input type="text" style="width:100%;" name="product_supplier_reference_no" class="prodSuppFieldFocus d-none" value="'.$item->product_supplier_reference_no.'">';
                return $html_string;
            })
            ->addColumn('import_tax_actual',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="import_tax_actual"  data-fieldvalue="'.$item->import_tax_actual.'">'.($item->import_tax_actual != NULL ? $item->import_tax_actual : "--").'</span>
                <input type="number" style="width:100%;" name="import_tax_actual" class="prodSuppFieldFocus d-none" value="'.$item->import_tax_actual.'">';
                return $html_string;
            })
            ->addColumn('gross_weight',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="gross_weight"  data-fieldvalue="'.$item->gross_weight.'">'.($item->gross_weight != NULL ? $item->gross_weight : "--").'</span>
                <input type="number" style="width:100%;" name="gross_weight" class="prodSuppFieldFocus d-none" value="'.$item->gross_weight.'">';
                return $html_string;              
            })
            ->addColumn('freight',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="freight"  data-fieldvalue="'.$item->freight.'">'.($item->freight != NULL ? $item->freight : "--").'</span>
                <input type="number" style="width:100%;" name="freight" class="prodSuppFieldFocus d-none" value="'.$item->freight.'">';
                return $html_string;              
            })
            ->addColumn('landing',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="landing"  data-fieldvalue="'.$item->landing.'">'.($item->landing != NULL ? $item->landing : "--").'</span>
                <input type="number" style="width:100%;" name="landing" class="prodSuppFieldFocus d-none" value="'.$item->landing.'">';
                 return $html_string;
            })
            ->addColumn('buying_price',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="buying_price"  data-fieldvalue="'.$item->buying_price.'">'.($item->buying_price != NULL ? $item->buying_price : "--").'</span>
                <input type="number" style="width:100%;" name="buying_price" class="prodSuppFieldFocus d-none" value="'.$item->buying_price.'">';
                return $html_string;   
            })
            ->addColumn('leading_time',function($item){
                $class='';
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                  $class= '';
                } 
                else 
                {
                  $class= 'prodSuppInputDoubleClick';
                }
                $html_string = '<span class="m-l-15 '.$class.' " id="leading_time"  data-fieldvalue="'.$item->leading_time.'">'.($item->leading_time != NULL ? $item->leading_time : "--").'</span>
                <input type="number" style="width:100%;" name="leading_time" class="prodSuppFieldFocus d-none" value="'.$item->leading_time.'">';
                return $html_string;
            })
            ->setRowId(function ($item) {
                    return $item->id;
            })
             // greyRow is a custom style in style.css file
            ->setRowClass(function ($item) {
                $checkLastSupp = Product::find($item->product_id);
                if($item->supplier_id == $checkLastSupp->supplier_id)
                {
                    return $item->supplier_id == $checkLastSupp->supplier_id ? 'greyRow' : '';
                }
            })
            ->rawColumns(['action','company','product_supplier_reference_no','import_tax_actual','buying_price','freight','landing','leading_time','gross_weight'])
            ->make(true);
    
    }

    public function getDetailsOfCompletedPo($id)
    {
		$po_group = PoGroup::find($id);
		$all_record = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*', 'po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$id)
            ->whereNotNull('purchase_order_details.product_id')
            ->get();

		return Datatables::of($all_record)

		    ->addColumn('po_number',function($item){
			        return  $html_string = '<a target="_blank" href="'.url('importing/get-single-po-detail/'.$item->po_id).'"  >'.$item->ref_id.'</a>';
		        })
			->addColumn('supplier',function($item){
                if($item->supplier_id !== NULL)
                { 
				    $sup_name = Supplier::where('id',$item->supplier_id)->first();
				    return  $html_string = '<a target="_blank" href="'.url('common/get-common-supplier-detail/'.$item->supplier_id).'"  >'.$sup_name->company.'</a>';
		            // return $sup_name->company != null ? $sup_name->company :"--" ;
                }
                else
                {
                    $sup_name = Warehouse::where('id',$item->from_warehouse_id)->first();
                    return  $html_string = $sup_name->warehouse_title;
                    // return $sup_name->company != null ? $sup_name->company :"--" ;
                }
		        })

		    ->addColumn('reference_number',function($item){
                if($item->supplier_id !== NULL)
                {
		    	    $sup_name = SupplierProducts::where('supplier_id',$item->supplier_id)->where('product_id',$item->product_id)->first();
		            return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no :"--" ;
                }
                else
                {
                    return "N.A";
                }
		    })

		    ->addColumn('prod_reference_number',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
                return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  >'.$product->refrence_code.'</a>';
		    })

		    ->addColumn('desc',function($item){
		    	$product = Product::where('id',$item->product_id)->first();
				return $product->short_desc != null ? $product->short_desc : '' ;
		    })

		    ->addColumn('buying_price',function($item){
				return $item->pod_unit_price != null ? number_format($item->pod_unit_price,3,'.',','): '' ;
		    })

            ->addColumn('currency_conversion_rate',function($item){             
                $currency_conversion_rate = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0 ;
                return $currency_conversion_rate;
            })

            ->addColumn('buying_price_in_thb',function($item){
                return $item->unit_price_in_thb != null ? number_format($item->unit_price_in_thb,3,'.',','): '' ;
            })

		    ->addColumn('total_buying_price',function($item){
				return $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb,3,'.',','): '' ;
		    })

		    ->addColumn('import_tax_book',function($item){
				return $item->pod_import_tax_book != null ? $item->pod_import_tax_book.'%' : '' ;
		    })	    

		    ->addColumn('freight',function($item){
		    	
		    	$freight = $item->pod_freight;

				return number_format($freight,2,'.',',');
		    })

		    ->addColumn('landing',function($item){

		    	$landing = $item->pod_landing;

				return number_format($landing,2,'.',',');
		    })

		    ->addColumn('book_tax',function($item){
		    	$import_tax = $item->pod_import_tax_book;
                $total_price = $item->total_unit_price_in_thb;

                $book_tax = (($import_tax*$total_price)/100);
                if($book_tax != 0)
                {                    
                    return number_format($book_tax,2,'.',',');
                }
                else
                {
                    $count = PurchaseOrderDetail::whereIn('po_id',PoGroupDetail::where('po_group_id',$item->po_group_id)->pluck('purchase_order_id'))->count();
                    $book_tax = (1/$count)* $item->total_unit_price_in_thb;
                    return number_format($book_tax,2,'.',',');
                }
		    })

		    ->addColumn('weighted',function($item){
                $total_import_tax = $item->po_group_import_tax_book;
                if($total_import_tax == 0){
                    return "--";
                }
                else
                {
                $import_tax = $item->pod_import_tax_book;
                $total_price = $item->total_unit_price_in_thb;

                $book_tax = (($import_tax*$total_price)/100);

                $weighted = (($book_tax/$total_import_tax)*100);

                return number_format($weighted,2,'.',',').'%';
                }
            })

		    ->addColumn('actual_tax',function($item){
                $total_import_tax = $item->po_group_import_tax_book;
                if($total_import_tax == 0){
                    return "--";
                }
                else
                {
                $import_tax = $item->pod_import_tax_book;
                $total_price = $item->total_unit_price_in_thb;

                $book_tax = (($import_tax*$total_price)/100);

                $weighted = ($book_tax/$total_import_tax);
                $tax = $item->tax;
                return number_format(($weighted*$tax),2,'.',',');
                }
            })

		    ->addColumn('actual_tax_percent',function($item){

		    	$actual_tax_percent = $item->pod_actual_tax_percent;
				return number_format($actual_tax_percent,2,'.',',').'%';
		    })

		    ->addColumn('kg',function($item){
		    	$product = Product::where('id',$item->product_id)->first();

				return $product->units->title != null ? $product->units->title : '';
			    
		    })

            ->addColumn('qty_ordered',function($item){
                if($item->order_product_id != null)
                {
                    $order_product = OrderProduct::find($item->order_product_id);
                    return number_format($order_product->quantity,3,'.',',');
                }
                else
                {
                    return '--';
                }
            })

		    ->addColumn('qty',function($item){
                return number_format($item->quantity,3,'.',',');
		    })

            ->addColumn('pod_total_gross_weight',function($item){               
                $pod_total_gross_weight = $item->pod_total_gross_weight != null ? number_format( $item->pod_total_gross_weight ,2,'.',','): 0 ;
                return $pod_total_gross_weight;
            })

            ->addColumn('pod_total_extra_cost',function($item){             
                $pod_total_extra_cost = $item->pod_total_extra_cost != null ?number_format( $item->pod_total_extra_cost,2,'.',',') : 0 ;
                return $pod_total_extra_cost;
            })
			
		    ->rawColumns(['po_number','supplier','reference_number','desc','currency_conversion_rate','kg','qty','pod_total_gross_weight','pod_total_extra_cost','qty_receive','prod_reference_number'])

		    ->make(true);
    }

    public function productReceivingQueue($id)
    {
		$po_group = PoGroup::find($id);
		$po_group_detail = $po_group->po_group_detail;
		$product_receiving_history = ProductReceivingHistory::with('get_user')->where('updated_by',$this->user->id)->where('po_group_id',$id)->get();
        $status_history = PoGroupStatusHistory::with('get_user')->where('po_group_id',$id)->get();
$group_detail = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*','po_groups.target_receive_date as datee','po_group_details.*','purchase_orders.*','purchase_order_details.*')->where('po_groups.id',$id)->where('purchase_order_details.quantity','!=',0)
            ->count();
            // dd($group_detail);
        return $this->render('importing.products.products-receiving',compact('po_group','id','product_receiving_history','status_history','group_detail')); 
	}

	public function exportGroupToPDF(Request $request)
    {
        // dd('here');
    	$group_detail = DB::table('po_groups')
            ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
            ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
            ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->select('po_groups.*', 'po_group_details.*','purchase_orders.*','purchase_order_details.*')->whereNotNull('purchase_order_details.product_id')->where('po_groups.id',$request->po_group_id)->where('purchase_order_details.quantity','!=',0)
            ->get();
            //dd($group_detail);
        $pdf = PDF::loadView('importing.products.print_pdf',compact('group_detail'))->setPaper('a4', 'landscape');

        // making pdf name starts
        $makePdfName = 'Group No-'.$request->po_group_id;        
        return $pdf->download($makePdfName.'.pdf');
    }

    public function exportGroupToPDFF(Request $request)
    {
        // dd($request->all());
    	// $group_detail = DB::table('po_groups')
     //        ->join('po_group_details', 'po_groups.id', '=', 'po_group_details.po_group_id')
     //        ->join('purchase_orders', 'po_group_details.purchase_order_id', '=', 'purchase_orders.id')
     //        ->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
     //        ->select('po_groups.*','po_groups.target_receive_date as datee','po_group_details.*','purchase_orders.*','purchase_order_details.*')->whereNotNull('purchase_order_details.product_id')->where('po_groups.id',$request->po_group_id)->where('purchase_order_details.quantity','!=',0)
     //        ->get();
            // dd($group_detail);
        // $pdf = PDF::loadView('importing.products.print_pdf2',compact('group_detail'))->setPaper('a4', 'landscape');
        $group_detail = PoGroupProductDetail::where('status',1)->where('po_group_id',$request->po_group_id)->get();

        $pdf = PDF::loadView('importing.products.print_pdf3',compact('group_detail'))->setPaper('a4', 'landscape');

        // making pdf name starts
        $makePdfName = 'Group No-'.$request->po_group_id;        
        return $pdf->download($makePdfName.'.pdf');

    }

    public function pickInstruction(){
         // dd('here');
        return $this->render('importing.pick-instruction.index');
    }

    public function getDratInvoices(Request $request)
    {
        $query = Order::with('customer')->where('primary_status', 2)->whereHas('user',function($q){
            $q->whereHas('get_warehouse',function($qu){
                $qu->where('id',Auth::user()->get_warehouse->id);
            });
        })->orderBy('id', 'DESC');

        if($request->orders_status != '' && (int)$request->orders_status)
        {
            $query->where('status', $request->orders_status)->orderBy('id', 'DESC');
        }
        else
        {
            $query->orderBy('id', 'DESC');
        }   
        return Datatables::of($query)

        ->addColumn('customer', function ($item) { 
            if($item->customer_id != null){
                if($item->customer['company'] != null)
                {
                    $ref_no = $item->customer !== null ? $item->customer->company : "--" ;
                    return  $html_string = '<a target="_blank" href="'.route('get-common-customer-detail',$item->customer->id).'"  >'.$ref_no.'</a>';
                }
                else
                {
                    $html_string = $item->customer->reference_name;
                }
            }
            else{
                $html_string = 'N.A';
            }            
           
            return $html_string;         
        })

        ->addColumn('user_id',function($item){
            return ($item->user_id != null ? $item->user->name : "N.A");
        })

        ->addColumn('customer_ref_no',function($item){
            return $item->customer->reference_number;
        })

        ->addColumn('target_ship_date',function($item){
            return Carbon::parse(@$item->target_ship_date)->format('d/m/Y');
        })

        ->addColumn('status',function($item){
            $html = '<span class="sentverification">'.$item->statuses->title.'</span>';
            return $html;
        })

        ->addColumn('ref_id', function($item) { 
        $ref_no = $item->ref_id !== null ? $item->ref_id : "--" ;
        return  $html_string = '<a target="_blank" href="'.route('get-order-detail',$item->id).'"  >'.$ref_no.'</a>';
            return ($item->user_ref_id !== null ? $item->user_ref_id : $item->ref_id);

        })
       
        ->addColumn('invoice_date', function($item) { 
    
            return Carbon::parse(@$item->updated_at)->format('d/m/Y');

        })

        ->addColumn('total_amount', function($item) { 
    
            return number_format($item->total_amount,3,'.',',');
        })

        ->addColumn('action', function ($item) { 
        $html_string = '<a href="'.url('importing/pick-instruction', ['id' => $item->id]).'" title="View Pick Instruction" class="actionicon viewIcon"><i class="fa fa-eye"></i>';               
        return $html_string;         
        })

        ->rawColumns(['action','ref_id', 'customer', 'number_of_products','status'])
        ->make(true);

    }

    public function pickInstructionDetail($id){
        $order = Order::find($id);
        // dd($order);
        return $this->render('importing.pick-instruction.pickInstruction',compact('order'));
    }

    public function getPickInstruction($id)
    {
        //dd($request->all());
        $query = OrderProduct::with('get_order')->where('order_id', $id)->whereNotNull('product_id')->orderBy('id', 'ASC')->get();
            
        return Datatables::of($query)

        ->addColumn('item_no', function ($item) { 
            return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product->id).'"  >'.$item->product->refrence_code.'</a>';

        })

        ->addColumn('description',function($item){
            $html_string = $item->product != null ? $item->product->short_desc : 'N.A';
           
            return $html_string; 
        })

        ->addColumn('location_code',function($item){
            return 34;
        })

        ->addColumn('pcs_ordered',function($item){
            $html_string = $item->number_of_pieces != null ? $item->number_of_pieces : 'N.A';
           
            return $html_string;
        })

        ->addColumn('qty_ordered', function($item) {             
            $html_string = $item->quantity != null ? $item->quantity : 'N.A';
           
            return $html_string;
        })
       
        ->addColumn('unit_of_measure', function($item) { 
        $html_string = $item->product != null ? $item->product->sellingUnits->title : 'N.A';
           
            return $html_string; 

        })

        ->addColumn('qty_to_ship', function($item) { 
    
            return 0;
        })

        ->addColumn('unit_price', function ($item) { 
            $html_string = $item->product != null ? number_format($item->unit_price,3,'.',','): 'N.A';
           
            return $html_string;       
        })

        ->addColumn('pcs_shipped', function ($item) { 
            if($item->status == 10){
            $html_string = '<input type="number"  name="pcs_shipped" data-id="'.$item->id.'" class="fieldFocus" value="'.$item->pcs_shipped.'" readonly disabled style="width:50%">';
            }
            else{
                $html_string = $item->pcs_shipped;
            }
            return $html_string;      
        })

        ->addColumn('qty_shipped', function ($item) { 
            if($item->status == 10){
                $html_string = '<input type="number"  name="qty_shipped" data-id="'.$item->id.'" class="fieldFocus" value="'.$item->qty_shipped.'" readonly disabled style="width:50%">';
            }
            else{
                $html_string = $item->qty_shipped;
            }
            return $html_string;      
        })

        ->setRowClass(function ($item) {
         if($item->status != 10){
            return  'yellowRow';
         }            
        })

        ->rawColumns(['item_no','pcs_shipped', 'qty_shipped'])
        ->make(true);

    }


	public function productReceived($id)
    {
		$po_group = PoGroup::find($id);
		$product_receiving_history = ProductReceivingHistory::with('get_user')->where('updated_by',$this->user->id)->where('po_group_id',$id)->get();
        $status_history = PoGroupStatusHistory::with('get_user')->where('po_group_id',$id)->get();

        return $this->render('importing.products.products-received',compact('po_group','id','product_receiving_history','status_history'));
	}

	public function saveGroupData(Request $request)
	{
		$po_group = PoGroup::find($request->gId);
		foreach($request->except('gId') as $key => $value)
        {
        	$po_group->$key = $value;
        	$po_group->save();
        }
        // dd($key);
        $p_o_ds = PurchaseOrderDetail::whereIn('po_id',PoGroupDetail::where('po_group_id',$request->gId)->pluck('purchase_order_id'))->where('quantity','!=',0)->get();
        foreach ($p_o_ds as $p_o_d) {
            if($key == 'freight')
            {
            	$item_gross_weight = $p_o_d->pod_total_gross_weight;
    	    	$total_gross_weight = $po_group->po_group_total_gross_weight;
    	    	$total_freight = $po_group->freight;
    	    	$total_quantity = $p_o_d->quantity;
    	    	$freight = (($total_freight*($item_gross_weight/$total_gross_weight))/$total_quantity);
            	$p_o_d->pod_freight = $freight;                
            }
            else if($key == 'landing')
            {
                $item_gross_weight = $p_o_d->pod_total_gross_weight;
                $total_gross_weight = $po_group->po_group_total_gross_weight;
                $total_quantity = $p_o_d->quantity;
	    	    $total_landing = $po_group->landing;
        	    $landing = (($total_landing*($item_gross_weight/$total_gross_weight))/$total_quantity);
        	    $p_o_d->pod_landing = $landing;
            }

            else if($key == 'tax')
            {
                $tax = $po_group->tax;
                $total_import_tax = $po_group->po_group_import_tax_book;
                $import_tax = $p_o_d->pod_import_tax_book;
                $actual_tax_percent = ($tax/$total_import_tax*$import_tax); 
        	    $p_o_d->pod_actual_tax_percent = $actual_tax_percent;
            }
        	$p_o_d->save();
        }
        return response()->json(['success' => true]);
	}
	
	public function savePoGroupChanges(Request $request)
    {
        $group_detail = PoGroup::where('id',$request->group_id)->first();

        foreach($request->except('group_id') as $key => $value)
        {
        //   if($key == 'country')
        //   {
        //       $supp_detail->$key = $value;
        //       $supp_detail->state = NULL;
        //   }
          if($value == '')
          {
              // $supp_detail->$key = null;
          }
          else
          {
              $group_detail->$key = $value;
          }
        }
          $group_detail->save();

        return response()->json(['success' => true]);
	}
	
	public function savePoGroupDetailChanges(Request $request)
    {
    	//dd($request->all());
        $po_detail = PurchaseOrderDetail::where('id',$request->pod_id)->first();

        foreach($request->except('pod_id','po_group_id') as $key => $value)
        {        
          	if($value == ''){
              // $supp_detail->$key = null;
          	}
          	elseif($key == 'quantity_received'){
          		if( $value > $po_detail->quantity ){
          			return response()->json(['success' => false,'extra_quantity'=>$value-$po_detail->quantity]);
          		}
          		else
          		{
	          	$params['term_key']  = $key;
	            $params['old_value'] = $po_detail->$key;
	            $params['new_value'] = $value;
	            $params['ip_address'] = $request->ip();
	            $this->saveProductReceivingHistory($params, $po_detail->id,$request->po_group_id);
	            $po_detail->$key = $value;
	        	}
	    	}
            elseif($key == 'pod_total_gross_weight'){
                #Here we store the pod total gross weight
                #which will update the purchase order's total_gross_weight
                #which at the end will update the po group's po_group_total_gross_weight
                $po_detail->$key = $value;
                $po_detail->pod_gross_weight = $value/$po_detail->quantity;
                $po_detail->save();

                $total_gross_weight = 0;
                $purchase_order_details = $po_detail->PurchaseOrder->PurchaseOrderDetail;
                foreach ($purchase_order_details as $detail) {
                    $total_gross_weight += $detail->pod_total_gross_weight;
                }

                $po_detail->PurchaseOrder->total_gross_weight = $total_gross_weight;
                $po_detail->PurchaseOrder->save();

                $po_group_total_gross_weight = 0;
                $po_group = PoGroup::where('id',$request->po_group_id)->first();

                foreach ($po_group->po_group_detail as $group_detail) {
                    $po_group_total_gross_weight += $group_detail->purchase_order->total_gross_weight;
                }
                $po_group->po_group_total_gross_weight = $po_group_total_gross_weight;
                $po_group->save();

                #and at the end we will calculate the freight and landing of pod
                foreach ($po_group->po_group_detail as $group_detail) {
                    $p_o_ds = PurchaseOrderDetail::whereIn('po_id',PoGroupDetail::where('po_group_id',$group_detail->po_group_id)->pluck('purchase_order_id'))->where('quantity','!=',0)->get();
                    //dd($p_o_ds);
                foreach ($p_o_ds as $p_o_d) {
                    $item_gross_weight = $p_o_d->pod_total_gross_weight;
                    $total_gross_weight = $po_group->po_group_total_gross_weight;
                    $total_quantity = $p_o_d->quantity;

                    $total_freight = $po_group->freight;
                    $total_landing = $po_group->landing;

                    $freight = (($total_freight*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    $landing = (($total_landing*($item_gross_weight/$total_gross_weight))/$total_quantity);
                    
                    $p_o_d->pod_freight = $freight;
                    $p_o_d->pod_landing = $landing;
                    $p_o_d->save();

                }}
                return response()->json(['gross_weight' => true,'po_group' => $po_group]);
            }
            elseif($key == 'pod_import_tax_book')
            {
                //dd($key);
                $po_detail->$key = $value;
                $po_detail->pod_import_tax_book_price = ($value/100)*$po_detail->total_unit_price_in_thb;
                $po_detail->save();

                $po_group = PoGroup::where('id',$request->po_group_id)->first();

                $total_import_tax_book_price = 0;
                $po_group_details = $po_group->po_group_detail;
                foreach ($po_group_details as $po_group_detail) {
                    $purchase_order_details = $po_group_detail->purchase_order->PurchaseOrderDetail;
                    foreach ($purchase_order_details as $detail) {
                        $total_import_tax_book_price += $detail->pod_import_tax_book_price;
                    }
                }
                $po_group->po_group_import_tax_book = $total_import_tax_book_price;
                $po_group->save();

                foreach ($po_group->po_group_detail as $group_detail) {
                foreach ($group_detail->purchase_order->PurchaseOrderDetail as $p_o_d) {

                    $tax = $po_group->tax;
                    $total_import_tax = $po_group->po_group_import_tax_book;
                    $import_tax = $p_o_d->pod_import_tax_book;
                    if($total_import_tax != 0 )
                    {
                        $actual_tax_percent = ($tax/$total_import_tax*$import_tax); 
                        $p_o_d->pod_actual_tax_percent = $actual_tax_percent;
                    }
                    $p_o_d->save();

                }}
                return response()->json(['import_tax' => true,'po_group' => $po_group]);
            }
	    	else{         	
            $po_detail->$key = $value;
          }
        }
        $po_detail->save();

        return response()->json(['success' => true]);
	}

	private function saveProductReceivingHistory($params = [], $pod_id,$po_group_id)
	{  
		$product_receiving_history              = new ProductReceivingHistory;
		$product_receiving_history->po_group_id = $po_group_id;
		$product_receiving_history->pod_id      = $pod_id;

        if($params['term_key'] == 'quantity_received'){
            $key =  ucwords(str_replace('_', ' ',$params['term_key']));
            $old_value  = $params['old_value']; 
            $new_value  = $params['new_value']; 
        }

		$product_receiving_history->term_key   = $key;
		$product_receiving_history->old_value  = $old_value;
		$product_receiving_history->new_value  = $new_value;
		$product_receiving_history->updated_by = Auth::user()->id;
		$product_receiving_history->ip_address = $params['ip_address'];
        $product_receiving_history->save();
    }

    public function getIncompletePos(Request $request)
    {
    	$required_extra_quantity = 0;
    	//dd($request->all());
    	$p_o_d = PurchaseOrderDetail::find($request->pod_id);
    	/*$pods = DB::table('purchase_orders')
    	->join('purchase_order_details', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
    	->join('products', 'purchase_order_details.product_id', '=', 'products.id')
    	->where('purchase_orders.status',15)
    	->where('purchase_orders.supplier_id',$p_o_d->PurchaseOrder->supplier_id)
    	->where('purchase_order_details.product_id',$p_o_d->product_id)
    	->where('purchase_order_details.is_completed',0)->get();
*/
		// $query = PurchaseOrder::where('status', 15)
		// 	->where('supplier_id', $p_o_d->PurchaseOrder->supplier_id)
		// 	->with(['PurchaseOrderDetail' => function($q) use ($p_o_d){
		// 		$q->where('product_id', '=',$p_o_d->product_id)->where('is_completed',0);
		// 	}]);
			
		$query = PurchaseOrder::where('status', 15)
		->where('supplier_id', $p_o_d->PurchaseOrder->supplier_id)
		->whereHas('PurchaseOrderDetail' , function($q) use ($p_o_d){
			$q->where('product_id', '=',$p_o_d->product_id)->where('is_completed',0);
		})
		->with(['PurchaseOrderDetail' => function($q) use ($p_o_d){
			$q->where('product_id', '=',$p_o_d->product_id)->where('is_completed',0);
		}])->get();

			

		 // $query->where(function($q) use ($p_o_d){
		 //       $q->where('status', 15);
		 //       $q->where('supplier_id', $p_o_d->PurchaseOrder->supplier_id);
		 //   });
		 //   $query->whereHas('PurchaseOrderDetail', function($query) use ($p_o_d){ 
		 //   	$query->where('product_id', '=',$p_o_d->product_id)->where('is_completed',0); });


    	// ->where('status',15)->where('supplier_id',$p_o_d->PurchaseOrder->supplier_id)->whereHas('PurchaseOrderDetail', function($query) use ($p_o_d){ $query->where('product_id', '=',$p_o_d->product_id)->where('is_completed',0); })->get();
    	if($query == null){
    		return response()->json(['success' => false]);
    	}

    	foreach($query as $po){
        foreach($po->PurchaseOrderDetail as $pod){
        	$required_extra_quantity += $pod->quantity - $pod->quantity_received;
        	}
        }
		if($request->extra_quantity > $required_extra_quantity){
			return response()->json(['success' => false,'extra_quantity'=>true]);
		}
        /*$getIncompletePod = PurchaseOrderDetail::where('is_completed',0)->where('product_id',$p_o_d->product_id)->get();*/


		$html_string ='<div class="table-responsive">
        <table class="table table-bordered text-center">
        <thead class="table-bordered">
        <tr>
            <th>S.no</th>
            <th>PO#</th>
            <th>Product</th>
            <th>QTY Required</th>
            <th>QTY Received</th>
            <th>New QTY Rcvd</th>
        </tr>
        </thead><tbody>';
        if($query->count() > 0){
        $i = 0;
        foreach($query as $po){
        foreach($po->PurchaseOrderDetail as $pod){
        	$required = $pod->quantity-$pod->quantity_received;
        	if($required < $request->extra_quantity){
        		$new = $required;
        		$request->extra_quantity -= $required; 
        	}
        	else{
        		$new = $request->extra_quantity;
        		$request->extra_quantity -= $request->extra_quantity; 
        	}
        	//$new = $required < $request->extra_quantity ? $required : $request->extra_quantity;
        	//dd($pod->product);
        $i++;   
		$html_string .= '<tr>
            <td>'.$i.'</td>
            <td>PO#'.$pod->PurchaseOrder->id.'</td>
            <td>'.$pod->product->short_desc.'</td>
            <td>'.$pod->quantity.'</td>
            <td>'.$pod->quantity_received.'</td>
            <td><input type="text" name="quantity_received" class="font-weight-bold form-control-lg form-control" value="'.($new).'" style="width:50%"></td>
         </tr>';                
        }   
        }   
        }else{
		$html_string .= '<tr>
            <td colspan="4">No PO\'s Found</td>
         </tr>';            
        }                    
        $html_string .= '</tbody></table></div>';

        return response()->json(['success' => true,'html_string'=>$html_string]);
    }
	
	public function saveGoodsData(Request $request)
	{
		// dd($request->all());
		$group_detail = PurchaseOrderDetail::where('id',$request->id)->first();
		if($request->value == 'normal' || $request->value == 'problem'){
			$group_detail->good_condition = $request->value;
		}
		else if($request->value == 'pass' || $request->value == 'fail'){
			$group_detail->result = $request->value;
		}
		else if($request->value == '1' || $request->value == '2' || $request->value == '3'|| $request->value == '4'){
			$group_detail->good_type = $request->value;
		}
		$group_detail->save();
		return response()->json(['success' => true]);
	}

	public function confirmPoGroup(Request $request)
	{
		$po_group = PoGroup::with('po_group_product_details:id,po_group_id,is_review')->find($request->id);
		
		$purchase_orders = PurchaseOrder::whereIn('id',PoGroupDetail::where('po_group_id',$request->id)->pluck('purchase_order_id'))->get();
		foreach ($purchase_orders as $PO) 
        {
            $purchase_order_details = PurchaseOrderDetail::where('po_id',$PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity','!=',0)->get();
			foreach ($purchase_order_details as $p_o_d)
			{
				$product_id = $p_o_d->product_id;
				$supplier_product = SupplierProducts::where('supplier_id',$PO->supplier_id)->where('product_id',$product_id)->first();
                
				$supplier_product->freight           = $p_o_d->pod_freight;
				$supplier_product->landing           = $p_o_d->pod_landing;
				$supplier_product->extra_cost        = $p_o_d->pod_total_extra_cost/$p_o_d->quantity;
				$supplier_product->import_tax_actual = $p_o_d->pod_actual_tax_percent;
				$supplier_product->gross_weight 	 = $p_o_d->pod_gross_weight;
				$supplier_product->save();
				#is_completed is a column in purchase_order_details table
                #which check it quantity and quantity_received are equal

                $product = Product::find($p_o_d->product_id);	
                // if($product->supplier_id == $PO->supplier_id){
                	// this is the price of after conversion for THB
                	$supplier_conv_rate_thb = @$supplier_product->supplier->getCurrency->conversion_rate;
                	$importTax = $supplier_product->import_tax_actual;
                	$buying_price_in_thb = ($supplier_product->buying_price / $supplier_conv_rate_thb);                
			        $total_buying_price = (($importTax/100) * $buying_price_in_thb) + $buying_price_in_thb;

			        $total_buying_price = ($supplier_product->freight)+($supplier_product->landing)+($supplier_product->extra_cost)+($total_buying_price);			        
			        $product->total_buy_unit_cost_price = $total_buying_price;
			        //this is supplier buying unit cost price 
			        $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;   			        
			        //this is selling price
			        $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;
			        $product->selling_price = $total_selling_price;
			        $product->save();
                // }	  		


                if($p_o_d->order_product_id != null)
                {
                    $order = Order::find($p_o_d->order_id);
                    $price_calculate_return = $product->price_calculate($product,$order);   
                    $unit_price = $price_calculate_return[0];
                    $total_price = $unit_price*$p_o_d->order_product->quantity;
                    $discount = $p_o_d->order_product->discount;

                     if($discount != null)
                     {
                      $dis = $discount / 100;
                         $discount_value = $dis * $total_price;
                          $result = $total_price - $discount_value;
                    }else{
                      $result = $total_price;
                    }
                    $p_o_d->order_product->actual_cost = (($p_o_d->order_product->vat/100)*$result)+$result;
                    $p_o_d->order_product->save();  
                }  
			}
		}
        $po_group->is_review = 1;
        $po_group->save();

        foreach($po_group->po_group_product_details as $detail)
        {
            $detail->is_review = 1;
            $detail->save();
        }
		return response()->json(['success' => true]);
	}

	public function getPurchaseOrderDetail($id){
		// dd($id);
        $getPurchaseOrderDetail = PurchaseOrderDetail::with('customer')->where('po_id',$id)->first();     
       
        $getPurchaseOrder = PurchaseOrder::find($id);
        $getPoNote = PurchaseOrderNote::where('po_id',$id)->first();
        $checkPoDocs = PurchaseOrderDocument::where('po_id',$id)->get()->count();

        if($getPurchaseOrder->supplier_id != null)
        {
       return view('importing.purchase-order.purchase-order-detail',compact('getPurchaseOrderDetail', 'id','getPurchaseOrder','checkPoDocs','getPoNote'));
        }
        else
        {
            return view('importing.purchase-order.warehouse-purchase-order-detail',compact('getPurchaseOrderDetail', 'id','getPurchaseOrder','checkPoDocs','getPoNote'));
        }
    }

    public function getPurchaseOrderProdDetail($id)
    {
        $details = PurchaseOrderDetail::with('customer','product')->where('po_id',$id)->get();
        return Datatables::of($details)
            
            ->addColumn('action', function ($item) {
                if($item->order_id != NULL)
                {
                    $html_string = '
                    <a href="javascript:void(0);" class="actionicon deleteIcon delete-product-from-list" data-order_id="' . $item->order_id . '" data-order_product_id="'. $item->order_product_id .'" data-po_id ="'. $item->po_id .'" data-id="'.$item->id.'" title="Delete"><i class="fa fa-trash"></i></a>';
                }
                else
                {
                    $html_string = '
                    <a href="javascript:void(0);" class="actionicon deleteIcon delete-product-from-list" data-order_id="' . $item->order_id . '" data-order_product_id="'. $item->order_product_id .'" data-po_id ="'. $item->po_id .'" data-id="'.$item->id.'" title="Delete"><i class="fa fa-trash"></i></a>';
                }
                return $html_string;
            }) 
            ->addColumn('supplier_id', function ($item) {
                if($item->PurchaseOrder->supplier_id != null)
                {
                    $gettingProdSuppData = SupplierProducts::where('product_id',$item->product_id)->where('supplier_id',$item->PurchaseOrder->PoSupplier->id)->first();
 
                    $ref_no1 = $gettingProdSuppData->product_supplier_reference_no !== null ? $gettingProdSuppData->product_supplier_reference_no : "--";

                    return  $html_string1 = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"><b>'.$ref_no1.'</b></a>';
                }
                else{

                }

            })
            ->addColumn('item_ref', function ($item) { 
                $ref_no = $item->product_id !== null ? $item->product->refrence_code : "--" ;
                return  $html_string = '<a target="_blank" href="'.url('get-product-detail/'.$item->product_id).'"  ><b>'.$ref_no.'</b></a>';
            })
            ->addColumn('customer', function ($item) {
                return $item->customer_id !== null ? @$item->customer->company : '--';
            })
            ->addColumn('short_desc', function ($item) {
                return $item->product_id !== null ? $item->product->short_desc : 'N.A';
            })
            ->addColumn('buying_unit', function ($item) {
                return $item->product_id !== null ? $item->product->units->title : 'N.A';
            })
            ->addColumn('quantity', function ($item) {
                if($item->quantity != null)
                {
                    $html_string = '
                <span class="m-l-15 inputDoubleClickQuantity quantity" data-id id="quantity"  data-fieldvalue="'.@$item->quantity.'">';
                $html_string .= $item->quantity;
                $html_string .= '</span>';

                $html_string .= '<input type="number" style="width:100%;" name="quantity" class="fieldFocusQuantity d-none" min="0" value="'.$item->quantity .'">';
                }
                return $html_string;
            })
            ->addColumn('gross_weight', function ($item) {
               
                return $item->pod_total_gross_weight;
            })
            ->addColumn('unit_price', function ($item) {
                $supplier_products = new SupplierProducts;
                $unit_price = $item->pod_unit_price;

                $html_string = '
                <span class="m-l-15 inputDoubleClickQuantity quantity" data-id id="quantity"  data-fieldvalue="'.@$unit_price.'">';
                $html_string .= number_format($unit_price, 3, '.', ',');
                $html_string .= '</span>';
                $html_string .= '<input type="number" style="width:100%;" name="unit_price" class="unitfieldFocus d-none" min="0" value="'.$unit_price.'">';
                return $html_string;
                
            })
            ->addColumn('amount', function ($item) {
                
                $amount = $item->pod_total_unit_price;
                return number_format($amount, 3, '.', ',');
                
            })
            ->addColumn('order_no', function ($item) {
                return $item->order_id !== null ? $item->order_id : '--';
            })
            ->addColumn('warehouse', function ($item) {
                if($item->warehouse_id != NULL)
                {
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity" id="warehouse_id"  data-fieldvalue="'.@$item->getWarehouse->name.'">';
                    $html_string .= $item->warehouse_id != null ? $item->getWarehouse->name : 'Select';
                    $html_string .= '</span>';

                    $html_string .= '<select class="form-control select-common warehouse_id d-none" name="warehouse_id" id="warehouse_id_'.$item->id.'">
                    <option value="" disabled="" selected="">Choose Warehouse</option>';
            
                    $getWarehouses = User::where('role_id', 6)->whereNull('parent_id')->get();
                    if($getWarehouses)
                    {
                      foreach ($getWarehouses as $value) 
                      {
                        $condition = @$item->warehouse_id == $value->id ? 'selected' : "";
                        $html_string .= '<option '.$condition.' value="'.$value->id.'">'.$value->name.'</option>';
                      }
                    }
                    $html_string .= '</select>';
                    
                    return $html_string;
                }
                else
                {
                    $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity" id="warehouse_id"  data-fieldvalue="'.@$item->getWarehouse->name.'">';
                    $html_string .= 'Select';
                    $html_string .= '</span>';

                    $html_string .= '<select class="form-control warehouse_id select-common d-none" name="warehouse_id" id="warehouse_id_'.$item->id.'">
                    <option value="" disabled="" selected="">Choose Warehouse</option>';
            
                    $getWarehouses = User::where('role_id', 6)->whereNull('parent_id')->get();
                    if($getWarehouses)
                    {
                      foreach ($getWarehouses as $value) 
                      {
                        $html_string .= '<option value="'.$value->id.'">'.$value->name.'</option>';
                      }
                    }
                    $html_string .= '</select>';
                    
                    return $html_string;
                }
            })
            ->setRowId(function ($item) {
                    return @$item->id;
            })
            ->rawColumns(['action', 'supplier_id','supplier_ref','item_ref','customer','short_desc','buying_unit','quantity','unit_price','amount','order_no','warehouse','gross_weight'])
            ->make(true);
    }

     public function getPurchaseOrderFiles(Request $request)
    {
        $purchase_order_files = PurchaseOrderDocument::where('po_id', $request->po_id)->get();

            $html_string ='<div class="table-responsive">
                            <table class="table dot-dash text-center">
                            <thead class="dot-dash">
                            <tr>
                                <th>S.no</th>
                                <th>File</th>
                                
                            </tr>
                            </thead><tbody>';
                            if($purchase_order_files->count() > 0){
                            $i = 0;
                            foreach($purchase_order_files as $file){
                            $i++;   
            $html_string .= '<tr id="purchase-order-file-'.$file->id.'">
                                <td>'.$i.'</td>
                                <td><a href="'.asset('public/uploads/documents/'.$file->file_name).'" target="_blank">'.$file->file_name.'</a></td>
                               
                             </tr>';                
                            }     
                            }else{
            $html_string .= '<tr>
                                <td colspan="3">No File Found</td>
                             </tr>';            
                            }
                          

            $html_string .= '</tbody></table></div>';
            return $html_string;
    }
}
