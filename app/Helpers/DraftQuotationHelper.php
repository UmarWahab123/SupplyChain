<?php
namespace App\Helpers;
use App\Exports\invoicetableExport;
use App\GlobalAccessForRole;
use App\Http\Controllers\Controller;
use App\Imports\AddProductToOrder;
use App\Imports\AddProductToTempQuotation;
use App\InvoiceSetting;
use App\Models\Common\Bank;
use App\Models\Common\Company;
use App\Models\Common\CompanyBank;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Configuration;
use App\Models\Common\Country;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\OrderHistory;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\DraftQuotation;
use App\Models\Common\Order\DraftQuotationAttachment;
use App\Models\Common\Order\DraftQuotationNote;
use App\Models\Common\Order\DraftQuotationProduct;
use App\Models\Common\Order\DraftQuotationProductNote;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderAttachment;
use App\Models\Common\Order\OrderNote;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderProductNote;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\PaymentTerm;
use App\Models\Common\PaymentType;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\State;
use App\Models\Common\Status;
use App\Models\Common\StockManagementIn;
use App\Models\Common\StockManagementOut;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Unit;
use App\Models\Common\UserDetail;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\Sales\Customer;
use App\DraftQuatationProductHistory;
use App\Jobs\InvoiceSaleExpJob;
use App\Jobs\CancelledOrderJob;
use App\PrintHistory;
use App\ExportStatus;
use App\QuotationConfig;
use App\RoleMenu;
use App\User;
use App\Variable;
use Auth;
use DateTime;
use Dompdf\Exception;
use Excel;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use PDF;
use App\Exports\DraftQuotationExport;
use App\Exports\CompleteQuotationExport;
use Yajra\Datatables\Datatables;
use App\Version;
use App\Helpers\QuantityReservedHistory;
use App\Imports\BulkImportForOrder;
use App\Imports\DraftQuotationImport;
use App\ImportFileHistory;
use App\Helpers\MyHelper;
use App\Helpers\UpdateQuotationDataHelper;
use App\Helpers\UpdateOrderQuotationDataHelper;
use App\Notifications\DraftInvoiceQtyChangeNotification;
use App\NotificationConfiguration;
use App\CustomEmail;
use App\CustomerSecondaryUser;
use App\Http\Controllers\Sales\OrderController;

class DraftQuotationHelper
{
	public static function AddCustomerToQuotation($request)
    {
		$customer = Customer::find($request->customer_id);
		$customer_id = $request->customer_id;
		$customerAddress = CustomerBillingDetail::where('customer_id',$request->customer_id)->where('is_default',1)->first();
		$shipping_address = CustomerBillingDetail::where('customer_id',$request->customer_id)->where('is_default_shipping',1)->first();
		if(!$customerAddress)
		{
			$customerAddress = CustomerBillingDetail::where('customer_id',$request->customer_id)->orderBy('id','desc')->first();
			}
			$totalAddresses = CustomerBillingDetail::select('id')->where('customer_id',$request->customer_id)->count();
			$quotation = DraftQuotation::find($request->quotation_id);
			if ($quotation != null)
			{
			$old_customer_id = $quotation->customer_id;
			$order_products = DraftQuotationProduct::where('draft_quotation_id',$quotation->id)->whereNotNull('product_id')->get();
			if ($order_products != null)
			{
				$quotation->customer_id = $request->customer_id;
				$quotation->billing_address_id = @$customerAddress->id;
				$quotation->shipping_address_id = $shipping_address != null ? @$shipping_address->id : @$customerAddress->id;
				$quotation->target_ship_date = null;
				$quotation->delivery_request_date = null;
				$quotation->payment_due_date = null;
				$quotation->save();
				foreach ($order_products as $prod)
				{
					$product = Product::where('id',$prod->product_id)->where('status',1)->first();
					$price_calculate_return = $product->price_calculate($product,$quotation);
					$price_type = $price_calculate_return[1];
					$CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id',$product->id)->where('customer_type_id',$quotation->customer->category_id)->first();
					if($CustomerTypeProductMargin != null )
					{
						$margin      = $CustomerTypeProductMargin->default_value;
						$margin = (($margin/100)*$product->selling_price);
						$product_ref_price  = $margin+($product->selling_price);
						$exp_unit_cost = $product_ref_price;
					}
					$prod->exp_unit_cost       = @$exp_unit_cost;
					$prod->margin              = $price_type;
					$prod->save();
				}
				$quotation->payment_terms_id = $customer->credit_term;
				$quotation->save();

				(new DraftQuotationHelper)->DraftQuotationHistory($quotation->id, null, 'BILL TO', $old_customer_id, $quotation->customer_id); //Creating History

				$html = '
				<div class="d-flex align-items-center mb-1">
				<div><img src="'.asset('public/img/profileImg.jpg').'" style="width: 80px;height: 80px;" class="img-fluid" align="big-qummy"></div>
				<div class="pl-2 comp-name" id="unique" data-customer-id="'.$customer->id.'"><p>'.$customer->reference_name.'</p> </div>
				</div>';
				$html_body = '<p><input type="hidden" value="'.@$customerAddress->id.'">';
				if(@$totalAddresses > 1)
				{
					$html_body .= ' <i class="fa fa-edit edit-address" data-id="'.$request->customer_id.'"></i>';
				}
				$html_body .= ''.@$customerAddress->billing_address.', '.@$customerAddress->getcountry->name.','.@$customerAddress->getstate->name .','.@$customerAddress->billing_city .','.@$customerAddress->billing_zip .'</p>
				 <ul class="d-flex list-unstyled">
				    <li><a href="#"><i class="fa fa-phone pr-2"></i> '.@$customerAddress->billing_phone.'</a></li>
				    <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> '.@$customerAddress->billing_email.'</a></li>
				  </ul>
				  <ul class="d-flex list-unstyled">
				    <li><b>Tax ID:</b> '.@$customerAddress->tax_id.'</li>
				  </ul>
				</div>';
				$customerAddress = $shipping_address != null ? $shipping_address : $customerAddress;
				$html_2 = '
				<div class="d-flex align-items-center mb-1">
				  <div><img src="'.asset('public/img/sm-logo.png').'" class="img-fluid" align="big-qummy"></div>
				  <div class="pl-2 comp-name" data-customer-id="'.$customer->id.'"><p>'.$customer->company.'</p> </div>
				</div> ';
				$html_2_body = '<p><input type="hidden" value="'.@$customerAddress->id.'">';
				if(@$totalAddresses > 1)
				{
					$html_2_body .= ' <i class="fa fa-edit edit-address-ship" data-id="'.$request->customer_id.'"></i>';
				}
				$html_2_body .= ''.@$customerAddress->billing_address.', '.@$customerAddress->getcountry->name.','.@$customerAddress->getstate->name .','.@$customerAddress->billing_city .','.@$customerAddress->billing_zip .'</p>
				 <ul class="d-flex list-unstyled">
				    <li><a href="#"><i class="fa fa-phone pr-2"></i> '.@$customerAddress->billing_phone.'</a></li>
				    <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> '.@$customerAddress->billing_email.'</a></li>
				  </ul>
				</div>';
				//Edit customer drop downs
				$edit_customer = '';
				$sales_persons = Customer::with('primary_sale_person')->where('id', $customer_id)->first();
				$sales_person_html = '
				  <optgroup label = "Primary Sale Person">
				    <option value = "'.$sales_persons->primary_sale_id.'" selected>'.$sales_persons->primary_sale_person != null ?$sales_persons->primary_sale_person->name:''.'</option>
				  </optgroup>';
				$secondary_sales = CustomerSecondaryUser::where('customer_id', $customer_id)->get();
				if ($secondary_sales->count() != 0)
				{
					$sales_person_html .= '<optgroup label = "Secondary Sales Person">';
					foreach ($secondary_sales as $secondary)
					{
						$sales_person_html .= '
						<option value = "'.$secondary->user_id.'">'.$secondary->secondarySalesPersons->name.'</option>';
					}
					$sales_person_html .= '</optgroup>';
				}
				$quotation->user_id = $sales_persons->primary_sale_id;
				$quotation->save();
				return response()->json(['html' => $html,'html_body'=> $html_body,'html_2'=>$html_2,'html_2_body'=>$html_2_body,'customer_id'=>$customer_id, 'payment_term' => $customer->getpayment_term,'edit_customer'=>$edit_customer, 'sales_person_html' => $sales_person_html]);
			}
		}
		else
		{
			return response()->json([
			  'msg' => 'Order Invoice Already Deleted',
			  'success' => false
			]);
		}
    }

