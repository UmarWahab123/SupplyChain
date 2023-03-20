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

class QuotationsCommonHelper
{
    public static function checkCustomerCreditLimit($request)
    {
		$customer = Customer::find($request->customer_id);
		$order = $request->type == 'draft' ? DraftQuotation::findorfail($request->id) : Order::find($request->id);
		if($order)
		{
			$customer = Customer::find($order->customer_id);
		}
		$customer_orders = Order::select('id','total_amount','total_paid','primary_status','status')->whereIn('primary_status',[1,2,3])->where('customer_id',$request->customer_id)->get();
		$customer_total_dues = $customer_orders->count() > 0 ? $customer_orders->sum('total_amount') - $customer_orders->sum('total_paid') : 0;
		$customer_credit_limit = $customer->id != null ? $customer->customer_credit_limit : 0;
		return response()->json(['customer_total_dues' => $customer_total_dues,'customer_credit_limit' => $customer_credit_limit]);
    }

    public static function doActionInvoice($request)
    {
    	DB::beginTransaction();
    	try {
    	$action = $request->action;
		if($action == 'discard')
		{
			$draft_quotation = DraftQuotation::find($request->inv_id);
			if ($draft_quotation != null)
			{
				if ($draft_quotation->draft_quotation_product != null)
				{
					$draft_quotation->draft_quotation_products()->delete();
				}
				$draft_quotation->delete();
				$errorMsg =  'Order Invoice Discarded!';
				return response()->json(['success' => true, 'errorMsg' => $errorMsg]);
			}
			$errorMsg = 'Order Invoice Already Deleted';
			return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
		}
    	elseif($action == 'save')
    	{
        	$draft_quotation = DraftQuotation::find($request->inv_id);
	        if($draft_quotation != null && $draft_quotation->draft_quotation_products->count() == 0)
	        {
	        	$errorMsg =  'Please add some products in the invoice';
	        	DB::rollBack();
	        	return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
	        }
	        else
	        {
				$missingPrams = 0;
				$draftQuotationProduct = DraftQuotationProduct::where('draft_quotation_id',$draft_quotation->id)->get();
        		if($draftQuotationProduct->count() > 0)
          		{
		            foreach ($draftQuotationProduct as $value)
		            {
		            	if($value->quantity == 0 || $value->quantity == null)
		            	{
		                	$errorMsg =  'Quantity cannot be 0 or Null, please enter quantity of the added items';
		                	DB::rollBack();
		                	return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
		              	}
		            }
		            foreach ($draftQuotationProduct as $value)
		            {
		            	if($value->is_billed == "Billed" && $value->short_desc == null)
		            	{
		                	$missingPrams = 1;
		              	}
		            }
					if($missingPrams == 0)
					{
						$quot_status     = Status::where('id',1)->first();
						$draf_status     = Status::where('id',2)->first();
						$counter_formula = $quot_status->counter_formula;
						$counter_formula = explode('-',$counter_formula);
						$counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;
						$date = Carbon::now();
						$date = $date->format($counter_formula[0]); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday
						$company_prefix          = @Auth::user()->getCompany->prefix;
						$draft_customer_category = $draft_quotation->customer->CustomerCategory;
                        $config = Configuration::first();
						if($config->server != 'lucilla' && $draft_quotation->customer->category_id == 6)
						{
							$p_cat = CustomerCategory::where('id',4)->first();
							$ref_prefix = $p_cat->short_code;
						}
						else
						{
							$ref_prefix              = $draft_customer_category->short_code;
						}
						$quot_status_prefix      = $quot_status->prefix.$company_prefix;
						$draft_status_prefix     = $draf_status->prefix.$company_prefix;
						$c_p_ref = Order::whereIn('status_prefix',[$quot_status_prefix,$draft_status_prefix])->where('ref_id','LIKE',"$date%")->where('ref_prefix',$ref_prefix)->orderby('id','DESC')->first();
						$str = @$c_p_ref->ref_id;
						$onlyIncrementGet = substr($str, 4);
						if($str == NULL)
						{
							$onlyIncrementGet = 0;
						}
						$system_gen_no = str_pad(@$onlyIncrementGet + 1,$counter_length,0, STR_PAD_LEFT);
						$system_gen_no = $date . $system_gen_no;
                        $request_total = str_replace(',', '', $request->total);
                        // $request_total = number_format($request->total, 4,'.','');
						$var = number_format(floor($request_total*10000)/10000,4,'.',''); // removing commas
						$order                        = new Order;
						$order->status_prefix         = $quot_status_prefix;
						$order->ref_prefix            = $ref_prefix;
						$order->ref_id                = $system_gen_no;
						$order->ref_id_unique         = @$config->server == 'lucilla' ? $system_gen_no : null;
						$order->customer_id           = $draft_quotation->customer_id;
						$order->total_amount          = $var;
						$order->target_ship_date      = $request->target_ship_date;
						$order->memo                  = $request->memo;
						$order->discount              = $draft_quotation->discount;
						$order->from_warehouse_id     = $draft_quotation->from_warehouse_id;
						$order->shipping              = $draft_quotation->shipping;
						$order->target_ship_date      = $draft_quotation->target_ship_date;
						$order->payment_due_date      = $draft_quotation->payment_due_date;
						$order->payment_terms_id      = $draft_quotation->payment_terms_id;
						$order->delivery_request_date = $draft_quotation->delivery_request_date;
						$order->billing_address_id    = $draft_quotation->billing_address_id;
						$order->shipping_address_id   = $draft_quotation->shipping_address_id;

						$order->user_id               = $request->user_id != null ? $request->user_id : Auth::user()->id;
						$order->converted_to_invoice_on = Carbon::now();
						$order->manual_ref_no         = $draft_quotation->manual_ref_no;
						$order->is_vat                = $draft_quotation->is_vat;
						$order->created_by            = Auth::user()->id;
						$order->primary_status        = 1;
						$order->status                = 6;
						$order->save();

						$checkUserReport = User::find($order->user_id);
						if($checkUserReport)
						{
							if($checkUserReport->is_include_in_reports == 0)
							{
								$order->dont_show        = 1;
								$order->save();
							}
						}
						//Transfer draft order Histrory
						$draftqoutationHistory = DraftQuatationProductHistory::where('order_id',$request->inv_id)->get();
						foreach ($draftqoutationHistory as $value)
						{
							$OrderHistory = new OrderHistory;
							$OrderHistory->user_id           = $value->user_id;
							$OrderHistory->reference_number  = $value->reference_number;
							$OrderHistory->column_name       = $value->column_name;
							$OrderHistory->old_value         = $value->old_value;
							$OrderHistory->new_value         = $value->new_value;
							$OrderHistory->order_id          = $order->id;
							$OrderHistory->save();
							if($request->copy_and_update != 'yes')
							{
								$value->delete();
							}
						}
						// End Transfer draft order history
						$status_history             = new OrderStatusHistory;
						$status_history->user_id    = Auth::user()->id;
						$status_history->order_id   = $order->id;
						$status_history->status     = 'Created';
						$status_history->new_status = 'Quotation';
						$status_history->save();
						$order_products = DraftQuotationProduct::where('draft_quotation_id',$draft_quotation->id)->get();
						foreach($order_products as $product)
						{
							$user_warehouse_id = $product->from_warehouse_id != NULL ? $product->from_warehouse_id : Auth::user()->warehouse_id;
							if($product->product_id == null) //if if is inquiry product
							{
								$new_order_product = OrderProduct::create([
								'order_id'             => $order->id,
								'product_id'           => $product->product_id,
								'category_id'          => $product->category_id,
								'short_desc'           => $product->short_desc,
								'brand'                => $product->brand,
								'type_id'              => $product->type_id,
								'number_of_pieces'     => $product->number_of_pieces,
								'quantity'             => $product->quantity,
								'qty_shipped'          => $product->quantity,
								'selling_unit'         => $product->selling_unit,
								'margin'               => $product->margin,
								'vat'                  => $product->vat,
								'vat_amount_total'     => $product->vat_amount_total,
								'unit_price'           => $product->unit_price,
								'last_updated_price_on'=> $product->last_updated_price_on,
								'unit_price_with_vat'  => $product->unit_price_with_vat,
								'unit_price_with_discount'  => $product->unit_price_with_discount,
								'is_mkt'               => $product->is_mkt,
								'total_price'          => $product->total_price,
								'total_price_with_vat' => $product->total_price_with_vat,
								'supplier_id'          => $product->supplier_id,
								'from_warehouse_id'    => $product->from_warehouse_id,
								'user_warehouse_id'    => $order->from_warehouse_id,
								'warehouse_id'         => $order->from_warehouse_id,
								'is_warehouse'         => $product->is_warehouse,
								'status'               => 6,
								'is_billed'            => $product->is_billed,
								'default_supplier'     => $product->default_supplier,
								'created_by'           => $product->created_by,
								'discount'             => $product->discount,
								'is_retail'            => $product->is_retail,
								'import_vat_amount'    => $product->import_vat_amount,
								]);
								if($product->is_billed == 'Inquiry')
								{
									$email = Auth::user()->user_name;
									$o_email = Auth::user()->email;
									$html = '<h4>From : '.$email.'<br>Name: '.Auth::user()->name.'<br>Subject : Add Inquiry Product <br> Description : '.$product->short_desc.'<br> Unit Price : '.$product->unit_price.'</h4>';
									Mail::send(array(), array(), function ($message) use ($html,$o_email) {
									$message->to('purchasing@fdx.co.th')
									->subject('Inquiry Product')
									->from($o_email, Auth::user()->name)
									->replyTo($o_email,Auth::user()->name)
									->setBody($html, 'text/html');
									});
								}
							}
							else
							{
								$new_order_product = OrderProduct::create([
								'order_id'             => $order->id,
								'product_id'           => $product->product_id,
								'category_id'          => $product->category_id,
								'short_desc'           => $product->short_desc,
								'brand'                => $product->brand,
								'type_id'              => $product->type_id,
								'supplier_id'          => $product->product->supplier_id,
								'number_of_pieces'     => $product->number_of_pieces,
								'quantity'             => $product->quantity,
								'selling_unit'         => @$product->selling_unit,
								'exp_unit_cost'        => $product->exp_unit_cost,
								'margin'               => $product->margin,
								'vat'                  => $product->vat,
								'vat_amount_total'     => $product->vat_amount_total,
								'is_mkt'               => $product->is_mkt,
								'unit_price'           => $product->unit_price,
								'last_updated_price_on' => $product->last_updated_price_on,
								'unit_price_with_vat'  => $product->unit_price_with_vat,
								'unit_price_with_discount'  => $product->unit_price_with_discount,
								'total_price'          => $product->total_price,
								'total_price_with_vat' => $product->total_price_with_vat,
								'actual_cost'          => $product->actual_unit_cost,
								'locked_actual_cost'   => $product->actual_unit_cost,
								'supplier_id'          => $product->supplier_id,
								'from_warehouse_id'    => $product->from_warehouse_id,
								'user_warehouse_id'    => $order->from_warehouse_id,
								'warehouse_id'         => $order->from_warehouse_id,
								'is_warehouse'         => $product->is_warehouse,
								'status'               => 6,
								'is_billed'            => $product->is_billed,
								'default_supplier'     => $product->default_supplier,
								'created_by'           => $product->created_by,
								'discount'             => $product->discount,
								'is_retail'            => $product->is_retail,
								'import_vat_amount'    => $product->import_vat_amount,
								]);
							}
							$d_q_p_notes = DraftQuotationProductNote::where('draft_quotation_product_id',$product->id)->get();
							foreach ($d_q_p_notes as $note)
							{
								$order_product_notes = new OrderProductNote;
								$order_product_notes->order_product_id    = $new_order_product->id;
								$order_product_notes->note                = $note->note;
								$order_product_notes->show_on_invoice     = $note->show_on_invoice;
								$order_product_notes->save();
							}
						}
						$order_attachments = DraftQuotationAttachment::where('draft_quotation_id',$draft_quotation->id)->get();
						foreach ($order_attachments as $attachment)
						{
							OrderAttachment::create([
							'order_id'   => $order->id,
							'file_title' => $attachment->file_name,
							'file'       => $attachment->file_name,
							]);
						}
						$draft_notes = DraftQuotationNote::where('draft_quotation_id',$draft_quotation->id)->get();
						if(@$draft_notes != null)
						{
							foreach ($draft_notes as $note)
							{
								$order_note           = new OrderNote;
								$order_note->order_id = $order->id;
								$order_note->note     = $note->note;
								$order_note->type = $note->type == 'customer' ? 'customer' : 'warehouse';
								$order_note->save();
							}
						}
						if($request->file_title[0] != null)
						{
							for($i=0 ; $i<sizeof($request->file_title) ; $i++)
							{
								if($request->file[$i])
								{
									$extension=$request->file[$i]->extension();
									$filename=date('m-d-Y').mt_rand(999,999999).'__'.time().'.'.$extension;
									$request->file[$i]->move('public/uploads/invoice_attachments/',$filename);
								}
								$order->order_attachment()->updateOrCreate(['file_title' => $request->file_title[$i],'file'=> $filename]);
							}
						}
						$draft_quotation = DraftQuotation::find($request->inv_id);
						if($request->copy_and_update != 'yes')
						{
							$draft_quotation->draft_quotation_products()->delete();
							$draft_quotation->draft_quotation_attachments()->delete();
							$draft_quotation->draft_quotation_notes()->delete();
							$draft_quotation->delete();
						}
						if($request->direct_draft_invoice == 'direct-draft-invoice')
						{
							$errorMsg =  'Draft Invoice Created';
							$new_request = new \Illuminate\Http\Request();
							$new_request->replace(['inv_id' => $order->id, 'user_id' => $request->user_id]);
							$draft_invoice = QuotationsCommonHelper::makeDraftInvoice($new_request);
							$direct_d_inv = true;
						}
						else
						{
							$errorMsg =  'Quotation Created';
							$direct_d_inv = false;
						}
						DB::commit();
						return response()->json(['success' => true, 'errorMsg' => $errorMsg,'direct_d_inv' => $direct_d_inv]);
					}
					else
					{
						DB::rollBack();
						$errorMsg =  'Must fill description, to add this item into inquiry product!!!';
						return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
					}
				}
        	}
      	}
      } catch (\Exception $e) {
    		return response()->json(['success' => false, 'msg' => $e]);
    	}
    }