    public static function SaveQuotationDiscount($request)
    {
        $order = DraftQuotation::find($request->quotation_id);
        if ($order != null)
		{
			foreach($request->except('quotation_id') as $key => $value)
			{
				$customer_note = DraftQuotationNote::where('draft_quotation_id',$order->id)->where('type','customer')->first();
				$warehouse_note = DraftQuotationNote::where('draft_quotation_id',$order->id)->where('type','warehouse')->first();
				if($key == 'comment')
				{
					if($customer_note != null)
					{
						$customer_note->note = $value;
						$customer_note->save();
						return response()->json(['success'=>true]);
					}
					else
					{
						$cust_note = new DraftQuotationNote;
						$cust_note->draft_quotation_id = $order->id;
						$cust_note->note = $value;
						$cust_note->type = 'customer';
						$cust_note->save();
						return response()->json(['success'=>true]);
					}
				}
				if($key == 'comment_warehouse')
				{
					if($warehouse_note != null)
					{
						$warehouse_note->note = $value;
						$warehouse_note->save();
						return response()->json(['success'=>true]);
					}
					else
					{
						$cust_note = new DraftQuotationNote;
						$cust_note->draft_quotation_id = $order->id;
						$cust_note->note = $value;
						$cust_note->type = 'warehouse';
						$cust_note->save();
						return response()->json(['success'=>true]);
					}
				}
				if($key == 'delivery_request_date')
				{
					$value = str_replace("/","-",$request->delivery_request_date);
					$value =  date('Y-m-d',strtotime($value));
					$order->$key = $value;
					if($order->payment_terms_id !== null)
					{
						$getCreditTerm = PaymentTerm::find($order->payment_terms_id);
						$creditTerm = @$getCreditTerm->title;
						$int = intval(preg_replace('/[^0-9]+/', '', @$creditTerm), 10);
						if(@$creditTerm == "COD") // today data if COD
						{
						  @$payment_due_date = @$value;
						}
						@$needle = "EOM";
						if(strpos(@$creditTerm,@$needle) !== false)
						{
							@$trdate = @$value;
							@$getDayOnly = date('d', strtotime(@$trdate));
							@$extractMY = new DateTime(@$trdate);

							@$daysOfMonth = cal_days_in_month(CAL_GREGORIAN, (int)@$extractMY->format('m'), @$extractMY->format('Y') );
							@$subtractDays = @$daysOfMonth - @$getDayOnly;

							@$days = @$int + @$subtractDays;
							@$newdate = strtotime(date("Y-m-d", strtotime(@$trdate)) . "+$days days");
							$newdate = date("Y-m-d",@$newdate);
							$payment_due_date = @$newdate;
						}
						else
						{
							$days = @$int;
							$trdate = @$value;
							$newdate = strtotime(date("Y-m-d", strtotime(@$trdate)) . "+$days days");
							$newdate = date("Y-m-d",@$newdate);
							$payment_due_date = @$newdate;
						}
						$order->payment_due_date = @$payment_due_date;
					}
				}
				if($key == 'target_ship_date')
				{
					$value = str_replace("/","-",$request->target_ship_date);
					$value =  date('Y-m-d',strtotime($value));
					$order->$key = $value;
				}
				$order->$key = $value;
			}
			$order->save();
			$sub_total     = 0 ;
			$sub_total_with_vat = 0;
			$query = DraftQuotationProduct::where('draft_quotation_id',$order->id)->get();
			foreach ($query as  $value)
			{
				$sub_total += $value->quantity * $value->unit_price;
				$sub_total_with_vat = $sub_total_with_vat + $value->total_price_with_vat;
			}
			$vat = $sub_total_with_vat-$sub_total;
			$total = ($sub_total)-($order->discount)+($order->shipping)+($vat);
			return response()->json(['total' => number_format($total, 2, '.', ','),'discount' => number_format($order->discount, 2, '.', ','), 'shipping' => number_format($order->shipping, 2, '.', ',') , 'order'=>$order]);
		}
		else
		{
			return response()->json([
			'msg' => 'Order Invoice Already Deleted',
			'success' => false
			]);
		}
    }

    public static function paymentTermSaveInDquotation($request)
    {
        $draft_qoutation = DraftQuotation::find($request->order_id);
        if ($draft_qoutation == null)
        {
          return response()->json([
            'success' => false,
            'msg' => 'Order Invoice Already Deleted'
          ]);
        }
        $draft_qoutation->payment_terms_id = $request->payment_terms_id;
        if($draft_qoutation->delivery_request_date != null)
        {
            $getCreditTerm = PaymentTerm::find($request->payment_terms_id);
            $creditTerm = $getCreditTerm->title;
            $int = intval(preg_replace('/[^0-9]+/', '', $creditTerm), 10);
            if($creditTerm == "COD") // today data if COD
            {
                $payment_due_date = $draft_qoutation->delivery_request_date;
            }
            $needle = "EOM";
            if(strpos($creditTerm,$needle) !== false)
            {
                $trdate = $draft_qoutation->delivery_request_date;
                $getDayOnly = date('d', strtotime($trdate));
                $extractMY = new DateTime($trdate);
                $daysOfMonth = cal_days_in_month(CAL_GREGORIAN, (int)$extractMY->format('m'), $extractMY->format('Y') );
                $subtractDays = $daysOfMonth - $getDayOnly;
                $days = $int + $subtractDays;
                $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
                $newdate = date("Y-m-d",$newdate);
                $payment_due_date = $newdate;
            }
            else
            {
                $days = $int;
                $trdate = $draft_qoutation->delivery_request_date;
                $newdate = strtotime(date("Y-m-d", strtotime($trdate)) . "+$days days");
                $newdate = date("Y-m-d",$newdate);
                $payment_due_date = $newdate;
            }
            $draft_qoutation->payment_due_date = $payment_due_date;
        }
        $draft_qoutation->save();
        return response()->json([
            'success' => true,
            'payment_due_date' => $draft_qoutation->payment_due_date
        ]);
    }

    public static function fromWarehouseSaveInDquotation($request)
    {
        $draft_qoutation = DraftQuotation::find($request->order_id);
        if ($draft_qoutation == null)
        {
          return response()->json([
            'success' => false,
            'msg' => 'Order Invoice Already Deleted'
          ]);
        }
        $draft_qoutation->from_warehouse_id = $request->from_warehouse_id;
        $draft_quotation_products = DraftQuotationProduct::where('draft_quotation_id',$request->order_id)->where('is_warehouse',1)->get();
        $draft_quotation_products1 = DraftQuotationProduct::where('draft_quotation_id',$request->order_id)->where('is_warehouse',0)->get();
        foreach ($draft_quotation_products as $prod)
        {
          $prod->from_warehouse_id = $request->from_warehouse_id;
          $prod->warehouse_id = $request->from_warehouse_id;
          $prod->save();
        }
        $draft_qoutation->save();
        foreach ($draft_quotation_products1 as $prod)
        {
         $prod->warehouse_id = $request->from_warehouse_id;
         $prod->save();
        }
        return response()->json([
            'success' => true,
        ]);
    }

  	public static function DraftQuotExportToPDF($request, $id,$page_type,$column_name, $default_sort, $discount, $bank_id, $vat)
  	{
	    $show_discount = $discount;
	    $with_vat = @$vat;
	    $proforma = @$request->is_proforma;
	    $order = DraftQuotation::find($id);
	    $draft_quotation_products = DraftQuotationProduct::where('draft_quotation_id', $id);
    	if ($column_name == 'reference_code' && $default_sort !== 'id_sort')
        {
			$draft_quotation_products = $draft_quotation_products->leftJoin('products', 'products.id', '=', 'draft_quotation_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
        }
        elseif($column_name == 'short_desc' && $default_sort != 'id_sort')
        {
			$draft_quotation_products = $draft_quotation_products->orderBy('short_desc', $default_sort)->get();
        }
        elseif($column_name == 'supply_from' && $default_sort !== 'id_sort')
        {
			$draft_quotation_products = $draft_quotation_products->leftJoin('suppliers', 'suppliers.id', '=', 'draft_quotation_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
        }
        elseif($column_name == 'type_id' && $default_sort !== 'id_sort')
        {
			$draft_quotation_products = $draft_quotation_products->leftJoin('types', 'types.id', '=', 'draft_quotation_products.type_id')->orderBy('types.title', $default_sort)->get();
        }
        elseif($column_name == 'brand' && $default_sort !== 'id_sort')
        {
			$draft_quotation_products = $draft_quotation_products->orderBy($column_name, $default_sort)->get();
        }
        else
        {
			$draft_quotation_products = $draft_quotation_products->orderBy('id', 'ASC')->get();
        }
	    $company_info = Company::where('id',$order->user->company_id)->first();
	    $bank = Bank::find($bank_id);
	    $address = CustomerBillingDetail::select('billing_phone')->where('customer_id',$order->customer_id)->where('is_default',1)->first();
	    $customerAddress = CustomerBillingDetail::where('customer_id',$order->customer_id)->where('id',$order->billing_address_id)->first();
	    $inv_note = DraftQuotationNote::where('draft_quotation_id', $order->id)->where('type','customer')->first();
	    $warehouse_note = DraftQuotationNote::where('draft_quotation_id', $order->id)->where('type','warehouse')->first();
	    $query2 = null;
	    $is_texica = Status::where('id',1)->pluck('is_texica')->first();
	    $customPaper = array(0,0,576,792);
	    $all_orders_count = DraftQuotationProduct::where('draft_quotation_id', $id)->where(function($q){
	      $q->where('quantity','>',0)->orWhereHas('get_order_product_notes');
	    })->orderBy('id', 'ASC')->count();
	    // getting count on all order products
	    $do_pages_count = ceil($all_orders_count / 3);
	    $getPrintBlade = Status::select('print_1')->where('id',1)->first();
	    if($is_texica && $is_texica == 1)
	    {
	      $pdf = PDF::loadView('sales.invoice.draft-quotation-invoice',compact('order','query2','address','company_info','with_vat', 'customerAddress', 'customerAddressShip','show_discount','do_pages_count','bank','inv_note','warehouse_note', 'draft_quotation_products'))->setPaper('a4', 'landscape');
	    }
	    else
	    {
	      $config=Configuration::first();
	      $pages = ceil($all_orders_count / 13);
	      if($pages == 0)
	      {
	        $pages = 1;
	      }
          if ($config->server == 'lucilla') {
            $pdf = PDF::loadView('sales.invoice.lucila-draft-quotation-invoice',compact('order','query2','address','company_info','with_vat', 'customerAddress', 'customerAddressShip','show_discount','do_pages_count','bank','inv_note','warehouse_note', 'pages', 'config', 'draft_quotation_products'))->setPaper($customPaper);
          }
          else{
            $pdf = PDF::loadView('sales.invoice.draft-qout-print-exc-vat',compact('order','query2','address','company_info','with_vat', 'customerAddress', 'customerAddressShip','show_discount','do_pages_count','bank','inv_note','warehouse_note', 'pages', 'config', 'draft_quotation_products'))->setPaper($customPaper);
          }
	    }
	    $ref_no = 'draft-quotation-'.$id;
	    $makePdfName=$ref_no;
	    return $pdf->stream(
	      $makePdfName.'.pdf',
	      array(
	        'Attachment' => 0
	      )
	    );
	}

	public static function DraftQuotExportToPDFIncVat($request, $id , $page_type,$column_name, $default_sort, $discount ,$bank, $is_proforma)
	{
		$show_discount = $discount;
		$proforma = @$request->is_proforma;
		$order = DraftQuotation::find($id);
		$draft_quotation_products = DraftQuotationProduct::where('draft_quotation_id', $id);
		if ($column_name == 'reference_code' && $default_sort !== 'id_sort')
		{
			$draft_quotation_products = $draft_quotation_products->leftJoin('products', 'products.id', '=', 'draft_quotation_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
		}
		elseif($column_name == 'short_desc' && $default_sort != 'id_sort')
		{
			$draft_quotation_products = $draft_quotation_products->orderBy('short_desc', $default_sort)->get();
		}
		elseif($column_name == 'supply_from' && $default_sort !== 'id_sort')
		{
			$draft_quotation_products = $draft_quotation_products->leftJoin('suppliers', 'suppliers.id', '=', 'draft_quotation_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
		}
		elseif($column_name == 'type_id' && $default_sort !== 'id_sort')
		{
			$draft_quotation_products = $draft_quotation_products->leftJoin('types', 'types.id', '=', 'draft_quotation_products.type_id')->orderBy('types.title', $default_sort)->get();
		}
		elseif($column_name == 'brand' && $default_sort !== 'id_sort')
		{
			$draft_quotation_products = $draft_quotation_products->orderBy($column_name, $default_sort)->get();
		}
		else{
			$draft_quotation_products = $draft_quotation_products->orderBy('id', 'ASC')->get();
		}
		$company_info = Company::where('id',$order->user->company_id)->first();
		$bank = Bank::find($bank);
		$address = CustomerBillingDetail::select('billing_phone')->where('customer_id',$order->customer_id)->where('is_default',1)->first();
		$customerAddress = CustomerBillingDetail::where('customer_id',$order->customer_id)->where('id',$order->billing_address_id)->first();
		$inv_note = DraftQuotationNote::where('draft_quotation_id', $order->id)->where('type','customer')->first();
		$warehouse_note = DraftQuotationNote::where('draft_quotation_id', $order->id)->where('type','warehouse')->first();
		$query2 = null;
		$is_texica = Status::where('id',1)->pluck('is_texica')->first();
		$customPaper = array(0,0,576,792);
		$all_orders_count = DraftQuotationProduct::where('draft_quotation_id', $id)->where(function($q){
		  $q->where('quantity','>',0)->orWhereHas('get_order_product_notes');
		})->orderBy('id', 'ASC')->count();
		// getting count on all order products
		$do_pages_count = ceil($all_orders_count / 3);
		$getPrintBlade = Status::select('print_2')->where('id',1)->first();
		if($is_texica && $is_texica == 1)
		{
			$pdf = PDF::loadView('sales.invoice.draft-'.$getPrintBlade->print_2.'',compact('order','query2','address','company_info', 'customerAddress', 'customerAddressShip','show_discount','do_pages_count','bank','inv_note','warehouse_note', 'draft_quotation_products'))->setPaper('a4', 'landscape');
		}
		else
		{
			$config=Configuration::first();
			$pages = ceil($all_orders_count / 13);
			if($pages == 0)
			{
				$pages = 1;
			}
            if ($config->server == 'lucilla') {
                $pdf = PDF::loadView('sales.invoice.lucila-draft-qout-print-inc-vat',compact('order','query2','address','company_info', 'customerAddress', 'customerAddressShip','show_discount','do_pages_count','bank','inv_note','warehouse_note', 'pages', 'config', 'draft_quotation_products'))->setPaper($customPaper);
            }
            else{
                $pdf = PDF::loadView('sales.invoice.draft-qout-print-inc-vat',compact('order','query2','address','company_info', 'customerAddress', 'customerAddressShip','show_discount','do_pages_count','bank','inv_note','warehouse_note', 'pages', 'config', 'draft_quotation_products'))->setPaper($customPaper);
            }
		}
		$ref_no = 'draft-quotation-'.$id;
		$makePdfName=$ref_no;
		return $pdf->stream(
		  $makePdfName.'.pdf',
		  array(
		    'Attachment' => 0
		  )
		);
	}

	public static function uploadQuotationExcel($request)
  	{
        $import = new DraftQuotationImport($request->order_id,$request->customer_id);
        $errors = $import->getErrors();
        try
        {
          $result = Excel::import($import ,$request->file('product_excel'));
        }
        catch (\Exception $e)
        {
			if($e->getMessage() == 'Please enter a valid PF#.')
			{
				return response()->json(['success'=>false,'msg'=> $e->getMessage()]);
			}
			elseif($e->getMessage() == 'Please Upload Valid File')
			{
				return response()->json(['success'=>false,'msg'=> $e->getMessage()]);
			}
			elseif($e->getMessage() == 'Some products not belong to this quotation.')
			{
				return response()->json(['success'=>false,'msg'=> $e->getMessage()]);
			}
			elseif($e->getMessage() == 'Product Not Found in Catalog')
			{
				return response()->json(['success'=>false,'msg'=> $e->getMessage()]);
			}
			elseif($e->getMessage() == 'Please Dont Upload Empty File')
			{
				return response()->json(['success'=>false,'msg'=> $e->getMessage()]);
			}
			else
			{
				return response()->json(['success'=>false,'msg'=> 'Soemthing went wrong']);
			}
        }
        if ($errors->original != null && $errors->original['msg'] != null)
        {
			return response()->json(['success'=>false,'msg'=> $errors->original['msg']]);
        }
        else
        {
			ImportFileHistory::insertRecordIntoDb(Auth::user()->id,'Draft Quotation Detail Page',$request->file('product_excel'));
			return response()->json(['success'=>true,'msg'=>'Imported Successfully']);
        }
    }

    public static function checkProductQtyDraft($request)
    {
		$draftQuotationProduct = DraftQuotationProduct::where('draft_quotation_id',$request->draft_quot_id)->get();
		if($draftQuotationProduct->count() == 0)
		{
			$errorMsg =  'Please add some products in the invoice';
			return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
		}
		return response()->json(['success' => true]);
    }

    public static function exportDraftQuotation($request)
    {
		if ($request->type == 'example')
		{
			$query = null;
		}
		else
		{
			$query = DraftQuotationProduct::with('product','get_draft_quotation','productType','product.units','product.supplier_products')->where('draft_quotation_id', $request->id);
			if ($request->column_name == 'reference_code' && $request->default_sort !== 'id_sort')
			{
				$query = $query->join('products', 'products.id', '=', 'draft_quotation_products.product_id')->orderBy('products.refrence_code', $request->default_sort)->get();
			}
			elseif($request->column_name == 'short_desc' && $request->default_sort != 'id_sort')
			{
				$query = $query->orderBy('short_desc', $request->default_sort)->get();
			}
			elseif($request->column_name == 'supply_from' && $request->default_sort !== 'id_sort')
			{
				$query = $query->leftJoin('suppliers', 'suppliers.id', '=', 'draft_quotation_products.supplier_id')->orderBy('suppliers.reference_name', $request->default_sort)->get();
			}
			elseif($request->column_name == 'type_id' && $request->default_sort !== 'id_sort')
			{
				$query = $query->leftJoin('types', 'types.id', '=', 'draft_quotation_products.type_id')->orderBy('types.title', $request->default_sort)->get();
			}
			elseif($request->column_name == 'brand' && $request->default_sort !== 'id_sort')
			{
				$query = $query->orderBy($request->column_name, $request->default_sort)->get();
			}
			else
			{
				$query = $query->orderBy('id', 'ASC')->get();
			}
		}
		$not_visible_arr = explode(',',$request->table_hide_columns);
		\Excel::store(new DraftQuotationExport($query,$not_visible_arr), 'Draft Quotation Export'.$request->id.'.xlsx');
        return response()->json(['success' => true]);
    }

    public static function uploadDraftQuotationDocuments($request)
    {
        if(isset($request->order_docs))
        {
            for($i=0;$i<sizeof($request->order_docs);$i++)
            {
                $order_doc        = new DraftQuotationAttachment;
                $order_doc->draft_quotation_id = $request->order_id;
                //file
                $extension=$request->order_docs[$i]->extension();
                $filename=date('m-d-Y').mt_rand(999,999999).'__'.time().'.'.$extension;
                $request->order_docs[$i]->move(public_path('uploads/documents/quotations/'),$filename);
                $order_doc->file_name = $filename;
                $order_doc->save();
            }
        	return response()->json(['success' => true]);
        }
        return response()->json(['success' => false]);
    }

    public static function getDraftQuotationFiles($request)
    {
    	$quotation_files = DraftQuotationAttachment::where('draft_quotation_id', $request->quotation_id)->get();
        $html_string ='<div class="table-responsive">
                        <table class="table dot-dash text-center">
                        <thead class="dot-dash">
                        <tr>
                            <th>S.no</th>
                            <th>File</th>
                            <th>Action</th>
                        </tr>
                        </thead><tbody>';
		if($quotation_files->count() > 0)
		{
            $i = 0;
            foreach($quotation_files as $file)
            {
                $i++;
        		$html_string .= '<tr id="quotation-file-'.$file->id.'">
                            <td>'.$i.'</td>
                            <td><a href="'.asset('public/uploads/documents/quotations/'.$file->file_name).'" target="_blank">'.$file->file_name.'</a></td>
                            <td><a href="javascript:void(0);" data-id="'.$file->id.'" class="actionicon deleteFileIcon delete-quotation-file" title="Delete Quotation File"><i class="fa fa-trash"></i></a></td>
                         </tr>';
            }
        }
        else
        {
        	$html_string .= '<tr>
                            <td colspan="3">No File Found</td>
                         </tr>';
        }
        $html_string .= '</tbody></table></div>';
        return $html_string;
	}


    public static function removeDraftQuotationFile($request)
    {
        if(isset($request->id)){
            $quotation_file = DraftQuotationAttachment::find($request->id); // remove images from directory
            $directory  = public_path().'/uploads/documents/quotations/';
            (new OrderController)->removeFile($directory, $quotation_file->file_name); //remove main
            $quotation_file->delete(); // delete record
            return "done"."-SEPARATOR-".$request->id;
        }
    }

    public static function addByRefrenceNumber($request)
    {
		$order = DraftQuotation::with('customer')->find($request->id['id']);
		$customer = Customer::select('category_id')->where('id',$order->customer_id)->first();
		$unit_price = 0;
		if($order->customer_id == null)
		{
			return response()->json(['success' => false, 'successmsg' => 'Please select customer First']);
		}
		$refrence_number = $request->refrence_number;
		$product = Product::where('refrence_code',$refrence_number)->where('status',1)->first();
		if($product)
		{
	        $vat_amount_import = NULL;
	        $getSpData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->supplier_id)->first();
			if($getSpData)
			{
				$vat_amount_import = $getSpData->vat_actual;
			}
        	$is_mkt = CustomerTypeProductMargin::select('is_mkt')->where('product_id',$product->id)->where('customer_type_id',$customer->category_id)->first();
	        $price_calculate_return = $product->price_calculate($product,$order);
	        // dd($price_calculate_return, $order->customer->discount);
	        $unit_price = $price_calculate_return[0];
	        $price_type = $price_calculate_return[1];
	        $price_date = $price_calculate_return[2];
	        $discount = $price_calculate_return[3];
	        $price_after_discount = $price_calculate_return[4];

        	$CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id',$product->id)->where('customer_type_id',$order->customer->category_id)->first();
	        if($CustomerTypeProductMargin != null )
	        {
				$margin      = $CustomerTypeProductMargin->default_value;
				$marginValue = (($margin/100)*$product->selling_price);
				$product_ref_price  = $marginValue+($product->selling_price);
				$exp_unit_cost = $product_ref_price;
	        }
        	$salesWarehouse_id = Auth::user()->get_warehouse->id;
	        //if this product is already in quotation then increment the quantity
	        $draft_quotation_products = DraftQuotationProduct::where('draft_quotation_id',$order->id)->where('product_id',$product->id)->first();
			$new_draft_quotation_products   = new DraftQuotationProduct;
			$new_draft_quotation_products->draft_quotation_id       = $order->id;
			$new_draft_quotation_products->product_id               = $product->id;
			$new_draft_quotation_products->short_desc               = $product->short_desc;
			$new_draft_quotation_products->selling_unit             = $product->selling_unit;
			$new_draft_quotation_products->type_id                  = $product->type_id;
			$new_draft_quotation_products->brand                    = $product->brand;
			$new_draft_quotation_products->category_id              = $product->category_id;
			$new_draft_quotation_products->exp_unit_cost            = $exp_unit_cost;
			$new_draft_quotation_products->actual_unit_cost         = $product->selling_price;
			$new_draft_quotation_products->margin                   = $price_type;
			$new_draft_quotation_products->unit_price               = number_format($unit_price,2,'.','');
			$new_draft_quotation_products->discount               = number_format($discount,2,'.',''); // Discount comes from ProductCustomerFixedPrice Table
			$new_draft_quotation_products->last_updated_price_on    = $price_date;
			// $new_draft_quotation_products->unit_price_with_discount = number_format($unit_price,2,'.','');
			$new_draft_quotation_products->unit_price_with_discount = $price_after_discount != null ? number_format($price_after_discount,2,'.','') : number_format($unit_price,2,'.',''); // comes from ProductCustomerFixedPrice Table
			$new_draft_quotation_products->import_vat_amount        = $vat_amount_import;
			if($order->is_vat == 0)
			{
				$new_draft_quotation_products->vat                = $product->vat;
				if(@$product->vat !== null)
				{
					$unit_p = number_format($unit_price,2,'.','');
					$vat_amount = $unit_p * (@$product->vat/100);
					$final_price_with_vat = $unit_p + $vat_amount;
					$new_draft_quotation_products->unit_price_with_vat = number_format($final_price_with_vat,4,'.','');
				}
				else
				{
					$new_draft_quotation_products->unit_price_with_vat = number_format($unit_price,2,'.','');
				}
			}
			else
			{
				$new_draft_quotation_products->vat                  = 0;
				$new_draft_quotation_products->unit_price_with_vat  = number_format($unit_price,2,'.','');
			}
			$new_draft_quotation_products->is_mkt              = $is_mkt->is_mkt;
			if($product->min_stock > 0)
			{
				$new_draft_quotation_products->is_warehouse = 1;
				$new_draft_quotation_products->from_warehouse_id = $order->from_warehouse_id;
			}
			else
			{
				$new_draft_quotation_products->supplier_id = @$product->supplier_id;
			}
			$new_draft_quotation_products->warehouse_id = $order->from_warehouse_id;
			$new_draft_quotation_products->save();
	        $sub_total     = 0 ;
	        $total_vat     = 0 ;
	        $grand_total   = 0 ;
	        $query         = DraftQuotationProduct::where('draft_quotation_id',$request->id['id'])->get();
			foreach ($query as  $value)
			{
				if($value->is_retail == 'qty')
				{
					$sub_total += $value->total_price;
				}
				else if($value->is_retail == 'pieces')
				{
					$sub_total += $value->total_price;
				}
				$total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total,2) : (@$value->total_price_with_vat - @$value->total_price);
			}
			//Create History Of new Added Product
			(new DraftQuotationHelper)->DraftQuotationHistory($request->id['id'], $refrence_number, 'New Product', '--', 'Added');
        	$grand_total = ($sub_total)-($order->discount)+($order->shipping)+($total_vat);
	        $new_order_p = DraftQuotationProduct::find($new_draft_quotation_products->id);
	        $getColumns = (new DraftQuotationProduct)->getColumns($new_order_p);
        	return response()->json(['success' => true, 'successmsg' => 'Product successfully Added', 'sub_total' => number_format(@$sub_total, 2, '.', ','), 'total_vat' => number_format(@$total_vat, 2, '.', ','), 'grand_total' => number_format(@$grand_total, 2, '.', ','),'total_products' => $order->draft_quotation_products->count($request->id['id']),'getColumns' => $getColumns]);
		}
		else
		{
			if (@$request->from_bulk)
			{
				throw new \ErrorException('Product Not Found in Catalog');
			}
			return response()->json(['success' => false, 'successmsg' => 'Product Not Found in Catalog']);
		}
    }


    public static function addProdToQuotation($request)
    {
        // dd('here');
		try
		{
			$customer = Customer::select('category_id')->where('id',$request->customer_id)->first();
            // dd($customer->category_id);
			$order = DraftQuotation::find($request->quotation_id);
			if ($order == null)
			{
				return response()->json([
				'success' => false,
				'msg' => 'Order Invoice Already Deleted'
				]);
			}
			$product_arr = explode(',', $request->selected_products);
            if($product_arr[0] == "") {
				return response()->json(['success' => false, 'message' => 'Select Product !!']);
            }
			foreach($product_arr as $product)
			{
				$is_mkt = CustomerTypeProductMargin::select('is_mkt')->where('product_id',$product)->where('customer_type_id',$customer->category_id)->first();
				$product = Product::find($product);
				$vat_amount_import = NULL;
				$getSpData = SupplierProducts::where('product_id',$product->id)->where('supplier_id',$product->supplier_id)->first();
				if($getSpData)
				{
					$vat_amount_import = $getSpData->vat_actual;
				}
				$order = DraftQuotation::find($request->quotation_id);
				$exp_unit_cost = $product->selling_price;
				$price_calculate_return = $product->price_calculate($product,$order);
				$unit_price = $price_calculate_return[0];
				$price_type = $price_calculate_return[1];
				$price_date = $price_calculate_return[2];
				$discount = $price_calculate_return[3];
				$price_after_discount = $price_calculate_return[4];
				$CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id',$product->id)->where('customer_type_id',$order->customer->category_id)->first();
				if($CustomerTypeProductMargin != null )
				{
					$margin = $CustomerTypeProductMargin->default_value;
					$margin = (($margin/100)*$product->selling_price);
					$product_ref_price  = $margin+($product->selling_price);
					$exp_unit_cost = $product_ref_price;
				}
				//Create History Of new Added Product
				(new DraftQuotationHelper)->DraftQuotationHistory($request->quotation_id, $product->refrence_code, 'New Product', '--', 'Added');
				//if this product is already in quotation then increment the quantity and calculate price accordingly
				$quotation_product = DraftQuotationProduct::where('draft_quotation_id',$request->quotation_id)->where('product_id',$product->id)->first();
				$salesWarehouse_id = Auth::user()->get_warehouse->id;
				$new_quotation_product                           = new DraftQuotationProduct;
				$new_quotation_product->draft_quotation_id       = $request->quotation_id;
				$new_quotation_product->product_id               = $product->id;
				$new_quotation_product->short_desc               = $product->short_desc;
				$new_quotation_product->selling_unit             = $product->selling_unit;
				$new_quotation_product->brand                    = $product->brand;
				$new_quotation_product->category_id              = $product->category_id;
				$new_quotation_product->hs_code                  = $product->hs_code;
				$new_quotation_product->product_temprature_c     = $product->product_temprature_c;
				$new_quotation_product->type_id                  = $product->type_id;
				$new_quotation_product->exp_unit_cost            = $exp_unit_cost;
				$new_quotation_product->actual_unit_cost         = $product->selling_price;
				$new_quotation_product->margin                   = $price_type;
				$new_quotation_product->last_updated_price_on    = $price_date;
				$new_quotation_product->unit_price               = number_format($unit_price,2,'.','');
				$new_quotation_product->discount               = number_format($discount,2,'.',''); // Discount comes from ProductCustomerFixedPrice Table
				// $new_quotation_product->unit_price_with_discount = number_format($unit_price,2,'.','');
				$new_quotation_product->unit_price_with_discount = $price_after_discount != null ? number_format($price_after_discount,2,'.','') : number_format($unit_price,2,'.',''); // comes from ProductCustomerFixedPrice Table
				$new_quotation_product->import_vat_amount        = $vat_amount_import;
				if($order->is_vat == 0)
				{
					$new_quotation_product->vat                = $product->vat;
					if(@$product->vat !== null)
					{
						$unit_p = number_format($unit_price,2,'.','');
						$vat_amount = $unit_p * (@$product->vat/100);
						$final_price_with_vat = $unit_p + $vat_amount;
						$new_quotation_product->unit_price_with_vat = number_format($final_price_with_vat,2,'.','');
					}
					else
					{
						$new_quotation_product->unit_price_with_vat = number_format($unit_price,2,'.','');
					}
				}
				else
				{
					$new_quotation_product->vat                  = 0;
					$new_quotation_product->unit_price_with_vat  = number_format($unit_price,2,'.','');
				}
				$new_quotation_product->is_mkt               = $is_mkt->is_mkt;
				if($product->min_stock > 0)
				{
					$new_quotation_product->is_warehouse = 1;
					$new_quotation_product->from_warehouse_id = $order->from_warehouse_id;
				}
                $new_quotation_product->supplier_id = $product->supplier_id;

				$new_quotation_product->warehouse_id = $order->from_warehouse_id;
				$new_quotation_product->save();
			}
			$sub_total     = 0 ;
			$total_vat     = 0 ;
			$grand_total   = 0 ;
			$query         = DraftQuotationProduct::where('draft_quotation_id',$request->quotation_id)->get();
			foreach ($query as  $value)
			{
				if($value->is_retail == 'qty')
				{
					$sub_total += $value->total_price;
				}
				else if($value->is_retail == 'pieces')
				{
					$sub_total += $value->total_price;
				}
				$total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total,2) : (@$value->total_price_with_vat - @$value->total_price);
				$grand_total = ($sub_total)-($order->discount)+($order->shipping)+($total_vat);
				$new_order_p = DraftQuotationProduct::find($new_quotation_product->id);
				$getColumns = (new DraftQuotationProduct)->getColumns($new_order_p);
				return response()->json(['success' => true, 'successmsg' => 'Product successfully Added', 'sub_total' => number_format(@$sub_total, 2, '.', ','), 'total_vat' => number_format(@$total_vat, 2, '.', ','), 'grand_total' => number_format(@$grand_total, 2, '.', ','),'total_products' => $order->draft_quotation_products->count($request->id),'getColumns' => $getColumns]);
			}
		}
		catch (Exception $e)
		{
			return["status"=>false,"message"=>$e->getMessage()];
		}
    }

    private function DraftQuotationHistory($quotation_id, $refrence_code, $column_name, $old_value, $new_value)
    {
    	$order_history = new DraftQuatationProductHistory;
		$order_history->user_id = Auth::user()->id;
		$order_history->order_id = $quotation_id;
		$order_history->reference_number = $refrence_code;
		$order_history->old_value = $old_value;
		$order_history->column_name = $column_name;
		$order_history->new_value = $new_value;
		$order_history->save();
    }

    public static function addInquiryProduct($request)
    {
        $inv_id = $request->inv_id;
        $order = DraftQuotation::find($inv_id);
        if ($order == null)
        {
          return response()->json([
            'success' => false,
            'msg' => 'Order Invoice Already Deleted'
          ]);
        }
        $salesWarehouse_id = Auth::user()->get_warehouse->id;
        $dq_products = new DraftQuotationProduct;
        $dq_products->draft_quotation_id = $order->id;
        $dq_products->name             = $request->product_name;
        $dq_products->short_desc       = $request->description;
        $dq_products->quantity         = 1;
        $dq_products->warehouse_id     = $salesWarehouse_id;
        $dq_products->is_billed        = "Billed";
        $dq_products->created_by       = Auth::user()->id;
        $dq_products->save();

        $new_order_p = DraftQuotationProduct::find($dq_products->id);
        $getColumns = (new DraftQuotationProduct)->getColumns($new_order_p);

        return response()->json(['success' => true, 'getColumns' => $getColumns]);
    }

    public static function UpdateQuotationData(Request $request){
    	$draft_quotation_product = DraftQuotationProduct::find($request->draft_quotation_id);
        if($draft_quotation_product == null)
        {
          throw new \ErrorException('Some products not belong to this quotation.');
        }
        $order = DraftQuotation::find($draft_quotation_product->draft_quotation_id);
        $radio_click = @$request->old_value;
        $item_unit_price = number_format($draft_quotation_product->unit_price,2,'.','');
        $supply_from = '';
        foreach($request->except('draft_quotation_id','old_value') as $key => $value)
        {
        	if($key == 'quantity' && $draft_quotation_product->product != null)
			{
				$decimal_places = $draft_quotation_product->product->sellingUnits->decimal_places;
				$value = round($value,$decimal_places);
			}
			if($key == 'short_desc')
            {
                $draft_quotation_product->$key = $value;
            }
            elseif($key == 'from_warehouse_id')
            {
            	$Stype = explode('-', $value);
				if($Stype[0] == 's')
				{
					$draft_quotation_product->supplier_id = $Stype[1];
					$draft_quotation_product->from_warehouse_id = null;
					$draft_quotation_product->is_warehouse = 0;

					$supply_from = $draft_quotation_product->from_supplier != null ? $draft_quotation_product->from_supplier->reference_name : '--';
				}
				else
				{
					$draft_quotation_product->from_warehouse_id = $draft_quotation_product->get_draft_quotation != null ? $draft_quotation_product->get_draft_quotation->from_warehouse_id : null;
					$draft_quotation_product->is_warehouse = 1;
					$draft_quotation_product->supplier_id = null;
					$supply_from = 'Warehouse';
				}
            }
            elseif($key == 'selling_unit')
            {
              $draft_quotation_product->selling_unit = $value;
            }
			else{

				if($key == 'quantity' && @$radio_click == 'clicked')
				{
					$draft_quotation_product->is_retail = 'qty';
				}
				if($key == 'number_of_pieces' && @$radio_click == 'clicked')
	        	{
	        		$draft_quotation_product->is_retail = 'pieces';
	        	}
	        	if($key == 'unit_price_with_vat'){
	        		$value = number_format(@$value,2,'.','');
					$send_value_vat = $value*(@$draft_quotation_product->vat / 100);
					$final_valuee = ($value * 100) / (100 + @$draft_quotation_product->vat);
					$final_value = number_format($final_valuee,2,'.','');
					$draft_quotation_product->unit_price = @$final_value;
	        	}
				$draft_quotation_product->$key = $value;
				$draft_quotation_product->save();
				$calcu = DraftQuotationHelper::orderCalculation($draft_quotation_product);
			}
        	$draft_quotation_product->save();
        }
        //taking history
        $hist = DraftQuotationHelper::takeHistory($draft_quotation_product, $request, $key, $value, $order);

        $sub_total     = 0 ;
        $sub_total_with_vat = 0 ;
        $sub_total_w_w = 0;
        $total_vat = 0;
        $sub_total_without_discount = 0;
        $item_level_dicount = 0;
        $query         = DraftQuotationProduct::where('draft_quotation_id',$draft_quotation_product->draft_quotation_id)->get();
        foreach ($query as $value)
        {
			$order_id = ($value->id);
			if($value->discount != 0)
			{
				if($value->discount == 100)
				{
					if($value->is_retail == 'pieces')
					{
						$discount_full = $value->unit_price_with_vat * $value->number_of_pieces;
						$sub_total_without_discount += $discount_full;
					}
					else
					{
						$discount_full = $value->unit_price_with_vat * $value->quantity;
						$sub_total_without_discount += $discount_full;
					}
					$item_level_dicount += $discount_full;
				}
				else
				{
					$sub_total_without_discount += $value->total_price / ((100 - $value->discount)/100);
					$item_level_dicount += ($value->total_price / ((100 - $value->discount)/100)) - $value->total_price;
				}
			}
			else
			{
				$sub_total_without_discount += $value->total_price;
			}
            $sub_total += $value->total_price;
            $sub_total_with_vat = $sub_total_with_vat + $value->total_price_with_vat;
            $sub_total_w_w += number_format($value->total_price_with_vat,2,'.','');
            $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total,2) : (@$value->total_price_with_vat - @$value->total_price);
        }
        $vat = $total_vat;
        $grand_total = ($sub_total_w_w)-($order->discount)+($order->shipping);
        //item level calculations
        $total_amount_wo_vat = number_format((float)$draft_quotation_product->total_price,2,'.','');
        $total_amount_w_vat = number_format((float)$draft_quotation_product->total_price_with_vat,2,'.','');
        $unit_price_after_discount = $draft_quotation_product->unit_price_with_discount != null ?  number_format($draft_quotation_product->unit_price_with_discount,2,'.','') : '--';
        $unit_price = $draft_quotation_product->unit_price != null ?  number_format($draft_quotation_product->unit_price,2,'.','') : '--';
        $unit_price_w_vat = $draft_quotation_product->unit_price_with_vat != null ?  number_format($draft_quotation_product->unit_price_with_vat,2,'.','') : '--';
        $quantity = round($draft_quotation_product->quantity,4);
        $pcs = round($draft_quotation_product->number_of_pieces,4);
        if($draft_quotation_product->supplier_id != null)
        {
        	$supply_from = $draft_quotation_product->from_supplier != null ? $draft_quotation_product->from_supplier->reference_name : '--';
        }
        else
        {
        	$supply_from = 'Warehouse';
        }
        $type = $draft_quotation_product->productType != null ? $draft_quotation_product->productType->title : 'Select';
        return response()->json(['success' => true,'vat' => number_format($vat,2, '.', ','), 'grand_total' => number_format($grand_total, 2, '.', ','),'sub_total' => number_format(@$sub_total, 2, '.', ','),'sub_total_without_discount' => number_format(floor(@$sub_total_without_discount*100)/100, 2, '.', ','),'item_level_dicount' => number_format(floor(@$item_level_dicount*100)/100, 2, '.', ','),'total_amount_wo_vat' => $total_amount_wo_vat,'total_amount_w_vat' => $total_amount_w_vat,'id' => $draft_quotation_product->id,'unit_price_after_discount' => $unit_price_after_discount,'unit_price' => $unit_price,'unit_price_w_vat' => $unit_price_w_vat,'supply_from' => $supply_from,'quantity' => $quantity,'pcs' => $pcs,'type' => $type,'type_id' => $draft_quotation_product->type_id]);
    }