    public static function makeDraftInvoice(Request $request)
    {
        try
        {
	        $order = Order::find($request->inv_id);
	        if($order->primary_status == 3)
	        {
	        	return response()->json(['already_invoice' => true]);
	        }
	        if($order->primary_status == 2)
	        {
	        	return response()->json(['already_draft_invoice' => true]);
	        }
			foreach ($order->order_products as $order_product)
			{
				if($order_product->is_billed == 'Incomplete' || $order_product->is_billed == 'Inquiry')
				{
					$errorMsg =  'Unable to Convert Quotation to Draft Invoice! Contain Inquiry/Incomplete products. Contact Purchasing!!!';
					return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
				}

				if($order_product->is_billed == 'Billed' && $order_product->unit_price == null)
				{
					$errorMsg =  'Must fill description & unit price of the billed item';
					return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
				}

				if($order_product->quantity == 0 || $order_product->quantity == null)
				{
					$errorMsg =  'Quantity cannot be 0 or Null, please enter the quantity of the added items';
					return response()->json(['success' => false, 'errorMsg' => $errorMsg]);
				}
			}
	        $prefix = Configuration::all()->first();
	        $user_warehouse = Auth::user()->get_warehouse->id;
	        $total_product_status = 0;
	        $direct_invoice = 0;
	        foreach ($order->order_products as $order_prod)
	        {
				if($order_prod->is_billed == "Product")
				{
					$direct_invoice = 1;
				}
				if($order_prod->is_billed == "Billed")
				{
					$order_prod->status = 6;
				}
				else if($order_prod->user_warehouse_id == $order_prod->from_warehouse_id)
				{
					$order_prod->status = 10;
				}
				else
				{
					$total_product_status = 1;
					$order_prod->status = 7;
				}
	        	$order_prod->save();
				if($order_prod->is_billed == "Product")
				{
					$new_his = new QuantityReservedHistory;
					$re      = $new_his->updateReservedQuantity($order_prod,'Reserved Quantity','add');
				}
	        }
			if($direct_invoice == 0)
			{
				$inv_status              = Status::where('id',3)->first();
				$counter_formula         = $inv_status->counter_formula;
				$counter_formula         = explode('-',$counter_formula);
				$counter_length          = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;
				$date = Carbon::now();
				$date = $date->format($counter_formula[0]);
				$company_prefix          = @Auth::user()->getCompany->prefix;
				$draft_customer_category = $order->customer->CustomerCategory;
                $config = Configuration::first();
				if($config->server != 'lucilla' && $order->customer->category_id == 6)
				{
					$p_cat = CustomerCategory::where('id',4)->first();
					$ref_prefix = $p_cat->short_code;
				}
				else
				{
					$ref_prefix              = $draft_customer_category->short_code;
				}
				$status_prefix           = $inv_status->prefix.$company_prefix;
				$c_p_ref = Order::where('in_status_prefix','=',$status_prefix)->where('in_ref_prefix','=',$ref_prefix)->where('in_ref_id','LIKE',"$date%")->orderby('converted_to_invoice_on','DESC')->first();
				$str = @$c_p_ref->in_ref_id;
				$onlyIncrementGet = substr($str, 4);
				if($str == NULL)
				{
					$onlyIncrementGet = 0;
				}
				$system_gen_no           = str_pad(@$onlyIncrementGet + 1,$counter_length,0, STR_PAD_LEFT);
				$system_gen_no           = $date.$system_gen_no;
				$order->in_status_prefix = $status_prefix;
				$order->in_ref_prefix    = $ref_prefix;
				$order->in_ref_id        = $system_gen_no;
				$order->primary_status   = 3;
				$order->converted_to_invoice_on = Carbon::now();
				$order->full_inv_no = $status_prefix.'-'.$ref_prefix.$system_gen_no;
			}
			else
			{
				$quot_status           = Status::where('id',2)->first();
				$company_prefix          = @Auth::user()->getCompany->prefix;
				$order->status_prefix  = $quot_status->prefix.$company_prefix;
				$order->primary_status = 2;
				$order->converted_to_invoice_on = Carbon::now();
				$customer = Customer::where('id',$order->customer_id)->first();
				if ($customer->last_order_date < $order->created_at)
				{
					$customer->last_order_date = $order->created_at;
					$customer->save();
				}
			}
	        if(@$total_product_status == 1)
	        {
				$order->status = 7;
	        }
	        else if($direct_invoice == 0)
	        {
	        	$order->status = 11;
	        }
	        else
	        {
	        	$order->status = 10;
	        }
	        $order->user_id = $request->user_id != null ? $request->user_id : Auth::user()->id;
	        $order->save();
	        $status_history = new OrderStatusHistory;
	        $status_history->user_id = Auth::user()->id;
	        $status_history->order_id = $order->id;
	        $status_history->status = 'Confirmed';
	        if($direct_invoice == 0)
	        {
	        	$status_history->new_status = 'Invoice';
	        }
	        else if($order->status == 10)
	        {
	        	$status_history->new_status = 'DI(Waiting To Pick)';
	        }
	        else
	        {
	        	$status_history->new_status = 'DI(Waiting Gen PO)';
	        }
        	$status_history->save();
	        if($direct_invoice == 0)
	        {
	        	$errorMsg =  'Quotation Converted to Invoice!';
	        }
	        else
	        {
	        	$errorMsg =  'Quotation Converted to Draft Invoice!';
	        }
	        return response()->json(['success' => true, 'errorMsg' => $errorMsg,'direct_invoice'=>$direct_invoice]);
      	}
		catch(\Exception $e)
		{
			$errorCode = @$e->errorInfo[1];
				if($errorCode == 1062)
				{
				// houston, we have a duplicate entry problem
				$result = QuotationsCommonHelper::makeDraftInvoice($request);
				$order_check = Order::where('id', $request->inv_id)->first();
					if($order_check->primary_status == 3)
					{
						return response()->json(['success' => true]);
					}
				}
			return response()->json(['success' => false,'error' => $e->getMessage()]);
		}
    }
}