    public static function UpdateQuotationDataOld(Request $request)
    {
        $draft_quotation_product = DraftQuotationProduct::find($request->draft_quotation_id);
        if($draft_quotation_product == null)
        {
          throw new \ErrorException('Some products not belong to this quotation.');
        }
        $order = DraftQuotation::find($draft_quotation_product->draft_quotation_id);
        $radio_click = @$request->old_value;
        $item_unit_price = number_format($draft_quotation_product->unit_price,2,'.','');
        $supply_from = '';
        foreach($request->except('draft_quotation_id','old_value') as $key => $value)
        {
			if($key == 'quantity' && $draft_quotation_product->product != null)
			{
				$decimal_places = $draft_quotation_product->product->sellingUnits->decimal_places;
				$value = round($value,$decimal_places);
			}
			if($key == 'unit_price')
			{
                $draft_quotation_product->$key = $value == '--' ? '0.0' : number_format($value,2,'.','');
				if($draft_quotation_product->discount != null && $draft_quotation_product->discount != 0)
				{
				$draft_quotation_product->unit_price_with_discount = $draft_quotation_product->unit_price * (100 - $draft_quotation_product->discount)/100;
				}
				else
				{
				$draft_quotation_product->unit_price_with_discount = $draft_quotation_product->unit_price;
				}
			}
			else if($key == 'unit_price_with_vat')
			{
				if($draft_quotation_product->vat == 0)
				{
					$draft_quotation_product->$key = number_format($value,2,'.','');
				}
				else
				{
					$draft_quotation_product->$key = number_format($value,2,'.','');
				}
				if($draft_quotation_product->discount != null && $draft_quotation_product->discount != 0)
				{
					$draft_quotation_product->unit_price_with_discount = $draft_quotation_product->unit_price * (100 - $draft_quotation_product->discount)/100;
				}
				else
				{
					$draft_quotation_product->unit_price_with_discount = $draft_quotation_product->unit_price;
				}
			}
			else
			{
				$draft_quotation_product->$key = $value;
			}

			if($key == 'quantity')
			{
                $decimal_places = $draft_quotation_product->product->sellingUnits->decimal_places;
                $value = round($value, $decimal_places);
				if(@$radio_click == 'clicked')
				{
					$draft_quotation_product->is_retail = 'qty';
				}
				if(@$draft_quotation_product->is_retail == 'qty')
				{
					if($draft_quotation_product->product_id == null)
					{
						$total_price = $item_unit_price*$value;
						$discount = $draft_quotation_product->discount;
						if($discount != null)
						{
						  	$dis = $discount / 100;
						    $discount_value = $dis * $total_price;
						    $result = $total_price - $discount_value;
						}
						else
						{
						  $result = $total_price;
						}
						$draft_quotation_product->total_price = number_format($result,2,'.','');
						$vat = $draft_quotation_product->vat;
						$vat_amountt = @$item_unit_price * ( @$vat / 100 );
						$vat_amount = number_format($vat_amountt,4,'.','');
						$vat_amount_total_over_item = $vat_amount * $value;
						$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
						if($draft_quotation_product->vat !== null && $draft_quotation_product->unit_price_with_vat !== null)
						{
							$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
						}
						else
						{
							$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
						}
						if(@$discount !== null)
						{
							$percent_value = $discount / 100;
							$dis_value = $unit_price_with_vat * $percent_value;
							$tpwt = $unit_price_with_vat - @$dis_value;

							$vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
							$vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
							$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
						}
						else
						{
							$tpwt = $unit_price_with_vat;
						}
						$draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
					}
					else
					{
						$total_price = $item_unit_price*$value;
						$discount = $draft_quotation_product->discount;
						if($discount != null)
						{
						  	$dis = $discount / 100;
						    $discount_value = $dis * $total_price;
						     $result = $total_price - $discount_value;
						}
						else
						{
							$result = $total_price;
						}
						$draft_quotation_product->total_price = number_format($result,2,'.','');
						$vat = $draft_quotation_product->vat;
						$vat_amountt = @$item_unit_price * ( @$vat / 100 );
						$vat_amount = number_format($vat_amountt,4,'.','');
						$vat_amount_total_over_item = $vat_amount * $value;
						$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
						if($draft_quotation_product->vat !== null && $draft_quotation_product->unit_price_with_vat !== null)
						{
							$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
						}
						else
						{
							$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
						}
						if(@$discount !== null)
						{
							$percent_value = $discount / 100;
							$dis_value = $unit_price_with_vat * $percent_value;
							$tpwt = $unit_price_with_vat - @$dis_value;

							$vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
							$vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
							$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
						}
						else
						{
							$tpwt = $unit_price_with_vat;
						}
						$draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
					}
				}
            }
            else if($key == 'number_of_pieces')
            {
            	if(@$radio_click == 'clicked')
            	{
            		$draft_quotation_product->is_retail = 'pieces';
            	}
            	if(@$draft_quotation_product->is_retail == 'pieces')
            	{
					if($draft_quotation_product->product_id == null )
					{
						$total_price = $item_unit_price*$value;
						$discount = $draft_quotation_product->discount;
						if($discount != null)
						{
							$dis = $discount / 100;
							$discount_value = $dis * $total_price;
							$result = $total_price - $discount_value;
						}
						else
						{
							$result = $total_price;
						}
						$draft_quotation_product->total_price = number_format($result,2,'.','');
						$vat = $draft_quotation_product->vat;
						$vat_amountt = @$item_unit_price * ( @$vat / 100 );
						$vat_amount = number_format($vat_amountt,4,'.','');
						$vat_amount_total_over_item = $vat_amount * $value;
						$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
						if($draft_quotation_product->vat !== null && $draft_quotation_product->unit_price_with_vat !== null)
						{
							$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
						}
						else
						{
							$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
						}
						if(@$discount !== null)
						{
							$percent_value = $discount / 100;
							$dis_value = $unit_price_with_vat * $percent_value;
							$tpwt = $unit_price_with_vat - @$dis_value;

							$vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
							$vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
							$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
						}
						else
						{
							$tpwt = $unit_price_with_vat;
						}
						$draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
					}
					else
					{
						$total_price = $item_unit_price*$value;
						$discount = $draft_quotation_product->discount;
						if($discount != null)
						{
							$dis = $discount / 100;
							$discount_value = $dis * $total_price;
							$result = $total_price - $discount_value;
						}
						else
						{
							$result = $total_price;
						}
						$draft_quotation_product->total_price = number_format($result,2,'.','');
						$vat = $draft_quotation_product->vat;
						$vat_amountt = @$item_unit_price * ( @$vat / 100 );
						$vat_amount = number_format($vat_amountt,4,'.','');
						$vat_amount_total_over_item = $vat_amount * $value;
						$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');

						if($draft_quotation_product->vat !== null && $draft_quotation_product->unit_price_with_vat !== null)
						{
							$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
						}
						else
						{
							$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
						}
						if(@$discount !== null)
						{
							$percent_value = $discount / 100;
							$dis_value = $unit_price_with_vat * $percent_value;
							$tpwt = $unit_price_with_vat - @$dis_value;

							$vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
							$vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
							$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
						}
						else
						{
							$tpwt = $unit_price_with_vat;
						}
						$draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
					}

                    // Sup-739 Funcionality. Order Qty per piece comes from product detail page and change qty inv column when value enteres in pcs column
                    $order_qty_per_piece = $draft_quotation_product->product->order_qty_per_piece;
                    if ($order_qty_per_piece != null && $order_qty_per_piece != 0 && $order_qty_per_piece != '0' && $order_qty_per_piece != '0' && $value != null && $value != 0 && $value != "0")
                    {
                        $order_qty_per_piece_value = $value * $order_qty_per_piece;
                        $draft_quotation_product->quantity = $order_qty_per_piece_value;
                    }
            	}
                else if(@$draft_quotation_product->is_retail == 'qty')
                {
                    // Sup-739 Funcionality. Order Qty per piece comes from product detail page and change qty inv column when value enteres in pcs column
                    $order_qty_per_piece = $draft_quotation_product->product->order_qty_per_piece;
                    if ($order_qty_per_piece != null && $order_qty_per_piece != 0 && $order_qty_per_piece != '0' && $order_qty_per_piece != '0' && $value != null && $value != 0 && $value != "0")
                    {
                        $order_qty_per_piece_value = $value * $order_qty_per_piece;
                        $draft_quotation_product->quantity = $order_qty_per_piece_value;

                        if($draft_quotation_product->product_id == null)
                        {
                            $total_price = $item_unit_price*$order_qty_per_piece_value;
                            $discount = $draft_quotation_product->discount;
                            if($discount != null)
                            {
                                  $dis = $discount / 100;
                                $discount_value = $dis * $total_price;
                                $result = $total_price - $discount_value;
                            }
                            else
                            {
                              $result = $total_price;
                            }
                            $draft_quotation_product->total_price = number_format($result,2,'.','');
                            $vat = $draft_quotation_product->vat;
                            $vat_amountt = @$item_unit_price * ( @$vat / 100 );
                            $vat_amount = number_format($vat_amountt,4,'.','');
                            $vat_amount_total_over_item = $vat_amount * $order_qty_per_piece_value;
                            $draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
                            if($draft_quotation_product->vat !== null && $draft_quotation_product->unit_price_with_vat !== null)
                            {
                                $unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
                            }
                            else
                            {
                                $unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
                            }
                            if(@$discount !== null)
                            {
                                $percent_value = $discount / 100;
                                $dis_value = $unit_price_with_vat * $percent_value;
                                $tpwt = $unit_price_with_vat - @$dis_value;

                                $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                                $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                                $draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
                            }
                            else
                            {
                                $tpwt = $unit_price_with_vat;
                            }
                            $draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
                        }
                        else
                        {
                            $total_price = $item_unit_price*$order_qty_per_piece_value;
                            $discount = $draft_quotation_product->discount;
                            if($discount != null)
                            {
                                  $dis = $discount / 100;
                                $discount_value = $dis * $total_price;
                                 $result = $total_price - $discount_value;
                            }
                            else
                            {
                                $result = $total_price;
                            }
                            $draft_quotation_product->total_price = number_format($result,2,'.','');
                            $vat = $draft_quotation_product->vat;
                            $vat_amountt = @$item_unit_price * ( @$vat / 100 );
                            $vat_amount = number_format($vat_amountt,4,'.','');
                            $vat_amount_total_over_item = $vat_amount * $order_qty_per_piece_value;
                            $draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
                            if($draft_quotation_product->vat !== null && $draft_quotation_product->unit_price_with_vat !== null)
                            {
                                $unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
                            }
                            else
                            {
                                $unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
                            }
                            if(@$discount !== null)
                            {
                                $percent_value = $discount / 100;
                                $dis_value = $unit_price_with_vat * $percent_value;
                                $tpwt = $unit_price_with_vat - @$dis_value;

                                $vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
                                $vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
                                $draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
                            }
                            else
                            {
                                $tpwt = $unit_price_with_vat;
                            }
                            $draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
                        }

                    }
                }

            }
            elseif($key == 'unit_price')
            {
              	if($draft_quotation_product->is_retail == 'qty')
              	{
                	$quantity = $draft_quotation_product->quantity;
                }
                else if(@$draft_quotation_product->is_retail == 'pieces')
                {
                	$quantity = @$draft_quotation_product->number_of_pieces;
                }
				if($draft_quotation_product->product_id == null )
				{
					$total_pricee = @$quantity*number_format($value,2,'.','');
					$total_price = $total_pricee;
					$discount = $draft_quotation_product->discount;
					if($discount != null)
					{
						$dis = $discount / 100;
						$discount_value = $dis * $total_price;
						$result = $total_price - $discount_value;
					}
					else
					{
						$result = $total_price;
					}
					$draft_quotation_product->total_price = number_format($result,2,'.','');
					$unit_price = number_format($value,2,'.','');
					$vat = $draft_quotation_product->vat;
					$vat_amountt = @$unit_price * ( @$vat / 100 );
					$vat_amount = number_format($vat_amountt,4,'.','');
					$vat_amount_total_over_item = $vat_amount * $quantity;
					$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');

					$draft_quotation_product->unit_price_with_vat = $unit_price + $vat_amount;
					$draft_quotation_product->save();
					if($draft_quotation_product->vat !== null && $draft_quotation_product->unit_price_with_vat !== null)
					{
						$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
					}
					else
					{
						$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
					}
					if(@$discount !== null)
					{
						$percent_value = $discount / 100;
						$dis_value = $unit_price_with_vat * $percent_value;
						$tpwt = $unit_price_with_vat - @$dis_value;

						$vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
						$vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
						$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
					}
					else
					{
						$tpwt = $unit_price_with_vat;
					}
					$draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
				}
				else
				{
                    $total_pricee = $value == '--' ? '0.0' : @$quantity*number_format($value,2,'.','');
					$total_price = $total_pricee;
					$discount = $draft_quotation_product->discount;
					if($discount != null)
					{
						$dis = $discount / 100;
						$discount_value = $dis * $total_price;
						$result = $total_price - $discount_value;
					}
					else
					{
						$result = $total_price;
					}
                    $draft_quotation_product->total_price = $result == '--' ? '0.0' : number_format($result,2,'.','');
                    $unit_price = $value == '--' ? '0.0' : number_format($value,2,'.','');

					$vat = $draft_quotation_product->vat;
					$vat_amountt = @$unit_price * ( @$vat / 100 );
                    $vat_amount = $vat_amountt == '--' ? '0.0' : number_format($vat_amountt,4,'.','');
					$vat_amount_total_over_item = $vat_amount * $quantity;
					$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
					$draft_quotation_product->unit_price_with_vat = number_format(($unit_price + $vat_amount),2,'.','');
					$draft_quotation_product->save();
					if($draft_quotation_product->vat !== null && $draft_quotation_product->unit_price_with_vat !== null)
					{
						$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
					}
					else
					{
						$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
					}
					if(@$discount !== null)
					{
						$percent_value = $discount / 100;
						$dis_value = $unit_price_with_vat * $percent_value;
						$tpwt = $unit_price_with_vat - @$dis_value;
						$vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
						$vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
						$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
					}
					else
					{
						$tpwt = $unit_price_with_vat;
					}
					$draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
				}
            }
            elseif($key == 'unit_price_with_vat')
            {
				$value = number_format(@$value,2,'.','');
				$send_value_vat = $value*(@$draft_quotation_product->vat / 100);
				$final_valuee = ($value * 100) / (100 + @$draft_quotation_product->vat);
				$final_value = number_format($final_valuee,2,'.','');
				$draft_quotation_product->unit_price = @$final_value;

            	if(@$order->primary_status == 3)
            	{
	                if($draft_quotation_product->is_retail == 'qty')
	                {
	                  $quantity = $draft_quotation_product->qty_shipped;
	                }
	                else if(@$draft_quotation_product->is_retail == 'pieces')
	                {
	                  $quantity = @$draft_quotation_product->number_of_pieces;
                	}
				}
				else
				{
					if($draft_quotation_product->is_retail == 'qty')
					{
						$quantity = $draft_quotation_product->quantity;
					}
					else if(@$draft_quotation_product->is_retail == 'pieces')
					{
						$quantity = @$draft_quotation_product->number_of_pieces;
					}
				}
            	$total_price = $final_value*$quantity;
                $discount = $draft_quotation_product->discount;

                if($discount != null)
                {
                	$dis = $discount / 100;
                    $discount_value = $dis * $total_price;
                    $result = $total_price - $discount_value;
                }
                else
                {
                	$result = $total_price;
                }
                $draft_quotation_product->total_price = number_format($result,2,'.','');
                $unit_price = $final_value;
                $vat = $draft_quotation_product->vat;
                $vat_amountt = @$unit_price * ( @$vat / 100 );
                $vat_amount = number_format($vat_amountt,4,'.','');
                $vat_amount_total_over_item = $vat_amount * $quantity;
                $draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
                $draft_quotation_product->$key = number_format(($unit_price + $vat_amount),2,'.','');
                $unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
                if(@$discount !== null)
                {
					$percent_value = $discount / 100;
					$dis_value = $unit_price_with_vat * $percent_value;
					$tpwt = $unit_price_with_vat - @$dis_value;

					$vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
					$vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
					$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
                }
                else
                {
                	$tpwt = $unit_price_with_vat;
                }
                $draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
            }
            elseif($key == 'vat')
            {
                if($draft_quotation_product->is_retail == 'qty')
                {
                	$quantity = $draft_quotation_product->quantity;
                }
                else if(@$draft_quotation_product->is_retail == 'pieces')
                {
                	$quantity = @$draft_quotation_product->number_of_pieces;
                }
                $total_price = $item_unit_price*$quantity;
                $discount = $draft_quotation_product->discount;
                if($discount != null)
                {
                	$dis = $discount / 100;
                    $discount_value = $dis * $total_price;
                    $result = $total_price - $discount_value;
                }
                else
                {
                	$result = $total_price;
                }
                $draft_quotation_product->total_price = number_format($result,2,'.','');
                $vat = $draft_quotation_product->vat;
                $vat_amountt = @$item_unit_price * ( @$vat / 100 );
                $vat_amount = number_format($vat_amountt,4,'.','');
                $vat_amount_total_over_item = $vat_amount * $quantity;
                $draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
                $draft_quotation_product->unit_price_with_vat = number_format(($item_unit_price + $vat_amount),2,'.','');
                $draft_quotation_product->save();
                if($draft_quotation_product->unit_price_with_vat !== null && $value !== 0)
                {
                	$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
                }
                else
                {
                	$unit_price_with_vat = round($total_price,2)+round($vat_amount_total_over_item,2);
                }
                if(@$discount !== null)
                {
					$percent_value = $discount / 100;
					$dis_value = $unit_price_with_vat * $percent_value;
					$tpwt = $unit_price_with_vat - @$dis_value;

					$vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
					$vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
					$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
                }
                else
                {
                	$tpwt = $unit_price_with_vat;
                }
                $draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
            }
            elseif($key == 'short_desc')
            {
                $draft_quotation_product->$key = $value;
            }
            elseif($key == 'from_warehouse_id')
            {
            	$Stype = explode('-', $value);
				if($Stype[0] == 's')
				{
					$draft_quotation_product->supplier_id = $Stype[1];
					$draft_quotation_product->from_warehouse_id = null;
					$draft_quotation_product->is_warehouse = 0;

					$supply_from = $draft_quotation_product->from_supplier != null ? $draft_quotation_product->from_supplier->reference_name : '--';
				}
				else
				{
					$draft_quotation_product->from_warehouse_id = $draft_quotation_product->get_draft_quotation != null ? $draft_quotation_product->get_draft_quotation->from_warehouse_id : null;
					$draft_quotation_product->is_warehouse = 1;
					$draft_quotation_product->supplier_id = null;
					$supply_from = 'Warehouse';
				}
            }
            elseif($key == 'selling_unit')
            {
              $draft_quotation_product->selling_unit = $value;
            }
            elseif($key == 'discount')
            {
				$percent_value = $value / 100;
				if($draft_quotation_product->is_retail == 'qty')
				{
					$quantity = $draft_quotation_product->quantity;
				}
				else if(@$draft_quotation_product->is_retail == 'pieces')
				{
					$quantity = @$draft_quotation_product->number_of_pieces;
				}
				$discount_val = $item_unit_price * $percent_value;
				$value_discount = $item_unit_price - $discount_val;
				$draft_quotation_product->unit_price_with_discount = $value_discount;
            	$total = $item_unit_price*$quantity;
				$discount_value = $percent_value * $total;
				$result = $total - $discount_value;
				$draft_quotation_product->total_price = number_format($result,2,'.','');
                $vat = $draft_quotation_product->vat;
                $vat_amountt = @$item_unit_price * ( @$vat / 100 );
                $vat_amount = number_format($vat_amountt,4,'.','');
                $vat_amount_total_over_item = $vat_amount * $quantity;
                $draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
                if($draft_quotation_product->vat !== null && $draft_quotation_product->unit_price_with_vat !== null)
                {
                	$unit_price_with_vat = round($total,2)+round($vat_amount_total_over_item,2);
                }
                else
                {
                	$unit_price_with_vat = round($total,2)+round($vat_amount_total_over_item,2);
                }
            	if($value !== null)
                {
					$percent_value = $value / 100;
					$dis_value = $unit_price_with_vat * $percent_value;
					$tpwt = $unit_price_with_vat - @$dis_value;

					$vat_amount_total_over_item_with_discount = @$vat_amount_total_over_item * $percent_value;
					$vat_amount_total_over_item = $vat_amount_total_over_item - $vat_amount_total_over_item_with_discount;
					$draft_quotation_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
                }
                else
                {
                	$tpwt = $unit_price_with_vat;
                }
                $draft_quotation_product->total_price_with_vat = number_format(@$tpwt,2,'.','');
            }
            $draft_quotation_product->save();
            //Draft quation History
            $refrence_code = $draft_quotation_product->is_billed == "Product" ? $draft_quotation_product->product->refrence_code : null;
            $old_value = @$request->old_value == 'clicked' ? '--' : @$request->old_value;
			if($key == 'from_warehouse_id')
			{
				$column_name = "Supply From";
			}
			else if($key == 'short_desc')
			{
				$column_name = "Description";
			}
			else if($key == 'selling_unit')
			{
				$column_name = "Sales Unit";
			}
			else if($key == 'discount')
			{
				$column_name = "Discount";
			}
			else if($key == 'vat')
			{
				$column_name = "VAT";
			}
			else if($key == 'brand')
			{
				$column_name = "Brand";
			}
			else if($key == 'quantity')
			{
				$column_name = $order->primary_status == 3 ? 'Qty Ordered' : 'Qty';
			}
			else if($key == 'qty_shipped')
			{
				$column_name = "Qty Sent";
			}
			else if($key == 'number_of_pieces')
			{
				$column_name = $order->primary_status == 3 ? 'Pieces Ordered' : 'Pieces';
			}
			else if($key == 'pcs_shipped')
			{
				$column_name = "Pieces Sent";
			}
			else if($key == 'unit_price_with_vat')
			{
				$column_name = "Unit Price (+VAT)";
			}
			else if($key == 'unit_price')
			{
				$column_name = "Default Price";
			}
			else if($key == 'type_id')
			{
				$column_name = "Type";
			}
			else
			{
				$column_name = @$key;
			}
			if($key == 'from_warehouse_id')
			{
				$value = explode('-', $value);
				if($Stype[0] == 's')
				{
					$supplier =supplier::find(@$value[1]);
					$new_value= $supplier->reference_name;
				}
				elseif($Stype[0] == 'w')
				{
					$new_value ='warehouse';
				}
			}
			else
			{
				if($key == 'pcs_shipped')
				{
					$new_value = @$request->old_value == 'clicked' ? 'pc' : @$value;
				}
				else if($key == 'qty_shipped')
				{
					$new_value = @$request->old_value == 'clicked' ? 'kg' : @$request->qty_shipped;
				}
				else if($key == 'quantity')
				{
					$new_value = @$request->old_value == 'clicked' ? 'kg' : @$value;
				}
				else if($key == 'number_of_pieces')
				{
					$new_value = @$request->old_value == 'clicked' ? 'pc' : @$value;
				}
				else
				{
					$new_value = @$value;
				}
			}
            (new DraftQuotationHelper)->DraftQuotationHistory(@$draft_quotation_product->draft_quotation_id, $refrence_code, $column_name, $old_value, $new_value);
        }
        $sub_total     = 0 ;
        $sub_total_with_vat = 0 ;
        $sub_total_w_w = 0;
        $total_vat = 0;
        $sub_total_without_discount = 0;
        $item_level_dicount = 0;
        $query         = DraftQuotationProduct::where('draft_quotation_id',$draft_quotation_product->draft_quotation_id)->get();
        foreach ($query as $value)
        {
			$order_id = ($value->id);
			if($value->discount != 0)
			{
				if($value->discount == 100)
				{
					if($value->is_retail == 'pieces')
					{
						$discount_full = $value->unit_price_with_vat * $value->number_of_pieces;
						$sub_total_without_discount += $discount_full;
					}
					else
					{
						$discount_full = $value->unit_price_with_vat * $value->quantity;
						$sub_total_without_discount += $discount_full;
					}
					$item_level_dicount += $discount_full;
				}
				else
				{
					$sub_total_without_discount += $value->total_price / ((100 - $value->discount)/100);
					$item_level_dicount += ($value->total_price / ((100 - $value->discount)/100)) - $value->total_price;
				}
			}
			else
			{
				$sub_total_without_discount += $value->total_price;
			}
            $sub_total += $value->total_price;
            $sub_total_with_vat = $sub_total_with_vat + $value->total_price_with_vat;
            $sub_total_w_w += number_format($value->total_price_with_vat,2,'.','');
            $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total,2) : (@$value->total_price_with_vat - @$value->total_price);
        }
        $vat = $total_vat;
        $grand_total = ($sub_total_w_w)-($order->discount)+($order->shipping);
        //item level calculations
        $total_amount_wo_vat = number_format((float)$draft_quotation_product->total_price,2,'.','');
        $total_amount_w_vat = number_format((float)$draft_quotation_product->total_price_with_vat,2,'.','');
        $unit_price_after_discount = $draft_quotation_product->unit_price_with_discount != null ?  number_format($draft_quotation_product->unit_price_with_discount,2,'.','') : '--';
        $unit_price = $draft_quotation_product->unit_price != null ?  number_format($draft_quotation_product->unit_price,2,'.','') : '--';
        $unit_price_w_vat = $draft_quotation_product->unit_price_with_vat != null ?  number_format($draft_quotation_product->unit_price_with_vat,2,'.','') : '--';
        $quantity = round($draft_quotation_product->quantity,4);
        $pcs = round($draft_quotation_product->number_of_pieces,4);
        if($draft_quotation_product->supplier_id != null)
        {
        	$supply_from = $draft_quotation_product->from_supplier != null ? $draft_quotation_product->from_supplier->reference_name : '--';
        }
        else
        {
        	$supply_from = 'Warehouse';
        }
        $type = $draft_quotation_product->productType != null ? $draft_quotation_product->productType->title : 'Select';
        return response()->json(['success' => true,'vat' => number_format($vat,2, '.', ','), 'grand_total' => number_format($grand_total, 2, '.', ','),'sub_total' => number_format(@$sub_total, 2, '.', ','),'sub_total_without_discount' => number_format(floor(@$sub_total_without_discount*100)/100, 2, '.', ','),'item_level_dicount' => number_format(floor(@$item_level_dicount*100)/100, 2, '.', ','),'total_amount_wo_vat' => $total_amount_wo_vat,'total_amount_w_vat' => $total_amount_w_vat,'id' => $draft_quotation_product->id,'unit_price_after_discount' => $unit_price_after_discount,'unit_price' => $unit_price,'unit_price_w_vat' => $unit_price_w_vat,'supply_from' => $supply_from,'quantity' => $quantity,'pcs' => $pcs,'type' => $type,'type_id' => $draft_quotation_product->type_id]);
    }

    public static function orderCalculation($order_product, $order = null){

    	// Sup-739 Funcionality. Order Qty per piece comes from product detail page and change qty inv column when value enteres in pcs column
        $order_qty_per_piece = @$order_product->product->order_qty_per_piece;
        if ($order_qty_per_piece != null && $order_qty_per_piece != 0 && $order_qty_per_piece != '0' && $order_qty_per_piece != '0' && $order_product->number_of_pieces != null && $order_product->number_of_pieces != 0 && $order_product->number_of_pieces != "0")
        {
            $order_qty_per_piece_value = $order_product->number_of_pieces * $order_qty_per_piece;
            $order_product->quantity = $order_qty_per_piece_value;
            $order_product->save();
        }
    	//to find whether pieces is selected or qty
    	if($order != null){
    		if($order->primary_status == 3){
    				$quantity = $order_product->is_retail == 'qty' ?
    				$order_product->qty_shipped :
    				$order_product->pcs_shipped;
    		}else{
    				$quantity = $order_product->is_retail == 'qty' ?
    				$order_product->quantity :
    				$order_product->number_of_pieces;
    		}
    	}else{
    		$quantity = $order_product->is_retail == 'qty' ?
    				$order_product->quantity :
    				$order_product->number_of_pieces;
    	}
    	//to calculate unit price with discount
    	$percent_value = $order_product->discount / 100;
    	$discount_val = $order_product->unit_price * $percent_value;
		$value_discount = $order_product->unit_price - $discount_val;
		$order_product->unit_price_with_discount = $value_discount;

    	//to calculate vat amount total and unit price with vat
    	$vat = $order_product->vat;
		$vat_amountt = @$order_product->unit_price * ( @$vat / 100 );
        $vat_amount = $vat_amountt == '--' ? '0.0' : number_format($vat_amountt,4,'.','');
		$vat_amount_total_over_item = $vat_amount * $quantity;
		$order_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
		$order_product->unit_price_with_vat = number_format((@$order_product->unit_price + $vat_amount),2,'.','');

		//to calculate total price
		$total_pricee = @$quantity*number_format($order_product->unit_price,2,'.','');
		$total_price = $total_pricee;
		$discount = $order_product->discount;
		if($discount != null)
		{
			$dis = $discount / 100;
			$discount_value = $dis * $total_price;
			$result = $total_price - $discount_value;
			$vat_amount_total_over_item = $vat_amount_total_over_item - (@$vat_amount_total_over_item * $dis);
			$order_product->vat_amount_total = number_format($vat_amount_total_over_item,4,'.','');
		}
		else
		{
			$result = $total_price;
		}
        $order_product->total_price = $result == '--' ? '0.0' : number_format($result,2,'.','');

        $order_product->total_price_with_vat = round(round($result,2) + round($vat_amount_total_over_item,4),4);
        // \Log::info($result.' '.$vat_amount_total_over_item);
        // \Log::info(round($result + round($vat_amount_total_over_item,4),4));

        $order_product->save();



        return $order_product;
    }

    public static function takeHistory($draft_quotation_product, $request, $key, $value, $order){
    	//Draft quation History
            $refrence_code = $draft_quotation_product->is_billed == "Product" ? $draft_quotation_product->product->refrence_code : null;
            $old_value = @$request->old_value == 'clicked' ? '--' : @$request->old_value;
			if($key == 'from_warehouse_id')
			{
				$column_name = "Supply From";
			}
			else if($key == 'short_desc')
			{
				$column_name = "Description";
			}
			else if($key == 'selling_unit')
			{
				$column_name = "Sales Unit";
			}
			else if($key == 'discount')
			{
				$column_name = "Discount";
			}
			else if($key == 'vat')
			{
				$column_name = "VAT";
			}
			else if($key == 'brand')
			{
				$column_name = "Brand";
			}
			else if($key == 'quantity')
			{
				$column_name = $order->primary_status == 3 ? 'Qty Ordered' : 'Qty';
			}
			else if($key == 'qty_shipped')
			{
				$column_name = "Qty Sent";
			}
			else if($key == 'number_of_pieces')
			{
				$column_name = $order->primary_status == 3 ? 'Pieces Ordered' : 'Pieces';
			}
			else if($key == 'pcs_shipped')
			{
				$column_name = "Pieces Sent";
			}
			else if($key == 'unit_price_with_vat')
			{
				$column_name = "Unit Price (+VAT)";
			}
			else if($key == 'unit_price')
			{
				$column_name = "Default Price";
			}
			else if($key == 'type_id')
			{
				$column_name = "Type";
			}
			else
			{
				$column_name = @$key;
			}
			if($key == 'from_warehouse_id')
			{
				$Stype = explode('-', $value);
				if($Stype[0] == 's')
				{
					$supplier =supplier::find(@$value[1]);
					$new_value= @$supplier->reference_name;
				}
				elseif($Stype[0] == 'w')
				{
					$new_value ='warehouse';
				}
			}
			else
			{
				if($key == 'pcs_shipped')
				{
					$new_value = @$request->old_value == 'clicked' ? 'pc' : @$value;
				}
				else if($key == 'qty_shipped')
				{
					$new_value = @$request->old_value == 'clicked' ? 'kg' : @$request->qty_shipped;
				}
				else if($key == 'quantity')
				{
					$new_value = @$request->old_value == 'clicked' ? 'kg' : @$value;
				}
				else if($key == 'number_of_pieces')
				{
					$new_value = @$request->old_value == 'clicked' ? 'pc' : @$value;
				}
				else
				{
					$new_value = @$value;
				}
			}
            (new DraftQuotationHelper)->DraftQuotationHistory(@$draft_quotation_product->draft_quotation_id, $refrence_code, $column_name, $old_value, $new_value);
            return true;
    }


}
