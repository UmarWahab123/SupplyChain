<?php

namespace App\Http\Controllers\Sales;

use App\CustomEmail;
use App\CustomerSecondaryUser;
use App\DraftQuatationProductHistory;
use App\ExportStatus;
use App\Exports\CompleteQuotationExport;
use App\Exports\DraftQuotationExport;
use App\Exports\invoicetableExport;
use App\GlobalAccessForRole;
use App\Helpers\Datatables\CancelOrdersDatatable;
use App\Helpers\DraftQuotationHelper;
use App\Helpers\MyHelper;
use App\Helpers\QuantityReservedHistory;
use App\Helpers\QuotationHelper;
use App\Helpers\QuotationsCommonHelper;
use App\Helpers\UpdateOrderQuotationDataHelper;
use App\Helpers\UpdateQuotationDataHelper;
use App\Http\Controllers\Controller;
use App\ImportFileHistory;
use App\Imports\AddProductToOrder;
use App\Imports\AddProductToTempQuotation;
use App\Imports\BulkImportForOrder;
use App\Imports\DraftQuotationImport;
use App\InvoiceSetting;
use App\Jobs\CancelledOrderJob;
use App\Jobs\InvoiceSaleExpJob;
use App\Models\Common\Bank;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Company;
use App\Models\Common\CompanyBank;
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
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductCustomerFixedPrice;
use App\Models\Common\ProductType;
use App\Models\Common\PurchaseOrders\DraftPurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\State;
use App\Models\Common\Status;
use App\Models\Common\StockManagementIn;
use App\Models\Common\StockManagementOut;
use App\Models\Common\StockOutHistory;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Unit;
use App\Models\Common\UserDetail;
use App\Models\Common\Warehouse;
use App\Models\Common\WarehouseProduct;
use App\Models\Sales\Customer;
use App\NotificationConfiguration;
use App\Notifications\DraftInvoiceQtyChangeNotification;
use App\PrintHistory;
use App\QuotationConfig;
use App\RoleMenu;
use App\User;
use App\Variable;
use App\Version;
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
use Yajra\Datatables\Datatables;

class OrderController extends Controller
{
    public function postInvoiceDirect()
    {
        $draft_quotation = DraftQuotation::create(['created_by' => Auth::user()->id, 'from_warehouse_id' => Auth::user()->warehouse_id]);
        return redirect()->route("get-invoice", $draft_quotation->id);
    }

    public function postInvoice(Request $request)
    {
        if ($request->action == '') {
            // check for any open order invoices //
            $open_invoices = Order::with('order_products')->where('user_id', $this->user->id)->where('customer_id', $request->selected_customer)->where('primary_status', 1)->orderBy('ref_id')->where('status', 6)->get();

            if ($open_invoices->count() > 0) {
                return response()->json(['success' => false, 'invoices' => $open_invoices]);
            }
        }

        $draft_quotation = DraftQuotation::create(['customer_id' => $request->selected_customer]);
        return response()->json(['success' => true, 'id' => $draft_quotation->id]);
    }

    public function index($id)
    {
        $order = DraftQuotation::with('user.getCompany', 'draft_quotation_products', 'draft_quotation_notes', 'customer')->find($id);
        if(!$order)
            return redirect()->route('sales');
        $warehouse_id = Auth::user()->warehouse_id;

        $query = Customer::query();
        $query->with('getcountry', 'getstate', 'getpayment_term');
        if (Auth::user()->role_id == 1 || Auth::user()->role_id == 11) {
            $admin_customers = Customer::where('status', 1)->get();
        }

        if (Auth::user()->role_id == 4) {
            $users = User::select('id')->where('warehouse_id', $warehouse_id)->where(function ($q) {
                $q->where('role_id', 3)->orWhere('role_id', 4);
            })->whereNull('parent_id')->pluck('id')->toArray();
            $ids = $users;
            $sales_coordinator_customers = Customer::where(function ($q) use ($ids) {
                $q->whereIn('primary_sale_id', $ids)->orWhereHas('CustomerSecondaryUser', function ($query) use ($ids) {
                    $query->WhereIn('user_id', $ids);
                });
            })->where('status', 1)->orderBy('id', 'DESC')->get();
        } else {
            $sales_coordinator_customers = null;
        }

        $company_info = $order->user != null ? $order->user->getCompany : null;
        $countries = null;
        $states = State::select('id', 'name')->orderby('name', 'ASC')->where('country_id', 217)->get();

        if ($order->status == 1) {
            return redirect()->back();
        }
        $user_id = Auth::user()->id;
        if (Auth::user()->role_id == 3) {
            $customers     = Customer::where(function ($query) use ($user_id) {
                $query->where('primary_sale_id', Auth::user()->id)->orWhereHas('CustomerSecondaryUser', function ($query) use ($user_id) {
                    $query->where('user_id', $user_id);
                });
            })->where('status', 1)->get();
        } else {
            $customers = null;
        }
        $total_products = $order->draft_quotation_products->count('id');
        $sub_total     = 0;
        $sub_total_with_vat = 0;
        $sub_total_w_w = 0;
        $total_vat = 0;
        $sub_total_without_discount = 0;
        $item_level_dicount = 0;
        $query         = $order->draft_quotation_products;
        foreach ($query as  $value) {
            // dd($value);
            if ($value->discount != 0) {
                if ($value->discount == 100) {
                    if ($value->is_retail == 'pieces') {
                        $discount_full = $value->unit_price_with_vat * $value->number_of_pieces;
                        $sub_total_without_discount += $discount_full;
                    } else {
                        $discount_full = $value->unit_price_with_vat * $value->quantity;
                        $sub_total_without_discount += $discount_full;
                    }
                    $item_level_dicount += $discount_full;
                } else {
                    $sub_total_without_discount += $value->total_price / ((100 - $value->discount) / 100);
                    $item_level_dicount += ($value->total_price / ((100 - $value->discount) / 100)) - $value->total_price;
                }
            } else {
                $sub_total_without_discount += $value->total_price;
            }
            // dd($sub_total_without_discount);
            $sub_total += $value->total_price;

            $sub_total_with_vat = $sub_total_with_vat + $value->total_price_with_vat;

            // $sub_total += $value->total_price;
            $sub_total_w_w += $value->total_price_with_vat;
            $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 2) : (@$value->total_price_with_vat - @$value->total_price);
        }
        // dd($sub_total);
        $vat = $total_vat;
        $total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);
        $payment_term = PaymentTerm::all();
        // dd($payment_term);
        $payment_types = PaymentType::all();

        //for customer adding
        // $category = CustomerCategory::where('is_deleted',0)->pluck('title','id');
        $inv_note = $order->draft_quotation_notes->where('type', 'customer')->first();
        $warehouse_note = $order->draft_quotation_notes->where('type', 'warehouse')->first();
        // $status_history = DraftQuatationProductHistory::with('user')->where('order_id',$order->id)->get();

        $quotation_config      = QuotationConfig::where('section', 'quotation')->first();
        $hidden_by_default     = '';
        $columns_prefrences    = null;
        $shouldnt_show_columns = [11, 12, 15, 17];
        $hidden_columns        = null;
        $hidden_columns_by_admin = [];
        if ($quotation_config == null) {
            $hidden_by_default = '';
        } else {
            $dislay_prefrences = $quotation_config->display_prefrences;
            $hide_columns = $quotation_config->show_columns;
            if ($quotation_config->show_columns != null) {
                $hidden_columns = json_decode($hide_columns);
                if (!in_array($hidden_columns, $shouldnt_show_columns)) {
                    $hidden_columns = array_merge($hidden_columns, $shouldnt_show_columns);
                    $hidden_columns = implode(",", $hidden_columns);
                    $hidden_columns_by_admin = explode(",", $hidden_columns);
                }
            } else {
                $hidden_columns = implode(",", $shouldnt_show_columns);
                $hidden_columns_by_admin = explode(",", $hidden_columns);
            }
            $user_hidden_columns = [];
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', 'draft_quotation_product')->where('user_id', Auth::user()->id)->first();
            if ($not_visible_columns != null) {
                $user_hidden_columns = $not_visible_columns->hide_columns;
            } else {
                $user_hidden_columns = "";
            }
            $user_plus_admin_hidden_columns = $user_hidden_columns . ',' . $hidden_columns;
            $columns_prefrences = json_decode($quotation_config->display_prefrences);
            $columns_prefrences = implode(",", $columns_prefrences);
        }

        $display_purchase_list = ColumnDisplayPreference::where('type', 'draftt_quotation_product')->where('user_id', Auth::user()->id)->first();
        $customer_total_dues = 0;
        if ($order->customer_id != null) {
            $customer_credit_limit = $order->customer->customer_credit_limit;
        } else {
            $customer_credit_limit = 0;
        }

        $dropdownClass = $order->customer_id != null ? 'd-none' : '';


        $warehouses = Warehouse::select('id', 'status', 'warehouse_title')->where('status', 1)->get();
        $company_banks = CompanyBank::with('getBanks')->where('company_id', Auth::user()->company_id)->get();
        $is_texica = Status::where('id', 1)->pluck('is_texica')->first();

        $print_prefrences = unserialize($quotation_config->print_prefrences);


        $sales_person = Customer::with('primary_sale_person')->where('id', $order->customer_id)->first();
        $secondary_sales = null;
        if ($sales_person != null) {
            $secondary_sales = CustomerSecondaryUser::where('customer_id', $order->customer_id)->get();
        }

        // dd($customer_credit_limit);
        $display_prods = ColumnDisplayPreference::where('type', 'draftt_quotation_product')->where('user_id', Auth::user()->id)->first();
        return view('sales.invoice.index', compact('display_purchase_list', '
          ', 'user_plus_admin_hidden_columns', 'hidden_columns_by_admin', 'company_info', 'customers', 'order', 'states', 'countries', 'category', 'total_products', 'sub_total', 'total', 'payment_term', 'payment_types', 'vat', 'inv_note', 'sales_coordinator_customers', 'warehouse_note', 'admin_customers', 'sub_total_without_discount', 'item_level_dicount', 'columns_prefrences', 'table_hide_columns', 'hidden_by_default', 'customer_credit_limit', 'customer_total_dues', 'warehouses', 'status_history', 'dropdownClass', 'company_banks', 'id', 'is_texica', 'print_prefrences', 'sales_person', 'secondary_sales', 'display_prods'));
    }

    public function changeDraftVat(Request $request)
    {
        // dd($request->all());
        $draft_qoutation = DraftQuotation::find($request->quotation_id);
        $draft_qoutation->is_vat = $request->val;
        $draft_qoutation->save();

        $draft_qt_prod = DraftQuotationProduct::where('draft_quotation_id', $request->quotation_id)->where('is_billed', 'Product')->get();

        if ($draft_qoutation->is_vat == 0) {
            if ($draft_qt_prod->count() > 0) {
                foreach ($draft_qt_prod as $value) {
                    $product = Product::find($value->product_id);
                    $value->vat                  = @$product->vat;
                    if (@$product->vat !== null) {
                        $unit_p = number_format($value->unit_price, 2, '.', '');
                        $vat_amount = $unit_p * (@$product->vat / 100);
                        $final_price_with_vat = $unit_p + $vat_amount;

                        if ($value->is_retail == 'qty') {
                            $value->vat_amount_total   = number_format($vat_amount * $value->quantity, 4, '.', '');
                        } else {
                            $value->vat_amount_total   = number_format($vat_amount * $value->number_of_pieces, 4, '.', '');
                        }

                        $value->unit_price_with_vat  = number_format($final_price_with_vat, 2, '.', '');
                        $value->total_price_with_vat = number_format($value->unit_price_with_vat * $value->quantity, 4, '.', '');
                    } else {
                        $value->vat_amount_total     = 0;
                        $value->unit_price_with_vat  = number_format($value->unit_price, 2, '.', '');
                        $value->total_price_with_vat = number_format($value->total_price, 2, '.', '');
                    }

                    $value->save();
                }
            }
        } else {
            if ($draft_qt_prod->count() > 0) {
                foreach ($draft_qt_prod as $value) {
                    $value->vat                  = 0;
                    $value->vat_amount_total     = 0;
                    $value->unit_price_with_vat  = number_format($value->unit_price, 2, '.', '');
                    $value->total_price_with_vat = number_format($value->total_price, 2, '.', '');
                    $value->save();
                }
            }
        }

        $sub_total          = 0;
        $sub_total_with_vat = 0;
        $sub_total_w_w      = 0;
        $total_vat          = 0;
        $sub_total_without_discount = 0;
        $item_level_dicount = 0;
        $query  = DraftQuotationProduct::where('draft_quotation_id', $request->quotation_id)->get();
        foreach ($query as  $value) {
            if ($value->discount != 0) {
                if ($value->discount == 100) {
                    if ($value->is_retail == 'pieces') {
                        $discount_full = $value->unit_price_with_vat * $value->number_of_pieces;
                        $sub_total_without_discount += $discount_full;
                    } else {
                        $discount_full = $value->unit_price_with_vat * $value->quantity;
                        $sub_total_without_discount += $discount_full;
                    }
                    $item_level_dicount += $discount_full;
                } else {
                    $sub_total_without_discount += $value->total_price / ((100 - $value->discount) / 100);
                    $item_level_dicount += ($value->total_price / ((100 - $value->discount) / 100)) - $value->total_price;
                }
            } else {
                $sub_total_without_discount += $value->total_price;
            }
            $sub_total += $value->total_price;
            $sub_total_with_vat = $sub_total_with_vat + $value->total_price_with_vat;
            $sub_total_w_w += number_format($value->total_price_with_vat, 4, '.', '');
            $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 2) : (@$value->total_price_with_vat - @$value->total_price);
        }

        $vat = $total_vat;
        $grand_total = ($sub_total_w_w) - ($draft_qoutation->discount) + ($draft_qoutation->shipping);
        return response()->json(['success' => true, 'vat' => number_format($vat, 2, '.', ','), 'grand_total' => number_format($grand_total, 2, '.', ''), 'sub_total' => number_format(@$sub_total, 2, '.', ','), 'sub_total_without_discount' => number_format(floor(@$sub_total_without_discount * 100) / 100, 2, '.', ','), 'item_level_dicount' => number_format(floor(@$item_level_dicount * 100) / 100, 2, '.', ',')]);
    }

    public function changeQuotationVat(Request $request)
    {
        // dd($request->all());
        $draft_qoutation = Order::find($request->quotation_id);
        $draft_qoutation->is_vat = $request->val;
        $draft_qoutation->save();

        $order_history = new OrderHistory();
        $order_history->user_id = Auth::user()->id;
        $order_history->column_name = "Vat/Non-Vat";

        if ($draft_qoutation->is_vat == 0) {
            $order_history->old_value   = "Non-Vat";
            $order_history->new_value   = "Vat";
        } else {
            $order_history->old_value   = "Vat";
            $order_history->new_value   = "Non-Vat";
        }

        $order_history->order_id    = $request->quotation_id;
        $order_history->save();

        $order_product = OrderProduct::where('order_id', $request->quotation_id)->where('is_billed', 'Product')->get();

        if ($draft_qoutation->is_vat == 0) {
            if ($order_product->count() > 0) {
                foreach ($order_product as $value) {
                    $product = Product::find($value->product_id);
                    $value->vat                  = @$product->vat;
                    if (@$product->vat !== null) {
                        $unit_p = number_format($value->unit_price, 2, '.', '');
                        $vat_amount = $unit_p * (@$product->vat / 100);
                        $final_price_with_vat = $unit_p + $vat_amount;

                        if ($value->is_retail == 'qty') {
                            $value->vat_amount_total   = number_format($vat_amount * $value->quantity, 4, '.', '');
                        } else {
                            $value->vat_amount_total   = number_format($vat_amount * $value->number_of_pieces, 4, '.', '');
                        }

                        $value->unit_price_with_vat  = number_format($final_price_with_vat, 2, '.', '');
                        $value->total_price_with_vat = number_format($value->unit_price_with_vat * $value->quantity, 4, '.', '');
                    } else {
                        $value->vat_amount_total     = 0;
                        $value->unit_price_with_vat  = number_format($value->unit_price, 2, '.', '');
                        $value->total_price_with_vat = number_format($value->total_price, 2, '.', '');
                    }

                    $value->save();
                }
            }
        } else {
            if ($order_product->count() > 0) {
                foreach ($order_product as $value) {
                    $value->vat                  = 0;
                    $value->vat_amount_total     = 0;
                    $value->unit_price_with_vat  = number_format($value->unit_price, 2, '.', '');
                    $value->total_price_with_vat = number_format($value->total_price, 2, '.', '');
                    $value->save();
                }
            }
        }

        $sub_total     = 0;
        $total_vat     = 0;
        $grand_total   = 0;
        $sub_total_w_w   = 0;
        $sub_total_without_discount = 0;
        $item_level_dicount = 0;
        $query         = OrderProduct::where('order_id', $request->quotation_id)->get();
        foreach ($query as  $value) {
            $sub_total += $value->total_price;
            $sub_total_w_w += number_format($value->total_price_with_vat, 4, '.', '');
            $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 2) : (@$value->total_price_with_vat - @$value->total_price);

            if ($value->discount != 0) {
                if ($value->discount == 100) {
                    if (@$draft_qoutation->primary_status == 3) {
                        if ($value->is_retail == 'pieces') {
                            $discount_full =  $value->unit_price_with_vat * $value->pcs_shipped;
                            $sub_total_without_discount += $discount_full;
                        } else {
                            $discount_full =  $value->unit_price_with_vat * $value->qty_shipped;
                            $sub_total_without_discount += $discount_full;
                        }
                    } else {
                        if ($value->is_retail == 'pieces') {
                            $discount_full =  $value->unit_price_with_vat * $value->number_of_pieces;
                            $sub_total_without_discount += $discount_full;
                        } else {
                            $discount_full =  $value->unit_price_with_vat * $value->quantity;
                            $sub_total_without_discount += $discount_full;
                        }
                    }
                    $item_level_dicount += $discount_full;
                } else {
                    $sub_total_without_discount += $value->total_price / ((100 - $value->discount) / 100);
                    $item_level_dicount += ($value->total_price / ((100 - $value->discount) / 100)) - $value->total_price;
                }
            } else {
                $sub_total_without_discount += $value->total_price;
            }
        }

        $grand_total = ($sub_total_w_w) - ($draft_qoutation->discount) + ($draft_qoutation->shipping);

        $draft_qoutation->update(['total_amount' => number_format($grand_total, 2, '.', '')]);

        return response()->json(['success' => true, 'status' => $draft_qoutation->statuses->title, 'sub_total' => number_format(@$sub_total, 2, '.', ','), 'total_vat' => number_format(@$total_vat, 2, '.', ','), 'grand_total' => number_format(@$grand_total, 2, '.', ','), 'sub_total_without_discount' => number_format(floor(@$sub_total_without_discount * 100) / 100, 2, '.', ','), 'item_level_dicount' => number_format(floor(@$item_level_dicount * 100) / 100, 2, '.', ',')]);
    }

    public function copyQuotation(Request $request)
    {
        // dd($request->all());
        $old_order = Order::find($request->order_id);

        $quot_status     = Status::where('id', 1)->first();
        $draf_status     = Status::where('id', 2)->first();
        $counter_formula = $quot_status->counter_formula;
        $counter_formula = explode('-', $counter_formula);
        $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

        $date = Carbon::now();
        $date = $date->format($counter_formula[0]); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday
        $company_prefix          = @Auth::user()->getCompany->prefix;
        $draft_customer_category = $old_order->customer->CustomerCategory;
        $ref_prefix       = $draft_customer_category->short_code;
        $quot_status_prefix    = $quot_status->prefix . $company_prefix;
        $draft_status_prefix    = $draf_status->prefix . $company_prefix;

        $c_p_ref = Order::whereIn('status_prefix', [$quot_status_prefix, $draft_status_prefix])->where('ref_id', 'LIKE', "$date%")->where('ref_prefix', $ref_prefix)->orderby('id', 'DESC')->first();
        $str = @$c_p_ref->ref_id;
        $onlyIncrementGet = substr($str, 4);
        if ($str == NULL) {
            $onlyIncrementGet = 0;
        }
        $system_gen_no = str_pad(@$onlyIncrementGet + 1, $counter_length, 0, STR_PAD_LEFT);
        $system_gen_no = $date . $system_gen_no;

        $order                      = new Order;
        $order->status_prefix       = $quot_status_prefix;
        $order->ref_prefix          = $ref_prefix;
        $order->ref_id              = $system_gen_no;
        $order->customer_id         = $old_order->customer_id;
        $order->total_amount        = $old_order->total_amount;
        $order->from_warehouse_id   = $old_order->from_warehouse_id;
        $order->memo                = $old_order->memo;
        $order->discount            = $old_order->discount;
        $order->shipping            = $old_order->shipping;
        $order->target_ship_date    = $old_order->target_ship_date;
        $order->payment_due_date    = $old_order->payment_due_date;
        $order->payment_terms_id    = $old_order->payment_terms_id;
        $order->delivery_request_date    = $old_order->delivery_request_date;
        $order->billing_address_id  = $old_order->billing_address_id;
        $order->shipping_address_id = $old_order->shipping_address_id;
        $order->user_id             = $old_order->user_id;
        $order->converted_to_invoice_on = Carbon::now();
        $order->manual_ref_no = $old_order->manual_ref_no;
        $order->is_vat = $old_order->is_vat;
        $order->created_by          = $this->user->id;
        $order->primary_status      = 1;
        $order->status              = 6;
        $order->save();

        $status_history             = new OrderStatusHistory;
        $status_history->user_id    = Auth::user()->id;
        $status_history->order_id   = $order->id;
        $status_history->status     = 'Created';
        $status_history->new_status = 'Quotation';
        $status_history->save();

        $order_products = OrderProduct::where('order_id', $old_order->id)->get();

        foreach ($order_products as $product) {
            if ($product->product_id == null) //if if is inquiry product
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
                    'last_updated_price_on'           => $product->last_updated_price_on,
                    'unit_price_with_vat'  => $product->unit_price_with_vat,
                    'is_mkt'               => $product->is_mkt,
                    'total_price'          => $product->total_price,
                    'total_price_with_vat' => $product->total_price_with_vat,
                    'supplier_id'          => $product->supplier_id,
                    'from_warehouse_id'    => $product->from_warehouse_id,
                    'user_warehouse_id'    => $product->user_warehouse_id,
                    'warehouse_id'         => $product->warehouse_id,
                    'is_warehouse'         => $product->is_warehouse,
                    'status'               => 6,
                    'is_billed'            => $product->is_billed,
                    'created_by'           => $product->created_by,
                    'discount'             => $product->discount,
                    'is_retail'            => $product->is_retail,
                ]);
            } else {
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
                    'total_price'          => $product->total_price,
                    'total_price_with_vat' => $product->total_price_with_vat,
                    'actual_cost'          => $product->actual_unit_cost,
                    'locked_actual_cost'   => $product->actual_unit_cost,
                    'supplier_id'          => $product->supplier_id,
                    'from_warehouse_id'    => $product->from_warehouse_id,
                    'user_warehouse_id'    => $product->user_warehouse_id,
                    'warehouse_id'         => $product->warehouse_id,
                    'is_warehouse'         => $product->is_warehouse,
                    'status'               => 6,
                    'is_billed'            => $product->is_billed,
                    'created_by'           => $product->created_by,
                    'discount'             => $product->discount,
                    'is_retail'            => $product->is_retail,

                ]);
            }

            $q_p_notes = OrderProductNote::where('order_product_id', $product->id)->get();
            foreach ($q_p_notes as $note) {
                $order_product_notes = new OrderProductNote;
                $order_product_notes->order_product_id    = $new_order_product->id;
                $order_product_notes->note                = $note->note;
                $order_product_notes->show_on_invoice     = $note->show_on_invoice;
                $order_product_notes->save();
            }
        }


        $order_attachments = OrderAttachment::where('order_id', $old_order->id)->get();
        foreach ($order_attachments as $attachment) {
            OrderAttachment::create([
                'order_id'   => $order->id,
                'file_title' => $attachment->file_name,
                'file'       => $attachment->file_name,
            ]);
        }

        $draft_notes = OrderNote::where('order_id', $old_order->id)->get();
        if (@$draft_notes != null) {
            foreach ($draft_notes as $note) {
                $order_note = new OrderNote;
                $order_note->order_id    = $order->id;
                $order_note->note                = $note->note;
                if ($note->type == 'customer') {
                    $order_note->type = 'customer';
                } else {
                    $order_note->type = 'warehouse';
                }
                $order_note->save();
            }
        }

        $errorMsg =  'Quotation Created';

        return response()->json(['success' => true, 'errorMsg' => $errorMsg, 'order_id' => $order->id]);
    }

    public function discardQuotation(Request $request)
    {
        $draft_quotation = Order::find($request->order_id);
        $draft_quotation->order_products()->delete();
        $draft_quotation->delete();
        $errorMsg =  'Quotation Discarded!';
        return response()->json(['success' => true, 'errorMsg' => $errorMsg]);
    }

    public function paymentTermSaveInDquotation(Request $request)
    {
        return DraftQuotationHelper::paymentTermSaveInDquotation($request);
    }

    public function fromWarehouseSaveInDquotation(Request $request)
    {
        return DraftQuotationHelper::fromWarehouseSaveInDquotation($request);
    }

    public function paymentTermSaveInMyQuotation(Request $request)
    {
        return QuotationHelper::paymentTermSaveInMyQuotation($request);
    }

    public function fromWarehouseSaveInMyQuotation(Request $request)
    {
        return QuotationHelper::fromWarehouseSaveInMyQuotation($request);
    }

    public function AddCustomerToQuotation(Request $request)
    {
        return DraftQuotationHelper::AddCustomerToQuotation($request);
    }

    public function editCustomerAddressOnCompletedQuotation(Request $request)
    {
        return QuotationHelper::editCustomerAddressOnCompletedQuotation($request);
    }

    public function editCustomerAddress(Request $request)
    {
        $customer = Customer::find($request->customer_id);

        $quotation = DraftQuotation::find($request->quotation_id);
        $quotation->customer_id = $request->customer_id;
        $quotation->billing_address_id = $request->address_id;
        // $quotation->shipping_address_id = $customerAddress->id;
        $quotation->save();
        $customerAddress = CustomerBillingDetail::where('customer_id', $request->customer_id)->where('id', $request->address_id)->first();
        $html = '
         <div class="d-flex align-items-center mb-1">
          <div><img src="' . asset('public/img/sm-logo.png') . '" class="img-fluid" align="big-qummy"></div>
          <div class="pl-2 comp-name" data-customer-id="' . $customer->id . '"><p>' . $customer->company . '</p> </div>
        </div>';

        $html_body = '
        <p><input type="hidden" value="' . @$customerAddress->id . '"><i class="fa fa-edit edit-address" data-id="' . $request->customer_id . '"></i>
       <a href="#" data-toggle="modal" data-target="#add_billing_detail_modal">

     ' . @$customerAddress->billing_address . ', ' . @$customerAddress->getcountry->name . ',' . @$customerAddress->getstate->name . ',' . @$customerAddress->billing_city . ',' . @$customerAddress->billing_zip . '</p>
         <ul class="d-flex list-unstyled">
            <li><a href="#"><i class="fa fa-phone pr-2"></i> ' . @$customerAddress->billing_phone . '</a></li>
            <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> ' . @$customerAddress->billing_email . '</a></li>
          </ul>

          <ul class="d-flex list-unstyled">
            <li class=""><b>Tax ID: </b>' . @$customerAddress->tax_id . '</li>
          </ul>
        </div>';



        return response()->json(['html' => $html, 'html_body' => $html_body]);
    }

    public function editCustomerAddressShip(Request $request)
    {

        $customer = Customer::find($request->customer_id);

        $quotation = DraftQuotation::find($request->quotation_id);
        $quotation->customer_id = $request->customer_id;
        $quotation->shipping_address_id = $request->address_id;
        $quotation->save();
        $customerAddress = CustomerBillingDetail::where('customer_id', $request->customer_id)->where('id', $request->address_id)->first();
        $html = '
         <div class="d-flex align-items-center mb-1">
          <div><img src="' . asset('public/img/sm-logo.png') . '" class="img-fluid" align="big-qummy"></div>
          <div class="pl-2 comp-name" data-customer-id="' . $customer->id . '"><p>' . $customer->company . '</p> </div>
        </div>';
        $html_body = '
        <p><input type="hidden" value="' . @$customerAddress->id . '"><i class="fa fa-edit edit-address-ship" data-id="' . $request->customer_id . '"></i>
       <a href="#" data-toggle="modal" data-target="#add_billing_detail_modal">

     ' . @$customerAddress->billing_address . ', ' . @$customerAddress->getcountry->name . ',' . @$customerAddress->getstate->name . ',' . @$customerAddress->billing_city . ',' . @$customerAddress->billing_zip . '</p>
         <ul class="d-flex list-unstyled">
            <li><a href="#"><i class="fa fa-phone pr-2"></i> ' . @$customerAddress->billing_phone . '</a></li>
            <li class="pl-3"> <a href="#"><i class="fa fa-envelope pr-2"></i> ' . @$customerAddress->billing_email . '</a></li>
          </ul>
        </div>';



        return response()->json(['html' => $html, 'html_body' => $html_body]);
    }

    public function autocompleteFetchProduct(Request $request)
    {
        // dd($request->all());
        if ($request->page == "draft_Quot") {
            $order = DraftQuotation::find($request->inv_id);
        }
        elseif ($request->page == "Quot") {
            $order = Order::find($request->inv_id);
            // if ($order == null) {
            //     $order = DraftQuotation::find($request->inv_id);
            // }
        } else if($request->page == "Po"){
            $po = PurchaseOrder::find($request->inv_id);
        }
        else if($request->page == "draft_Po"){

            $po = DraftPurchaseOrder::find($request->inv_id);
        }

        $checkSearchConfig = QuotationConfig::where('section', 'search_configuration')->first();
        if ($checkSearchConfig) {
            if ($checkSearchConfig->print_prefrences != null) {
                $globalaccessForConfig = unserialize($checkSearchConfig->print_prefrences);
                if ($globalaccessForConfig['slug'][0] === "sup_ref_no") {
                    $sup_ref_no = $globalaccessForConfig['status'][0];
                    $sup_ref_no = ($sup_ref_no == '0' ? 'd-none' : '');
                }
                if ($globalaccessForConfig['slug'][1] === "prod_code") {
                    $prod_code = $globalaccessForConfig['status'][1];
                    $prod_code = ($prod_code == '0' ? 'd-none' : '');
                }
                if ($globalaccessForConfig['slug'][2] === "sup_ref_name") {
                    $sup_ref_name = $globalaccessForConfig['status'][2];
                    $sup_ref_name = ($sup_ref_name == '0' ? 'd-none' : '');
                }
                if ($globalaccessForConfig['slug'][3] === "brand") {
                    $brandCls = $globalaccessForConfig['status'][3];
                    $brandCls = ($brandCls == '0' ? 'd-none' : '');
                }
                if ($globalaccessForConfig['slug'][4] === "prod_desc") {
                    $prod_desc = $globalaccessForConfig['status'][4];
                    $prod_desc = ($prod_desc == '0' ? 'd-none' : '');
                }
                if ($globalaccessForConfig['slug'][5] === "prod_type") {
                    $prod_type = $globalaccessForConfig['status'][5];
                    $prod_type = ($prod_type == '0' ? 'd-none' : '');
                }
                if ($globalaccessForConfig['slug'][6] === "prod_note") {
                    $prod_note = $globalaccessForConfig['status'][6];
                    $prod_note = ($prod_note == '0' ? 'd-none' : '');
                }
                if ($globalaccessForConfig['slug'][7] === "rsv") {
                    $rsv = $globalaccessForConfig['status'][7];
                    $rsv = ($rsv == '0' ? 'd-none' : '');
                }
                if ($globalaccessForConfig['slug'][8] === "stock") {
                    $stock = $globalaccessForConfig['status'][8];
                    $stock = ($stock == '0' ? 'd-none' : '');
                }
                if ($globalaccessForConfig['slug'][9] === "available") {
                    $available = $globalaccessForConfig['status'][9];
                    $available = ($available == '0' ? 'd-none' : '');
                }
            } else {
                $sup_ref_no   = '';
                $prod_code    = '';
                $sup_ref_name = '';
                $brandCls     = '';
                $prod_desc    = '';
                $prod_type    = '';
                $prod_note    = '';
                $rsv          = '';
                $stock        = '';
                $available    = '';
                $pqty = null;
            }
        } else {
            $sup_ref_no   = '';
            $prod_code    = '';
            $sup_ref_name = '';
            $brandCls     = '';
            $prod_desc    = '';
            $prod_type    = '';
            $prod_note    = '';
            $rsv          = '';
            $stock        = '';
            $available    = '';
            $pqty = null;
        }

        $checkSearchConfigCol = QuotationConfig::where('section', 'search_apply_configuration')->first();
        if ($checkSearchConfigCol) {
            if ($checkSearchConfigCol->print_prefrences != null) {
                $globalaccessForConfig = unserialize($checkSearchConfigCol->print_prefrences);
                if ($globalaccessForConfig['slug'][0] === "prod_code") {
                    $pf_search = $globalaccessForConfig['status'][0];
                    $pf_search = ($pf_search == '0' ? '0' : '1');
                }
                if ($globalaccessForConfig['slug'][1] === "prod_desc") {
                    $desc_search = $globalaccessForConfig['status'][1];
                    $desc_search = ($desc_search == '0' ? '0' : '1');
                }
                if ($globalaccessForConfig['slug'][2] === "brand") {
                    $brand_search = $globalaccessForConfig['status'][2];
                    $brand_search = ($brand_search == '0' ? '0' : '1');
                }
                if ($globalaccessForConfig['slug'][3] === "sup_ref_name") {
                    $supp_search = $globalaccessForConfig['status'][3];
                    $supp_search = ($supp_search == '0' ? '0' : '1');
                }
                if ($globalaccessForConfig['slug'][4] === "sup_ref_no") {
                    $supp_no_search = $globalaccessForConfig['status'][4];
                    $supp_no_search = ($supp_no_search == '0' ? '0' : '1');
                }
            } else {
                $pf_search      = '0';
                $desc_search    = '0';
                $brand_search   = '0';
                $supp_search    = '0';
                $supp_no_search = '0';
            }
        } else {
            $pf_search      = '0';
            $desc_search    = '0';
            $brand_search   = '0';
            $supp_search    = '0';
            $supp_no_search = '0';
        }

        $params = $request->except('_token');
        $detail = [];
        if ($request->get('query')) {
            if($request['query'] != 'default' && $request['query'] != 'Po') {
                $query = $request->get('query');
                $search_box_value = explode(' ', $query);
                $product_query    = Product::query();
                $supplier_query   = Supplier::query();
                $sup_prod_query   = SupplierProducts::query();

                // search config code starts
                if ($desc_search == '1') {
                    $product_query = $product_query->where(function ($q) use ($search_box_value) {
                        foreach ($search_box_value as $value) {
                            $q->where('short_desc', 'LIKE', '%' . $value . '%');
                        }
                    });
                }
                if ($pf_search == '1') {
                    $product_query = $product_query->orWhere('refrence_code', 'LIKE', '%' . $query . '%');
                }
                if ($brand_search == '1') {
                    $product_query = $product_query->orWhere('brand', 'LIKE', '%' . $query . '%');
                }
                if ($supp_no_search == '1') {
                    $sup_prod_query = $sup_prod_query->where('product_supplier_reference_no', $query)->where('is_deleted', 0);
                    $sup_prod_query_p = $sup_prod_query->pluck('product_id')->toArray();
                    $sup_prod_query_s = $sup_prod_query->pluck('supplier_id')->toArray();
                } else {
                    $sup_prod_query_p = [];
                    $sup_prod_query_s = [];
                }
                if ($supp_search == '1') {
                    $supplier_query = $supplier_query->orWhere('reference_name', 'LIKE', $query . '%');
                    $supplier_query = $supplier_query->pluck('id')->toArray();
                } else {
                    $supplier_query = [];
                }

                $product_query  = $product_query->pluck('id')->toArray();
                if (!empty($product_query) || !empty($supplier_query) || !empty($sup_prod_query_p) || !empty($sup_prod_query_s)) {
                    $product_detail = Product::leftJoin('supplier_products', 'supplier_products.product_id', '=', 'products.id')->select('products.*', 'supplier_products.product_id as s_product_id', 'supplier_products.product_supplier_reference_no as s_product_supplier_reference_no', 'supplier_products.is_deleted as s_is_deleted', 'supplier_products.supplier_id as s_supplier_id', 'suppliers.reference_name');

                    $product_detail = $product_detail->join('suppliers', 'suppliers.id', '=', 'supplier_products.supplier_id');

                    $product_detail->orWhere(function ($q) use ($product_query, $supplier_query, $sup_prod_query_p, $sup_prod_query_s) {

                        if (!empty($product_query)) {
                            $q->orWhereIn('products.id', $product_query);
                        }
                        if (!empty($sup_prod_query_p)) {
                            $q->orWhereIn('supplier_products.product_id', $sup_prod_query_p);
                        }
                        if (!empty($supplier_query)) {
                            $q->orWhereIn('products.supplier_id', $supplier_query);
                        }
                        if (!empty($sup_prod_query_s)) {
                            $q->orWhereIn('supplier_products.supplier_id', $sup_prod_query_s);
                        }
                    });

                    $product_detail->where('products.status', 1)->where('supplier_products.is_deleted', 0);
                    if ($request->supplier_id !== null) {
                        $product_detail->where('supplier_products.supplier_id', $request->supplier_id);
                    }
                    $detail = $product_detail->orderBy('suppliers.reference_name', 'ASC')->orderBy('products.short_desc', 'ASC')->get();
                }
            } else if($request['query'] == 'Po') {
                // if ($request->page == "draft_Po") {
                //     $product_ids = DB::table('draft_purchase_order_details')
                //     ->select('draft_purchase_order_details.product_id',
                //     DB::raw('COUNT(draft_purchase_order_details.product_id) as total_sales'))
                //     ->join('draft_purchase_orders', 'draft_purchase_order_details.po_id', '=', 'draft_purchase_orders.id')
                //     ->where('draft_purchase_orders.supplier_id',$request->supplier_id)
                //     ->groupBy('draft_purchase_order_details.product_id')
                //     ->orderByDesc('total_sales')
                //     ->limit(10)
                //     ->pluck('product_id')
                //     ->toArray();
                // }
                // else
                // {
                // }
                $product_ids = DB::table('purchase_order_details')
                ->select('purchase_order_details.product_id',
                DB::raw('COUNT(purchase_order_details.product_id) as total_sales'))
                ->join('purchase_orders', 'purchase_order_details.po_id', '=', 'purchase_orders.id')
                ->where('purchase_orders.supplier_id',$request->supplier_id)
                ->groupBy('purchase_order_details.product_id')
                ->orderByDesc('total_sales')
                ->limit(10)
                ->pluck('product_id')
                ->toArray();
                $detail = Product::with(['warehouse_products' => function($q) use ($po){
                    $q->where('warehouse_id', $po->to_warehouse_id);
                }])->whereIn('id', $product_ids)->get();

            } else if($request['query'] == 'default') {
                // if ($request->page == "draft_Quot") {
                //     $product_ids = DB::table('draft_quotation_products')
                //     ->select('draft_quotation_products.draft_quotation_id', 'draft_quotation_products.product_id',
                //     DB::raw('COUNT(draft_quotation_products.product_id) as total_sales'))
                //     ->join('draft_quotations', 'draft_quotation_products.draft_quotation_id', '=', 'draft_quotations.id')
                //     ->where('draft_quotations.customer_id',$request->customer)
                //     ->groupBy('draft_quotation_products.draft_quotation_id')
                //     ->orderByDesc('total_sales')
                //     ->limit(10)
                //     ->pluck('product_id')
                //     ->toArray();
                // }
                // else{
                // }
                $product_ids = DB::table('order_products')
                ->select('order_products.order_id', 'order_products.product_id',
                DB::raw('COUNT(order_products.product_id) as total_sales'))
                ->join('orders', 'order_products.order_id', '=', 'orders.id')
                ->where('orders.customer_id',$request->customer)
                ->where('orders.primary_status',3)
                ->groupBy('order_products.order_id')
                ->orderByDesc('total_sales')
                ->limit(10)
                ->pluck('product_id')
                ->toArray();
                $detail = Product::select('products.*','products.supplier_id as s_supplier_id')->whereIn('id', $product_ids)->get();
            }

            if (!empty($detail)) {
                $variable = Variable::where('slug', 'type')->first();
                if ($variable->terminology != null) {
                    $type = $variable->terminology;
                } else {
                    $type = $variable->standard_name;
                }

                $product_description = Variable::where('slug', 'product_description')->first();
                if ($product_description->terminology != null) {
                    $desc = $product_description->terminology;
                } else {
                    $desc = $product_description->standard_name;
                }

                $our_reference_number = Variable::where('slug', 'our_reference_number')->first();
                if ($our_reference_number->terminology != null) {
                    $product_ref = $our_reference_number->terminology;
                } else {
                    $product_ref = $our_reference_number->standard_name;
                }

                $terminology_brand = Variable::where('slug', 'brand')->first();
                if ($terminology_brand->terminology != null) {
                    $brand = $terminology_brand->terminology;
                } else {
                    $brand = $terminology_brand->standard_name;
                }

                $terminology_note = Variable::where('slug', 'note_two')->first();
                if ($terminology_note->terminology != null) {
                    $note = $terminology_note->terminology;
                } else {
                    $note = $terminology_note->standard_name;
                }


                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px; max-height: 380px;overflow-y: scroll; word-break: break-all; position: relative;">';
                $output = '<div class="sm-dropdown"><ul class="dropdown-menu search-dropdown ul-dropdown" id="scroll__x_y" style="display:block; top:34px; left:0px; width:180%; padding:0px; max-height: 380px;overflow-y: scroll;overflow-x: scroll; word-break: normal;">';

                $output = '<div><div style="overflow: auto"><ul class="dropdown-menu search-dropdown ul-dropdown w-100" id="scroll__x_y" style="display:block; left:0px; padding:0px; max-height: 380px;overflow-y: scroll;overflow-x: scroll; word-break: normal; position: relative;">';

                if ($request->position !== 'header') {
                    $output .= '<li>
              <a href="javascript:void(0);" class="search_product fontbold d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;visibility:hidden;"></i>';
                    if ($request->page == "draft_Quot" || $request->page == "Quot" || $request->page == "Td") {
                        $output .= '<div class="supplier_ref pr-2 ' . $sup_ref_no . '">Sup Ref#</div><div class="pf pr-2 ' . $prod_code . '">' . $product_ref . '</div><div class="supplier pr-2 ' . $sup_ref_name . '">Supplier</div><div class="p_winery pr-2 ' . $brandCls . '">' . $brand . '</div><div class="description pr-2 ' . $prod_desc . '">' . $desc . '</div><div class="p_type pr-2 ' . $prod_type . '">' . $type . '</div><div class="p_notes pr-2 ' . $prod_note . '">' . $note . '</div><span class="rsv pl-2 ' . $rsv . '">Rsv</span><span class="pStock pl-2 ' . $stock . '">Stock</span><span class="aStock pl-2 ' . $available . '">Available</span></a>
              </li>';
                    } elseif ($request->page == "Po" || $request->page == "draft_Po") {
                        $output .= '<div class="supplier_ref pr-2 ' . $sup_ref_no . '">Sup Ref#</div><div class="pf pr-2 ' . $prod_code . '">' . $product_ref . '</div><div class="supplier pr-2 ' . $sup_ref_name . '">Supplier</div><div class="p_winery pr-2 ' . $brandCls . '">' . $brand . '</div><div class="description pr-2 ' . $prod_desc . '">' . $desc . '</div><div class="p_type pr-2 ' . $prod_type . '">' . $type . '</div><div class="p_notes pr-2 ' . $prod_note . '">' . $note . '</div><div>Stock</div></a>
              </li>';
                    }
                }

                foreach ($detail as $row) {
                    if ($request->position == 'header') {
                        $output .= '<li>';
                        $output .= '<a id="product_' . $row->id . '" target="_blank" href="' . url('get-product-detail', $row->id) . '" data-prod_id="' . $row->id . '" class="search_product"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . @$row->refrence_code . ' ' . $row->short_desc . ' ' . @$row->def_or_last_supplier->reference_name . '</a></li>';
                    } else {
                        if ($request->page == "Td") {
                            $w_id = $request->warehouse_id;
                            $warehouse_products = WarehouseProduct::where('product_id', $row->id)->where('warehouse_id', $request->warehouse_id)->first();
                            $order_products = ($warehouse_products->reserved_quantity + $warehouse_products->ecommerce_reserved_quantity);
                        } else {
                            $w_id = Auth::user()->warehouse_id;
                            if ($request->page == "Po" || $request->page == "draft_Po") {
                                $w_id = $po->to_warehouse_id != null ? $po->to_warehouse_id : Auth::user()->warehouse_id;
                            }
                            else {
                                $w_id = $order->from_warehouse_id != null ? $order->from_warehouse_id : Auth::user()->warehouse_id;
                            }
                            $warehouse_products = WarehouseProduct::where('product_id', $row->id)->where('warehouse_id', $w_id)->first();
                            $order_products = ($warehouse_products->reserved_quantity  + $warehouse_products->ecommerce_reserved_quantity);
                        }

                        $getSupplierName = Supplier::select('reference_name')->find($row->s_supplier_id);
                        $output .= '<li>
                <a id="product_' . $row->id . '" href="javascript:void(0);" data-supplier_id="' . ($row->s_supplier_id != null ?  @$row->s_supplier_id : $row->supplier_id) . '" data-inv_id="' . $request->inv_id . '" data-prod_id="' . $row->id . '" data-prod_name="'.$row->refrence_code.'" data-prod_description="'.$row->short_desc.'" class="add_product_to_tags search_product d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>';
                        if ($request->page == "draft_Quot" || $request->page == "Quot" || $request->page == "Td") {

                            $output .= ('<div class="supplier_ref pr-2 ' . $sup_ref_no . '">' . ($row->s_product_supplier_reference_no != null ? $row->s_product_supplier_reference_no : "-") . '</div><div class="pf pr-2 ' . $prod_code . '">' . $row->refrence_code . '</div><div class="supplier pr-2 ' . $sup_ref_name . '">' . @$getSupplierName->reference_name . '</div><div class="p_winery pr-2 ' . $brandCls . '">' . ($row->brand != null ? $row->brand : "-") . '</div><div class="description pr-2 ' . $prod_desc . '">' . $row->short_desc) . '</div><div class="p_type pr-2 ' . $prod_type . '">' . ($row->type_id != null ? $row->productType->title : "N.A") . '</div><div class="p_notes pr-2 ' . $prod_note . '">' . ($row->product_notes != null ? $row->product_notes : "-") . '</div><span class="rsv pl-2 ' . $rsv . '">' . round(@$order_products, 3) . '</span><span class="pStock pl-2 ' . $stock . '">' . (@$warehouse_products->current_quantity != null ? round(@$warehouse_products->current_quantity, 3) : 0) . '</span><span class="aStock pl-2 ' . @$available . '">' . (@$warehouse_products->available_quantity != null ? round(@$warehouse_products->available_quantity, 3) : 0) . '</span>';
                        } elseif ($request->page == "Po" || $request->page == "draft_Po") {
                            $output .= ('<div class="supplier_ref pr-2 ' . $sup_ref_no . '">' . ($row->s_product_supplier_reference_no != null ? $row->s_product_supplier_reference_no : "-") . '</div><div class="pf pr-2 ' . $prod_code . '">' . $row->refrence_code . '</div><div class="supplier pr-2 ' . $sup_ref_name . '">' . @$getSupplierName->reference_name . '</div><div class="p_winery pr-2 ' . $brandCls . '">' . ($row->brand != null ? $row->brand : "-") . '</div><div class="description pr-2 ' . $prod_desc . '">' . $row->short_desc) . '</div><div class="p_type pr-2 ' . $prod_type . '">' . ($row->type_id != null ? $row->productType->title : "N.A") . '</div><div class="p_notes pr-2 ' . $prod_note . '">' . ($row->product_notes != null ? $row->product_notes : "-") . '</div><div style="position: relative;"><span style="position: absolute; right: -100%;">'.round(@$row->warehouse_products[0]->current_quantity,2).'</span></div>';
                        }
                        $output .= '</a></li>';
                    }
                }
                $output .= '</ul><div></div>';

                echo $output;
            } else {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }
        } else {
            echo '';
        }
    }

    public function autocompleteFetchProductCdp(Request $request)
    {
        $params = $request->except('_token');
        $detail = [];
        if ($request->get('query')) {

            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            $product_query  = Product::query();
            $supplier_query = Supplier::query();

            $product_query = $product_query->where(function ($q) use ($search_box_value) {
                foreach ($search_box_value as $value) {
                    $q->where('short_desc', 'LIKE', '%' . $value . '%');
                }
            })->orWhere('refrence_code', 'LIKE', $query . '%');


            $supplier_query = $supplier_query->orWhere('reference_name', 'LIKE', '%' . $query . '%');

            $product_query  = $product_query->pluck('id')->toArray();
            $supplier_query = $supplier_query->pluck('id')->toArray();

            if (!empty($product_query) || !empty($supplier_query)) {
                $product_detail = Product::orderBy('id', 'ASC');

                $product_detail->orWhere(function ($q) use ($product_query, $supplier_query) {

                    if (!empty($product_query)) {
                        $q->orWhereIn('id', $product_query);
                    }
                    if (!empty($supplier_query)) {
                        $q->orWhereIn('supplier_id', $supplier_query);
                    }
                });

                $product_detail->where('status', 1);
                $detail = $product_detail->get();
            }



            if (!empty($detail)) {

                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px; max-height: 380px;overflow-y: scroll;">';
                if ($request->position !== 'header') {
                    $output .= '<li>
                   <a href="javascript:void(0);" class="search_product fontbold d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;visibility:hidden;"></i>';
                    $output .= '<div class="pf pr-2">PF#</div><div class="supplier pr-2">Supplier</div><div class="description">Description</div></a>
                </li>';
                }
                foreach ($detail as $row) {
                    if ($request->position == 'header') {
                        $output .= '
                            <li>';
                        $output .= '<a target="_blank" href="' . url('get-product-detail', $row->id) . '" data-prod_id="' . $row->id . '" class="search_product"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . @$row->refrence_code . ' ' . $row->short_desc . ' ' . @$row->def_or_last_supplier->reference_name . '</a></li>
                            ';
                    } else {

                        $warehouse_products = WarehouseProduct::where('product_id', $row->id)->where('warehouse_id', Auth::user()->warehouse_id)->first();

                        $output .= '
                            <li>

                            <a href="javascript:void(0);"  data-prod_id="' . $row->id . '" class="add_product_to search_product d-flex justify-content-between"><i class="fa fa-search sIcon pr-2" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>';

                        $output .= ('<div class="pf pr-2">' . $row->refrence_code . '</div><div class="supplier pr-2">' . @$row->def_or_last_supplier->reference_name . '</div><div class="description">' . $row->short_desc) . '</div>';
                        $output .= '</a></li>';
                    }
                }
                $output .= '</ul>';
                echo $output;
            } else {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }
        } else {
            echo '';
        }
    }

    public function autocompleteFetchOrders(Request $request)
    {
        $params = $request->except('_token');
        $detail = [];
        if ($request->get('query')) {
            $query = $request->get('query');
            $search_box_value = explode(' ', $query);
            $in_order_query  = Order::query();
            $order_query  = Order::query();

            foreach ($search_box_value as $result) {
                if (strstr($result, '-')) {
                    $in_order_query = $in_order_query->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%$result%");
                    $order_query = $order_query->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%$result%")->whereIn('primary_status', [1, 2]);
                } else {
                    $result = preg_replace("/[^0-9]/", "", $result);
                    $in_order_query = $in_order_query->orWhere('in_status_prefix', 'LIKE', "%$result%")->orWhere('in_ref_id', 'LIKE', "%$result%");
                    $order_query = $order_query->orWhere('status_prefix', 'LIKE', "%$result%")->orWhere('ref_id', 'LIKE', "%$result%")->whereIn('primary_status', [1, 2]);
                }
            }

            $detail2  = $in_order_query->take(10)->get();
            $detail  = $order_query->take(10)->get();

            if (!empty($detail)) {

                $output = '<ul class="dropdown-menu p-0" style="display:block; width:100%; margin-top:0; z-index:1000; max-height: 380px;overflow-y: scroll;">';
                foreach ($detail2 as $row) {
                    if ($row->primary_status == 3) {
                        $link = 'get-completed-invoices-details';
                    } elseif ($row->primary_status == 2) {
                        $link = 'get-completed-draft-invoices';
                    } elseif ($row->primary_status == 17) {
                        $link = 'get-cancelled-order-detail';
                    } else {
                        $link = 'get-completed-quotation-products';
                    }
                    $output .= '<li><a href="' . route($link, ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->status_prefix . '-' . @$row->ref_prefix . $row->ref_id . '</a></li>';
                }
                foreach ($detail as $row) {
                    if ($row->primary_status == 3) {
                        $link = 'get-completed-invoices-details';
                    } elseif ($row->primary_status == 1) {
                        $link = 'get-completed-quotation-products';
                    } elseif ($row->primary_status == 17) {
                        $link = 'get-cancelled-order-detail';
                    } else {
                        $link = 'get-completed-draft-invoices';
                    }
                    $output .= '<li><a href="' . route($link, ['id' => $row->id]) . '" target="_blank" class="add_product_to search_product"><i class="fa fa-search" aria-hidden="true" style="color:#ccc;margin-right:5px;"></i>' . $row->status_prefix . '-' . @$row->ref_prefix . $row->ref_id . '</a></li>';
                }
                $output .= '</ul>';
                echo $output;
            } else {
                $output = '<ul class="dropdown-menu search-dropdown" style="display:block; top:34px; left:0px; width:100%; padding:0px ">';
                $output .= '<li style="color:red;" align="center">No record found!!!</li>';
                $output .= '</ul>';
                echo $output;
            }
        }
    }

    public function checkItemShortDesc(Request $request)
    {
        $query = DraftQuotationProduct::where('id', $request->id)->first();
        if ($query) {
            if ($query->short_desc == null) {
                return response()->json(['success' => false]);
            } else {
                return response()->json(['success' => true]);
            }
        }
    }

    public function checkItemShortDescInOp(Request $request)
    {
        $query = OrderProduct::where('id', $request->id)->first();
        if ($query) {
            if ($query->short_desc == null || $query->unit_price == null) {
                return response()->json(['success' => false]);
            } else {
                return response()->json(['success' => true]);
            }
        }
    }

    public function getData(Request $request, $id)
    {
        $purchasing_role_menu = RoleMenu::where('role_id', 2)->where('menu_id', 40)->first();
        $query = DraftQuotationProduct::with('product.sellingUnits', 'get_draft_quotation', 'product.units', 'product.supplier_products.supplier', 'productType', 'unit', 'product.customer_type_product_margins', 'from_supplier', 'get_order_product_notes', 'single_note', 'product.productSubCategory', 'product.warehouse_products')->where('draft_quotation_id', $id)->orderBy('id', 'asc');

        $product_type = ProductType::select('id', 'title')->get();
        $units = Unit::orderBy('title')->get();

        return Datatables::of($query)

            ->addColumn('action', function ($item) {
                $html_string = '';
                if ($item->product == null && $item->is_billed != "Inquiry") {
                    $html_string = '
                      <a href="javascript:void(0);" class="actionicon viewIcon add-as-product" data-id="' . $item->id . '" title="Add as New Inquiry Product "><i class="fa fa-envelope"></i></a>';
                    $html_string .= '<button type="button" class="actionicon d-none inquiry_modal" data-toggle="modal" data-target="#inquiryModal">
                                  Add as Inquiry Product
                                </button>';
                }
                if ($item->is_billed == "Inquiry") {
                    $html_string = '
                      <a href="javascript:void(0);" class="actionicon viewIcon" title="This inquiry item will be visible once the quotation is saved"><i class="fa fa-info"></i></a>';
                }
                $html_string .= '
                 <a href="javascript:void(0);" class="actionicon deleteIcon" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                return $html_string;
            })
            ->addColumn('refrence_code', function ($item) {

                if ($item->product == null) {
                    return "N.A";
                } else {
                    $reference_code = $item->product->refrence_code;
                    if (Auth::user()->role_id == 3) {
                        return  $html_string = '<a target="_black" href="' . url('get-product-detail/' . @$item->product->id) . '"  >' . '<b>' . $reference_code . '</b>' . '</a>';
                    } else {
                        return  $html_string = '<a target="_black" href="' . url('get-product-detail/' . @$item->product->id) . '"  >' . '<b>' . $reference_code . '</b>' . '</a>';
                    }
                }
            })
            ->addColumn('description', function ($item) {
                // if($item->product == null)
                // {
                $html = '<span class="inputDoubleClick description_' . $item->id . '" data-fieldvalue="' . $item->short_desc . '">' . ($item->short_desc != null ? $item->short_desc : "--") . '</span><input type="text" name="short_desc" value="' . $item->short_desc . '"  class="short_desc form-control input-height d-none" style="width:100%">';
                return $html;
                // }
                // else
                // {
                //   return $item->product->short_desc !== null ? $item->product->short_desc: "--";
                // }
            })
            ->addColumn('brand', function ($item) {
                $html = '<span class="inputDoubleClick" data-fieldvalue="' . $item->brand . '" id="brand_form_span_' . $item->product_id . '">' . ($item->brand != null ? $item->brand : "--") . '</span><input type="text" name="brand" value="' . $item->brand . '" min="0" class="brand form-control input-height d-none" id="brand_form_' . $item->product_id . '" style="width:100%">';
                return $html;
            })
            ->addColumn('type_id', function ($item) use ($product_type) {
                if ($item->type_id == null) {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick product_type_' . $item->id . '" id="product_type" data-fieldvalue="' . $item->type_id . '">';
                    $html_string .= 'Select';
                    $html_string .= '</span>';

                    $html_string .= '<select name="type_id" class="select-common form-control product_type d-none">
                <option value="" selected="" disabled="">Choose Type</option>';
                    foreach ($product_type as $type) {
                        $html_string .= '<option value="' . $type->id . '" >' . $type->title . '</option>';
                    }
                    $html_string .= '</select>';
                } else {
                    $html_string = '
                <span class="m-l-15 inputDoubleClick product_type_' . $item->id . '" id="product_type"  data-fieldvalue="' . $item->type_id . '">';
                    $html_string .= $item->productType->title;
                    $html_string .= '</span>';
                    $html_string .= '<select name="type_id" class="select-common form-control product_type d-none type_select' . $item->id . '">
                <option value="" disabled="">Choose Type</option>';
                    foreach ($product_type as $type) {
                        $value = $item->type_id == $type->id ? 'selected' : "";
                        $html_string .= '<option ' . $value . ' value="' . $type->id . '">' . $type->title . '</option>';
                    }
                    $html_string .= '</select>';
                }
                return $html_string;
            })
            ->addColumn('sell_unit', function ($item) use ($units) {
                if ($item->product_id !== null) {
                    return $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
                } else {

                    $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
                    $html =  '<span class="inputDoubleClick">' . @$unit . '</span>';
                    $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" name="selling_unit" >';
                    $html .= '<optgroup label="Select Sale Unit">';
                    foreach ($units as $w) {
                        if ($item->selling_unit == $w->id) {
                            $html = $html . '<option selected value="' . $w->id . '">' . $w->title . '</option>';
                        } else {
                            $html = $html . '<option value="' . $w->id . '">' . $w->title . '</option>';
                        }
                    }

                    $html = $html . '</optgroup>';
                    $html .= '<optgroup label="Sale Unit">';

                    $html .= ' </optgroup></select>';
                    return $html;
                }
                // return $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
            })
            ->addColumn('buying_unit', function ($item) {
                return ($item->product && $item->product->units !== null ? $item->product->units->title : "N.A");
            })
            ->addColumn('quantity', function ($item) use ($units) {
                if ($item->product_id !== null) {
                    $sale_unit = $item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : "N.A";
                } else {
                    $unit = $item->unit != null ? @$item->unit->title : ($item->product && $item->product->sellingUnits ? $item->product->sellingUnits->title : '--');
                    $html =  '<span class="inputDoubleClick">' . @$unit . '</span>';
                    $html .= '<select class="font-weight-bold form-control-lg form-control selling_unit select-tag input-height d-none" name="selling_unit" >';
                    $html .= '<optgroup label="Select Sale Unit">';
                    // $units = Unit::orderBy('title')->get();
                    foreach ($units as $w) {
                        if ($item->selling_unit == $w->id) {
                            $html = $html . '<option selected value="' . $w->id . '">' . $w->title . '</option>';
                        } else {
                            $html = $html . '<option value="' . $w->id . '">' . $w->title . '</option>';
                        }
                    }

                    $html = $html . '</optgroup>';
                    $html .= '<optgroup label="Sale Unit">';

                    $html .= ' </optgroup></select>';
                    $sale_unit = $html;
                }
                $html = '';
                if ($item->quantity == null) {
                    $style = "color:red;";
                } else {
                    $style = "";
                }
                if ($item->is_retail == "qty") {
                    $radio = "disabled";
                } else {
                    $radio = "";
                }
                $html = '<span class="inputDoubleClick quantity_span_' . $item->id . '" id="quantity_span_id_' . $item->product_id . '" data-fieldvalue="' . $item->quantity . '" style="' . $style . '">' . ($item->quantity != null ? $item->quantity : "--") . '</span><input type="number" name="quantity" value="' . $item->quantity . '" id="draft_quotation_qty_' . $item->product_id . '" class="quantity form-control input-height d-none" style="width:100%">';
                $html .= ' ' . @$sale_unit;
                if ($item->is_billed == 'Product') {
                    $html .= '
                <div class="custom-control custom-radio custom-control-inline pull-right">';
                    $html .= '<input type="checkbox" class="condition custom-control-input qty_' . $item->id . '" id="is_retail' . @$item->id . '" name="is_retail" data-id="' . $item->id . ' ' . @$item->quantity . '" value="qty" ' . ($item->is_retail == "qty" ? "checked" : "") . ' ' . $radio . '>';

                    $html .= '<label class="custom-control-label" for="is_retail' . @$item->id . '"></label></div>';
                }

                return $html;
            })

            ->addColumn('number_of_pieces', function ($item) {

                if ($item->is_retail == "pieces") {
                    $radio = "disabled";
                } else {
                    $radio = "";
                }
                if ($item->is_billed == 'Product') {
                    $html = '<span class="inputDoubleClick pcs_span_' . $item->id . '" data-fieldvalue="' . $item->number_of_pieces . '" id="draft_quotation_pieces_span_' . $item->product_id . '">' . ($item->number_of_pieces != null ? $item->number_of_pieces : "--") . '</span><input type="number" name="number_of_pieces" id="draft_quotation_pieces_' . $item->product_id . '" value="' . $item->number_of_pieces . '" class="number_of_pieces form-control input-height d-none" style="width:100%">';

                    $html .= '
                <div class="custom-control custom-radio custom-control-inline pull-right">';
                    $html .= '<input type="checkbox" class="condition custom-control-input pieces_' . $item->id . '" id="pieces' . @$item->id . '" name="is_retail" data-id="' . $item->id . ' ' . @$item->number_of_pieces . '" value="pieces" ' . ($item->is_retail == "pieces" ? "checked" : "") . ' ' . $radio . '>';

                    $html .= '<label class="custom-control-label" for="pieces' . @$item->id . '"></label></div>';
                } else {
                    $html = 'N.A';
                }

                return $html;
            })

            ->addColumn('discount', function ($item) {
                $html = '<span class="inputDoubleClick" data-fieldvalue="' . $item->discount . '" id = "discount_span_' . $item->product_id . '">' . ($item->discount != null ? $item->discount : "--") . '</span><input type="number" name="discount" value="' . $item->discount . '" class="discount form-control input-height d-none" id="discount_' . $item->product_id . '" style="width:100%">';
                return $html . ' %';
            })

            ->addColumn('unit_price_discount', function ($item) {
                return $item->unit_price_with_discount != null ? '<span class="unit_price_after_discount_' . $item->id . '">' . $item->unit_price_with_discount . '</span>' : '--';
            })

            ->addColumn('exp_unit_cost', function ($item) {
                if ($item->product == null) {
                    return "N.A";
                } else {
                    $checkItemPo = new Product;
                    $checkItemPo = $checkItemPo->checkItemImportExistance($item->product_id);
                    if ($checkItemPo == 0) {
                        $redHighlighted = 'style=color:red';
                        $tooltip = "This item has never been imported before in our system, so the suggested price may be incorrect";
                    } else {
                        $redHighlighted = '';
                        $tooltip = '';
                    }

                    $html_string = '<span title="' . $tooltip . '" class="unit-price-' . $item->id . '" ' . $redHighlighted . '>' . number_format(floor($item->exp_unit_cost * 100) / 100, 2) . '</span>';
                }

                return $html_string;
            })

            ->addColumn('margin', function ($item) {
                if ($item->product == null) {
                    return "N.A";
                } else {
                    if (is_numeric($item->margin)) {
                        return $item->margin . '%';
                    } else {
                        return $item->margin;
                    }
                }
            })
            ->addColumn('unit_price', function ($item) {
                $star = '';
                if ($item->product == null) {
                    $html = '<span class="inputDoubleClick unit_price_' . $item->id . '" data-fieldvalue="' . number_format(@$item->unit_price, 2, '.', '') . '">' . ($item->unit_price !== null ? number_format(@$item->unit_price, 2, '.', '') : "--") . '</span><input type="number" name="unit_price" step="0.01" value="' . number_format(@$item->unit_price, 2, '.', '') . '" class="unit_price form-control input-height d-none unit_price_field_' . $item->id . '" style="width:100%">';
                    return $html;
                } else {
                    if (is_numeric($item->margin)) {
                        $product_margin = $item->product->customer_type_product_margins->where('product_id', @$item->product->id)->where('customer_type_id', $item->get_draft_quotation->customer->category_id)->where('is_mkt', 1)->first();
                        if ($product_margin) {
                            $star = '*';
                        }
                    }

                    $html = '<span class="inputDoubleClick unit_price_' . $item->id . '" data-fieldvalue="' . number_format(@$item->unit_price, 2, '.', '') . '">' . $star . number_format(@$item->unit_price, 2, '.', '') . '</span><input type="number" name="unit_price" step="0.01" value="' . number_format(@$item->unit_price, 2, '.', '') . '" class="unit_price form-control input-height d-none unit_price_field_' . $item->id . '" style="width:100%">';
                    return $html;
                }
            })
            ->addColumn('last_updated_price_on', function ($item) {
                if ($item->last_updated_price_on != null) {
                    return Carbon::parse($item->last_updated_price_on)->format('d/m/Y');
                } else {
                    return '--';
                }
            })
            ->addColumn('unit_price_with_vat', function ($item) {

                $unit_price = round($item->unit_price, 2);
                $vat = $item->vat;
                $vat_amount = @$unit_price * (@$vat / 100);
                if ($item->unit_price_with_vat !== null) {
                    $unit_price_with_vat = preg_replace('/(\.\d\d).*/', '$1', @$item->unit_price_with_vat);
                } else {
                    $unit_price_with_vat = number_format(@$unit_price + @$vat_amount, 2, '.', ',');
                }

                $html = '<span class="inputDoubleClick unit_price_w_vat_' . $item->id . '" data-fieldvalue="' . number_format(floor(@$item->unit_price_with_vat * 10000) / 10000, 4, '.', '') . '">' . number_format(floor(@$item->unit_price_with_vat * 100) / 100, 2) . '</span><input type="number" name="unit_price_with_vat" step="0.01"  value="' . number_format(floor(@$item->unit_price_with_vat * 10000) / 10000, 4, '.', '') . '" class="unit_price_with_vat form-control input-height d-none unit_price_w_vat_field' . $item->id . '" style="width:100%;  border-radius:0px;">';

                return $html;
            })
            ->addColumn('total_price', function ($item) {
                $total_price = $item->total_price;
                $html_string = '<span class="total-price total-price-' . $item->id . ' total_amount_wo_vat_' . $item->id . '">' . number_format((float)@$total_price, 2, '.', '') . '</span>';
                return $html_string;
            })
            ->addColumn('total_amount', function ($item) {
                $total_price = $item->total_price_with_vat;
                $html_string = '<span class="total-price total-price-' . $item->id . ' total_amount_w_vat_' . $item->id . '">' . number_format(floor(@$total_price * 100) / 100, 2) . '</span>';
                return $html_string;
            })
            ->addColumn('vat', function ($item) {


                if (Auth::user()->role_id == 2 || Auth::user()->role_id == 5 || Auth::user()->role_id == 6) {
                    return $item->vat != null ? $item->vat : '--';
                } else {
                    if ($item->unit_price != null && $item->get_draft_quotation->is_vat == 0) {
                        $clickable = "inputDoubleClick";
                    } else {
                        $clickable = "inputDoubleClick";
                    }
                    $html = '<span class="' . $clickable . '" data-fieldvalue="' . $item->vat . '">' . ($item->vat != null ? $item->vat : '--') . '</span><input type="number" name="vat" value="' . @$item->vat . '" class="vat form-control input-height d-none" style="width:90%"> %';
                    return $html;
                }
            })

            ->addColumn('supply_from', function ($item) use ($purchasing_role_menu) {
                if ($item->product_id == null) {
                    return "N.A";
                } else {


                    if ($item->is_warehouse == 0 && $item->supplier_id == null) {
                        $label = 'Select Supply From';
                    } else {
                        $label = $item->is_warehouse == 1 ? 'Warehouse' : @$item->from_supplier->reference_name;
                        // $label = @$item->from_supplier != null ? @$item->from_supplier->reference_name : 'Warehouse';
                    }

                    $html =  '<span class="inputDoubleClick supply_from_' . $item->id . '" data-fieldvalue="' . @$label . '">' . @$label . '</span>';
                    $html .= '<select class="font-weight-bold form-control-lg form-control warehouse_id select-tag input-height d-none" name="from_warehouse_id" >';
                    $html .= '<option value="" selected disabled>Choose Supply From</option>';
                    $html .= '<optgroup label="Select Warehouse">';
                    if ($item->is_warehouse == 1) {
                        // $html = $html . '<option selected value="w-1">Warehouse</option>';
                        $html = $html . '<option selected value="w-'.$item->get_draft_quotation->from_warehouse_id.'">Warehouse</option>';
                    } else {
                        $html = $html . '<option value="w-'.$item->get_draft_quotation->from_warehouse_id.'">Warehouse</option>';
                    }
                    $html = $html . '</optgroup>';

                    if ($purchasing_role_menu != null) {
                        $html .= '<optgroup label="Suppliers">';
                        // $getSuppliersByCat = SupplierProducts::where('product_id',$item->product->id)->pluck('supplier_id')->toArray();
                        $getSuppliersByCat = $item->product != null ? $item->product->supplier_products : null;
                        // if(!empty($getSuppliersByCat))
                        if ($getSuppliersByCat != null) {
                            // foreach($getSuppliersByCat as $supplierCat)
                            // {
                            // $getSuppliers = Supplier::whereIn('id',$getSuppliersByCat)->orderBy('reference_name')->get();
                            foreach ($getSuppliersByCat as $getSupplier) {
                                // dd($getSupplier->supplier);
                                if ($getSupplier->is_deleted == 0) {
                                    $value = $item->supplier_id == @$getSupplier->supplier->id ? 'selected' : "";
                                    $html .= '<option value="s-' . @$getSupplier->supplier->id . '">' . $getSupplier->supplier->reference_name . '</option>';
                                }
                            }
                            // }
                        }
                        $html .= ' </optgroup>';
                    } else {
                        //'Do Nothing';
                    }




                    $html .= '</select>';
                    return $html;
                }
            })

            ->addColumn('notes', function ($item) {
                // check already uploaded images //
                // $notes = DraftQuotationProductNote::where('draft_quotation_product_id', $item->id)->count();
                // $note = DraftQuotationProductNote::where('draft_quotation_product_id', $item->id)->first();

                $notes = $item->get_order_product_notes->count();
                $note = $item->single_note;

                $html_string = '<div class="d-flex justify-content-center text-center">';
                if ($notes > 0) {
                    $html_string .= ' <a href="javascript:void(0)" data-toggle="modal" data-target="#notes-modal" data-id="' . $item->id . '" class="fa fa-eye d-block show-notes mr-2" title="View Notes"></a>';
                    if ($note->show_on_invoice == 1) {
                        $html_string .=  '<input class="ml-2" type="checkbox" data-id="' . $note->id . '" data-draft_quot_id = "' . $item->id . '" name="show_note_checkbox" id="show_note_checkbox" style="vertical-align: middle;" checked/></div>';
                    } else {
                        $html_string .=  '<td><input class="ml-2" type="checkbox" data-id="' . $note->id . '" data-draft_quot_id = "' . $item->id . '" name="show_note_checkbox" id="show_note_checkbox" style="vertical-align: middle;"/></div>';
                    }
                }
                if ($notes == 0) {
                    $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#add_notes_modal" data-id="' . $item->id . '"  class="add-notes fa fa-plus" title="Add Note"></a>
                          </div>';
                }
                return $html_string;
            })
            ->addColumn('hs_code', function ($item) {
                if ($item->product_id != null)
                    return $item->product->hs_code;
                else
                    return 'N.A';
            })
            ->addColumn('category_id', function ($item) {
                if ($item->product_id != null)
                    return $item->product->productSubCategory->title;
                else
                    return 'N.A';
            })
            ->addColumn('temprature', function ($item) {
                if ($item->product_id !== NULL)
                    return  $item->unit ? $item->product->product_temprature_c : "N.A";
            })
            ->addColumn('hs_code', function ($item) {
                if ($item->product_id != null)
                    return $item->product->hs_code;
                else
                    return 'N.A';
            })
            ->addColumn('quantity_ship', function ($item) {
                return '--';
            })
            ->addColumn('pcs_shipped', function ($item) {
                return '--';
            })
            ->addColumn('po_quantity', function ($item) {
                return '--';
            })
            ->addColumn('po_number', function ($item) {
                return '--';
            })

            ->addColumn('last_price', function ($item) {

                $order = Order::with('order_products')->whereHas('order_products', function ($q) use ($item) {
                    $q->where('is_billed', 'Product');
                    $q->where('product_id', $item->product_id);
                })->where('customer_id', $item->get_draft_quotation->customer_id)->where('primary_status', 3)->orderBy('converted_to_invoice_on', 'desc')->first();

                if ($order) {
                    $cust_last_price = number_format($order->order_products->where('product_id', $item->product_id)->first()->unit_price, 2, '.', ',');
                } else {
                    $cust_last_price = "N.A";
                }

                return $cust_last_price;
            })

            ->addColumn('available_qty', function ($item) {
                $warehouse_id = $item->from_warehouse_id != null ? $item->from_warehouse_id : Auth::user()->warehouse_id;
                // $stock = $item->product != null ? $item->product->get_stock($item->product->id, $warehouse_id) : 'N.A';
                $stock = $item->product != null ? ($item->product->warehouse_products != null ? $item->product->warehouse_products->where('warehouse_id', $warehouse_id)->first() : null) : null;
                if ($stock != null) {
                    $stock = number_format($stock->available_quantity, 3, '.', '');
                }
                return $stock;
            })

            ->addColumn('restaurant_price', function ($item) {
                $getRecord = new Product;
                $prodFixPrice   = $getRecord->getDataOfProductMargins($item->product_id, 1, "prodFixPrice");
                if ($prodFixPrice != "N.A") {
                    $formated_value = number_format($prodFixPrice->fixed_price, 3, '.', ',');
                    return (@$formated_value !== null) ? $formated_value : '--';
                } else {
                    return 'N.A';
                }

                //return 'price';

            })
            ->addColumn('size', function ($item) {
                if ($item->product_id != null) {
                    if ($item->product->product_notes != null)
                        return $item->product->product_notes;
                    else
                        return '--';
                } else {
                    return '--';
                }
            })

            ->setRowId(function ($item) {
                return $item->id;
            })
            // yellowRow is a custom style in style.css file
            ->setRowClass(function ($item) {
                if ($item->product == null) {
                    return  'yellowRow';
                }
            })
            ->rawColumns(['action', 'refrence_code', 'quantity', 'number_of_pieces', 'exp_unit_cost', 'margin', 'unit_price', 'total_price', 'supply_from', 'notes', 'description', 'vat', 'brand', 'sell_unit', 'discount', 'unit_price_with_vat', 'total_amount', 'type_id', 'hs_code', 'temprature', 'category_id', 'last_price', 'available_qty', 'unit_price_discount'])
            ->make(true);
    }

    public function exportDraftQuotation(Request $request)
    {
        return DraftQuotationHelper::exportDraftQuotation($request);
    }

    public function checkProductQtyDraft(Request $request)
    {
        return DraftQuotationHelper::checkProductQtyDraft($request);
    }

    public function addDraftQuotProdNote(Request $request)
    {
        $draft_quot_pr  = new DraftQuotationProductNote;
        $draft_quot_pr->draft_quotation_product_id = $request['draft_quot_id'];
        $draft_quot_pr->note = $request['note_description'];
        $draft_quot_pr->show_on_invoice = $request['show_note_invoice'];
        $draft_quot_pr->save();
        return response()->json(['success' => true]);
    }

    public function getDraftQuotProdNote(Request $request)
    {
        $draft_quot_notes = DraftQuotationProductNote::where('draft_quotation_product_id', $request->draft_quot_id)->get();

        $html_string = '<div class="table-responsive">
                        <table class="table table-bordered text-center">
                        <thead class="table-bordered">
                        <tr>
                            <th>S.no</th>
                            <th>Description</th>
                            <th>Action</th>
                        </tr>
                        </thead><tbody>';
        if ($draft_quot_notes->count() > 0) {
            $i = 0;
            foreach ($draft_quot_notes as $note) {
                $i++;
                $html_string .= '<tr id="gem-note-' . $note->id . '">
                            <td>' . $i . '</td>
                            <td>' . $note->note . '</td>';
                $html_string .=   '<td><a href="javascript:void(0);" data-id="' . $note->id . '" data-draft_quot_id = "' . $request->draft_quot_id . '" id="delete-draft-note" class=" actionicon" title="Delete Note"><i class="fa fa-trash" style="color:red;"></i></a></td>
                         </tr>';
            }
        } else {
            $html_string .= '<tr>
                            <td colspan="4">No Note Found</td>
                         </tr>';
        }


        $html_string .= '</tbody></table></div>';
        return $html_string;
    }

    public function deleteDraftQuotProdNote(Request $request)
    {
        $draft_quot_pr  = DraftQuotationProductNote::find($request->note_id);
        $draft_quot_pr->delete();
        return response()->json(['success' => true]);
    }

    public function updateDraftQuotProdNote(Request $request)
    {
        $draft_quot_pr  = DraftQuotationProductNote::find($request->note_id);
        $draft_quot_pr->show_on_invoice = $request->show_on_invoice;
        $draft_quot_pr->save();
        return response()->json(['success' => true]);
    }

    public function SaveQuotationDiscount(Request $request)
    {
        return DraftQuotationHelper::SaveQuotationDiscount($request);
    }

    public function SaveOrderData(Request $request)
    {
        return QuotationHelper::SaveOrderData($request);
    }

    public function postExistingInvoice(Request $request)
    {
        $user_detail = UserDetail::where('user_id', $this->user->id)->first();
        $order = Order::find($request->id);
        echo "done";
    }

    public function addInquiryProduct(Request $request)
    {
        return DraftQuotationHelper::addInquiryProduct($request);
    }

    public function AddEnquiryItemAsNewPr(Request $request)
    {
        // dd($request->all());
        $quot_pr_data = DraftQuotationProduct::find($request->id);
        $quot_pr_data->is_billed = "Inquiry";
        $quot_pr_data->default_supplier = @$request->supplier_id;
        $quot_pr_data->save();
        return response()->json(['success' => true]);
    }

    public function AddEnquiryItemAsNewOrdPr(Request $request)
    {
        try {
            $quot_opr_data = OrderProduct::find($request->id);
            $quot_opr_data->is_billed = "Inquiry";
            $quot_opr_data->default_supplier = @$request->supplier_id;
            $quot_opr_data->save();
            //To send email
            $email = Auth::user()->user_name;
            $o_email = Auth::user()->email;
            $html = '<h4>From : ' . $email . '<br>Name: ' . Auth::user()->name . '<br>Subject : Add Inquiry Product <br> Description : ' . $quot_opr_data->short_desc . '<br> Unit Price : ' . $quot_opr_data->unit_price . '</h4>';
            Mail::send(array(), array(), function ($message) use ($html, $o_email) {
                $message->to('purchasing@fdx.co.th')
                    ->subject('Inquiry Product')
                    ->from($o_email, Auth::user()->name)
                    ->replyTo($o_email, Auth::user()->name)
                    ->setBody($html, 'text/html');
            });
            return response()->json(['success' => true]);
        } catch (\Throwable $th) {
            return response()->json(['success' => true]);
        }
    }

    public function addInquiryProductToOrder(Request $request)
    {
        $inv_id = $request->inv_id;
        $order = Order::find($inv_id);
        $salesWarehouse_id = Auth::user()->get_warehouse->id;

        $order_products = new OrderProduct;
        $order_products->order_id         = $order->id;
        $order_products->name             = $request->product_name;
        $order_products->short_desc       = $request->description;
        $order_products->quantity         = 1;
        $order_products->qty_shipped = 1;
        $order_products->warehouse_id     = @$salesWarehouse_id;
        $order_products->status           = 6;
        $order_products->is_billed        = "Billed";
        $order_products->created_by       = Auth::user()->id;
        $order_products->save();

        $new_order_p = OrderProduct::find($order_products->id);
        $getColumns = (new OrderProduct)->getColumns($new_order_p);

        return response()->json(['success' => true, 'getColumns' => $getColumns]);
    }

    public function addByRefrenceNumber(Request $request)
    {
        return DraftQuotationHelper::addByRefrenceNumber($request);
    }

    public function removeProduct(Request $request)
    {
        // dd($request->all());
        $draft_quotation_products = DraftQuotationProduct::with('product')->find($request->id);
        $invoice     = DraftQuotation::find($draft_quotation_products->draft_quotation_id);
        //Create History Of new Added Product
        $order_history = new DraftQuatationProductHistory;
        $order_history->user_id = Auth::user()->id;
        $order_history->order_id = $draft_quotation_products->draft_quotation_id;
        $order_history->reference_number = $draft_quotation_products->product != null ? $draft_quotation_products->product->refrence_code : 'Billed Item';
        $order_history->old_value = "--";
        $order_history->column_name = 'Delete Product';
        $order_history->new_value = 'Deleted';
        $order_history->save();
        $draft_quotation_products->delete();

        $sub_total     = 0;
        $total_vat     = 0;
        $grand_total   = 0;
        $query         = DraftQuotationProduct::where('draft_quotation_id', $invoice->id)->get();
        foreach ($query as  $value) {
            if ($value->is_retail == 'qty') {
                $sub_total += $value->total_price;
            } else if ($value->is_retail == 'pieces') {
                $sub_total += $value->total_price;
            }
            $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 2) : (@$value->total_price_with_vat - @$value->total_price);
        }
        $grand_total = ($sub_total) - ($invoice->discount) + ($invoice->shipping) + ($total_vat);
        return response()->json(['success' => true, 'successmsg' => 'Product successfully removed', 'total_products' => $invoice->draft_quotation_products->count('id'), 'sub_total' => number_format(@$sub_total, 2, '.', ','), 'total_vat' => number_format(@$total_vat, 2, '.', ','), 'grand_total' => number_format(@$grand_total, 2, '.', ',')]);
    }

    //Draft Quatation Function
    public function UpdateQuotationData(Request $request)
    {
        DB::beginTransaction();
        try {
            $result = DraftQuotationHelper::UpdateQuotationData($request);
            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
        
    }

    //Order Function
    public function UpdateOrderQuotationData(Request $request)
    {
        return QuotationHelper::UpdateOrderQuotationData($request);
    }

    public function uploadExcel(Request $request)
    {
        $validator = $request->validate([
            'excel' => 'required|mimes:csv,xlsx,xls'
        ]);

        Excel::import(new AddProductToTempQuotation($request->inv_id), $request->file('excel'));
        return response()->json(['success' => true]);
    }

    public function uploadOrderExcel(Request $request)
    {
        $validator = $request->validate([
            'excel' => 'required|mimes:csv,xlsx,xls'
        ]);
        Excel::import(new AddProductToOrder($request->inv_id), $request->file('excel'));
        return response()->json(['success' => true]);
    }

    public function uploadOrderDocuments(Request $request)
    {
        return QuotationHelper::uploadOrderDocuments($request);
    }

    public function uploadDraftQuotationDocuments(Request $request)
    {
        return DraftQuotationHelper::uploadDraftQuotationDocuments($request);
    }


    public function downloadOrderDocuments(Request $request, $id)
    {
        $downloadDocs = OrderAttachment::where('order_id', $id)->get();
        $zipper = new \Chumper\Zipper\Zipper;
        $path = public_path('uploads\\documents\\quotations\\');

        foreach ($downloadDocs as $docs) {
            $files[] = glob($path . $docs->file);
        }

        $zipper->make(public_path('uploads\\documents\\quotations\\' . $id . '\\zipped\\files.zip'))->add($files);
        $zipper->close();
        return response()->download(public_path('uploads\\documents\\quotations\\' . $id . '\\zipped\\files.zip'));
    }

    public function getQuotationFiles(Request $request)
    {
        return QuotationHelper::getQuotationFiles($request);
    }

    public function getDraftQuotationFiles(Request $request)
    {
        return DraftQuotationHelper::getDraftQuotationFiles($request);
    }

    public function removeQuotationFile(Request $request)
    {
        return QuotationHelper::removeQuotationFile($request);
    }

    public function removeDraftQuotationFile(Request $request)
    {
        return DraftQuotationHelper::removeDraftQuotationFile($request);
    }

    public function removeFile($directory, $imagename)
    {
        if (isset($directory) && isset($imagename))
            File::delete($directory . $imagename);
        return true;
        return false;
    }

    public function searchProduct(Request $request)
    {
        $result = $request->hs_code;
        $product = Product::where('hs_code', 'LIKE', "%$result%")->where('status', 1)->get();
        return response()->json(['success' => true, 'product' => $product]);
    }

    public function addProdToQuotation(Request $request)
    {
        return DraftQuotationHelper::addProdToQuotation($request);
    }

    public function addProdToOrderQuotation(Request $request)
    {
        // dd($request->all());
        $order = Order::find($request->quotation_id);

        $product_arr = explode(',', $request->selected_products);
        $suppliers_arr = explode(',', $request->suppliers);
        if($product_arr[0] == "") {
            return response()->json(['success' => false, 'message' => 'Select Product !!']);
        }

        foreach ($product_arr as $key => $product) {
            $product = Product::find($product);
            $supplier_id_s = isset($suppliers_arr[$key]) ? $suppliers_arr[$key] : null;
            $vat_amount_import = NULL;
            $getSpData = SupplierProducts::where('product_id', $product->id)->where('supplier_id', $supplier_id_s)->first();
            if ($getSpData) {
                $vat_amount_import = $getSpData->vat_actual;
            }else{
                $supplier_id_s = null;
            }

            $order = Order::find($request->quotation_id);
            $price_calculate_return = $product->price_calculate($product, $order);
            $unit_price = $price_calculate_return[0];
            $price_type = $price_calculate_return[1];
            $price_date = $price_calculate_return[2];
            $discount = $price_calculate_return[3];
            $price_after_discount = $price_calculate_return[4];
            $user_warehouse = @$order->customer->primary_sale_person->get_warehouse->id;
            $total_product_status = 0;
            $CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id', $product->id)->where('customer_type_id', $order->customer->category_id)->first();
            if ($CustomerTypeProductMargin != null) {
                $margin            = $CustomerTypeProductMargin->default_value;
                $margin            = (($margin / 100) * $product->selling_price);
                $product_ref_price = $margin + ($product->selling_price);
                $exp_unit_cost     = $product_ref_price;
            }
            //if this product is already in quotation then increment the quantity
            $order_products = OrderProduct::where('order_id', $order->id)->where('product_id', $product->id)->first();
            if ($order_products) {
                $total_price_with_vat = (($product->vat / 100) * $unit_price) + $unit_price;
                $supplier_id = $product->supplier_id;
                $salesWarehouse_id = Auth::user()->get_warehouse->id;

                $new_draft_quotation_products   = new OrderProduct;
                $new_draft_quotation_products->order_id                 = $order->id;
                $new_draft_quotation_products->product_id               = $product->id;
                $new_draft_quotation_products->category_id              = $product->category_id;
                // $new_draft_quotation_products->supplier_id              = $supplier_id;
                $new_draft_quotation_products->short_desc               = $product->short_desc;
                $new_draft_quotation_products->type_id                  = $product->type_id;
                $new_draft_quotation_products->brand                    = $product->brand;
                $new_draft_quotation_products->exp_unit_cost            = $exp_unit_cost;
                $new_draft_quotation_products->margin                   = $price_type;
                $new_draft_quotation_products->last_updated_price_on    = $price_date;
                $new_draft_quotation_products->unit_price               = number_format($unit_price, 2, '.', '');
                $new_draft_quotation_products->discount               = number_format($discount, 2, '.', ''); //Discount comes from ProductCustomerFixedPrice Table
                $new_draft_quotation_products->unit_price_with_discount = $price_after_discount != null ? number_format($price_after_discount,2,'.','') : number_format($unit_price,2,'.',''); // comes from ProductCustomerFixedPrice Table

                // $new_draft_quotation_products->unit_price_with_discount = number_format($unit_price, 2, '.', '');
                $new_draft_quotation_products->import_vat_amount        = $vat_amount_import;
                if ($order->is_vat == 0) {
                    $new_draft_quotation_products->vat               = $product->vat;
                    if (@$product->vat !== null) {
                        $unit_p = number_format($unit_price, 2, '.', '');
                        $vat_amount = $unit_p * (@$product->vat / 100);
                        $final_price_with_vat = $unit_p + $vat_amount;

                        $new_draft_quotation_products->unit_price_with_vat = number_format($final_price_with_vat, 2, '.', '');
                    } else {
                        $new_draft_quotation_products->unit_price_with_vat = number_format($unit_price, 2, '.', '');
                    }
                } else {
                    $new_draft_quotation_products->vat                  = 0;
                    $new_draft_quotation_products->unit_price_with_vat  = number_format($unit_price, 2, '.', '');
                }

                $new_draft_quotation_products->actual_cost         = $product->selling_price;
                $new_draft_quotation_products->locked_actual_cost  = $product->selling_price;
                $new_draft_quotation_products->warehouse_id        = $salesWarehouse_id;

                $new_draft_quotation_products->user_warehouse_id = $order->from_warehouse_id;

                if ($product->min_stock > 0) {
                    $new_draft_quotation_products->from_warehouse_id = $order->from_warehouse_id;
                    $new_draft_quotation_products->is_warehouse = 1;
                    // $new_draft_quotation_products->from_warehouse_id = Auth::user()->get_warehouse->id;
                } else if ($supplier_id_s !== null) {
                    $new_draft_quotation_products->supplier_id = $supplier_id_s;
                }
                if ($order->primary_status == 1) {
                    $new_draft_quotation_products->status              = 6;
                } elseif ($order->primary_status == 2) {
                    if ($new_draft_quotation_products->user_warehouse_id == $new_draft_quotation_products->from_warehouse_id) {
                        $new_draft_quotation_products->status = 10;
                    } else {
                        $total_product_status = 1;
                        $new_draft_quotation_products->status              = 7;
                    }
                } else if ($order->status == 11) {
                    $new_draft_quotation_products->status              = 11;
                } elseif ($order->primary_status == 25) {
                    $new_draft_quotation_products->status              = 26;
                } elseif ($order->primary_status == 28) {
                    $new_draft_quotation_products->status              = 29;
                }

                $new_draft_quotation_products->save();
            } else {
                $total_price_with_vat = (($product->vat / 100) * $unit_price) + $unit_price;
                $supplier_id = $product->supplier_id;
                $salesWarehouse_id = Auth::user()->get_warehouse->id;

                $new_draft_quotation_products   = new OrderProduct;
                $new_draft_quotation_products->order_id                 = $order->id;
                $new_draft_quotation_products->product_id               = $product->id;
                $new_draft_quotation_products->category_id              = $product->category_id;
                // $new_draft_quotation_products->supplier_id         = $supplier_id;
                $new_draft_quotation_products->short_desc               = $product->short_desc;
                $new_draft_quotation_products->type_id                  = $product->type_id;
                $new_draft_quotation_products->brand                    = $product->brand;
                $new_draft_quotation_products->exp_unit_cost            = $exp_unit_cost;
                $new_draft_quotation_products->margin                   = $price_type;
                $new_draft_quotation_products->last_updated_price_on    = $price_date;
                $new_draft_quotation_products->unit_price               = number_format($unit_price, 2, '.', '');
                $new_draft_quotation_products->unit_price_with_discount = number_format($unit_price, 2, '.', '');
                $new_draft_quotation_products->import_vat_amount        = $vat_amount_import;
                if ($order->is_vat == 0) {
                    $new_draft_quotation_products->vat               = $product->vat;
                    if (@$product->vat !== null) {
                        $unit_p = number_format($unit_price, 2, '.', '');
                        $vat_amount = $unit_p * (@$product->vat / 100);
                        $final_price_with_vat = $unit_p + $vat_amount;

                        $new_draft_quotation_products->unit_price_with_vat = number_format($final_price_with_vat, 2, '.', '');
                    } else {
                        $new_draft_quotation_products->unit_price_with_vat = number_format($unit_price, 2, '.', '');
                    }
                } else {
                    $new_draft_quotation_products->vat                  = 0;
                    $new_draft_quotation_products->unit_price_with_vat  = number_format($unit_price, 2, '.', '');
                }

                $new_draft_quotation_products->actual_cost         = $product->selling_price;
                $new_draft_quotation_products->locked_actual_cost  = $product->selling_price;
                $new_draft_quotation_products->warehouse_id        = $salesWarehouse_id;
                $new_draft_quotation_products->user_warehouse_id   = $order->from_warehouse_id;

                if ($product->min_stock > 0) {
                    $new_draft_quotation_products->from_warehouse_id = $order->from_warehouse_id;
                    $new_draft_quotation_products->is_warehouse = 1;
                } elseif ($supplier_id_s !== null) {
                    $new_draft_quotation_products->supplier_id = $supplier_id_s;
                }
                if ($order->primary_status == 1) {
                    $new_draft_quotation_products->status              = 6;
                } elseif ($order->primary_status == 2) {
                    if ($user_warehouse == $new_draft_quotation_products->from_warehouse_id) {
                        $new_draft_quotation_products->status = 10;
                    } else {
                        $total_product_status = 1;
                        $new_draft_quotation_products->status              = 7;
                    }
                } else if ($order->status == 11) {
                    $new_draft_quotation_products->status              = 11;
                } elseif ($order->primary_status == 25) {
                    $new_draft_quotation_products->status              = 26;
                } elseif ($order->primary_status == 28) {
                    $new_draft_quotation_products->status              = 29;
                }

                $new_draft_quotation_products->save();
            }
            if ($total_product_status == 1) {
                $order->status = 7;
            } else {
                $order_status = $order->order_products->where('is_billed', '=', 'Product')->min('status');
                $order->status = $order_status;
            }
            $order->save();

            $sub_total     = 0;
            $total_vat     = 0;
            $grand_total   = 0;
            $query         = OrderProduct::where('order_id', $order->id)->get();
            foreach ($query as  $value) {
                if ($value->is_retail == 'qty') {
                    $sub_total += $value->total_price;
                } else if ($value->is_retail == 'pieces') {
                    $sub_total += $value->total_price;
                }
                $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 2) : (@$value->total_price_with_vat - @$value->total_price);
            }
            $grand_total = ($sub_total) - ($order->discount) + ($order->shipping) + ($total_vat);
            $new_order_p = OrderProduct::find($new_draft_quotation_products->id);
            $getColumns = (new OrderProduct)->getColumns($new_order_p);
            $reference_number = @$new_order_p->product->refrence_code;
            $order_history = (new QuotationHelper)->MakeHistory(@$new_order_p->order_id, $reference_number, 'New Product', '--', 'Added');
        }

            return response()->json(['success' => true, 'status' => @$order->statuses->title, 'sub_total' => number_format(@$sub_total, 2, '.', ','), 'successmsg' => 'Product successfully Added', 'total_products' => $order->order_products->count('id'), 'total_vat' => number_format(@$total_vat, 2, '.', ','), 'grand_total' => number_format(@$grand_total, 2, '.', ','), 'getColumns' => $getColumns]);
    }

    public function checkIfInquiryItemExist(Request $request)
    {
        $missingPrams = 0;
        $draftQuotationProduct = DraftQuotationProduct::where('draft_quotation_id', $request->id)->get();
        if ($draftQuotationProduct->count() > 0) {
            foreach ($draftQuotationProduct as $value) {
                if ($value->is_billed == "Incomplete" || $value->is_billed == "Inquiry") {
                    $missingPrams = 1;
                } else {
                    $missingPrams = 0;
                }
            }

            if ($missingPrams == 0) {
                return response()->json(['success' => true]);
            } else {
                return response()->json(['success' => false]);
            }
        }
    }

    public function doActionInvoice(Request $request)
    {
        $res = QuotationsCommonHelper::doActionInvoice($request);
        return $res;
    }

    public function completedQuotations()
    {
        return view('sales.invoice.completed-quotations');
    }

    public function exportInvoiceTable(Request $request)
    {
        // dd($request->all());
        $status = ExportStatus::where('type', 'invoice_sale_report')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type    = 'invoice_sale_report';
            $new->status  = 1;
            $new->save();
            InvoiceSaleExpJob::dispatch($request->dosortbyx, $request->customer_id_select, $request->from_datex, $request->to_datex, $request->selecting_salex, $request->typex, $request->is_paidx, $request->date_radio_exp, $request->input_keyword_exp, Auth::user()->id, Auth::user()->role_id, $request->className, $request->selecting_customer_groupx);
            return response()->json(['msg' => "file is exporting.", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is ready to download.", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'invoice_sale_report')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);
            InvoiceSaleExpJob::dispatch($request->dosortbyx, $request->customer_id_select, $request->from_datex, $request->to_datex, $request->selecting_salex, $request->typex, $request->is_paidx, $request->date_radio_exp, $request->input_keyword_exp, Auth::user()->id, Auth::user()->role_id, $request->className, $request->selecting_customer_groupx);
            return response()->json(['msg' => "File is donwloaded.", 'status' => 1, 'exception' => null]);
        }
    }

    public function recursiveStatusCheckInvoiceTable()
    {
        $status = ExportStatus::where('type', 'invoice_sale_report')->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception, 'file_name' => $status->file_name]);
    }

    public function checkStatusFirstTimeForInvoiceTable()
    {
        //dd('here');
        $status = ExportStatus::where('type', 'invoice_sale_report')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function getCompletedQuotationsData(Request $request)
    {
        if (Auth::user()->role_id == 4) {
            $warehouse_id = Auth::user()->warehouse_id;
            $ids = User::select('id')->where('warehouse_id', $warehouse_id)->where(function ($query) {
                $query->where('role_id', 4)->orWhere('role_id', 3);
            })->whereNull('parent_id')->pluck('id')->toArray();
            $all_customer_ids = array_merge(Customer::whereIn('primary_sale_id', $ids)->pluck('id')->toArray(), Customer::with('CustomerSecondaryUser')->whereHas('CustomerSecondaryUser', function ($cus) use ($ids) {
                $cus->whereIn('user_id', $ids);
            })->pluck('id')->toArray());

            $query = Order::select(
                DB::raw('sum(CASE
          WHEN orders.primary_status=3 AND op.vat > 0 THEN op.total_price
          END) AS vat_total_amount,
          sum(CASE
          WHEN orders.primary_status=3 AND op.vat > 0 THEN op.vat_amount_total
          END) AS vat_amount_price,
          sum(CASE
          WHEN orders.primary_status=3 AND (op.vat IS NULL OR op.vat = 0) THEN op.total_price
          END) AS not_vat_total_amount,
          sum(CASE
          WHEN 1 THEN op.total_price
          END) AS sub_total_price,
          sum(CASE
          WHEN op.discount > 0 THEN (op.total_price/((100 - op.discount)/100))-op.total_price
          END) AS all_discount'),
                'orders.id',
                'orders.status_prefix',
                'orders.ref_prefix',
                'orders.ref_id',
                'orders.in_status_prefix',
                'orders.in_ref_prefix',
                'orders.in_ref_id',
                'orders.user_id',
                'orders.customer_id',
                'orders.total_amount',
                'orders.delivery_request_date',
                'orders.payment_terms_id',
                'orders.memo',
                'orders.primary_status',
                'orders.status',
                'orders.converted_to_invoice_on',
                'orders.payment_due_date',
                'orders.dont_show',
                'orders.target_ship_date'
            )->groupBy('op.order_id')->with(['customer', 'customer.primary_sale_person', 'customer.primary_sale_person.get_warehouse', 'customer.CustomerCategory', 'statuses', 'order_products', 'user', 'customer.getpayment_term', 'get_order_transactions.get_payment_ref', 'order_customer_note', 'order_warehouse_note','draft_invoice_pick_instruction_printed','invoice_proforma_printed',
            'customer.getbilling' => function ($q){
                $q->where('is_default', 1);
            }])->whereNotIn('orders.status', [34])->whereIn('orders.customer_id', $all_customer_ids);

            $query->leftJoin('order_products as op', 'op.order_id', '=', 'orders.id');
        } else if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 11) {
            $query = Order::select(
                DB::raw('sum(CASE
          WHEN orders.primary_status=3 AND op.vat > 0 THEN op.total_price
          END) AS vat_total_amount,
          sum(CASE
          WHEN orders.primary_status=3 AND op.vat > 0 THEN op.vat_amount_total
          END) AS vat_amount_price,
          sum(CASE
          WHEN 1 THEN op.total_price
          END) AS sub_total_price,
          sum(CASE
          WHEN orders.primary_status=3 AND (op.vat IS NULL OR op.vat = 0) THEN op.total_price
          END) AS not_vat_total_amount,
          sum(CASE
          WHEN op.discount > 0 THEN (op.total_price/((100 - op.discount)/100))-op.total_price
          END) AS all_discount'),
                'orders.id',
                'orders.status_prefix',
                'orders.ref_prefix',
                'orders.ref_id',
                'orders.in_status_prefix',
                'orders.in_ref_prefix',
                'orders.in_ref_id',
                'orders.user_id',
                'orders.customer_id',
                'orders.total_amount',
                'orders.delivery_request_date',
                'orders.payment_terms_id',
                'orders.memo',
                'orders.primary_status',
                'orders.status',
                'orders.converted_to_invoice_on',
                'orders.payment_due_date',
                'orders.dont_show',
                'orders.target_ship_date'
            )->groupBy('op.order_id')->with(['customer:reference_name,first_name,last_name,primary_sale_id,category_id,credit_term,id,reference_number,company', 'customer.primary_sale_person:id,name', 'statuses:id,title', 'order_products:id', 'user:id,name', 'get_order_transactions:order_id,id,payment_reference_no', 'get_order_transactions.get_payment_ref:id,payment_reference_no', 'order_customer_note:order_id,note', 'order_warehouse_note:order_id,note','draft_invoice_pick_instruction_printed','invoice_proforma_printed', 'customer.getbilling' => function ($q){
                $q->where('is_default', 1);
            }])->whereNotIn('orders.status', [34]);

            $query->leftJoin('order_products as op', 'op.order_id', '=', 'orders.id');
        } else {
            $ids = array_merge($this->user->customer->pluck('id')->toArray(), $this->user->user_customers_secondary->pluck('id')->toArray());

            $query = Order::select(
                DB::raw('sum(CASE
        WHEN orders.primary_status=3 AND op.vat > 0 THEN op.total_price
        END) AS vat_total_amount,
        sum(CASE
        WHEN orders.primary_status=3 AND op.vat > 0 THEN op.vat_amount_total
        END) AS vat_amount_price,
        sum(CASE
        WHEN orders.primary_status=3 AND (op.vat IS NULL OR op.vat = 0) THEN op.total_price
        END) AS not_vat_total_amount,
        sum(CASE
        WHEN 1 THEN op.total_price
        END) AS sub_total_price,
        sum(CASE
        WHEN op.discount > 0 THEN (op.total_price/((100 - op.discount)/100))-op.total_price
        END) AS all_discount'),
                'orders.id',
                'orders.status_prefix',
                'orders.ref_prefix',
                'orders.ref_id',
                'orders.in_status_prefix',
                'orders.in_ref_prefix',
                'orders.in_ref_id',
                'orders.user_id',
                'orders.customer_id',
                'orders.total_amount',
                'orders.delivery_request_date',
                'orders.payment_terms_id',
                'orders.memo',
                'orders.primary_status',
                'orders.status',
                'orders.converted_to_invoice_on',
                'orders.payment_due_date',
                'orders.dont_show',
                'orders.target_ship_date'
            )->groupBy('op.order_id')->with(['customer', 'customer.primary_sale_person', 'customer.primary_sale_person.get_warehouse', 'customer.CustomerCategory', 'statuses', 'order_products', 'user', 'customer.getpayment_term', 'order_notes', 'get_order_transactions', 'get_order_transactions.get_payment_ref','draft_invoice_pick_instruction_printed','invoice_proforma_printed',
            'customer.getbilling' => function ($q){
                $q->where('is_default', 1);
            }
        ]);

            $query->leftJoin('order_products as op', 'op.order_id', '=', 'orders.id');
        }

        Order::doSort($request, $query);

        if ($request->dosortby == 1) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 1);
            });
        } else if ($request->dosortby == 2) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2);
            });
        } else if ($request->dosortby == 3) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 3);
            });
        } else if ($request->dosortby == 6) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 1)->where('orders.status', 6);
            });
        } else if ($request->dosortby == 7) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 7);
            });
        } else if ($request->dosortby == 8) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 8);
            });
        } else if ($request->dosortby == 9) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 9);
            });
        } else if ($request->dosortby == 10) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 10);
            });
        } else if ($request->dosortby == 11) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 3)->where('orders.status', 11);
            });
        } else if ($request->dosortby == 24) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 3)->where('orders.status', 24);
            });
        } else if ($request->dosortby == 32) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 3)->where('orders.status', 32);
            });
        } else if ($request->dosortby == 35) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 35);
            });
        } else if ($request->dosortby == 36) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 36);
            });
        }

        if ($request->selecting_customer_group != null) {
            $id_split = explode('-', $request->selecting_customer_group);

            if ($id_split[0] == 'cat') {
                $query = $query->whereHas('customer', function ($q) use ($id_split) {
                    $q->where('category_id', $id_split[1]);
                });
            } else {
                $query = $query->where('customer_id', $id_split[1]);
            }
        }

        if ($request->selecting_sale != null) {
            $query = $query->where('user_id', $request->selecting_sale);
        } else if (Auth::user()->role_id == 3) {
            $user_i = Auth::user()->id;
            $query = $query->where(function ($or) use ($user_i) {
                $or->where('user_id', $user_i)->orWhereIn('customer_id', $this->user->customer->pluck('id')->toArray())->orWhereIn('customer_id', $this->user->user_customers_secondary->pluck('customer_id')->toArray());
            });
        } else {
            $query = $query->where('orders.dont_show', 0);
        }


        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            if ($request->dosortby == 3 || $request->dosortby == 11 || $request->dosortby == 24) {
                if ($request->date_type == '2') {
                    $query = $query->where('orders.delivery_request_date', '>=', $date);
                }
                if ($request->date_type == '1') {
                    $query = $query->where('orders.converted_to_invoice_on', '>=', $date . ' 00:00:00');
                }
                if ($request->date_type == '3') {
                    $query = $query->where('orders.target_ship_date', '>=', $date . ' 00:00:00');
                }
            } else {
                $query = $query->where('orders.delivery_request_date', '>=', $date);
            }
        }

        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            if ($request->dosortby == 3 || $request->dosortby == 11 || $request->dosortby == 24) {
                if ($request->date_type == '1') {
                    $query = $query->where('orders.converted_to_invoice_on', '<=', $date . ' 23:59:59');
                }
                if ($request->date_type == '2') {
                    $query = $query->where('orders.delivery_request_date', '<=', $date);
                }
                if ($request->date_type == '3') {
                    $query = $query->where('orders.target_ship_date', '>=', $date . ' 00:00:00');
                }
            } else {
                $query = $query->where('orders.delivery_request_date', '<=', $date);
            }
        }
        if (@$request->is_paid == 11 || @$request->is_paid == 24) {
            $query = $query->where('orders.status', @$request->is_paid);
        }


        if ($request->input_keyword != null) {
            $result = $request->input_keyword;
            if (strstr($result, '-')) {
                $query = $query->where(function ($q) use ($result) {
                    $q->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%" . $result . "%")->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%" . $result . "%");
                });
            } else {
                $resultt = preg_replace("/[^0-9]/", "", $result);
                $query = $query->where(function ($q) use ($resultt) {
                    $q->where('in_ref_id', 'LIKE', "%" . $resultt . "%")->orWhere('ref_id', 'LIKE', "%" . $resultt . "%");
                });
            }
        }

        if ($request->type == 'footer') {
            $ids = $query->pluck('id')->toArray();
            $sub_total = OrderProduct::select('total_price')->whereIn('order_id', $ids)->sum('total_price');
            $total_amount = Order::select('total_amount')->whereIn('id', $ids)->sum('total_amount');

            return response()->json(['post' => number_format($total_amount, 2), 'sub_total' => number_format($sub_total, 2)]);
        }
        // dd($query->get());
        $dt =  Datatables::of($query);
        $add_columns = ['action', 'sub_total_amount', 'total_amount', 'discount', 'due_date', 'invoice_date', 'sub_total_2', 'reference_id_vat_2', 'vat_1', 'sub_total_1', 'reference_id_vat', 'ref_id', 'received_date', 'payment_reference_no', 'sales_person', 'number_of_products', 'status', 'memo', 'comment_to_warehouse', 'remark', 'delivery_date', 'target_ship_date', 'customer_company', 'customer_ref_no', 'customer', 'inv_no', 'checkbox', 'tax_id', 'reference_address', 'printed'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                return Order::returnAddColumn($column, $item);
            });
        }

        $filter_columns = ['ref_id', 'sales_person', 'customer_ref_no', 'customer', 'customer_company', 'sub_total_amount', 'total_amount', 'remark', 'comment_to_warehouse', 'memo', 'tax_id'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return Order::returnFilterColumn($column, $item, $keyword);
            });
        }

        $dt->rawColumns(['action', 'inv_no', 'ref_id', 'sales_person', 'customer', 'number_of_products', 'status', 'customer_ref_no', 'checkbox', 'total_amount', 'reference_id_vat', 'comment_to_warehouse', 'customer_company', 'remark', 'due_date', 'sub_total_price', 'printed']);
        $dt->with(['post' => 'Loading...', 'sub_total' => 'Loading...']);
        return $dt->make(true);
    }

    public function getCompletedOtherData(Request $request)
    {
        if (Auth::user()->role_id == 4) {
            $warehouse_id = Auth::user()->warehouse_id;
            $ids = User::select('id')->where('warehouse_id', $warehouse_id)->where(function ($query) {
                $query->where('role_id', 4)->orWhere('role_id', 3);
            })->whereNull('parent_id')->pluck('id')->toArray();
            $all_customer_ids = array_merge(Customer::whereIn('primary_sale_id', $ids)->pluck('id')->toArray(), Customer::whereIn('secondary_sale_id', $ids)->pluck('id')->toArray());
            $query = Order::with('customer')->whereIn('customer_id', $all_customer_ids);
        } else if (Auth::user()->role_id == 2) {
            $query = Order::with('customer');
        } else if (Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11) {
            $query = Order::with('customer');
        } else {
            $ids = array_merge($this->user->customer->pluck('id')->toArray(), $this->user->secondary_customer->pluck('id')->toArray());
            // dd($ids);
            $query = Order::with('customer')->whereIn('customer_id', $ids);
        }

        if ($request->input_keyword != null) {
            $result = $request->input_keyword;

            if (strstr($result, '-')) {
                $query = $query->where(function ($q) use ($result) {
                    $q->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%" . $result . "%")->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%" . $result . "%")->orWhere('manual_ref_no', 'LIKE', "%" . $result . "%");
                });
            } else {
                $resultt = preg_replace("/[^0-9]/", "", $result);
                $query = $query->where(function ($q) use ($resultt) {
                    $q->where('in_ref_id', $resultt)->orWhere('ref_id', $resultt)->orWhere('manual_ref_no', $resultt);
                });
            }
        }

        if ($request->dosortby == 1) {
            $query->where(function ($q) {
                $q->where('primary_status', 1);
            });
        } else if ($request->dosortby == 2) {
            $query->where(function ($q) {
                $q->where('primary_status', 2);
            });
        } else if ($request->dosortby == 3) {
            $query->where(function ($q) {
                $q->where('primary_status', 3);
            });
        } else if ($request->dosortby == 6) {
            $query->where(function ($q) {
                $q->where('primary_status', 1)->where('status', 6);
            });
        } else if ($request->dosortby == 7) {
            $query->where(function ($q) {
                $q->where('primary_status', 2)->where('status', 7);
            });
        } else if ($request->dosortby == 8) {
            $query->where(function ($q) {
                $q->where('primary_status', 2)->where('status', 8);
            });
        } else if ($request->dosortby == 9) {
            $query->where(function ($q) {
                $q->where('primary_status', 2)->where('status', 9);
            });
        } else if ($request->dosortby == 10) {
            $query->where(function ($q) {
                $q->where('primary_status', 2)->where('status', 10);
            });
        } else if ($request->dosortby == 11) {
            $query->where(function ($q) {
                $q->where('primary_status', 3)->where('status', 11);
            });
        } else if ($request->dosortby == 24) {
            $query->where(function ($q) {
                $q->where('primary_status', 3)->where('status', 24);
            });
        } else if ($request->dosortby == 31) {
            $query->where(function ($q) {
                $q->where('primary_status', 31);
            });
        } else if ($request->dosortby == 32) {
            $query->where(function ($q) {
                $q->where('primary_status', 31)->where('status', 32);
            });
        }

        if ($request->selecting_customer != null) {
            $query->where('customer_id', $request->selecting_customer);
        }
        if ($request->selecting_customer_group != null) {
            $query->whereHas('customer', function ($q) use ($request) {
                $q->where('category_id', @$request->selecting_customer_group);
            });
        }
        if ($request->selecting_sale != null) {
            // $query->where('user_id', $request->selecting_sale);
            $query->whereIn('customer_id', User::where('id', $request->selecting_sale)->first()->customer->pluck('id'));
        }
        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            $query->where('orders.delivery_request_date', '>=', $date);
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            $query->where('orders.delivery_request_date', '<=', $date);
        }
        if (@$request->is_paid == 11 || @$request->is_paid == 24) {
            $query->where('orders.status', @$request->is_paid);
        }

        if ($request->dosortby == 3) {
            $query->orderBy('converted_to_invoice_on', 'DESC');
            // dd($query->get());
        } else {
            $query->orderBy('id', 'DESC');
        }

        // dd($query->toSql());
        // if(@$request->type == 'invoice'){
        //   $query->where('delivery_request_date', '>', Carbon::now()->subDays(30));
        // }
        return Datatables::of($query)

            ->addColumn('checkbox', function ($item) {
                // dd($item);

                $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check1" value="' . $item->id . '" id="quot_' . $item->id . '">
                                    <label class="custom-control-label" for="quot_' . $item->id . '"></label>
                                </div>';
                return $html_string;
            })
            ->addColumn('inv_no', function ($item) {
                // dd($item->customer->primary_sale_person->get_warehouse->order_short_code);
                if ($item->is_vat == 0) {
                    if ($item->in_status_prefix !== null || $item->in_ref_prefix !== null) {
                        $ref_no = @$item->in_status_prefix . '-' . $item->in_ref_prefix . $item->in_ref_id;
                    } else {
                        $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code . @$item->customer->CustomerCategory->short_code . @$item->in_ref_id;
                    }
                    $html_string = '<a href="' . route('get-completed-invoices-details', ['id' => $item->id]) . '" title="View Detail"><b>' . $ref_no . '</b></a>';
                    return $html_string;
                } else {
                    if ($item->manual_ref_no == null) {
                        if ($item->in_status_prefix !== null || $item->in_ref_prefix !== null) {
                            $ref_no = @$item->in_status_prefix . '-' . $item->in_ref_prefix . $item->in_ref_id;
                        } else {
                            $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code . @$item->customer->CustomerCategory->short_code . @$item->in_ref_id;
                        }
                    } else {
                        $ref_no = $item->manual_ref_no;
                    }
                    $html_string = '<a href="' . route('get-completed-invoices-details', ['id' => $item->id]) . '" title="View Detail"><b>' . $ref_no . '</b></a>';
                    return $html_string;
                }
            })

            ->addColumn('customer', function ($item) {
                if ($item->customer_id != null) {
                    if ($item->customer['reference_name'] != null) {
                        $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . $item->customer['reference_name'] . '</b></a>';
                    } else {
                        $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '">' . $item->customer['first_name'] . ' ' . $item->customer['last_name'] . '</a>';
                    }
                } else {
                    $html_string = 'N.A';
                }

                return $html_string;
            })

            ->filterColumn('customer', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('reference_name', 'LIKE', "%$keyword%");
                });
            })

            ->addColumn('customer_ref_no', function ($item) {
                $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . @$item->customer->reference_number . '</b></a>';
                return $html_string;
            })
            ->filterColumn('customer_ref_no', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('reference_number', 'LIKE', "%$keyword%");
                });
            })

            ->addColumn('customer_company', function ($item) {
                $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . @$item->customer->company . '</b></a>';
                return $html_string;
            })
            ->addColumn('target_ship_date', function ($item) {
                return @$item->target_ship_date != null ?  Carbon::parse($item->target_ship_date)->format('d/m/Y') : '--';
            })
            ->addColumn('delivery_date', function ($item) {
                return @$item->delivery_request_date != null ?  Carbon::parse($item->delivery_request_date)->format('d/m/Y') : '--';
            })

            ->addColumn('comment_to_warehouse', function ($item) {
                $warehouse_note = OrderNote::where('order_id', $item->id)->where('type', 'warehouse')->first();
                return @$warehouse_note != null ? @$warehouse_note->note : '--';
            })

            ->addColumn('memo', function ($item) {
                return @$item->memo != null ? @$item->memo : '--';
            })

            ->addColumn('status', function ($item) {



                $html = '<span class="sentverification">' . @$item->statuses->title . '</span>';
                return $html;
            })

            ->addColumn('number_of_products', function ($item) {
                $html_string = $item->order_products->count();
                return $html_string;
            })

            ->addColumn('sales_person', function ($item) {
                return ($item->customer !== null ? @$item->customer->primary_sale_person->name : '--');
            })

            ->filterColumn('sales_person', function ($query, $keyword) {
                $query->whereHas('customer', function ($q) use ($keyword) {
                    $q->whereHas('primary_sale_person', function ($q) use ($keyword) {
                        $q->where('name', 'LIKE', "%$keyword%");
                    });
                });
            }, true)

            ->addColumn('ref_id', function ($item) {
                if ($item->status_prefix !== null || $item->ref_prefix !== null) {
                    $ref_no = @$item->status_prefix . '-' . $item->ref_prefix . $item->ref_id;
                } else {
                    $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code . @$item->customer->CustomerCategory->short_code . @$item->ref_id;
                }

                $html_string = '';

                if ($item->primary_status == 31) {
                    if ($item->ref_id == null) {
                        $ref_no = '-';
                    }
                    $html_string .= $ref_no;
                }

                return $html_string;
            })

            ->filterColumn('ref_id', function ($query, $keyword) {
                $result = $keyword;
                if (strstr($result, '-')) {
                    $query = $query->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%" . $result . "%")->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%" . $result . "%");
                } else {
                    $resultt = preg_replace("/[^0-9]/", "", $result);
                    $query = $query->orWhere('ref_id', $resultt)->orWhere('in_ref_id', $resultt);
                }
            })

            ->addColumn('reference_id_vat', function ($item) {
                if ($item->manual_ref_no == null) {
                    return $item->in_status_prefix . '-' . @$item->in_ref_prefix . $item->in_ref_id . '-1';
                } else {
                    return $item->manual_ref_no . '-1';
                }
            })
            ->addColumn('sub_total_1', function ($item) {
                return @$item->order_products != null ? @$item->getOrderTotalVat($item->id, 0) : '--';
            })

            ->addColumn('vat_1', function ($item) {
                return @$item->order_products != null ? @$item->getOrderTotalVat($item->id, 1) : '--';
            })

            ->addColumn('reference_id_vat_2', function ($item) {
                if ($item->manual_ref_no == null) {
                    return $item->in_status_prefix . '-' . @$item->in_ref_prefix . $item->in_ref_id . '-2';
                } else {
                    return $item->manual_ref_no . '-2';
                }
            })

            ->addColumn('sub_total_2', function ($item) {
                return @$item->order_products != null ? @$item->getOrderTotalVat($item->id, 2) : '--';
            })

            ->addColumn('payment_term', function ($item) {
                return ($item->customer->getpayment_term !== null ? $item->customer->getpayment_term->title : '--');
            })

            ->addColumn('invoice_date', function ($item) {
                return Carbon::parse(@$item->updated_at)->format('d/m/Y');
            })

            ->addColumn('total_amount', function ($item) {
                return number_format(floor($item->total_amount * 100) / 100, 2, '.', ',');
            })

            ->addColumn('action', function ($item) {
                $html_string = '';

                if ($item->primary_status == 1 &&  Auth::user()->role_id != 7) {
                    $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon text-center deleteOrder" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                }


                return $html_string;
            })

            ->rawColumns(['action', 'inv_no', 'ref_id', 'sales_person', 'customer', 'number_of_products', 'status', 'customer_ref_no', 'checkbox', 'total_amount', 'reference_id_vat', 'comment_to_warehouse', 'customer_company'])
            ->with('post', $query->sum('total_amount'))
            ->make(true);
    }

    public function getCompletedDraftInvoice($id)
    {
        $order = Order::find($id);
        if($order->primary_status != 2 && $order->primary_status != 37)
            return redirect()->route('sales');
        $states = State::select('id', 'name')->orderby('name', 'ASC')->where('country_id', 217)->get();

        $billing_address = null;
        $shipping_address = null;
        $company_info = Company::where('id', $order->user->company_id)->first();
        if ($order->billing_address_id != null) {
            $billing_address = CustomerBillingDetail::where('id', $order->billing_address_id)->first();
        }
        if ($order->shipping_address_id) {
            $shipping_address = CustomerBillingDetail::where('id', $order->shipping_address_id)->first();
        }
        $total_products = $order->order_products->count('id');
        $vat = 0;
        $sub_total = 0;
        $sub_total_w_w = 0;
        $sub_total_without_discount = 0;

        $item_level_dicount = 0;
        $query = OrderProduct::where('order_id', $id)->get();
        foreach ($query as  $value) {
            $sub_total += $value->total_price;
            $sub_total_w_w += number_format($value->total_price_with_vat, 4, '.', '');

            $vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 4) : (@$value->total_price_with_vat - @$value->total_price);
            if ($value->discount != 0) {

                if ($value->discount == 100) {
                    if ($value->is_retail == 'pieces') {
                        $discount_full =  $value->unit_price_with_vat * $value->number_of_pieces;
                        $sub_total_without_discount += $discount_full;
                    } else {
                        $discount_full =  $value->unit_price_with_vat * $value->quantity;
                        $sub_total_without_discount += $discount_full;
                    }
                    $item_level_dicount += $discount_full;
                } else {
                    $sub_total_without_discount += $value->total_price / ((100 - $value->discount) / 100);
                    $item_level_dicount += ($value->total_price / ((100 - $value->discount) / 100)) - $value->total_price;
                }
            } else {
                $sub_total_without_discount += $value->total_price;
            }
        }
        $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);
        $status_history = OrderStatusHistory::with('get_user')->where('order_id', $id)->get();
        $checkDocs = OrderAttachment::where('order_id', $order->id)->get()->count();
        $inv_note = OrderNote::where('order_id', $order->id)->where('type', 'customer')->first();
        $warehouse_note = OrderNote::where('order_id', $order->id)->where('type', 'warehouse')->first();
        $payment_term = PaymentTerm::all();

        $warehouse_id = Auth::user()->warehouse_id;
        $users = User::select('id')->whereNull('parent_id')->where('warehouse_id', $warehouse_id)->where('role_id', 3)->get();
        $query = Customer::query();
        $ids = array();
        foreach ($users as $user) {
            array_push($ids, $user->id);
        }
        $sales_coordinator_customers = $query->where('status', 1)->whereIn('primary_sale_id', $ids)->orderBy('id', 'DESC')->get();
        $admin_customers = Customer::where('status', 1)->get();
        $user_id = Auth::user()->id;
        $customers     = Customer::where(function ($query) use ($user_id) {
            $query->where('primary_sale_id', Auth::user()->id)->orWhereHas('CustomerSecondaryUser', function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            });
        })->where('status', 1)->get();

        $quotation_config      = QuotationConfig::where('section', 'quotation')->first();
        $hidden_by_default     = '';
        $columns_prefrences    = null;
        $shouldnt_show_columns = [15, 17];
        $hidden_columns        = null;
        $hidden_columns_by_admin = [];
        if ($quotation_config == null) {
            $hidden_by_default = '';
        } else {
            $dislay_prefrences = $quotation_config->display_prefrences;
            $hide_columns = $quotation_config->show_columns;
            if ($quotation_config->show_columns != null) {
                $hidden_columns = json_decode($hide_columns);
                if (!in_array($hidden_columns, $shouldnt_show_columns)) {
                    $hidden_columns = array_merge($hidden_columns, $shouldnt_show_columns);
                    $hidden_columns = implode(",", $hidden_columns);
                    $hidden_columns_by_admin = explode(",", $hidden_columns);
                }
            } else {
                $hidden_columns = implode(",", $shouldnt_show_columns);
                $hidden_columns_by_admin = explode(",", $hidden_columns);
            }
            $user_hidden_columns = [];
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', 'draft_invoice_product')->where('user_id', Auth::user()->id)->first();
            if ($not_visible_columns != null) {
                $user_hidden_columns = $not_visible_columns->hide_columns;
            } else {
                $user_hidden_columns = "";
            }
            $user_plus_admin_hidden_columns = $user_hidden_columns . ',' . $hidden_columns;
            $columns_prefrences = json_decode($quotation_config->display_prefrences);
            $columns_prefrences = implode(",", $columns_prefrences);
        }

        $company_banks = CompanyBank::with('getBanks')->where('company_id', Auth::user()->company_id)->where('customer_category_id', @$order->customer->CustomerCategory->id)->get();
        $banks = Bank::all();

        $delivery_bill_last_date = PrintHistory::select('created_at', 'user_id')->where('page_type', 'draft_invoice')->where('print_type', 'delivery-bill')->where('order_id', $id)->orderby('id', 'DESC')->first();
        $proforma_last_date = PrintHistory::where('page_type', 'draft_invoice')->where('print_type', 'proforma')->where('order_id', $id)->orderby('id', 'DESC')->first();
        $pick_instruction_last_date = PrintHistory::where('page_type', 'draft_invoice')->where('print_type', 'pick-instruction')->where('order_id', $id)->orderby('id', 'DESC')->first();
        $display_purchase_list = ColumnDisplayPreference::where('type', 'complete_draft_product')->where('user_id', Auth::user()->id)->first();

        $print_prefrences = unserialize($quotation_config->print_prefrences);


        $sales_person = Customer::with('primary_sale_person')->where('id', $order->customer_id)->first();
        $secondary_sales = null;
        if ($sales_person != null) {
            $secondary_sales = CustomerSecondaryUser::where('customer_id', $order->customer_id)->get();
        }

        $config = Configuration::first();
        $display_prods = ColumnDisplayPreference::where('type', 'complete_draft_product')->where('user_id', Auth::user()->id)->first();
        $warehouses = Warehouse::select('id', 'status', 'warehouse_title')->where('status', 1)->get();
        return view('sales.invoice.sep-completed-draft-invoices', compact('display_purchase_list', 'order', 'company_info', 'user_plus_admin_hidden_columns', 'hidden_columns_by_admin', 'total_products', 'sub_total', 'grand_total', 'status_history', 'vat', 'id', 'checkDocs', 'inv_note', 'billing_address', 'shipping_address', 'states', 'warehouse_note', 'payment_term', 'sales_coordinator_customers', 'admin_customers', 'customers', 'sub_total_without_discount', 'item_level_dicount', 'columns_prefrences', 'table_hide_columns', 'hidden_by_default', 'company_banks', 'banks', 'delivery_bill_last_date', 'proforma_last_date', 'pick_instruction_last_date', 'warehouses', 'print_prefrences', 'sales_person', 'secondary_sales', 'display_prods', 'config'));
    }

    public function getCompletedInvoicesDetails($id)
    {
        $order = Order::with('customer.CustomerCategory', 'user', 'customer_billing_address', 'customer_shipping_address', 'order_products')->find($id);
        $company_banks = CompanyBank::with('getBanks')->where('company_id', Auth::user()->company_id)->where('customer_category_id', $order->customer->CustomerCategory->id)->get();
        $banks = Bank::all();
        $states = State::select('id', 'name')->orderby('name', 'ASC')->where('country_id', 217)->get();
        $billing_address = null;
        $shipping_address = null;
        $company_info = Company::where('id', $order->user->company_id)->first();
        if ($order->billing_address_id != null) {
            $billing_address = $order->customer_billing_address;
        }
        if ($order->shipping_address_id) {
            $shipping_address = $order->customer_shipping_address;
        }
        $config = Configuration::first();
        $total_products = $order->order_products->count('id');
        $vat = 0;
        $sub_total = 0;
        $sub_total_w_w = 0;
        $sub_total_without_discount = 0;
        $item_level_dicount = 0;
        foreach ($order->order_products as  $value) {
            $sub_total += $value->total_price;
            $sub_total_w_w += number_format($value->total_price_with_vat, 4, '.', '');
            $vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 4) : (@$value->total_price_with_vat - @$value->total_price);
            if ($value->discount != 0) {
                if ($value->discount == 100) {
                    if ($value->is_retail == 'pieces') {
                        $discount_full =  $value->unit_price_with_vat * $value->pcs_shipped;
                        $sub_total_without_discount += $discount_full;
                    } else {
                        $discount_full =  $value->unit_price_with_vat * $value->qty_shipped;
                        $sub_total_without_discount += $discount_full;
                    }
                    $item_level_dicount += $discount_full;
                } else {
                    $sub_total_without_discount += $value->total_price / ((100 - $value->discount) / 100);
                    $item_level_dicount += ($value->total_price / ((100 - $value->discount) / 100)) - $value->total_price;
                }
            } else {
                $sub_total_without_discount += $value->total_price;
            }
        }
        // dd($vat);
        $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);
        $status_history = OrderStatusHistory::with('get_user')->where('order_id', $id)->get();
        $checkDocs = OrderAttachment::where('order_id', $order->id)->get()->count();
        $inv_note = OrderNote::where('order_id', $order->id)->where('type', 'customer')->first();
        $warehouse_note = OrderNote::where('order_id', $order->id)->where('type', 'warehouse')->first();
        $payment_term = PaymentTerm::all();

        $warehouse_id = Auth::user()->warehouse_id;
        $users = User::select('id')->whereNull('parent_id')->where('warehouse_id', $warehouse_id)->where('role_id', 3)->get();
        $query = Customer::query();
        $ids = array();
        foreach ($users as $user) {
            array_push($ids, $user->id);
        }
        $sales_coordinator_customers = $query->where('status', 1)->whereIn('primary_sale_id', $ids)->orderBy('id', 'DESC')->get();
        $admin_customers = Customer::where('status', 1)->get();
        $customers     = Customer::where(function ($query) {
            $query->where('primary_sale_id', Auth::user()->id)->orWhere('secondary_sale_id', Auth::user()->id);
        })->where('status', 1)->get();


        $quotation_config      = QuotationConfig::where('section', 'quotation')->first();
        $hidden_by_default     = '';
        $columns_prefrences    = null;
        $shouldnt_show_columns = [];
        $hidden_columns        = null;
        $hidden_columns_by_admin = [];
        if ($quotation_config == null) {
            $hidden_by_default = '';
        } else {
            $dislay_prefrences = $quotation_config->display_prefrences;
            $hide_columns = $quotation_config->show_columns;
            if ($quotation_config->show_columns != null) {
                $hidden_columns = json_decode($hide_columns);
                if (!in_array($hidden_columns, $shouldnt_show_columns)) {
                    $hidden_columns = array_merge($hidden_columns, $shouldnt_show_columns);
                    $hidden_columns = implode(",", $hidden_columns);
                    $hidden_columns_by_admin = explode(",", $hidden_columns);
                }
            } else {
                $hidden_columns = implode(",", $shouldnt_show_columns);
                $hidden_columns_by_admin = explode(",", $hidden_columns);
            }
            $user_hidden_columns = [];
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', 'complete_invoice_product')->where('user_id', Auth::user()->id)->first();
            if ($not_visible_columns != null) {
                $user_hidden_columns = $not_visible_columns->hide_columns;
            } else {
                $user_hidden_columns = "";
            }
            $user_plus_admin_hidden_columns = $user_hidden_columns . ',' . $hidden_columns;
            $columns_prefrences = json_decode($quotation_config->display_prefrences);
            $columns_prefrences = implode(",", $columns_prefrences);
        }


        $display_purchase_list = ColumnDisplayPreference::where('type', 'complete_invoices_product')->where('user_id', Auth::user()->id)->first();
        $delivery_bill_last_date = PrintHistory::select('created_at', 'user_id')->where('page_type', 'complete-invoice')->where('print_type', 'delivery-bill')->where('order_id', $id)->orderby('id', 'DESC')->first();
        $proforma_last_date = PrintHistory::select('created_at', 'user_id')->where('page_type', 'complete-invoice')->where('print_type', 'proforma')->where('order_id', $id)->orderby('id', 'DESC')->first();
        $pick_instruction_last_date = PrintHistory::select('created_at', 'user_id')->where('page_type', 'complete-invoice')->where('print_type', 'performa-to-pdf')->where('order_id', $id)->orderby('id', 'DESC')->first();

        $print_prefrences = unserialize($quotation_config->print_prefrences);


        $sales_person = Customer::with('primary_sale_person')->where('id', $order->customer_id)->first();
        $secondary_sales = null;
        if ($sales_person != null) {
            $secondary_sales = CustomerSecondaryUser::where('customer_id', $order->customer_id)->get();
        }

        $display_prods = ColumnDisplayPreference::where('type', 'quotation')->where('user_id', Auth::user()->id)->first();
        
        return view('sales.invoice.sep-completed-invoices-detail', compact('hidden_columns_by_admin', 'display_purchase_list', 'order', 'user_plus_admin_hidden_columns', 'company_info', 'total_products', 'sub_total', 'grand_total', 'status_history', 'vat', 'id', 'checkDocs', 'inv_note', 'billing_address', 'shipping_address', 'states', 'warehouse_note', 'payment_term', 'customers', 'sales_coordinator_customers', 'admin_customers', 'sub_total_without_discount', 'item_level_dicount', 'columns_prefrences', 'table_hide_columns', 'hidden_by_default', 'company_banks', 'banks', 'delivery_bill_last_date', 'proforma_last_date', 'pick_instruction_last_date', 'print_prefrences', 'sales_person', 'secondary_sales', 'display_prods', 'config'));
    }

    public function cancelOrders(Request $request)
    {
        $cancelled = null;
        $done = 0;
        foreach ($request->quotations as $quot) {
            $order = Order::find($quot);
            $old_status = @$order->statuses->title;
            $order->previous_primary_status = $order->primary_status;
            $order->previous_status = $order->status;
            $order->cancelled_date = carbon::now();
            if ($order->primary_status == 2) {

                $order_product = OrderProduct::where('order_id', $order->id)->get();
                if ($order_product->count() > 0) {
                    foreach ($order_product as $op) {
                        $warehouse_id = $op->user_warehouse_id != null ? $op->user_warehouse_id : ($op->from_warehouse_id != null ? $op->from_warehouse_id : Auth::user()->warehouse_id);
                        $op->status = 18;
                        $op->save();

                        $order->status = 18;
                        $order->primary_status = 17;
                        $order->save();
                        $done = $done + 1;
                        if ($op->product_id != null) {
                            DB::beginTransaction();
                            try {
                                $new_his = new QuantityReservedHistory;
                                $re      = $new_his->updateReservedQuantity($op, 'Draft Invoice cancelled reserved quantity subtracted', 'subtract');
                                DB::commit();
                            } catch (\Excepion $e) {
                                DB::rollBack();
                            }
                        }
                    }
                } else {
                    $order->status = 18;
                    $order->primary_status = 17;
                    $order->save();
                }

                $status_history = new OrderStatusHistory;
                $status_history->user_id = Auth::user()->id;
                $status_history->order_id = @$order->id;
                $status_history->status = $old_status;
                $status_history->new_status = 'Cancelled';
                $status_history->save();
            } else {
                $cancelled .= $order->ref_id . ' ';
            }
        }
        if ($done > 0 && !empty($cancelled)) {
            return response()->json(['order_cancelled' => true, 'msg' => ' <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong> Alert! </strong>Order(s) ' . @$cancelled . ' Cannot be cancelled because they are not in selecting vendor status.', 'success' => true]);
        } else if (!empty($cancelled)) {

            return response()->json(['order_cancelled' => true, 'msg' => '<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a><strong> Alert! </strong>Order(s) ' . @$cancelled . ' Cannot be cancelled because they are not in selecting vendor status.']);
        } else {
            return response()->json(['success' => true]);
        }
    }

    public function cancelInvoiceOrders(Request $request)
    {
        $confirm_from_draft = QuotationConfig::where('section', 'warehouse_management_page')->first();
        // $globalaccessForConfig=[];
        if ($confirm_from_draft) {
            $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
            foreach ($globalaccessForWarehouse as $val) {
                if ($val['slug'] === "has_warehouse_account") {
                    $has_warehouse_account = $val['status'];
                }
            }
        } else {
            $has_warehouse_account = '';
        }

        $cancelled = null;
        $done      = 0;
        foreach ($request->quotations as $quot) {
            $order = Order::find($quot);
            $order_product = OrderProduct::where('order_id', $order->id)->whereNotNull('product_id')->get();

            $order->previous_primary_status = $order->primary_status;
            $order->previous_status = $order->status;
            $order->cancelled_date = carbon::now();
            foreach ($order_product as $op) {
                $warehouse_id = $op->user_warehouse_id != null ? $op->user_warehouse_id : ($op->from_warehouse_id != null ? $op->from_warehouse_id : Auth::user()->warehouse_id);;
                $returned = 0;
                $op->status = 18;
                $op->save();
                // Latest Code For Stock Management On Cancellation Starts Here
                $quantity_returned =  $op->qty_shipped;
                $stock = StockManagementOut::whereNotNull('quantity_out')->where('order_id', $op->order_id)->where('order_product_id', $op->id)->where('product_id', $op->product_id)->where('warehouse_id', $warehouse_id)->get();
                if ($has_warehouse_account == 1) {
                    foreach ($stock as $st) {
                        $stock_out                   = new StockManagementOut;
                        $stock_out->smi_id           = $st->smi_id;
                        $stock_out->order_id         = $op->order_id;
                        $stock_out->order_product_id = $op->id;
                        $stock_out->title            = 'Return';
                        $stock_out->product_id       = $op->product_id;
                        $stock_out->quantity_in      = abs($st->quantity_out);
                        $stock_out->created_by       = Auth::user()->id;
                        $stock_out->warehouse_id     = $warehouse_id;
                        $stock_out->save();
                        $returned += abs($st->quantity_out);
                    }
                    if ($returned != $quantity_returned) {
                        $stock = StockManagementIn::whereNull('expiration_date')->where('product_id', $op->product_id)->where('warehouse_id', $warehouse_id)->first();
                        if ($stock == null) {
                            $stock = new StockManagementIn;
                            $stock->title           = 'Adjustment';
                            $stock->product_id      = $op->product_id;
                            $stock->created_by      = Auth::user()->id;
                            $stock->warehouse_id    = $warehouse_id;
                            $stock->save();
                        }

                        $stock_out                   = new StockManagementOut;
                        $stock_out->smi_id           = $stock->id;
                        $stock_out->order_id         = $op->get_order->id;
                        $stock_out->order_product_id = $op->id;
                        $stock_out->title            = 'Return';
                        $stock_out->product_id       = $op->product_id;
                        if ($quantity_returned - $returned < 0) {
                            $stock_out->quantity_out     = - ($returned - $quantity_returned);
                            $stock_out->available_stock     = - ($returned - $quantity_returned);
                        } else {
                            $stock_out->quantity_in      = $quantity_returned - $returned;
                            $stock_out->available_stock      = $quantity_returned - $returned;
                        }
                        $stock_out->created_by       = Auth::user()->id;
                        $stock_out->warehouse_id     = $warehouse_id;
                        $stock_out->save();

                        if ($quantity_returned - $returned < 0) {
                            //To find from which stock the order will be deducted
                            $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                            if ($find_stock->count() > 0) {
                                foreach ($find_stock as $out) {

                                    if (abs($stock_out->available_stock) > 0) {
                                        if ($out->available_stock >= abs($stock_out->available_stock)) {
                                            $stock_out->parent_id_in .= $out->id . ',';
                                            $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $stock_out->available_stock = 0;
                                        } else {
                                            $stock_out->parent_id_in .= $out->id . ',';
                                            $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                            $out->available_stock = 0;
                                        }
                                        $out->save();
                                        $stock_out->save();
                                    }
                                }
                            }
                        } else {
                            $find_stock = StockManagementOut::where('smi_id', $stock_out->smi_id)->whereNotNull('quantity_out')->where('available_stock', '<', 0)->get();
                            if ($find_stock->count() > 0) {
                                foreach ($find_stock as $out) {

                                    if ($stock_out->quantity_in > 0 && abs($stock_out->available_stock) > 0) {
                                        if ($stock_out->available_stock >= abs($out->available_stock)) {
                                            $out->parent_id_in .= $stock_out->id . ',';
                                            $stock_out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                            $out->available_stock = 0;
                                        } else {
                                            $out->parent_id_in .= $out->id . ',';
                                            $out->available_stock = $stock_out->available_stock - abs($out->available_stock);
                                            $stock_out->available_stock = 0;
                                        }
                                        $out->save();
                                        $stock_out->save();
                                    }
                                }
                            }
                        }
                    }


                    DB::beginTransaction();
                    try {
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateCurrentQuantity($op, $quantity_returned, 'add', null);
                        DB::commit();
                    } catch (\Excepion $e) {
                        DB::rollBack();
                    }
                }

                // Latest Code For Stock Management On Cancellation Ends Here

            }
            $order->status         = 18;
            $order->primary_status = 17;
            $order->save();

            $status_history             = new OrderStatusHistory;
            $status_history->user_id    = Auth::user()->id;
            $status_history->order_id   = @$order->id;
            $status_history->status     = 'Invoice';
            $status_history->new_status = 'Cancelled';
            $status_history->save();
        }
        return response()->json(['success' => true]);
    }

    public function revertInvoiceOrders(Request $request)
    {
        $cancelled = null;
        $done      = 0;
        $temp_primary_status = 0;
        $temp_status = 0;
        $quantity_order = 0;


        $confirm_from_draft = QuotationConfig::where('section', 'warehouse_management_page')->first();
        // $globalaccessForConfig=[];
        if ($confirm_from_draft) {
            $globalaccessForWarehouse = unserialize($confirm_from_draft->print_prefrences);
            foreach ($globalaccessForWarehouse as $val) {
                if ($val['slug'] === "has_warehouse_account") {
                    $has_warehouse_account = $val['status'];
                }
            }
        } else {
            $has_warehouse_account = '';
        }

        // dd($request->quotations);

        if (Auth::user()->role_id == 9) {
            $base_link  = config('app.ecom_url');
            $ecommerceconfig = QuotationConfig::where('section', 'ecommerce_configuration')->first();
            $check_status = unserialize($ecommerceconfig->print_prefrences);
            $default_warehouse_id =  $check_status['status'][5];
            //dd($default_warehouse_id);
            foreach ($request->quotations as $quot) {
                $order = Order::find($quot);
                $order_quantity = OrderProduct::whereNotNull('product_id')->where('order_id', $order->id)->get();
                foreach ($order_quantity as $value) {
                    $product = Product::find($value->product_id);
                    if ($product->ecom_selling_unit) {
                        $new_reserve_qty = $value->quantity;
                    } else {
                        $new_reserve_qty = $value->quantity;
                    }

                    DB::beginTransaction();
                    try {
                        $new_his = new QuantityReservedHistory;
                        $re      = $new_his->updateEcomReservedQuantity($value, 'Ecom Quantity Reserved by Revert Cancelled Invoice Into Invoice ', 'add');
                        DB::commit();
                    } catch (\Excepion $e) {
                        DB::rollBack();
                    }
                }

                $temp_primary_status = $order->primary_status;
                $temp_status = $order->status;
                $order->primary_status = $order->previous_primary_status;
                $order->status = $order->previous_status;
                $order->previous_primary_status = $temp_primary_status;
                $order->previous_status = $temp_status;
                $order->save();

                $uri = $base_link . "/api/updateorderstatus/" . $order->ecommerce_order_no . "/" . $order->primary_status . "/" . $order->status;
                // dd($uri);
                $test =  $this->sendRequest($uri);
            }
        } else {
            foreach ($request->quotations as $quot) {
                $order = Order::find($quot);
                $order_products = OrderProduct::where('order_id', $order->id)->whereNotNull('product_id')->get();
                if ($order->previous_primary_status == null) {
                    return response()->json(['success' => false]);
                } else {
                    $order->primary_status = $order->previous_primary_status;
                    $order->status = $order->previous_status;
                    foreach ($order_products as $order_product) {
                        $warehouse_id = $order_product->user_warehouse_id != null ? $order_product->user_warehouse_id : ($order_product->from_warehouse_id != null ? $order_product->from_warehouse_id : Auth::user()->warehouse_id);
                        $returned = 0;
                        $order_product->status = $order->previous_status;
                        $order_product->save();
                        if ($has_warehouse_account == 1) {
                            if ($order_product->qty_shipped != 0 && $order_product->qty_shipped != null) {
                                $total_cost = null;
                                $total_count = null;
                                if ($order_product->expiration_date != null) {
                                    $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->where('expiration_date', $order_product->expiration_date)->whereNotNull('expiration_date')->first();
                                    $shipped = $order_product->qty_shipped;
                                    if ($stock == null) {
                                        $stock = new StockManagementIn;
                                        $stock->title = 'Adjustment';
                                        $stock->product_id = $order_product->product_id;
                                        $stock->created_by = Auth::user()->id;
                                        $stock->warehouse_id = $warehouse_id;
                                        $stock->expiration_date = $order_product->expiration_date;
                                        $stock->save();
                                    }
                                    if ($stock != null) {
                                        $stock_out = new StockManagementOut;
                                        $stock_out->smi_id = $stock->id;
                                        $stock_out->order_id = $order_product->order_id;
                                        $stock_out->order_product_id = $order_product->id;
                                        $stock_out->product_id = $order_product->product_id;
                                        $stock_out->quantity_out = $order_product->qty_shipped != null ? '-' . $order_product->qty_shipped : 0;
                                        $stock_out->created_by = Auth::user()->id;
                                        $stock_out->warehouse_id = $warehouse_id;
                                        $stock_out->available_stock = $order_product->qty_shipped != null ? '-' . $order_product->qty_shipped : 0;
                                        $stock_out->save();
                                        //To find from which stock the order will be deducted
                                        $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                                        if ($find_stock->count() > 0) {
                                            foreach ($find_stock as $out) {

                                                if (abs($stock_out->available_stock) > 0) {
                                                    if ($out->available_stock >= abs($stock_out->available_stock)) {
                                                        $history_quantity = $stock_out->available_stock;
                                                        $stock_out->parent_id_in .= $out->id . ',';
                                                        $out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                        $stock_out->available_stock = 0;
                                                        $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($history_quantity));
                                                    } else {
                                                        $history_quantity = $stock_out->available_stock;
                                                        $stock_out->parent_id_in .= $out->id . ',';
                                                        $stock_out->available_stock = $out->available_stock - abs($stock_out->available_stock);
                                                        $out->available_stock = 0;
                                                        $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($history_quantity));
                                                    }
                                                    $out->save();
                                                    $stock_out->save();
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->whereNotNull('expiration_date')->orderBy('expiration_date', "ASC")->get();
                                    $shipped = $order_product->qty_shipped;
                                    foreach ($stock as $st) {
                                        $stock_out_in = StockManagementOut::where('smi_id', $st->id)->sum('quantity_in');
                                        $stock_out_out = StockManagementOut::where('smi_id', $st->id)->sum('quantity_out');
                                        $balance = ($stock_out_in) + ($stock_out_out);
                                        $balance = round($balance, 3);
                                        if ($balance > 0) {
                                            $inStock = $balance - $shipped;
                                            if ($inStock >= 0) {
                                                $stock_out = new StockManagementOut;
                                                $stock_out->smi_id = $st->id;
                                                $stock_out->order_id = $order_product->order_id;
                                                $stock_out->order_product_id = $order_product->id;
                                                $stock_out->product_id = $order_product->product_id;
                                                $stock_out->quantity_out = $shipped != null ? '-' . $shipped : 0;
                                                $stock_out->available_stock = '-' . $shipped;
                                                $stock_out->created_by = Auth::user()->id;
                                                $stock_out->warehouse_id = $warehouse_id;
                                                $stock_out->save();


                                                //To find from which stock the order will be deducted
                                                $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();
                                                if ($find_stock->count() > 0) {
                                                    foreach ($find_stock as $out) {

                                                        if ($shipped > 0) {
                                                            if ($out->available_stock >= $shipped) {
                                                                $history_quantity = $stock_out->available_stock;
                                                                $stock_out->parent_id_in .= $out->id . ',';
                                                                $out->available_stock = $out->available_stock - $shipped;
                                                                $stock_out->available_stock = 0;
                                                                $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($history_quantity));
                                                            } else {
                                                                $history_quantity = $out->available_stock;
                                                                $stock_out->parent_id_in .= $out->id . ',';
                                                                $stock_out->available_stock = $out->available_stock - $shipped;
                                                                $out->available_stock = 0;
                                                                $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($history_quantity));
                                                            }
                                                            $out->save();
                                                            $stock_out->save();
                                                            $shipped = abs($stock_out->available_stock);
                                                        }
                                                    }
                                                }
                                                $shipped = 0;
                                                break;
                                            } else {
                                                $stock_out = new StockManagementOut;
                                                $stock_out->smi_id = $st->id;
                                                $stock_out->order_id = $order_product->order_id;
                                                $stock_out->order_product_id = $order_product->id;
                                                $stock_out->product_id = $order_product->product_id;
                                                $stock_out->quantity_out = -$balance;
                                                // $stock_out->available_stock = $inStock;
                                                $stock_out->created_by = Auth::user()->id;
                                                $stock_out->warehouse_id = $warehouse_id;
                                                $stock_out->save();

                                                //To find from which stock the order will be deducted
                                                $find_stock = $st->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();

                                                $find_available_stock = $find_stock->sum('available_stock');
                                                if ($find_stock->count() > 0) {
                                                    foreach ($find_stock as $out) {

                                                        if ($shipped > 0) {
                                                            if ($out->available_stock >= $shipped) {
                                                                $history_quantity = $stock_out->available_stock;
                                                                $stock_out->parent_id_in .= $out->id . ',';
                                                                $out->available_stock = $out->available_stock - $shipped;
                                                                $stock_out->available_stock = 0;
                                                                $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($history_quantity));
                                                            } else {
                                                                $history_quantity = $out->available_stock;
                                                                $stock_out->parent_id_in .= $out->id . ',';
                                                                $stock_out->available_stock = $out->available_stock - $shipped;
                                                                $out->available_stock = 0;
                                                                $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($history_quantity));
                                                            }
                                                            $out->save();
                                                            $stock_out->save();

                                                            $shipped = abs($stock_out->available_stock);
                                                        }
                                                    }
                                                    $shipped = abs($stock_out->available_stock);

                                                    $stock_out->available_stock = 0;
                                                    $stock_out->save();
                                                } else {
                                                    $shipped = abs($inStock);
                                                }
                                            }
                                        }
                                    }
                                    if ($shipped != 0) {
                                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', $warehouse_id)->whereNull('expiration_date')->first();
                                        if ($stock == null) {
                                            $stock = new StockManagementIn;
                                            $stock->title = 'Adjustment';
                                            $stock->product_id = $order_product->product_id;
                                            $stock->created_by = Auth::user()->id;
                                            $stock->warehouse_id = $warehouse_id;
                                            $stock->expiration_date = $order_product->expiration_date;
                                            $stock->save();
                                        }

                                        //To find from which stock the order will be deducted
                                        $find_stock = $stock->stock_out()->whereNotNull('quantity_in')->where('available_stock', '>', 0)->orderBy('id', 'asc')->get();

                                        $stock_out = new StockManagementOut;
                                        $stock_out->smi_id = $stock->id;
                                        $stock_out->order_id = $order_product->order_id;
                                        $stock_out->order_product_id = $order_product->id;
                                        $stock_out->product_id = $order_product->product_id;
                                        $stock_out->quantity_out = $shipped != null ? '-' . $shipped : 0;
                                        $stock_out->available_stock = $shipped != null ? '-' . $shipped : 0;
                                        $stock_out->created_by = Auth::user()->id;
                                        $stock_out->warehouse_id = $warehouse_id;
                                        $stock_out->save();

                                        if ($find_stock->count() > 0) {
                                            foreach ($find_stock as $out) {

                                                if ($shipped > 0) {
                                                    if ($out->available_stock >= $shipped) {
                                                        $history_quantity = $stock_out->available_stock;
                                                        $stock_out->parent_id_in .= $out->id . ',';
                                                        $out->available_stock = $out->available_stock - $shipped;
                                                        $stock_out->available_stock = 0;
                                                        $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($history_quantity));
                                                    } else {
                                                        $history_quantity = $out->available_stock;
                                                        $stock_out->parent_id_in .= $out->id . ',';
                                                        $stock_out->available_stock = $out->available_stock - $shipped;
                                                        $out->available_stock = 0;
                                                        $new_stock_out_history = (new StockOutHistory)->setHistory($out, $stock_out, $order_product, abs($history_quantity));
                                                    }
                                                    $out->save();
                                                    $stock_out->save();
                                                    $shipped = abs($stock_out->available_stock);
                                                }
                                            }
                                        } else {
                                            $stock_out->available_stock = '-' . @$shipped;
                                            $stock_out->save();
                                        }
                                    }
                                }
                                DB::beginTransaction();
                                try {
                                    $new_his = new QuantityReservedHistory;
                                    $re      = $new_his->updateCurrentQuantity($order_product, $order_product->qty_shipped, 'subtract', null);
                                    DB::commit();
                                } catch (\Excepion $e) {
                                    DB::rollBack();
                                }
                            }
                        }
                        if ($order->previous_primary_status == 2) {
                            if ($order_product->product_id != null) {
                                DB::beginTransaction();
                                try {
                                    $new_his = new QuantityReservedHistory;
                                    $re      = $new_his->updateReservedQuantity($order_product, 'Cancelled draft invoice revert to draft invoice quantity reserved', 'add');
                                    DB::commit();
                                } catch (\Excepion $e) {
                                    DB::rollBack();
                                }
                            }
                        }
                    }
                    $status_history             = new OrderStatusHistory;
                    $status_history->user_id    = Auth::user()->id;
                    $status_history->order_id   = @$order->id;
                    $status_history->status     = 'Cancelled';
                    if ($order->previous_primary_status == 3) {
                        $status_history->new_status = 'Invoice';
                    } else {
                        $status_history->new_status = 'DI(' . @$order->pre_statuses->title . ')';
                    }
                    $status_history->save();

                    $order->save();
                }
            }
        }
        return response()->json(['success' => true]);
    }

    public function sendRequest($uri)
    {
        $curl = curl_init($uri);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    public function revertDraftInvoice(Request $request)
    {
        $cancelled = [];
        foreach ($request->quotations as $quot) {
            $order = Order::find($quot);
            if ($order->primary_status == 2 && ($order->status == 7 || $order->status == 17)) {
                $order_product = OrderProduct::where('order_id', $order->id)->whereIn('status', [8, 9, 10])->get();

                if ($order_product->count() > 0) {
                    $msg = "Cannot cancel this order because its item is in the PO Waititng Confirmation Status. Order No:" . $order->ref_id;
                    return response()->json(['success' => false, 'msg' => $msg]);
                } else {
                    $order_product = OrderProduct::where('order_id', $order->id)->get();
                    foreach ($order_product as $op) {
                        $op->status = 6;
                        $op->save();

                        $order->status = 6;
                        $order->primary_status = 1;
                        $quot_status           = Status::where('id', 1)->first();
                        $company_prefix          = @Auth::user()->getCompany->prefix;
                        $order->status_prefix  = $quot_status->prefix . $company_prefix;
                        $order->save();
                        DB::beginTransaction();
                        try {
                            $new_his = new QuantityReservedHistory;
                            $re      = $new_his->updateReservedQuantity($op, 'Draft Invoice Revert To Quotation Reserved Subtracted', 'subtract');
                            DB::commit();
                        } catch (\Excepion $e) {
                            DB::rollBack();
                        }
                    }

                    $status_history = new OrderStatusHistory;
                    $status_history->user_id = Auth::user()->id;
                    $status_history->order_id = @$order->id;
                    $status_history->status = 'DI(Waiting Gen PO)';
                    $status_history->new_status = 'Quotation';
                    $status_history->save();
                }
                return response()->json(['success' => true, 'order_cancelled' => true]);
            } else {
                return response()->json(['success' => false, 'msg' => 'Cannot Revert Order Is Not In Selecting Vendor Status']);
            }
        }
    }

    public function getPendingQuotationsData(Request $request)
    {
        $query = DraftQuotation::with('customer', 'customer.getpayment_term')->orderBy('id', 'DESC')->where('created_by', Auth::user()->id);
        if ($request->selecting_customer != null) {
            $query->where('customer_id', $request->selecting_customer);
        }
        if ($request->selecting_customer_group != null) {
            $id_split = explode('-', $request->selecting_customer_group);

            if ($id_split[0] == 'cat') {
                $query = $query->whereHas('customer', function ($q) use ($id_split) {
                    $q->where('category_id', $id_split[1]);
                });
            } else {
                $query = $query->where('customer_id', $id_split[1]);
            }
        }
        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            $query->where('draft_quotations.delivery_request_date', '>=', $date . ' 00:00:00');
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            $query->where('draft_quotations.delivery_request_date', '<=', $date . ' 00:00:00');
        }
        $status = Status::find(5);
        return Datatables::of($query)

            ->addColumn('checkbox', function ($item) {

                $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check" value="' . $item->id . '" id="stone_check_' . $item->id . '">
                                    <label class="custom-control-label" for="stone_check_' . $item->id . '"></label>
                                </div>';
                return $html_string;
            })


            ->addColumn('customer', function ($item) {
                if ($item->customer_id != null) {
                    if ($item->customer['reference_name'] != null) {
                        $html_string = '
                  <a href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '">' . $item->customer['reference_name'] . '</a>';
                    } else {
                        $html_string = 'N.A';
                    }


                    return $html_string;
                } else {
                    return 'N.A';
                }
            })

            ->addColumn('customer_ref_no', function ($item) {
                if ($item->customer_id != null) {
                    $ref = $item->customer !== null ? $item->customer->reference_number : '--';
                    return '
                  <a href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '">' . $ref . '</a>';
                } else {
                    return "N.A";
                }
            })

            ->addColumn('number_of_products', function ($item) {

                $html_string = $item->draft_quotation_products->count();
                return $html_string;
            })

            ->addColumn('status', function ($item) use ($status) {
                $html = '<span class="sentverification">' . @$status->title . '</span>';
                return $html;
            })

            ->addColumn('ref_id', function ($item) {

                // return ($item->id);
                $html_string = '<a href="' . route('get-invoice', ['id' => $item->id]) . '" title="View Products" id="invoice_' . $item->id . '"><b>' . $item->id . '</i>';
                return $html_string;
            })
            ->addColumn('payment_term', function ($item) {
                if ($item->customer_id != null) {
                    return (@$item->customer->getpayment_term !== null ? @$item->customer->getpayment_term->title : '');
                } else {
                    return "N.A";
                }
            })
            ->addColumn('invoice_date', function ($item) {
                return @$item->target_ship_date != null ?  Carbon::parse($item->target_ship_date)->format('d/m/Y') : '--';
                // return Carbon::parse(@$item->updated_at)->format('d/m/Y');

            })
            ->addColumn('delivery_date', function ($item) {
                return @$item->delivery_request_date != null ?  Carbon::parse($item->delivery_request_date)->format('d/m/Y') : '--';
                // return Carbon::parse(@$item->updated_at)->format('d/m/Y');

            })
            ->addColumn('total_amount', function ($item) {
                $total_amount = $item->draft_quotation_products->sum('total_price_with_vat');
                $total = number_format(floor($total_amount * 100) / 100, 2, '.', ',');

                return ($total);
            })
            ->addColumn('comment_to_warehouse', function ($item) {
                // $warehouse_note = DraftQuotationNote::where('draft_quotation_id', $item->id)->where('type','warehouse')->first();
                $warehouse_note = $item->draft_quotation_notes->where('type', 'warehouse')->first();
                return @$warehouse_note != null ? @$warehouse_note->note : '--';
            })
            ->addColumn('action', function ($item) {
                $html_string = '<a href="javascript:void(0);" data-id="' . $item->id . '" class="actionicon delete-btn" title="Delete Draft Quotation"><i class="fa fa-trash"></i></a>';
                return $html_string;
            })
            ->rawColumns(['action', 'checkbox', 'ref_id', 'customer', 'number_of_products', 'status', 'customer_ref_no', 'comment_to_warehouse'])
            ->make(true);
    }

    public function pendingQuotations()
    {
        return view('sales.invoice.pending-quotations');
    }

    public function getCompletedQuotationProducts($id)
    {
        $order = Order::with('order_products', 'customer_billing_address', 'customer_shipping_address')->find($id);
        if($order->primary_status != 1)
            return redirect()->route('sales');
        $states = State::select('id', 'name')->orderby('name', 'ASC')->where('country_id', 217)->get();
        $display_purchase_list = ColumnDisplayPreference::where('type', 'complete_quotation_product')->where('user_id', Auth::user()->id)->first();
        $billing_address = null;
        $shipping_address = null;

        $payment_term = PaymentTerm::all();
        $company_info = Company::where('id', $order->user->company_id)->first();
        if ($order->billing_address_id != null) {
            $billing_address = $order->customer_billing_address;
        }
        if ($order->shipping_address_id) {
            $shipping_address = $order->customer_shipping_address;
        }
        $total_products = $order->order_products->count('id');
        $vat = 0;
        $sub_total = 0;
        $sub_total_w_w = 0;
        $sub_total_without_discount = 0;
        $item_level_dicount = 0;
        foreach ($order->order_products as  $value) {
            $sub_total += $value->total_price;
            $sub_total_w_w += round($value->total_price_with_vat, 4);
            $vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 2) : (@$value->total_price_with_vat - @$value->total_price);

            if ($value->discount != 0) {
                if ($value->discount == 100) {
                    if ($value->is_retail == 'pieces') {
                        $discount_full =  $value->unit_price_with_vat * $value->number_of_pieces;
                        $sub_total_without_discount += $discount_full;
                    } else {
                        $discount_full =  $value->unit_price_with_vat * $value->quantity;
                        $sub_total_without_discount += $discount_full;
                    }
                    $item_level_dicount += $discount_full;
                } else {
                    $sub_total_without_discount += $value->total_price / ((100 - $value->discount) / 100);
                    $item_level_dicount += ($value->total_price / ((100 - $value->discount) / 100)) - $value->total_price;
                }
            } else {
                $sub_total_without_discount += $value->total_price;
            }
        }
        $check_inquiry = OrderProduct::where('order_id', $id)->where('is_billed', 'Inquiry')->count();
        // dd($check_inquiry);
        $grand_total = ($sub_total_w_w) - ($order->discount) + ($order->shipping);
        $status_history = OrderStatusHistory::with('get_user')->where('order_id', $id)->get();
        $checkDocs = OrderAttachment::where('order_id', $order->id)->get()->count();
        $inv_note = OrderNote::where('order_id', $order->id)->where('type', 'customer')->first();
        $warehouse_note = OrderNote::where('order_id', $order->id)->where('type', 'warehouse')->first();

        $warehouse_id = Auth::user()->warehouse_id;
        $users = User::select('id')->where('warehouse_id', $warehouse_id)->whereNull('parent_id')->where('role_id', 3)->get();
        $query = Customer::query();
        $ids = array();
        foreach ($users as $user) {
            array_push($ids, $user->id);
        }
        $sales_coordinator_customers = $query->where('status', 1)->whereIn('primary_sale_id', $ids)->orderBy('id', 'DESC')->get();
        $admin_customers = Customer::where('status', 1)->get();
        $user_id = Auth::user()->id;
        $customers     = Customer::where(function ($query) use ($user_id) {
            $query->where('primary_sale_id', Auth::user()->id)->orWhereHas('CustomerSecondaryUser', function ($u) use ($user_id) {
                $u->where('user_id', $user_id);
            });
        })->where('status', 1)->get();

        $quotation_config      = $quotation_config = QuotationConfig::where('section', 'quotation')->first();
        $hidden_by_default     = '';
        $columns_prefrences    = null;
        $shouldnt_show_columns = [11, 12, 15, 17];
        $hidden_columns        = null;
        $hidden_columns_by_admin = [];
        if ($quotation_config == null) {
            $hidden_by_default = '';
        } else {
            $dislay_prefrences = $quotation_config->display_prefrences;
            $hide_columns = $quotation_config->show_columns;
            if ($quotation_config->show_columns != null) {
                $hidden_columns = json_decode($hide_columns);
                if (!in_array($hidden_columns, $shouldnt_show_columns)) {
                    $hidden_columns = array_merge($hidden_columns, $shouldnt_show_columns);
                    $hidden_columns = implode(",", $hidden_columns);
                    $hidden_columns_by_admin = explode(",", $hidden_columns);
                }
            } else {
                $hidden_columns = implode(",", $shouldnt_show_columns);
                $hidden_columns_by_admin = explode(",", $hidden_columns);
            }
            $user_hidden_columns = [];
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', 'complete_quotation_product')->where('user_id', Auth::user()->id)->first();
            if ($not_visible_columns != null) {
                $user_hidden_columns = $not_visible_columns->hide_columns;
            } else {
                $user_hidden_columns = "";
            }
            $user_plus_admin_hidden_columns = $user_hidden_columns . ',' . $hidden_columns;
            $columns_prefrences = json_decode($quotation_config->display_prefrences);
            $columns_prefrences = implode(",", $columns_prefrences);
        }
        $globalAccessConfig = GlobalAccessForRole::where('type', 'quote_print')->where('status', 1)->get();
        $printConfig = [];
        if ($globalAccessConfig->count() > 0) {
            foreach ($globalAccessConfig as $ga) {
                $printConfig[$ga->slug] = 1;
            }
        }
        $company_banks = CompanyBank::with('getBanks')->where('company_id', Auth::user()->company_id)->where('customer_category_id', $order->customer->CustomerCategory->id)->get();
        $banks = Bank::all();
        $view_incvat_last_date = PrintHistory::select('created_at', 'user_id')->where('page_type', 'quotations')->where('print_type', 'view-incvat')->where('order_id', $id)->orderby('id', 'DESC')->first();
        $view_last_date = PrintHistory::where('page_type', 'quotations')->where('order_id', $id)->where('print_type', 'view')->orderby('id', 'DESC')->first();
        $warehouses = Warehouse::select('id', 'warehouse_title', 'status')->where('status', 1)->get();
        $is_texica = Status::where('id', 1)->pluck('is_texica')->first();

        $print_prefrences = unserialize($quotation_config->print_prefrences);


        $sales_person = Customer::with('primary_sale_person')->where('id', $order->customer_id)->first();
        $secondary_sales = null;
        if ($sales_person != null) {
            $secondary_sales = CustomerSecondaryUser::where('customer_id', $order->customer_id)->get();
        }

        $display_prods = ColumnDisplayPreference::where('type', 'complete_quotation_product')->where('user_id', Auth::user()->id)->first();
        return view('sales.invoice.completed-quotation-products', compact(
            'columns_prefrences',
            'display_purchase_list',
            'hidden_columns_by_admin',
            'hidden_by_default',
            'order',
            'company_info',
            'total_products',
            'sub_total',
            'grand_total',
            'status_history',
            'vat',
            'id',
            'checkDocs',
            'inv_note',
            'billing_address',
            'shipping_address',
            'states',
            'warehouse_note',
            'check_inquiry',
            'payment_term',
            'user_plus_admin_hidden_columns',
            'customers',
            'admin_customers',
            'sales_coordinator_customers',
            'sub_total_without_discount',
            'item_level_dicount',
            'printConfig',
            'company_banks',
            'banks',
            'view_incvat_last_date',
            'view_last_date',
            'warehouses',
            'is_texica',
            'print_prefrences',
            'sales_person',
            'secondary_sales',
            'display_prods'
        ));
    }

    public function addCompletedQuotProdNote(Request $request)
    {
        $request->validate([
            'note_description' => 'required'
        ]);
        if ($request['note_description'] != null && $request['note_description'] != '') {
            $compl_quot  = new OrderProductNote;
            $compl_quot->order_product_id = $request['completed_quot_id'];
            $compl_quot->note = $request['note_description'];
            if ($request['show_note_invoice'] != null) {
                $compl_quot->show_on_invoice = $request['show_note_invoice'];
            }
            $compl_quot->save();
            return response()->json(['success' => true]);
        }
    }

    public function getCompletedQuotProdNote(Request $request)
    {
        $compl_quot_notes = OrderProductNote::where('order_product_id', $request->compl_quot_id)->get();

        $html_string = '<div class="table-responsive">
                        <table class="table table-bordered text-center">
                        <thead class="table-bordered">
                        <tr>
                            <th>S.no</th>
                            <th>Description</th>';
        if (!$request->sold) {
            $html_string .= '<th>Action</th>';
        }
        $html_string .= '</tr>
                        </thead><tbody>';
        if ($compl_quot_notes->count() > 0) {
            $i = 0;
            foreach ($compl_quot_notes as $note) {
                $i++;
                $html_string .= '<tr id="gem-note-' . $note->id . '">
                            <td>' . $i . '</td>
                            <td>' . $note->note . '</td>';
                if (!$request->sold) {
                    $html_string .=   '<td><a href="javascript:void(0);" data-id="' . $note->id . '" data-compl_quot_id = "' . $request->compl_quot_id . '" id="delete-compl-note" class=" actionicon" title="Delete Note"><i class="fa fa-trash" style="color:red;"></i></a></td>';
                }
                $html_string .= '</tr>';
            }
        } else {
            return response()->json(['no_data' => true]);
            $html_string .= '<tr>
                            <td colspan="4">No Note Found</td>
                         </tr>';
        }


        $html_string .= '</tbody></table></div>';
        return $html_string;
    }

    public function deleteCompletedQuotProdNote(Request $request)
    {
        $draft_quot_pr  = OrderProductNote::find($request->note_id);
        $draft_quot_pr->delete();
        return response()->json(['success' => true]);
    }

    public function updateCompletedQuotProdNote(Request $request)
    {
        $draft_quot_pr  = OrderProductNote::find($request->note_id);
        $draft_quot_pr->show_on_invoice = $request->show_on_invoice;
        $draft_quot_pr->save();
        return response()->json(['success' => true]);
    }

    public function exportToPDF(Request $request, $id, $page_type, $column_name, $default_sort,  $discount = null, $bank_id = null, $vat = null)
    {
        $orders_array = explode(",", $id);
        $id = $orders_array[0];
        if ($vat == 1) {
            $print_type = 'view-incvat';
        } else {
            $print_type = 'view';
        }
        $show_discount = $discount;
        $with_vat = @$vat;
        $proforma = @$request->is_proforma;
        $order = Order::find($id);

        $order_products = OrderProduct::where('order_id', $id);
        $company_info = Company::where('id', $order->user->company_id)->first();
        // dd($order);
        $bank = Bank::find($bank_id);
        $address = CustomerBillingDetail::select('billing_phone')->where('customer_id', $order->customer_id)->where('is_default', 1)->first();
        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer_id)->where('id', $order->billing_address_id)->first();

        $inv_note = OrderNote::where('order_id', $order->id)->where('type', 'customer')->first();
        $warehouse_note = OrderNote::where('order_id', $order->id)->where('type', 'warehouse')->first();
        $query2 = null;
        $is_texica = Status::where('id', 1)->pluck('is_texica')->first();
        $customPaper = array(0, 0, 576, 792);
        if (@$order->primary_status == 1) {
            $all_orders_count = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('quantity', '>', 0)->orWhereHas('get_order_product_notes');
            })->orderBy('id', 'ASC')->count();
            // getting count on all order products
            $do_pages_count = ceil($all_orders_count / 3);
            if ($with_vat == 1) {
                $getPrintBlade = Status::select('print_2')->where('id', 1)->first();
                if ($is_texica && $is_texica == 1) {
                    $pdf = PDF::loadView('sales.invoice.' . $getPrintBlade->print_2 . '', compact('order', 'query2', 'address', 'company_info', 'with_vat', 'customerAddress', 'customerAddressShip', 'show_discount', 'do_pages_count', 'bank', 'inv_note', 'warehouse_note', 'order_products', 'orders_array', 'print_type', 'page_type', 'default_sort', 'column_name'))->setPaper('a4', 'landscape');
                } else {
                    $pdf = PDF::loadView('sales.invoice.' . $getPrintBlade->print_2 . '', compact('order', 'query2', 'address', 'company_info', 'with_vat', 'customerAddress', 'customerAddressShip', 'show_discount', 'do_pages_count', 'bank', 'inv_note', 'warehouse_note', 'order_products', 'orders_array', 'print_type', 'page_type', 'default_sort', 'column_name'))->setPaper($customPaper);
                }
            } else {
                $getPrintBlade = Status::select('print_1')->where('id', 1)->first();
                if ($is_texica && $is_texica == 1) {
                    $pdf = PDF::loadView('sales.invoice.' . $getPrintBlade->print_1 . '', compact('order', 'query2', 'address', 'company_info', 'with_vat', 'customerAddress', 'customerAddressShip', 'show_discount', 'do_pages_count', 'bank', 'inv_note', 'warehouse_note', 'order_products', 'print_type', 'page_type', 'orders_array', 'default_sort', 'column_name'))->setPaper('a4', 'landscape');
                } else {
                    $pdf = PDF::loadView('sales.invoice.' . $getPrintBlade->print_1 . '', compact('order', 'query2', 'address', 'company_info', 'with_vat', 'customerAddress', 'customerAddressShip', 'show_discount', 'do_pages_count', 'bank', 'inv_note', 'warehouse_note', 'order_products', 'print_type', 'page_type', 'orders_array', 'default_sort', 'column_name'))->setPaper($customPaper);
                }
            }
            // making pdf name starts
            if (@$order->status_prefix !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
            $makePdfName = $ref_no;
            // making pdf name ends
            return $pdf->stream(
                $makePdfName . '.pdf',
                array(
                    'Attachment' => 0
                )
            );
        } else {
            $pdf = PDF::loadView('sales.invoice.invoice', compact('order', 'query', 'query2', 'company_info', 'address', 'proforma', 'inv_note', 'warehouse_note'))->setPaper($customPaper);
            // making pdf name starts
            if (@$order->status_prefix !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }

            $makePdfName = $ref_no;
            return $pdf->download($makePdfName . '.pdf');
        }
    }

    public function exportToPDFCancelled(Request $request, $id, $discount = null, $vat = null)
    {
        $do_pages_count = '';
        $all_orders_count = '';
        $total_products_count_qty = 0;
        $total_products_count_pieces = 0;
        $last_page_to_show = '';
        $with_vat = @$request->with_vat;
        $proforma = @$is_proforma;
        $order = Order::find($id);
        $bank = Bank::find($request->print_bank_id);
        $company_info = Company::where('id', $order->user->company_id)->first();

        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->billing_address_id)->first();
        $customerShippingAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->shipping_address_id)->first();
        $query = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id)->orderBy('id', 'ASC')->get();

        $query2 = null;
        $customPaper = array(0, 0, 576, 792);
        if ($order->previous_primary_status == 2) {
            $total_products_count_qty = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('quantity', '>', 0)->orWhereHas('get_order_product_notes');
            })->where('is_retail', 'qty')->orderBy('id', 'ASC')->count();
            $total_products_count_pieces = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('number_of_pieces', '>', 0)->orWhereHas('get_order_product_notes');
            })->where('is_retail', 'pieces')->orderBy('id', 'ASC')->count();

            $all_orders_count = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('quantity', '>', 0)->orWhereHas('get_order_product_notes');
            })->orderBy('id', 'ASC')->count();
            // getting count on all order products
            if ($all_orders_count <= 8) {
                $do_pages_count = ceil($all_orders_count / 8);
                $final_pages = $all_orders_count % 8;
                if ($final_pages == 0) {
                }
            } else {
                $do_pages_count = ceil($all_orders_count / 9);
                $final_pages = $all_orders_count % 9;
                if ($final_pages == 0) {
                    $do_pages_count++;
                }
            }
        } else if ($order->previous_primary_status == 3) {
            $total_products_count_qty = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
            })->orderBy('id', 'ASC')->count();
            // dd($total_products_count_qty);
            $total_products_count_pieces = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('pcs_shipped', '>', 0)->orWhereHas('get_order_product_notes');
            })->where('is_retail', 'pieces')->orderBy('id', 'ASC')->count();

            $all_orders_count = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
            })->orderBy('id', 'ASC')->count();
            // getting count on all order products
            if ($all_orders_count <= 8) {
                $do_pages_count = ceil($all_orders_count / 8);
                $final_pages = $all_orders_count % 8;
                if ($final_pages == 0) {
                    // $do_pages_count++;
                }
            } else {
                $do_pages_count = ceil($all_orders_count / 9);
                $final_pages = $all_orders_count % 9;
                if ($final_pages == 0) {
                    $do_pages_count++;
                }
            }
        }
        $notes_count = 0;
        foreach ($order->order_products as $prod) {
            $notes_count += $prod->get_order_product_notes->count();
        }

        $inv_note = OrderNote::where('order_id', $id)->where('type', 'customer')->first();
        $pages = ceil(($total_products_count_qty + $total_products_count_pieces + $notes_count) / 13);

        $getPrintBlade = Status::select('print_2')->where('id', 17)->first();
        // dd($getPrintBlade);
        if ($pages == 0) {
            $pages = 1;
        }
        $order_products = $order->order_products;
        $pdf = PDF::loadView('sales.invoice.' . $getPrintBlade->print_2 . '', compact('order', 'query', 'query2', 'company_info', 'proforma', 'customerAddress', 'arr', 'customerShippingAddress', 'pages', 'do_pages_count', 'final_pages', 'all_orders_count', 'inv_note', 'bank', 'order_products'))->setPaper($customPaper);

        // making pdf name starts
        if ($order->previous_primary_status == 3) {
            if (@$order->in_status_prefix !== null) {
                $ref_no = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
        } else {
            if (@$order->status_prefix !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
        }

        $makePdfName = $ref_no;
        // $makePdfName='PO '.($mainObj->user_ref_id != null ? $mainObj->user_ref_id  : $mainObj->ref_id);
        // making pdf name ends
        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0
            )
        );
    }

    public function exportToPDFIncVat(Request $request, $id, $page_type, $column_name, $default_sort, $is_proforma = null, $bank = null)
    {
        return QuotationHelper::exportToPDFIncVat($request, $id, $page_type, $column_name, $default_sort, $is_proforma, $bank);
    }

    public function exportToPDFIncVatFromEcom(Request $request, $id, $page_type, $bank_id = null)
    {
        $do_pages_count = '';
        $all_orders_count = '';
        $last_page_to_show = '';
        $with_vat = @$request->with_vat;
        $proforma = @$is_proforma;

        $order = Order::where('ecommerce_order_id', $id)->first();
        $bank = Bank::find($bank_id);
        $company_info = Company::where('id', $order->user->company_id)->first();

        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->billing_address_id)->first();

        $customerShippingAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->shipping_address_id)->first();
        $query = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('ecommerce_order_id', $id)->orderBy('id', 'ASC')->get();

        $inv_note = OrderNote::where('order_id', $id)->where('type', 'customer')->first();

        $query2 = null;
        $customPaper = array(0, 0, 576, 792);
        if (@$order->primary_status == 1) {
            $pdf = PDF::loadView('sales.invoice.quotation-invoice', compact('order', 'query', 'query2', 'company_info', 'with_vat', 'customerAddress', 'arr'))->setPaper($customPaper);

            // making pdf name starts
            if (@$order->status_prefix !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }

            $makePdfName = $ref_no;
            return $pdf->download($makePdfName . '.pdf');
        } else {
            $discount_1 = 0;
            $discount_2 = 0;
            if ($order->primary_status == 2 || $order->previous_primary_status == 2) {
                $total_products_count_qty = OrderProduct::where('ecommerce_order_id', $id)->where(function ($q) {
                    $q->where('quantity', '>', 0)->orWhereHas('get_order_product_notes');
                })->where('is_retail', 'qty')->orderBy('id', 'ASC')->count();
                $total_products_count_pieces = OrderProduct::where('ecommerce_order_id', $id)->where(function ($q) {
                    $q->where('number_of_pieces', '>', 0)->orWhereHas('get_order_product_notes');
                })->where('is_retail', 'pieces')->orderBy('id', 'ASC')->count();

                $all_orders_count = OrderProduct::where('ecommerce_order_id', $id)->where(function ($q) {
                    $q->where('quantity', '>', 0)->orWhereHas('get_order_product_notes');
                })->orderBy('id', 'ASC')->count();
                // getting count on all order products
                if ($all_orders_count <= 8) {
                    $do_pages_count = ceil($all_orders_count / 8);
                    $final_pages = $all_orders_count % 8;
                    if ($final_pages == 0) {
                        // $do_pages_count++;
                    }
                } else {
                    $do_pages_count = ceil($all_orders_count / 8);
                    $final_pages = $all_orders_count % 8;
                    if ($final_pages == 0) {
                        $do_pages_count++;
                    }
                }

                $discount_1 = OrderProduct::select('id', 'ecommerce_order_id', 'is_retail', 'discount', 'quantity')->where('ecommerce_order_id', $id)->where('is_retail', 'qty')->where('discount', '>', 0)->where('quantity', '>', 0)->orderBy('id', 'ASC')->count();
                $discount_2 = OrderProduct::select('id', 'ecommerce_order_id', 'is_retail', 'discount', 'number_of_pieces')->where('ecommerce_order_id', $id)->where('is_retail', 'pieces')->where('discount', '>', 0)->where('number_of_pieces', '>', 0)->orderBy('id', 'ASC')->count();
            } else if ($order->primary_status == 3 || $order->previous_primary_status == 3) {
                $total_products_count_qty = OrderProduct::where('ecommerce_order_id', $id)->where(function ($q) {
                    $q->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
                })->orderBy('id', 'ASC')->count();
                // dd($total_products_count_qty);
                $total_products_count_pieces = OrderProduct::where('ecommerce_order_id', $id)->where(function ($q) {
                    $q->where('pcs_shipped', '>', 0)->orWhereHas('get_order_product_notes');
                })->where('is_retail', 'pieces')->orderBy('id', 'ASC')->count();

                $all_orders_count = OrderProduct::where('ecommerce_order_id', $id)->where(function ($q) {
                    $q->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
                })->orderBy('id', 'ASC')->count();
                // getting count on all order products
                if ($all_orders_count <= 8) {
                    $do_pages_count = ceil($all_orders_count / 8);
                    $final_pages = $all_orders_count % 8;
                    if ($final_pages == 0) {
                        // $do_pages_count++;
                    }
                } else {
                    $do_pages_count = ceil($all_orders_count / 8);
                    $final_pages = $all_orders_count % 8;
                    if ($final_pages == 0) {
                        $do_pages_count++;
                    }
                }

                $discount_1 = OrderProduct::select('id', 'ecommerce_order_id', 'is_retail', 'discount', 'qty_shipped')->where('ecommerce_order_id', $id)->where('is_retail', 'qty')->where('discount', '>', 0)->where('qty_shipped', '>', 0)->orderBy('id', 'ASC')->count();
                $discount_2 = OrderProduct::select('id', 'ecommerce_order_id', 'is_retail', 'discount', 'pcs_shipped')->where('ecommerce_order_id', $id)->where('is_retail', 'pieces')->where('discount', '>', 0)->where('pcs_shipped', '>', 0)->orderBy('id', 'ASC')->count();
            } else if ($order->primary_status == 17) {
                $total_products_count_qty = OrderProduct::where('ecommerce_order_id', $id)->where(function ($q) {
                    $q->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
                })->orderBy('id', 'ASC')->count();
                // dd($total_products_count_qty);
                $total_products_count_pieces = OrderProduct::where('ecommerce_order_id', $id)->where(function ($q) {
                    $q->where('pcs_shipped', '>', 0)->orWhereHas('get_order_product_notes');
                })->where('is_retail', 'pieces')->orderBy('id', 'ASC')->count();

                $all_orders_count = OrderProduct::where('ecommerce_order_id', $id)->where(function ($q) {
                    $q->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
                })->orderBy('id', 'ASC')->count();
                // getting count on all order products
                if ($all_orders_count <= 8) {
                    $do_pages_count = ceil($all_orders_count / 8);
                    $final_pages = $all_orders_count % 8;
                    if ($final_pages == 0) {
                        // $do_pages_count++;
                    }
                } else {
                    $do_pages_count = ceil($all_orders_count / 8);
                    $final_pages = $all_orders_count % 8;
                    if ($final_pages == 0) {
                        $do_pages_count++;
                    }
                }

                $discount_1 = OrderProduct::select('id', 'ecommerce_order_id', 'is_retail', 'discount', 'qty_shipped')->where('ecommerce_order_id', $id)->where('is_retail', 'qty')->where('discount', '>', 0)->where('qty_shipped', '>', 0)->orderBy('id', 'ASC')->count();
                $discount_2 = OrderProduct::select('id', 'ecommerce_order_id', 'is_retail', 'discount', 'pcs_shipped')->where('ecommerce_order_id', $id)->where('is_retail', 'pieces')->where('discount', '>', 0)->where('pcs_shipped', '>', 0)->orderBy('id', 'ASC')->count();
            }

            $notes_count = 0;
            foreach ($order->order_products as $prod) {
                $notes_count += $prod->get_order_product_notes->count();
            }
            $pages = ceil(($total_products_count_qty + $total_products_count_pieces + $notes_count + $discount_1 + $discount_2) / 13);
            $getPrintBlade = Status::select('print_1')->where('id', 3)->first();

            $pdf = PDF::loadView('sales.invoice.' . $getPrintBlade->print_1 . '', compact('order', 'query', 'query2', 'company_info', 'proforma', 'customerAddress', 'arr', 'customerShippingAddress', 'pages', 'do_pages_count', 'final_pages', 'all_orders_count', 'bank', 'inv_note'))->setPaper($customPaper);

            // making pdf name starts
            if ($order->primary_status == 3) {
                if (@$order->in_status_prefix !== null) {
                    $ref_no = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
                } else {
                    $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
                }
            } else {
                if (@$order->status_prefix !== null) {
                    $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
                } else {
                    $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
                }
            }

            $dynamic_path = '';
            // $makePdfName = $ref_no;
            $makePdfName = $ref_no . '-' . time();

            $path = public_path('uploads/orders_pdfs');
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            // $real_path = $path.'/'.$makePdfName.'.pdf';
            // if(File::exists($real_path))
            // {
            //   File::delete($real_path);
            // }

            $headers = array(
                'Content-Type: application/xlsx',
                'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0'
            );
            ob_end_clean();

            $pdf->save($path . '/' . $makePdfName . '.pdf');
            $url = config('app.url');
            $dynamic_path = $url . "/public/uploads/orders_pdfs/" . $makePdfName . '.pdf';
            return $dynamic_path;
        }
    }

    public function exportToPDFExcVat(Request $request, $id, $page_type, $column_name, $default_sort, $is_proforma = null, $bank_id = null)
    {
        return QuotationHelper::exportToPDFExcVat($request, $id, $page_type, $column_name, $default_sort, $is_proforma, $bank_id);
    }
    public function exportToPoPDFExcVat(Request $request, $id, $page_type, $column_name, $default_sort, $is_proforma = null, $bank_id = null)
    {
        return QuotationHelper::exportToPoPDFExcVat($request, $id, $page_type, $column_name, $default_sort, $is_proforma, $bank_id);
    }

    public function exportCreditNote(Request $request, $id, $column_name, $sortorder, $is_proforma = null)
    {
        $with_vat = @$request->with_vat;
        $proforma = @$is_proforma;
        $order = Order::find($id);

        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->billing_address_id)->first();
        $customerShippingAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->shipping_address_id)->first();
        $query = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id);

        $query = OrderProduct::doSortPrint($column_name, $sortorder, $query);

        $query2 = null;
        $customPaper = array(0, 0, 576, 792);

        $all_orders_count = OrderProduct::where('order_id', $id)->where(function ($q) {
            $q->where('quantity', '>', 0)->orWhereHas('get_order_product_notes');
        })->orderBy('id', 'ASC')->count();
        // getting count on all order products
        if ($all_orders_count <= 9) {
            $do_pages_count = ceil($all_orders_count / 9);
            $final_pages = $all_orders_count % 9;
            if ($final_pages == 0) {
                // $do_pages_count++;
            }
        } else {
            $do_pages_count = ceil($all_orders_count / 10);
            $final_pages = $all_orders_count % 10;
            if ($final_pages == 0) {
                $do_pages_count++;
            }
        }

        // dd($query2);
        $getPrintBlade = Status::select('print_1')->where('id', 25)->first();

        // dd($order);
        $config = Configuration::first();
        if ($config && $config->server == 'lucilla') {
            $getPrintBlade = 'lucila-credit-note-print';
        }
        else{
            $getPrintBlade = $getPrintBlade->print_1;
        }

        $pdf = PDF::loadView('accounting.invoices.' . $getPrintBlade . '', compact('order', 'query', 'query2', 'proforma', 'customerAddress', 'arr', 'customerShippingAddress', 'do_pages_count', 'all_orders_count'))->setPaper($customPaper);

        // making pdf name starts
        if ($order->primary_status == 3) {
            if (@$order->in_status_prefix !== null) {
                $ref_no = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
        } else {
            if (@$order->status_prefix !== null) {
                $ref_no = @$order->status_prefix . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
        }

        $makePdfName = $ref_no;
        // $makePdfName='PO '.($mainObj->user_ref_id != null ? $mainObj->user_ref_id  : $mainObj->ref_id);
        // making pdf name ends
        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0
            )
        );
        // return $pdf->download($makePdfName.'.pdf');
    }

    public function exportProformaToPDF(Request $request, $id, $page_type, $column_name, $default_sort, $bank_id = null)
    {
        $orders_array = explode(",", $id);
        $id = $orders_array[0];
        $print_history             = new PrintHistory;
        $print_history->order_id    = $id;
        $print_history->user_id    = Auth::user()->id;
        $print_history->print_type = 'performa-to-pdf';
        $print_history->page_type = $page_type;
        $print_history->save();

        $order = Order::find($id);
        $bank = Bank::find($bank_id);
        $address = CustomerBillingDetail::select('billing_phone')->where('customer_id', $order->customer_id)->where('is_default', 1)->first();
        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->billing_address_id)->first();
        $customerShippingAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->shipping_address_id)->first();
        $query = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id)->whereNotNull('order_products.vat')->where('order_products.vat', '!=', 0);

        if ($column_name == 'reference_code' && $default_sort !== 'id_sort') {
            $query = $query->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
        } elseif ($column_name == 'short_desc' && $default_sort != 'id_sort') {
            $query = $query->orderBy('short_desc', $default_sort)->get();
        } elseif ($column_name == 'supply_from' && $default_sort !== 'id_sort') {
            $query = $query->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
        } elseif ($column_name == 'type_id' && $default_sort !== 'id_sort') {
            $query = $query->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $default_sort)->get();
        } elseif ($column_name == 'brand' && $default_sort !== 'id_sort') {
            $query = $query->orderBy($column_name, $default_sort)->get();
        } else {
            $query = $query->orderBy('id', 'ASC')->get();
        }

        $vat_count = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id)->where('vat', '!=', 0)->where(function ($z) {
            $z->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
        })->orderBy('id', 'ASC')->count();

        $vat_count_notes = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->select('id')->WhereHas('get_order_product_notes', function ($q) {
            $q->where('show_on_invoice', 1);
        })->where(function ($q) {
            $q->where('vat', '!=', 0);
        })->where('order_id', $id)->orderBy('id', 'ASC')->count();
        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $query_count = $query->count() / 6;

        $query2 = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where(function ($q) {
            $q->where('order_products.vat', 0)->orWhereNull('order_products.vat');
        })->where('order_id', $id);

        if ($column_name == 'reference_code' && $default_sort !== 'id_sort') {
            $query2 = $query2->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
        } elseif ($column_name == 'short_desc' && $default_sort != 'id_sort') {
            $query2 = $query2->orderBy('short_desc', $default_sort)->get();
        } elseif ($column_name == 'supply_from' && $default_sort !== 'id_sort') {
            $query2 = $query2->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
        } elseif ($column_name == 'type_id' && $default_sort !== 'id_sort') {
            $query2 = $query2->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $default_sort)->get();
        } elseif ($column_name == 'brand' && $default_sort !== 'id_sort') {
            $query2 = $query2->orderBy($column_name, $default_sort)->get();
        } else {
            $query2 = $query2->orderBy('id', 'ASC')->get();
        }


        $non_vat_count = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where(function ($q) {
            $q->where('vat', 0)->orWhereNull('vat');
        })->where('order_id', $id)->where(function ($p) {
            $p->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
        })->orderBy('id', 'ASC')->count();
        #To find notes
        $query2_notes = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->select('id')->WhereHas('get_order_product_notes', function ($q) {
            $q->where('show_on_invoice', 1);
        })->where(function ($q) {
            $q->where('vat', 0)->orWhereNull('vat');
        })->where('order_id', $id)->orderBy('id', 'ASC')->count();

        #To find discounted items
        $query2_discounts = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->select('id')->where(function ($q) {
            $q->where('vat', 0)->orWhereNull('vat');
        })->where('order_id', $id)->where('qty_shipped', '>', 0)->where('discount', '>', '0')->orderBy('id', 'ASC')->count();

        $inv_note = OrderNote::where('order_id', $id)->where('type', 'customer')->first();
        if (($non_vat_count + @$query2_notes + $query2_discounts) > 16) {
            $query_count2 = ceil((@$non_vat_count + @$query2_notes + $query2_discounts) / 16);
        } else {
            $query_count2 = 1;
        }

        // dd($non_vat_count + @$query2_notes + $query2_discounts);

        if (($vat_count + $vat_count_notes) > 10) {
            $query_count = ceil(($vat_count + $vat_count_notes) / 10);
        } else {
            $query_count = 1;
        }
        $all_orders_count = OrderProduct::where('order_id', $id)->where(function ($q) {
            $q->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
        })->orderBy('id', 'ASC')->count();

        $getPrintBlade = Status::select('print_2')->where('id', 3)->first();
        // dd($getPrintBlade);
        if(@$getPrintBlade->print_2 == 'proforma-print-lucilla')
            $customPaper = array();

        $number_divide = @$getPrintBlade->print_2 == 'proforma-print-lucilla' ? 12 : 8;
        if ($all_orders_count <= $number_divide) {
            $do_pages_count = ceil($all_orders_count / $number_divide);
            $final_pages = $all_orders_count % $number_divide;
            if ($final_pages == 0) {
                // $do_pages_count++;
            }
        } else {
            $do_pages_count = ceil($all_orders_count / $number_divide);
            $final_pages = $all_orders_count % $number_divide;
            if ($final_pages == 0) {
                $do_pages_count++;
            }
        }
        $customPaper = array(0, 0, 576, 792);

        if (@$order->id == 4494) {
            $pdf = PDF::loadView('sales.invoice.proforma-print2', compact('order', 'query', 'query2', 'address', 'customerAddress', 'arr', 'customerShippingAddress', 'query_count2', 'query_count', 'non_vat_order_total', 'inv_note', 'default_sort', 'do_pages_count'))->setPaper($customPaper);
        } else {
            if(@$getPrintBlade->print_2 == 'proforma-print-lucilla' && @$order->in_ref_id <= 23011593){
                $pdf = PDF::loadView('sales.invoice.proforma-print-lucilla-old', compact('order', 'query', 'query2', 'address', 'customerAddress', 'arr', 'customerShippingAddress', 'query_count2', 'query_count', 'non_vat_order_total', 'do_pages_count', 'all_orders_count', 'bank', 'inv_note', 'default_sort', 'orders_array'))->setPaper($customPaper);
            }else{

            $pdf = PDF::loadView('sales.invoice.' . $getPrintBlade->print_2 . '', compact('order', 'query', 'query2', 'address', 'customerAddress', 'arr', 'customerShippingAddress', 'query_count2', 'query_count', 'non_vat_order_total', 'do_pages_count', 'all_orders_count', 'bank', 'inv_note', 'default_sort', 'orders_array'))->setPaper($customPaper);
            }
        }
        // making pdf name starts
        if ($order->primary_status == 3) {
            if (@$order->in_status_prefix !== null) {
                $ref_no = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
        } else {
            if (@$order->status_prefix !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
        }

        $makePdfName = $ref_no;
        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0
            )
        );
    }
    public function exportProformaToPDFCopy(Request $request, $id, $page_type, $default_sort, $bank_id = null)
    {
        $orders_array = explode(",", $id);
        $id = $orders_array[0];
        $print_history             = new PrintHistory;
        $print_history->order_id    = $id;
        $print_history->user_id    = Auth::user()->id;
        $print_history->print_type = 'performa-to-pdf';
        $print_history->page_type = $page_type;
        $print_history->save();
        $copy = true;

        $order = Order::find($id);
        $bank = Bank::find($bank_id);
        $address = CustomerBillingDetail::select('billing_phone')->where('customer_id', $order->customer_id)->where('is_default', 1)->first();
        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->billing_address_id)->first();
        $customerShippingAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->shipping_address_id)->first();
        $query = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id)->whereNotNull('order_products.vat')->where('order_products.vat', '!=', 0);

        if ($default_sort != 'id_sort') {
            $query = $query->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
        } else {
            $query = $query->orderBy('id', 'ASC')->get();
        }

        $vat_count = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id)->where('vat', '!=', 0)->where(function ($z) {
            $z->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
        })->orderBy('id', 'ASC')->count();

        $vat_count_notes = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->select('id')->WhereHas('get_order_product_notes', function ($q) {
            $q->where('show_on_invoice', 1);
        })->where(function ($q) {
            $q->where('vat', '!=', 0);
        })->where('order_id', $id)->orderBy('id', 'ASC')->count();
        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $query_count = $query->count() / 6;

        $query2 = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where(function ($q) {
            $q->where('order_products.vat', 0)->orWhereNull('order_products.vat');
        })->where('order_id', $id);

        if ($default_sort != 'id_sort') {
            $query2 = $query2->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->select('order_products.*')->get();
        } else {
            $query2 = $query2->orderBy('id', 'ASC')->get();
        }


        $non_vat_count = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where(function ($q) {
            $q->where('vat', 0)->orWhereNull('vat');
        })->where('order_id', $id)->where(function ($p) {
            $p->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
        })->orderBy('id', 'ASC')->count();
        #To find notes
        $query2_notes = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->select('id')->WhereHas('get_order_product_notes', function ($q) {
            $q->where('show_on_invoice', 1);
        })->where(function ($q) {
            $q->where('vat', 0)->orWhereNull('vat');
        })->where('order_id', $id)->orderBy('id', 'ASC')->count();

        #To find discounted items
        $query2_discounts = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->select('id')->where(function ($q) {
            $q->where('vat', 0)->orWhereNull('vat');
        })->where('order_id', $id)->where('qty_shipped', '>', 0)->where('discount', '>', '0')->orderBy('id', 'ASC')->count();

        $inv_note = OrderNote::where('order_id', $id)->where('type', 'customer')->first();
        if (($non_vat_count + @$query2_notes + $query2_discounts) > 16) {
            $query_count2 = ceil((@$non_vat_count + @$query2_notes + $query2_discounts) / 16);
        } else {
            $query_count2 = 1;
        }
        if (($vat_count + $vat_count_notes) > 10) {
            $query_count = ceil(($vat_count + $vat_count_notes) / 10);
        } else {
            $query_count = 1;
        }
        $all_orders_count = OrderProduct::where('order_id', $id)->where(function ($q) {
            $q->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
        })->orderBy('id', 'ASC')->count();

        if ($all_orders_count <= 8) {
            $do_pages_count = ceil($all_orders_count / 8);
            $final_pages = $all_orders_count % 8;
            if ($final_pages == 0) {
                // $do_pages_count++;
            }
        } else {
            $do_pages_count = ceil($all_orders_count / 8);
            $final_pages = $all_orders_count % 8;
            if ($final_pages == 0) {
                $do_pages_count++;
            }
        }
        $customPaper = array(0, 0, 576, 792);
        if (@$order->id == 4494) {
            $pdf = PDF::loadView('sales.invoice.proforma-print2', compact('order', 'query', 'query2', 'address', 'customerAddress', 'arr', 'customerShippingAddress', 'query_count2', 'query_count', 'non_vat_order_total', 'inv_note', 'default_sort', 'copy'))->setPaper($customPaper);
        } else {
            $getPrintBlade = Status::select('print_2')->where('id', 3)->first();

            $pdf = PDF::loadView('sales.invoice.' . $getPrintBlade->print_2 . '', compact('order', 'query', 'query2', 'address', 'customerAddress', 'arr', 'customerShippingAddress', 'query_count2', 'query_count', 'non_vat_order_total', 'do_pages_count', 'all_orders_count', 'bank', 'inv_note', 'default_sort', 'orders_array', 'copy'))->setPaper($customPaper);
        }
        if ($order->primary_status == 3) {
            if (@$order->in_status_prefix !== null) {
                $ref_no = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
        } else {
            if (@$order->status_prefix !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . $order->ref_id;
            }
        }

        $makePdfName = $ref_no;
        return $pdf->stream(
            $makePdfName . '.pdf',
            array(
                'Attachment' => 0
            )
        );
    }

    public function getProductsData(Request $request, $id)
    {
        $purchasing_role_menu = RoleMenu::where('role_id', 2)->where('menu_id', 40)->first();
        $query = OrderProduct::with('product.productSubCategory.get_Parent', 'get_order', 'product.units', 'product.supplier_products', 'product.sellingUnits', 'product.ecomSellingUnits', 'unit', 'get_order_product_notes', 'single_note', 'product.supplier_products.supplier', 'purchase_order_detail.PurchaseOrder', 'product.customer_type_product_margins', 'from_supplier', 'product.get_order_product', 'po_group_product_detail:id,product_id', 'product.warehouse_products', 'product.product_fixed_price', 'productType')->where('order_id', $id)->orderBy('id', 'asc');
        $product_type = ProductType::select('id', 'title')->get();
        $units = Unit::orderBy('title')->get();

        $dt = Datatables::of($query);
        $add_columns = ['size', 'restaurant_price', 'available_qty', 'last_price', 'notes', 'supply_from', 'vat', 'total_price', 'unit_price_discount', 'unit_price_with_vat', 'last_updated_price_on', 'unit_price', 'margin', 'exp_unit_cost', 'po_number', 'po_quantity', 'total_amount', 'buying_unit', 'sell_unit', 'quantity_ship', 'pcs_shipped', 'number_of_pieces', 'quantity', 'type_id', 'temprature', 'selling_unit', 'brand', 'category_id', 'description', 'discount', 'hs_code', 'refrence_code', 'action'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $product_type, $units, $purchasing_role_menu) {
                return OrderProduct::returnAddColumn($column, $item, $product_type, $units, $purchasing_role_menu);
            });
        }

        $dt->setRowId(function ($item) {
            return $item->id;
        });

        $dt->setRowClass(function ($item) {
            if ($item->product == null) {
                return  'yellowRow';
            } elseif ($item->is_billed == "Incomplete") {
                return  'yellowRow';
            }
        });

        $dt->rawColumns(['action', 'refrence_code', 'number_of_pieces', 'quantity', 'unit_price', 'total_price', 'exp_unit_cost', 'supply_from', 'notes', 'description', 'vat', 'brand', 'sell_unit', 'discount', 'quantity_ship', 'total_amount', 'unit_price_with_vat', 'pcs_shipped', 'type_id', 'temprature', 'hs_code', 'category_id', 'last_price', 'restaurant_price', 'size', 'unit_price_discount']);
        return $dt->make(true);
    }

    public function exportCompleteQuotation(Request $request)
    {
        return QuotationHelper::exportCompleteQuotation($request);
    }

    public function orderProductsEdits(Request $request)
    {
        OrderProduct::where('id', $request->order_product_id)->update(['category_id' => $request->category_id]);
        return response()->json(['success' => true,]);
    }

    public function removeOrderProduct(Request $request)
    {
        // dd($request->all());
        $order_products = OrderProduct::find($request->id);

        $prod_code = '';
        if ($order_products->product_id !== Null && $order_products->product != null) {
            $prod_code = $order_products->product->refrence_code;
        }
        $order_id = $order_products->get_order->id;
        // dd($order_products);
        if ($order_products->status > 7 && $order_products->status !== 26 && $order_products->status !== 29) {
            return response()->json(['picked' => true]);
        }
        $invoice     = Order::find($order_products->order_id);
        $old_status = $invoice->statuses->title;
        if (($invoice->primary_status == 1 && $invoice->status == 6) || ($invoice->primary_status == 2 && $invoice->status == 7)) {
            $invoice->total_amount -= ($order_products->total_price_with_vat);
            $invoice->save();
            // dd($invoice);
        } elseif ($order_products->is_billed == 'Billed') {
            $invoice->total_amount -= ($order_products->total_price_with_vat);
            $invoice->save();
        }

        if ($order_products->is_billed == "Product" && $order_products->product != null && $order_products->status == 7) {

            DB::beginTransaction();
            try {
                $new_his = new QuantityReservedHistory;
                $re      = $new_his->updateReservedQuantity($order_products, 'Reserved Deleted by Deleting Order Product', 'subtract');
                DB::commit();
            } catch (\Excepion $e) {
                DB::rollBack();
            }
        }
        // dd('ddthere');
        $order_products->delete();

        $order_history = new OrderHistory();
        $order_history->user_id = Auth::user()->id;
        $order_history->column_name = 'Action';
        $order_history->reference_number = @$prod_code;
        $order_history->old_value = @$prod_code;
        $order_history->new_value = 'DELETED';
        $order_history->order_id  = $order_id;
        $order_history->save();

        $order_status = $invoice->order_products->where('is_billed', '=', 'Product')->min('status');
        if ($order_status !== null) {
            $invoice->status = $order_status;
            $invoice->save();
            $new_status = Status::find($invoice->status);
            if ($old_status !== $new_status->title) {
                $status_history             = new OrderStatusHistory;
                $status_history->user_id    = Auth::user()->id;
                $status_history->order_id   = $invoice->id;
                $status_history->status     = 'DI(' . $old_status . ')';
                $status_history->new_status = 'DI(' . $new_status->title . ')';
                $status_history->save();
            }
        }

        $sub_total     = 0;
        $total_vat     = 0;
        $grand_total   = 0;
        $sub_total_w_w   = 0;
        $sub_total_without_discount = 0;
        $item_level_dicount = 0;
        $query         = OrderProduct::where('order_id', $order_products->order_id)->get();
        foreach ($query as  $value) {
            $sub_total += $value->total_price;
            $sub_total_w_w += number_format($value->total_price_with_vat, 4, '.', '');
            $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 2) : (@$value->total_price_with_vat - @$value->total_price);

            if ($value->discount != 0) {
                if ($value->discount == 100) {
                    if (@$invoice->primary_status == 3) {
                        if ($value->is_retail == 'pieces') {
                            $discount_full =  $value->unit_price_with_vat * $value->pcs_shipped;
                            $sub_total_without_discount += $discount_full;
                        } else {
                            $discount_full =  $value->unit_price_with_vat * $value->qty_shipped;
                            $sub_total_without_discount += $discount_full;
                        }
                    } else {
                        if ($value->is_retail == 'pieces') {
                            $discount_full =  $value->unit_price_with_vat * $value->number_of_pieces;
                            $sub_total_without_discount += $discount_full;
                        } else {
                            $discount_full =  $value->unit_price_with_vat * $value->quantity;
                            $sub_total_without_discount += $discount_full;
                        }
                    }

                    $item_level_dicount += $discount_full;
                } else {
                    $sub_total_without_discount += $value->total_price / ((100 - $value->discount) / 100);
                    $item_level_dicount += ($value->total_price / ((100 - $value->discount) / 100)) - $value->total_price;
                }
            } else {
                $sub_total_without_discount += $value->total_price;
            }
        }
        $grand_total = ($sub_total_w_w) - ($invoice->discount) + ($invoice->shipping);
        $order_products->get_order->update(['total_amount' => number_format($grand_total, 2, '.', '')]);


        return response()->json(['success' => true, 'sub_total' => number_format(@$sub_total, 2, '.', ','), 'total_vat' => number_format(@$total_vat, 2, '.', ','), 'grand_total' => number_format(@$grand_total, 2, '.', ','), 'item_level_dicount' => number_format(@$item_level_dicount, 2, '.', ','), 'sub_total_without_discount' => number_format(@$sub_total_without_discount, 2, '.', ','), 'successmsg' => 'Product successfully removed', 'total_products' => $invoice->order_products->count('id')]);
    }

    public function addToOrderByRefrenceNumber(Request $request)
    {
        // dd($request->all());
        $order = Order::find($request->id['id']);
        $refrence_number = $request->refrence_number;
        $product = Product::where('refrence_code', $refrence_number)->where('status', 1)->first();
        if ($product) {
            $vat_amount_import = NULL;
            $getSpData = SupplierProducts::where('product_id', $product->id)->where('supplier_id', $product->supplier_id)->first();
            if ($getSpData) {
                $vat_amount_import = $getSpData->vat_actual;
            }
            $order = Order::find($request->id['id']);
            $price_calculate_return = $product->price_calculate($product, $order);
            $unit_price = $price_calculate_return[0];
            $price_type = $price_calculate_return[1];
            $price_date = $price_calculate_return[2];
            $discount = $price_calculate_return[3];
            $price_after_discount = $price_calculate_return[4];
            $user_warehouse = @$order->customer->primary_sale_person->get_warehouse->id;
            $total_product_status = 0;
            $CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id', $product->id)->where('customer_type_id', $order->customer->category_id)->first();
            if ($CustomerTypeProductMargin != null) {
                $margin = $CustomerTypeProductMargin->default_value;
                $margin = (($margin / 100) * $product->selling_price);
                $product_ref_price  = $margin + ($product->selling_price);
                $exp_unit_cost = $product_ref_price;
            }

            //if this product is already in quotation then increment the quantity
            $order_products = OrderProduct::where('order_id', $order->id)->where('product_id', $product->id)->first();
            if ($order_products) {
                $total_price_with_vat = (($product->vat / 100) * $unit_price) + $unit_price;
                $supplier_id = $product->supplier_id;
                $salesWarehouse_id = Auth::user()->get_warehouse->id;

                $new_draft_quotation_products   = new OrderProduct;
                $new_draft_quotation_products->order_id                 = $order->id;
                $new_draft_quotation_products->product_id               = $product->id;
                $new_draft_quotation_products->category_id              = $product->category_id;
                $new_draft_quotation_products->hs_code                  = $product->hs_code;
                $new_draft_quotation_products->product_temprature_c     = $product->product_temprature_c;
                // $new_draft_quotation_products->supplier_id         = $supplier_id;
                $new_draft_quotation_products->short_desc               = $product->short_desc;
                $new_draft_quotation_products->type_id                  = $product->type_id;
                $new_draft_quotation_products->brand                    = $product->brand;
                $new_draft_quotation_products->exp_unit_cost            = $exp_unit_cost;
                $new_draft_quotation_products->margin                   = $price_type;
                $new_draft_quotation_products->last_updated_price_on    = $price_date;
                $new_draft_quotation_products->unit_price               = number_format($unit_price, 2, '.', '');
                $new_draft_quotation_products->discount               = number_format($discount, 2, '.', ''); //Discount comes from ProductCustomerFixedPrice Table
                // $new_draft_quotation_products->unit_price_with_discount = number_format($unit_price, 2, '.', '');
                $new_draft_quotation_products->unit_price_with_discount = $price_after_discount != null ? number_format($price_after_discount,2,'.','') : number_format($unit_price,2,'.',''); // comes from ProductCustomerFixedPrice Table
                $new_draft_quotation_products->import_vat_amount        = $vat_amount_import;
                if ($order->is_vat == 0) {
                    $new_draft_quotation_products->vat               = $product->vat;
                    if (@$product->vat !== null) {
                        $unit_p = number_format($unit_price, 2, '.', '');
                        $vat_amount = $unit_p * (@$product->vat / 100);
                        $final_price_with_vat = $unit_p + $vat_amount;

                        $new_draft_quotation_products->unit_price_with_vat = number_format($final_price_with_vat, 2, '.', '');
                    } else {
                        $new_draft_quotation_products->unit_price_with_vat = number_format($unit_price, 2, '.', '');
                    }
                } else {
                    $new_draft_quotation_products->vat                  = 0;
                    $new_draft_quotation_products->unit_price_with_vat  = number_format($unit_price, 2, '.', '');
                }

                $new_draft_quotation_products->actual_cost         = $product->selling_price;
                $new_draft_quotation_products->locked_actual_cost  = $product->selling_price;
                $new_draft_quotation_products->warehouse_id        = $salesWarehouse_id;
                if (@$product->min_stock > 0) {
                    $new_draft_quotation_products->from_warehouse_id = $order->from_warehouse_id;
                    $new_draft_quotation_products->is_warehouse = 1;
                }else{
                    $new_draft_quotation_products->supplier_id = @$getSpData->supplier_id;
                }
                $new_draft_quotation_products->user_warehouse_id = $order->from_warehouse_id;
                // dd($new_draft_quotation_products);
                if ($order->primary_status == 1) {
                    $new_draft_quotation_products->status              = 6;
                } elseif ($order->primary_status == 2) {
                    if ($new_draft_quotation_products->user_warehouse_id == $new_draft_quotation_products->from_warehouse_id) {
                        // dd('here');
                        $new_draft_quotation_products->status = 10;
                    } else {
                        $total_product_status = 1;
                        $new_draft_quotation_products->status = 7;
                    }
                } else if ($order->status == 11) {
                    $new_draft_quotation_products->status   = 11;
                } elseif ($order->primary_status == 25) {
                    $new_draft_quotation_products->status   = 26;
                } elseif ($order->primary_status == 28) {
                    $new_draft_quotation_products->status   = 29;
                }

                $new_draft_quotation_products->save();
            } else {
                $total_price_with_vat = (($product->vat / 100) * $unit_price) + $unit_price;
                $supplier_id = $product->supplier_id;
                $salesWarehouse_id = Auth::user()->get_warehouse->id;

                $new_draft_quotation_products   = new OrderProduct;
                $new_draft_quotation_products->order_id                   = $order->id;
                $new_draft_quotation_products->product_id                 = $product->id;
                $new_draft_quotation_products->category_id                = $product->category_id;
                $new_draft_quotation_products->hs_code                    = $product->hs_code;
                $new_draft_quotation_products->product_temprature_c       = $product->product_temprature_c;
                // $new_draft_quotation_products->supplier_id         = $supplier_id;
                $new_draft_quotation_products->short_desc                 = $product->short_desc;
                $new_draft_quotation_products->type_id                    = $product->type_id;
                $new_draft_quotation_products->brand                      = $product->brand;
                $new_draft_quotation_products->exp_unit_cost              = $exp_unit_cost;
                $new_draft_quotation_products->margin                     = $price_type;
                $new_draft_quotation_products->last_updated_price_on      = $price_date;
                $new_draft_quotation_products->unit_price                 = number_format($unit_price, 2, '.', '');
                $new_draft_quotation_products->unit_price_with_discount   = number_format($unit_price, 2, '.', '');
                $new_draft_quotation_products->import_vat_amount          = $vat_amount_import;
                if ($order->is_vat == 0) {
                    $new_draft_quotation_products->vat               = $product->vat;
                    if (@$product->vat !== null) {
                        $unit_p = number_format($unit_price, 2, '.', '');
                        $vat_amount = $unit_p * (@$product->vat / 100);
                        $final_price_with_vat = $unit_p + $vat_amount;

                        $new_draft_quotation_products->unit_price_with_vat = number_format($final_price_with_vat, 2, '.', '');
                    } else {
                        $new_draft_quotation_products->unit_price_with_vat = number_format($unit_price, 2, '.', '');
                    }
                } else {
                    $new_draft_quotation_products->vat                  = 0;
                    $new_draft_quotation_products->unit_price_with_vat  = number_format($unit_price, 2, '.', '');
                }

                $new_draft_quotation_products->actual_cost         = $product->selling_price;
                $new_draft_quotation_products->locked_actual_cost  = $product->selling_price;
                $new_draft_quotation_products->warehouse_id        = $salesWarehouse_id;
                if ($product->min_stock > 0) {
                    $new_draft_quotation_products->from_warehouse_id = $order->from_warehouse_id;
                    $new_draft_quotation_products->is_warehouse = 1;
                }else{
                    $new_draft_quotation_products->supplier_id = @$getSpData->supplier_id;
                }
                $new_draft_quotation_products->user_warehouse_id = $order->from_warehouse_id;
                if ($order->primary_status == 1) {
                    $new_draft_quotation_products->status  = 6;
                } elseif ($order->primary_status == 2) {
                    if ($user_warehouse == $new_draft_quotation_products->from_warehouse_id) {
                        $new_draft_quotation_products->status = 10;
                    } else {
                        $total_product_status = 1;
                        $new_draft_quotation_products->status = 7;
                    }
                } else if ($order->status == 11) {
                    $new_draft_quotation_products->status              = 11;
                } elseif ($order->primary_status == 25) {
                    $new_draft_quotation_products->status              = 26;
                } elseif ($order->primary_status == 28) {
                    $new_draft_quotation_products->status              = 29;
                }

                $new_draft_quotation_products->save();
            }

            if (@$total_product_status == 1) {
                $order->status = 7;
            } else {
                $order_status = $order->order_products->where('is_billed', '=', 'Product')->min('status');
                $order->status = $order_status;
            }
            $order->save();

            $sub_total     = 0;
            $total_vat     = 0;
            $grand_total   = 0;
            $query         = OrderProduct::where('order_id', $order->id)->get();
            foreach ($query as  $value) {
                if ($value->is_retail == 'qty') {
                    $sub_total += $value->total_price;
                } else if ($value->is_retail == 'pieces') {
                    $sub_total += $value->total_price;
                }
                $total_vat += @$value->vat_amount_total !== null ? round(@$value->vat_amount_total, 2) : (@$value->total_price_with_vat - @$value->total_price);
            }
            $grand_total = ($sub_total) - ($order->discount) + ($order->shipping) + ($total_vat);
            $new_order_p = OrderProduct::find($new_draft_quotation_products->id);
            $getColumns = (new OrderProduct)->getColumns($new_order_p);
            // dd($new_draft_quotation_products->is_retail);
            //Create History Of new Added Product

            $reference_number = @$new_order_p->product->refrence_code;
            $order_history = (new QuotationHelper)->MakeHistory(@$new_order_p->order_id, $reference_number, 'New Product', '--', 'Added');

            return response()->json(['success' => true, 'status' => @$order->statuses->title, 'sub_total' => number_format(@$sub_total, 2, '.', ','), 'total_vat' => number_format(@$total_vat, 2, '.', ','), 'grand_total' => number_format(@$grand_total, 2, '.', ','), 'successmsg' => 'Product successfully Added', 'total_products' => $order->order_products->count('id'), 'getColumns' => $getColumns]);
        } else {
            return response()->json(['success' => false, 'successmsg' => 'Product Not Found in Catalog']);
        }
    }


    public function draftInvoices()
    {
        return view('sales.invoice.draft-invoices');
    }

    public function getDraftInvoicesData()
    {
        $query = Order::with('customer')->where('user_id', $this->user->id)->where('primary_status', 2)->where('status', 7)->orderBy('id', 'DESC');

        return Datatables::of($query)

            ->addColumn('customer', function ($item) {
                if ($item->customer_id != null) {
                    if ($item->customer['company'] != null) {
                        $html_string = $item->customer['company'];
                    } else {
                        $html_string = $item->customer['first_name'] . ' ' . $item->customer['last_name'];
                    }
                } else {
                    $html_string = 'N.A';
                }


                return $html_string;
            })

            ->addColumn('customer_ref_no', function ($item) {
                return @$item->customer->reference_number;
            })

            ->addColumn('target_ship_date', function ($item) {
                return $item->target_ship_date;
            })

            ->addColumn('status', function ($item) {
                $html = '<span class="sentverification">' . $item->statuses->title . '</span>';
                return $html;
            })

            ->addColumn('number_of_products', function ($item) {

                $html_string = $item->order_products->count();
                return $html_string;
            })
            ->addColumn('ref_id', function ($item) {

                return ($item->user_ref_id !== null ? $item->user_ref_id : $item->ref_id);
            })
            ->addColumn('payment_term', function ($item) {

                return ($item->customer->getpayment_term !== null ? $item->customer->getpayment_term->title : '--');
            })
            ->addColumn('invoice_date', function ($item) {

                return ($item->updated_at);
            })
            ->addColumn('total_amount', function ($item) {
                return '$' . number_format($item->total_amount, 2, '.', ',');
                // return ($item->total_amount);

            })
            ->addColumn('action', function ($item) {
                $html_string = '<a href="' . route('get-completed-quotation-products', ['id' => $item->id]) . '" title="View Products" class="actionicon viewIcon"><i class="fa fa-eye"></i></a>';

                return $html_string;
            })
            ->rawColumns(['action', 'customer', 'number_of_products', 'status'])
            ->make(true);
    }

    public function deleteSingleDraftQuot(Request $request)
    {
        //    dd($request->all())
        $draft_quot = DraftQuotation::find($request->id);

        $draf_quot_prods = DraftQuotationProduct::where('draft_quotation_id', $draft_quot->id)->get();
        // dd($check_status->count());

        foreach ($draf_quot_prods as $draf_quot_prod) {
            // dd($draf_quot_prod);
            $draf_quot_prod->delete();
        }
        $draft_quot->delete();

        return response()->json(['success' => true]);
    }

    public function deleteSingleOrderQuot(Request $request)
    {
        $order = Order::find($request->id);

        $order_prod = OrderProduct::where('order_id', $order->id)->get();
        $order_attachments = OrderAttachment::where('order_id', $order->id)->get();
        $order_notes = OrderNote::where('order_id', $order->id)->get();
        $order_notes->each->delete();
        $order_prod->each->delete();
        $order_attachments->each->delete();
        $order->delete();

        return response()->json(['success' => true]);
    }

    public function deleteDraftQuots(Request $request)
    {
        //    dd($request->all())

        foreach ($request->quotations as $quot) {
            $draft_quot = DraftQuotation::find($quot);

            $draf_quot_prods = DraftQuotationProduct::where('draft_quotation_id', $draft_quot->id)->get();
            // dd($check_status->count());

            foreach ($draf_quot_prods as $draf_quot_prod) {
                // dd($draf_quot_prod);
                $draf_quot_prod->delete();
            }
            $draft_quot->delete();
        }
        return response()->json(['success' => true]);
    }

    public function deleteOrderQuots(Request $request)
    {
        foreach ($request->quotations as $quot) {
            $order = Order::find($quot);

            $order_prod = OrderProduct::where('order_id', $order->id)->get();
            $order_attachments = OrderAttachment::where('order_id', $order->id)->get();
            $order_notes = OrderNote::where('order_id', $order->id)->get();

            $order_notes->each->delete();
            $order_attachments->each->delete();

            foreach ($order_prod as $draf_quot_prod) {
                $draf_quot_prod->delete();
            }

            $order->delete();
        }
        return response()->json(['success' => true]);
    }

    public function getOrderHistory(Request $request)
    {
        $order = Order::find($request->order_id);
        $query = OrderHistory::with('user', 'product', 'oldCustomer', 'units', 'from_warehouse', 'supplier', 'productType', 'newCustomer')->where('order_id', $request->order_id)->orderBy('id', 'DESC');

        return Datatables::of($query)
            ->addColumn('user_name', function ($item) {
                return @$item->user != null ? $item->user->name : '--';
            })

            ->addColumn('item', function ($item) {
                $product = @$item->reference_number != null ? $item->reference_number : '--';
                if ($item->product !== null) {
                    return  $html_string = '<a target="_blank" href="' . url('get-product-detail/' . $item->product->id) . '"  ><b>' . $product . '<b></a>';
                } else {
                    return $product;
                }
            })

            ->addColumn('column_name', function ($item) use ($order) {
                if ($order->primary_status == 3) {
                    if ($item->column_name == 'Pieces') {
                        return 'Pieces <br>Ordered';
                    } else {
                        return @$item->column_name != null ? $item->column_name : '--';
                    }
                } else {
                    return @$item->column_name != null ? $item->column_name : '--';
                }
            })

            ->addColumn('old_value', function ($item) {
                if ($item->column_name == "BILL TO") {
                    return @$item->old_value != null ? $item->oldCustomer->reference_name : '--';
                } else {
                    return @$item->old_value != null ? $item->old_value : '--';
                }
            })

            ->addColumn('new_value', function ($item) {
                if (@$item->column_name == 'selling_unit') {
                    return @$item->new_value != null ? $item->units->title : '--';
                } else if (@$item->column_name == 'Supply From' && $item->old_value != 'Warehouse') {
                    return @$item->new_value != null ? ($item->from_warehouse != Null ? $item->from_warehouse->warehouse_title : ($item->supplier !== null ? $item->supplier->reference_name : '--')) : '--';
                }else if (@$item->column_name == 'Supply From' && $item->old_value == 'Warehouse') {
                    return @$item->new_value != null ? ($item->supplier !== null ? $item->supplier->reference_name : '--') : '--';
                } else if ($item->column_name == "Type") {
                    return @$item->new_value != null ? $item->productType->title : '--';
                } else if ($item->column_name == "BILL TO") {
                    return @$item->new_value != null ? $item->newCustomer->reference_name : '--';
                } else {
                    return @$item->new_value != null ? $item->new_value : '--';
                }
            })

            ->addColumn('created_at', function ($item) {
                return @$item->created_at != null ? $item->created_at->format('d/m/Y H:i:s') : '--';
            })
            ->rawColumns(['user_name', 'column_name', 'item', 'old_value', 'new_value', 'created_at'])
            ->make(true);
    }

    public function getCancelledOrders()
    {
        return view('sales.product.cancelled-orders');
    }

    public function getCancelledOrdersData(Request $request)
    {
        // dd($request->all());
        if (Auth::user()->role_id == 4) {
            //this code was commented as Noah asked us that sales coordinator should see all the customers
            $query = Order::with('customer')->orderBy('id', 'DESC');
        } else if (Auth::user()->role_id == 2) {
            $query = Order::with('customer')->orderBy('id', 'DESC');
        } else if (Auth::user()->role_id == 1 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11) {
            $query = Order::with('customer')->orderBy('id', 'DESC');
        } else if (Auth::user()->role_id == 3) {
            $all_customer_ids = array_merge(Auth::user()->customer->pluck('id')->toArray(), Auth::user()->secondary_customer->pluck('id')->toArray());

            $query = Order::with('customer')->whereIn('customer_id', $all_customer_ids)->orderBy('id', 'DESC');
        } else if (Auth::user()->role_id == 9) {
            $query = Order::where('primary_status', 17)->where('status', 18)->where('ecommerce_order', 1)->orderBy('orders.id', 'DESC');
        } else {
            $query = Order::with('customer')->where('user_id', $this->user->id)->orderBy('id', 'DESC');
        }
        if ($request->status_filter == "draft") {
            $query = $query->where('in_status_prefix', null);
        }
        if ($request->status_filter == "invoice") {
            $query = $query->where('in_status_prefix', '!=', null);
        }
        if ($request->from_date != null) {
            if ($request->date_type == '1') {
                $date = str_replace("/", "-", $request->from_date);
                $date =  date('Y-m-d', strtotime($date));
                $query = $query->where('target_ship_date', '>=', $date);
            } else {
                $date = str_replace("/", "-", $request->from_date);
                $date =  date('Y-m-d', strtotime($date));
                $query = $query->whereDate('cancelled_date', '>=', $date);
            }
        }
        if ($request->to_date != null) {
            if ($request->date_type == '1') {
                $date_to = str_replace("/", "-", $request->to_date);
                $date_to =  date('Y-m-d', strtotime($date_to));
                $query = $query->where('target_ship_date', '<=', $date_to);
            } else {
                $date_to = str_replace("/", "-", $request->to_date);
                $date_to =  date('Y-m-d', strtotime($date_to));
                $query = $query->whereDate('cancelled_date', '<=', $date_to);
            }
        }
        $query->where(function ($q) {
            $q->where('primary_status', 17)->orderBy('orders.id', 'DESC');
        });

        // dd($query->get());
        return Datatables::of($query)

            ->addColumn('checkbox', function ($item) {

                $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check1" value="' . $item->id . '" id="quot_' . $item->id . '">
                                    <label class="custom-control-label" for="quot_' . $item->id . '"></label>
                                </div>';
                return $html_string;
            })

            ->addColumn('customer', function ($item) {
                if ($item->customer_id != null) {
                    if (Auth::user()->role_id == 3) {
                        if ($item->customer['reference_name'] != null) {
                            // $html_string = $item->customer['reference_name'];
                            $html_string = '
                  <a href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . $item->customer['reference_name'] . '</b></a>';
                        } else {
                            $html_string = '
                  <a href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . $item->customer['first_name'] . ' ' . $item->customer['last_name'] . '</b></a>';
                            // $html_string = $item->customer['first_name'].' '.$item->customer['last_name'];
                        }
                    } else {
                        if ($item->customer['reference_name'] != null) {
                            // $html_string = $item->customer['reference_name'];
                            $html_string = '
                  <a href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . $item->customer['reference_name'] . '</b></a>';
                        } else {
                            $html_string = '
                  <a href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . $item->customer['first_name'] . ' ' . $item->customer['last_name'] . '</b></a>';
                            // $html_string = $item->customer['first_name'].' '.$item->customer['last_name'];
                        }
                    }
                } else {
                    $html_string = 'N.A';
                }


                return $html_string;
            })

            ->addColumn('customer_ref_no', function ($item) {
                if (Auth::user()->role_id == 3) {
                    return '
              <a href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . @$item->customer->reference_number . '</b></a>';
                } else {
                    return '
              <a href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . @$item->customer->reference_number . '</b></a>';
                }
                // return $item->customer->reference_number;
            })

            ->addColumn('target_ship_date', function ($item) {
                return Carbon::parse(@$item->target_ship_date)->format('d/m/Y');
            })

            ->addColumn('cancelled_date', function ($item) {
                return $item->cancelled_date != null ? Carbon::parse(@$item->cancelled_date)->format('d/m/Y') : '--';
            })

            ->addColumn('memo', function ($item) {
                return @$item->memo != null ? @$item->memo : '--';
            })

            ->addColumn('status', function ($item) {
                $html = '<span class="sentverification">' . @$item->statuses->title . '</span>';
                return $html;
            })

            ->addColumn('number_of_products', function ($item) {
                $html_string = $item->order_products->count();
                return $html_string;
            })

            ->addColumn('sales_person', function ($item) {
                // return ($item->user !== null ? $item->user->name : '--');
                return ($item->customer !== null ? @$item->customer->primary_sale_person->name : '--');
            })

            ->addColumn('ref_id', function ($item) {
                if ($item->status_prefix !== null) {
                    $ref_no = $item->status_prefix . '-' . $item->ref_prefix . $item->ref_id;
                    $html_string = '<a href="' . route('get-cancelled-order-detail', ['id' => $item->id]) . '"><b>' . $ref_no . '</b></a>';
                } else {
                    $ref_no = '--';
                    $html_string = '--';
                }
                return $html_string;
            })

            ->addColumn('in_ref_id', function ($item) {
                if ($item->in_status_prefix !== null) {
                    $ref_no = $item->in_status_prefix . '-' . $item->in_ref_prefix . $item->in_ref_id;
                    $html_string = '<a href="' . route('get-cancelled-order-detail', ['id' => $item->id]) . '"><b>' . $ref_no . '</b></a>';
                } else {
                    $ref_no = '--';
                    $html_string = '--';
                }
                return $html_string;
            })

            ->addColumn('payment_term', function ($item) {
                return (@$item->customer->getpayment_term !== null ? @$item->customer->getpayment_term->title : '');
            })

            ->addColumn('invoice_date', function ($item) {

                return Carbon::parse(@$item->updated_at)->format('d/m/Y');
            })

            ->addColumn('total_amount', function ($item) {

                return number_format($item->total_amount, 2, '.', ',');
            })

            ->addColumn('action', function ($item) {
                // $html_string = '<a href="'.route('get-completed-quotation-products', ['id' => $item->id]).'" title="View Products" class="actionicon viewIcon text-center"><i class="fa fa-eye"></i></a>';
                $html_string = '';
                if ($item->primary_status == 17) {
                    $html_string .= '<a href="' . route('get-cancelled-order-detail', ['id' => $item->id]) . '" title="View Products" class="actionicon viewIcon text-center"><i class="fa fa-eye"></i></a>';
                } elseif ($item->primary_status == 3) {
                    $html_string .= '<a href="' . route('get-completed-invoices-details', ['id' => $item->id]) . '" title="View Products" class="actionicon viewIcon text-center"><i class="fa fa-eye"></i></a>';
                } elseif ($item->primary_status == 1) {
                    $html_string = '<a href="' . route('get-completed-quotation-products', ['id' => $item->id]) . '" title="View Products" class="actionicon viewIcon text-center"><i class="fa fa-eye"></i></a>';
                }

                if ($item->primary_status == 1) {
                    $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon text-center deleteOrder" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                }

                // if($item->primary_status == 2 && $item->status == 7)
                // {
                //   $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon text-center deleteOrder" data-id="'.$item->id.'" title="Void"><i class="fa fa-times"></i></a>';
                // }
                return $html_string;
            })
            ->rawColumns(['action', 'ref_id', 'in_ref_id', 'sales_person', 'customer', 'number_of_products', 'status', 'customer_ref_no', 'cancelled_date', 'checkbox'])
            ->make(true);
    }

    public function exportCancelledOrders(Request $request)
    {
        // dd($request->all());
        $status = ExportStatus::where('type', 'cancel_order_export')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->user_id = Auth::user()->id;
            $new->type    = 'cancel_order_export';
            $new->status  = 1;
            $new->save();
            CancelledOrderJob::dispatch($request->filter_dropdown_exp, $request->to_date_exp, $request->from_date_exp, Auth::user()->id, Auth::user()->role_id, $request->date_radio_exp);
            return response()->json(['msg' => "file is exporting.", 'status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['msg' => "File is ready to download.", 'status' => 2]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'cancel_order_export')->update(['status' => 1, 'exception' => null, 'user_id' => Auth::user()->id]);

            CancelledOrderJob::dispatch($request->filter_dropdown_exp, $request->to_date_exp, $request->from_date_exp, Auth::user()->id, Auth::user()->role_id, $request->date_radio_exp);

            return response()->json(['msg' => "File is donwloaded.", 'status' => 1, 'exception' => null]);
        }
    }

    public function recursiveStatusCancelledOrder()
    {
        $status = ExportStatus::where('type', 'cancel_order_export')->first();
        return response()->json(['msg' => "File is now getting prepared", 'status' => $status->status, 'exception' => $status->exception, 'file_name' => $status->file_name]);
    }

    public function checkStatusFirstTimeForCancelledOrder()
    {
        //dd('here');
        $status = ExportStatus::where('type', 'cancel_order_export')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }

    public function getCancelledOrderDetail($id)
    {
        $states = State::select('id', 'name')->orderby('name', 'ASC')->where('country_id', 217)->get();

        $billing_address = null;
        $shipping_address = null;
        $order = Order::find($id);
        $company_info = Company::where('id', $order->user->company_id)->first();
        if ($order->billing_address_id != null) {
            $billing_address = CustomerBillingDetail::where('id', $order->billing_address_id)->first();
        }
        if ($order->shipping_address_id) {
            $shipping_address = CustomerBillingDetail::where('id', $order->shipping_address_id)->first();
        }
        $total_products = $order->order_products->count('id');
        $vat = 0;
        $sub_total = 0;
        $query = OrderProduct::where('order_id', $id)->get();
        foreach ($query as  $value) {
            // $sub_total += $value->quantity * $value->unit_price;
            $sub_total += $value->total_price;
            $vat += $value->total_price_with_vat - $value->total_price;
        }
        $grand_total = ($sub_total) - ($order->discount) + ($order->shipping) + ($vat);
        $status_history = OrderStatusHistory::with('get_user')->where('order_id', $id)->get();
        $checkDocs = OrderAttachment::where('order_id', $order->id)->get()->count();
        $inv_note = OrderNote::where('order_id', $order->id)->where('type', 'customer')->first();
        $warehouse_note = OrderNote::where('order_id', $order->id)->where('type', 'warehouse')->first();
        $company_banks = CompanyBank::with('getBanks')->where('company_id', Auth::user()->company_id)->where('customer_category_id', $order->customer->CustomerCategory->id)->get();
        $banks = Bank::all();

        return view('sales.invoice.cancelled-orders-detail', compact('order', 'company_info', 'total_products', 'sub_total', 'grand_total', 'status_history', 'vat', 'id', 'checkDocs', 'inv_note', 'billing_address', 'shipping_address', 'states', 'warehouse_note', 'banks', 'company_banks'));
    }

    public function updateOrderProducts(Request $request)
    {
        // dd($request->all());

        $choice = $request->value;

        $order_product = DraftQuotationProduct::find($request->prod_id);
        if ($choice == 'qty') {
            $order_product->is_retail = 'qty';
            $order_product->total_price = $order_product->unit_price * $order_product->quantity;
        } else if ($choice == 'pieces') {
            $order_product->is_retail = 'pieces';

            $order_product->total_price = $order_product->unit_price * $order_product->number_of_pieces;
        }
        $order_product->save();

        return response()->json(['success' => true]);
    }

    public function editCustomerForOrder(Request $request)
    {
        return QuotationHelper::editCustomerForOrder($request);
    }

    // Complete pick instruction from draft invoice
    public function confirmPickInstructionFromDraftInvoice(Request $request)
    {
        // dd($request->all());
        $order = Order::where('id', $request->order_id)->first();

        $order_products = OrderProduct::where('order_id', $order->id)->where('is_billed', '=', 'Product')->get();
        $order_products_billed = OrderProduct::where('order_id', $order->id)->where('is_billed', '=', 'Billed')->get();

        if ($order_products->count() > 0) {
            foreach ($order_products as $order_product) {
                $order_product->pcs_shipped = $order_product->number_of_pieces;
                $order_product->qty_shipped = $order_product->quantity;
                $order_product->save();
            }
        }

        foreach ($order_products as $order_product) {
            if ($order_product->is_retail == 'qty') {
                if ($order_product->qty_shipped == NULL) {
                    return response()->json(['qty_shipped' => 'is_null', 'product' => $order_product->product->refrence_code]);
                }
            } else if ($order_product->is_retail == 'pieces') {
                if ($order_product->pcs_shipped == NULL || $order_product->qty_shipped == NULL) {
                    return response()->json(['pcs_shipped' => 'is_null', 'product' => $order_product->product->refrence_code]);
                }
            }
        }

        $status_history             = new OrderStatusHistory;
        $status_history->user_id    = Auth::user()->id;
        $status_history->order_id   = $order->id;
        $status_history->status     = 'DI(Waiting To Pick)';
        $status_history->new_status = 'Invoice';
        $status_history->save();

        $order_total = 0;
        foreach ($order_products as $order_product) {
            $order_product->status = 11;
            $order_product->save();

            if ($order_product->qty_shipped != 0 && $order_product->qty_shipped != null) {
                if ($order_product->expiration_date != null) {
                    $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', Auth::user()->get_warehouse->id)->where('expiration_date', $order_product->expiration_date)->whereNotNull('expiration_date')->first();
                    if ($stock == null) {
                        $stock = new StockManagementIn;
                        $stock->title           = 'Adjustment';
                        $stock->product_id      = $order_product->product_id;
                        $stock->created_by      = Auth::user()->id;
                        $stock->warehouse_id    = Auth::user()->get_warehouse->id;
                        $stock->expiration_date = $order_product->expiration_date;
                        $stock->save();
                    }
                    if ($stock != null) {
                        $stock_out                   = new StockManagementOut;
                        $stock_out->smi_id           = $stock->id;
                        $stock_out->order_id         = $order_product->order_id;
                        $stock_out->order_product_id = $order_product->id;
                        $stock_out->product_id       = $order_product->product_id;
                        $stock_out->quantity_out     = $order_product->qty_shipped != null ? '-' . $order_product->qty_shipped : 0;
                        $stock_out->created_by       = Auth::user()->id;
                        $stock_out->warehouse_id     = Auth::user()->get_warehouse->id;
                        $stock_out->save();
                    }
                } else {
                    $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', Auth::user()->get_warehouse->id)->whereNotNull('expiration_date')->orderBy('expiration_date', "ASC")->get();
                    $shipped = $order_product->qty_shipped;
                    foreach ($stock as $st) {
                        $stock_out_in = StockManagementOut::where('smi_id', $st->id)->sum('quantity_in');
                        $stock_out_out = StockManagementOut::where('smi_id', $st->id)->sum('quantity_out');
                        $balance = ($stock_out_in) + ($stock_out_out);
                        $balance = round($balance, 3);
                        if ($balance > 0) {
                            $inStock = $balance - $shipped;
                            if ($inStock >= 0) {
                                $stock_out                   = new StockManagementOut;
                                $stock_out->smi_id           = $st->id;
                                $stock_out->order_id         = $order_product->order_id;
                                $stock_out->order_product_id = $order_product->id;
                                $stock_out->product_id       = $order_product->product_id;
                                $stock_out->quantity_out     =  $shipped != null ? '-' . $shipped : 0;

                                $stock_out->created_by       = Auth::user()->id;
                                $stock_out->warehouse_id     = Auth::user()->get_warehouse->id;
                                $stock_out->save();
                                $shipped = 0;
                                break;
                            } else {
                                $stock_out                   = new StockManagementOut;
                                $stock_out->smi_id           = $st->id;
                                $stock_out->order_id         = $order_product->order_id;
                                $stock_out->order_product_id = $order_product->id;
                                $stock_out->product_id       = $order_product->product_id;
                                $stock_out->quantity_out     = -$balance;

                                $stock_out->created_by       = Auth::user()->id;
                                $stock_out->warehouse_id     = Auth::user()->get_warehouse->id;
                                $stock_out->save();
                                $shipped = abs($inStock);
                            }
                        }
                    }
                    if ($shipped != 0) {
                        $stock = StockManagementIn::where('product_id', $order_product->product_id)->where('warehouse_id', Auth::user()->get_warehouse->id)->whereNull('expiration_date')->first();
                        if ($stock == null) {
                            $stock = new StockManagementIn;
                            $stock->title           = 'Adjustment';
                            $stock->product_id      = $order_product->product_id;
                            $stock->created_by      = Auth::user()->id;
                            $stock->warehouse_id    = Auth::user()->get_warehouse->id;
                            $stock->expiration_date = $order_product->expiration_date;
                            $stock->save();
                        }

                        $stock_out                   = new StockManagementOut;
                        $stock_out->smi_id           = $stock->id;
                        $stock_out->order_id         = $order_product->order_id;
                        $stock_out->order_product_id = $order_product->id;
                        $stock_out->product_id       = $order_product->product_id;
                        $stock_out->quantity_out     = $shipped != null ? '-' . $shipped : 0;
                        $stock_out->created_by       = Auth::user()->id;
                        $stock_out->warehouse_id     = Auth::user()->get_warehouse->id;
                        $stock_out->save();
                    }
                }

                // $warehouse_products = WarehouseProduct::where('warehouse_id',Auth::user()->get_warehouse->id)->where('product_id',$order_product->product->id)->first();
                // $my_helper =  new MyHelper;
                // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_products);

                $warehouse_products = WarehouseProduct::where('warehouse_id', Auth::user()->get_warehouse->id)->where('product_id', $order_product->product->id)->first();
                $warehouse_products->current_quantity  -= round($order_product->qty_shipped, 3);
                $warehouse_products->reserved_quantity -= round($order_product->qty_shipped, 3);
                $warehouse_products->save();
            }

            if ($order_product->is_retail == 'qty') {
                $total_price = $order_product->qty_shipped * $order_product->unit_price;
                $num = $order_product->qty_shipped;
            } else if ($order_product->is_retail == 'pieces') {
                $total_price = $order_product->pcs_shipped * $order_product->unit_price;
                $num = $order_product->pcs_shipped;
            }
            // $product = $order_product->product;
            $discount = $order_product->discount;



            if ($discount != null) {
                $dis = $discount / 100;
                $discount_value = $dis * $total_price;
                $result = $total_price - $discount_value;
            } else {
                $result = $total_price;
            }

            $order_product->total_price = $result;

            // $order_product->total_price_with_vat = (($order_product->vat/100)*$result)+$result;
            $unit_price = round($order_product->unit_price, 2);
            $vat = $order_product->vat;
            $vat_amount = @$unit_price * (@$vat / 100);
            if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                $unit_price_with_vat = $order_product->unit_price_with_vat * $num;
            } else {
                $unit_price_with_vat = round(@$unit_price + @$vat_amount, 2) * $num;
            }
            $order_product->total_price_with_vat = $unit_price_with_vat;
            $order_product->save();
            $order_total += @$order_product->total_price_with_vat;

            $order_history = new OrderHistory;
            $order_history->user_id = Auth::user()->id;
            $order_history->reference_number = $order_product->product->refrence_code;
            $order_history->column_name = "Qty Sent";
            $order_history->old_value = Null;
            $order_history->new_value = $order_product->qty_shipped;
            $order_history->order_id = $order_product->order_id;
            $order_history->save();

            if ($order_product->pcs_shipped != NULL) {
                $order_history = new OrderHistory;
                $order_history->user_id = Auth::user()->id;
                $order_history->reference_number = $order_product->product->refrence_code;
                $order_history->column_name = "Pieces Sent";
                $order_history->old_value = Null;
                $order_history->new_value = $order_product->pcs_shipped;
                $order_history->order_id = $order_product->order_id;
                $order_history->save();
            }
        }

        foreach ($order_products_billed as $order_product) {
            if ($order_product->is_retail == 'qty') {
                $total_price = $order_product->qty_shipped * $order_product->unit_price;
                $num = $order_product->qty_shipped;
            } else if ($order_product->is_retail == 'pieces') {
                $total_price = $order_product->pcs_shipped * $order_product->unit_price;
                $num = $order_product->pcs_shipped;
            }
            // $product = $order_product->product;
            $discount = $order_product->discount;



            if ($discount != null) {
                $dis = $discount / 100;
                $discount_value = $dis * $total_price;
                $result = $total_price - $discount_value;
            } else {
                $result = $total_price;
            }

            $order_product->total_price = $result;

            // $order_product->total_price_with_vat = (($order_product->vat/100)*$result)+$result;
            $unit_price = round($order_product->unit_price, 2);
            $vat = $order_product->vat;
            $vat_amount = @$unit_price * (@$vat / 100);
            if ($order_product->vat !== null && $order_product->unit_price_with_vat !== null) {
                $unit_price_with_vat = $order_product->unit_price_with_vat * $num;
            } else {
                $unit_price_with_vat = round(@$unit_price + @$vat_amount, 2) * $num;
            }
            $order_product->total_price_with_vat = $unit_price_with_vat;
            $order_product->save();
            $order_total += @$order_product->total_price_with_vat;
        }



        $order->primary_status          = 3;
        $order->status                  = 11;
        $order->total_amount            = @$order_total;
        $order->converted_to_invoice_on = Carbon::now();
        // $order->target_ship_date = Carbon::now()->format('Y-m-d');

        $inv_status              = Status::where('id', 3)->first();
        $counter_formula         = $inv_status->counter_formula;
        $counter_formula         = explode('-', $counter_formula);
        $counter_length          = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;

        $date = Carbon::now();
        $date = $date->format($counter_formula[0]);
        // $date = 2005;
        /* $year = Carbon::now()->year;
        $month = Carbon::now()->month;
        $year = substr($year, -2);
        $month = sprintf("%02d", $month);
        $date = $year.$month;*/

        // $w = @Auth::user()->get_warehouse->warehouse_title;
        $company_prefix          = @Auth::user()->getCompany->prefix;
        // $company_prefix          = @$order->customer->primary_sale_person->getCompany->prefix;
        $draft_customer_category = $order->customer->CustomerCategory;
        $ref_prefix              = $draft_customer_category->short_code;
        $status_prefix           = $inv_status->prefix . $company_prefix;

        $c_p_ref = Order::where('in_status_prefix', '=', $status_prefix)->where('in_ref_prefix', $ref_prefix)->where('in_ref_id', 'LIKE', "$date%")->orderby('converted_to_invoice_on', 'DESC')->first();
        // dd($c_p_ref);
        $str = @$c_p_ref->in_ref_id;
        $onlyIncrementGet = substr($str, 4);
        if ($str == NULL) {
            $onlyIncrementGet = 0;
        }
        $system_gen_no        = str_pad(@$onlyIncrementGet + 1, $counter_length, 0, STR_PAD_LEFT);
        $system_gen_no = $date . $system_gen_no;
        // dd($system_gen_no);
        $order->in_status_prefix = $status_prefix;
        $order->in_ref_prefix    = $ref_prefix;
        $order->in_ref_id        = $system_gen_no;

        $order->save();
        return response()->json(['success' => true]);
    }

    public function getProductsDataCancel($id)
    {
        $query = OrderProduct::with('product','get_order','product.units','product.supplier_products')->where('order_id', $id)->orderBy('id', 'ASC');

        $dt = Datatables::of($query);
        $add_columns = ['notes', 'supply_from', 'vat', 'total_price', 'unit_price_with_vat', 'unit_price', 'margin', 'exp_unit_cost', 'po_number', 'po_quantity', 'total_amount', 'buying_unit', 'sell_unit', 'quantity_ship', 'pcs_shipped', 'number_of_pieces', 'quantity', 'type_id', 'temprature', 'selling_unit', 'brand', 'category', 'description', 'discount', 'hs_code', 'refrence_code', 'action'];
        foreach ($add_columns as $column) {
            $dt->addColumn($column, function($item) use($column) {
                return CancelOrdersDatatable::returnAddColumn($column, $item);
            });
        }
        $dt->setRowId(function ($item) {
            return $item->id;
        });

        // yellowRow is a custom style in style.css file
        $dt->setRowClass(function ($item) {
        if($item->product == null)
        {
            return  'yellowRow';
        }
        elseif($item->is_billed == "Incomplete")
        {
            return  'yellowRow';
        }
        });
        $dt->rawColumns(['action','refrence_code','number_of_pieces','quantity','unit_price','total_price','exp_unit_cost','supply_from','notes','description','vat','brand','sell_unit','discount','quantity_ship','total_amount','unit_price_with_vat','pcs_shipped','type_id','temprature','hs_code','category']);
        return $dt->make(true);
    }

    public function fetchSuppliersForInquiry(Request $request)
    {
        $suppliers = Supplier::where('status', 1)->orderBy('reference_name', 'asc')->get();
        $html_string = '
        <div class="col-lg col-md-6" id="quotation-1">
          <label><b>Suppliers</b></label>
          <div class="form-group">
            <select class="form-control selecting-tables state-tags sort-by-value inquiry-product-supplier" name="supplier_id">
                <option value="" selected disabled>-- Suppliers --</option>';

        foreach ($suppliers as $sup) {
            $html_string .= '<option value="' . $sup->id . '">' . $sup->reference_name . '</option>';
        }

        $html_string .= '</select><input type="hidden" name="inquiry_id" class="inquiry_id" value="' . $request->id . '">
          </div>
        </div>
      ';
        return response()->json(['suppliers' => $html_string, 'success' => true]);
    }

    public function checkCustomerCreditLimit(Request $request)
    {
        return QuotationsCommonHelper::checkCustomerCreditLimit($request);
    }

    public function getQuotationHistory(Request $request)
    {
        $query = DraftQuatationProductHistory::with('user', 'type_old_value', 'type_new_value')->where('order_id', $request->order_id)->orderBy('id', 'DESC');

        return Datatables::of($query)
            ->addcolumn('user_name', function ($item) {
                return $item->user->name;
            })
            ->addcolumn('item', function ($item) {
                return $item->reference_number != null ? $item->reference_number : '--';
            })
            ->addColumn('created_at', function ($item) {
                return $item->created_at != null ? $item->created_at->format('d/m/Y H:i:s') : '--';
            })
            ->addColumn('old_value', function ($item) {
                if ($item->column_name == 'Type') {
                    return $item->old_value != null ? $item->type_old_value->title : '--';
                } elseif ($item->column_name == 'BILL TO') {
                    return $item->old_value != null ? $item->oldCustomer->reference_name : '--';
                } else {
                    return $item->old_value != null ? $item->old_value : '--';
                }
            })
            ->addColumn('new_value', function ($item) {
                if ($item->column_name == 'Type') {
                    return $item->new_value != null ? $item->type_new_value->title : '--';
                } elseif ($item->column_name == 'BILL TO') {
                    return $item->new_value != null ? $item->newCustomer->reference_name : '--';
                } else {
                    return $item->new_value != null ? $item->new_value : '--';
                }
            })
            ->rawColumns(['created_at', 'old_value', 'new_value'])
            ->make(true);
    }

    public function getProjectVersions()
    {
        $all_versions = Version::orderBy('id', 'asc')->get();

        return $all_versions;
    }

    public function bulkImortInOrders(Request $request)
    {
        return QuotationHelper::bulkImortInOrders($request);
    }

    public function getCompletedQuotationsDataFooter(Request $request)
    {
        if (Auth::user()->role_id == 4) {
            $warehouse_id = Auth::user()->warehouse_id;
            $ids = User::select('id')->where('warehouse_id', $warehouse_id)->where(function ($query) {
                $query->where('role_id', 4)->orWhere('role_id', 3);
            })->whereNull('parent_id')->pluck('id')->toArray();
            $all_customer_ids = array_merge(Customer::whereIn('primary_sale_id', $ids)->pluck('id')->toArray(), Customer::whereIn('secondary_sale_id', $ids)->pluck('id')->toArray());

            $query = Order::select(
                DB::raw('sum(CASE
          WHEN orders.primary_status=3 AND op.vat > 0 THEN op.total_price
          END) AS vat_total_amount,
          sum(CASE
          WHEN orders.primary_status=3 AND op.vat > 0 THEN op.vat_amount_total
          END) AS vat_amount_price,
          sum(CASE
          WHEN orders.primary_status=3 AND (op.vat IS NULL OR op.vat = 0) THEN op.total_price
          END) AS not_vat_total_amount,
          sum(CASE
          WHEN 1 THEN op.total_price
          END) AS sub_total_price,
          sum(CASE
          WHEN op.discount > 0 THEN (op.total_price/((100 - op.discount)/100))-op.total_price
          END) AS all_discount'),
                'orders.id',
                'orders.status_prefix',
                'orders.ref_prefix',
                'orders.ref_id',
                'orders.in_status_prefix',
                'orders.in_ref_prefix',
                'orders.in_ref_id',
                'orders.user_id',
                'orders.customer_id',
                'orders.total_amount',
                'orders.delivery_request_date',
                'orders.payment_terms_id',
                'orders.memo',
                'orders.primary_status',
                'orders.status',
                'orders.converted_to_invoice_on',
                'orders.payment_due_date',
                'orders.dont_show'
            )->groupBy('op.order_id')->with('customer', 'customer.primary_sale_person', 'customer.primary_sale_person.get_warehouse', 'customer.CustomerCategory', 'statuses', 'order_products', 'user', 'customer.getpayment_term', 'order_notes', 'get_order_transactions', 'get_order_transactions.get_payment_ref')->whereNotIn('orders.status', [34])->whereIn('orders.customer_id', $all_customer_ids);

            $query->leftJoin('order_products as op', 'op.order_id', '=', 'orders.id');
        } else if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 7 || Auth::user()->role_id == 8 || Auth::user()->role_id == 11) {
            $query = Order::select(
                DB::raw('sum(CASE
          WHEN orders.primary_status=3 AND op.vat > 0 THEN op.total_price
          END) AS vat_total_amount,
          sum(CASE
          WHEN orders.primary_status=3 AND op.vat > 0 THEN op.vat_amount_total
          END) AS vat_amount_price,
          sum(CASE
          WHEN 1 THEN op.total_price
          END) AS sub_total_price,
          sum(CASE
          WHEN orders.primary_status=3 AND (op.vat IS NULL OR op.vat = 0) THEN op.total_price
          END) AS not_vat_total_amount,
          sum(CASE
          WHEN op.discount > 0 THEN (op.total_price/((100 - op.discount)/100))-op.total_price
          END) AS all_discount'),
                'orders.id',
                'orders.status_prefix',
                'orders.ref_prefix',
                'orders.ref_id',
                'orders.in_status_prefix',
                'orders.in_ref_prefix',
                'orders.in_ref_id',
                'orders.user_id',
                'orders.customer_id',
                'orders.total_amount',
                'orders.delivery_request_date',
                'orders.payment_terms_id',
                'orders.memo',
                'orders.primary_status',
                'orders.status',
                'orders.converted_to_invoice_on',
                'orders.payment_due_date',
                'orders.dont_show'
            )->groupBy('op.order_id')->with('customer:reference_name,first_name,last_name,primary_sale_id,category_id,credit_term,id,reference_number,company', 'customer.primary_sale_person:id,name', 'statuses:id,title', 'order_products:id', 'user:id,name', 'get_order_transactions:order_id,id,payment_reference_no', 'get_order_transactions.get_payment_ref:id,payment_reference_no', 'order_customer_note:order_id,note', 'order_warehouse_note:order_id,note')->whereNotIn('orders.status', [34]);

            $query->leftJoin('order_products as op', 'op.order_id', '=', 'orders.id');
        } else {
            $ids = array_merge($this->user->customer->pluck('id')->toArray(), $this->user->secondary_customer->pluck('id')->toArray());

            $query = Order::select(
                DB::raw('sum(CASE
        WHEN orders.primary_status=3 AND op.vat > 0 THEN op.total_price
        END) AS vat_total_amount,
        sum(CASE
        WHEN orders.primary_status=3 AND op.vat > 0 THEN op.vat_amount_total
        END) AS vat_amount_price,
        sum(CASE
        WHEN orders.primary_status=3 AND (op.vat IS NULL OR op.vat = 0) THEN op.total_price
        END) AS not_vat_total_amount,
        sum(CASE
        WHEN 1 THEN op.total_price
        END) AS sub_total_price,
        sum(CASE
        WHEN op.discount > 0 THEN (op.total_price/((100 - op.discount)/100))-op.total_price
        END) AS all_discount'),
                'orders.id',
                'orders.status_prefix',
                'orders.ref_prefix',
                'orders.ref_id',
                'orders.in_status_prefix',
                'orders.in_ref_prefix',
                'orders.in_ref_id',
                'orders.user_id',
                'orders.customer_id',
                'orders.total_amount',
                'orders.delivery_request_date',
                'orders.payment_terms_id',
                'orders.memo',
                'orders.primary_status',
                'orders.status',
                'orders.converted_to_invoice_on',
                'orders.payment_due_date',
                'orders.dont_show'
            )->groupBy('op.order_id')->with('customer', 'customer.primary_sale_person', 'customer.primary_sale_person.get_warehouse', 'customer.CustomerCategory', 'statuses', 'order_products', 'user', 'customer.getpayment_term', 'order_notes', 'get_order_transactions', 'get_order_transactions.get_payment_ref');

            $query->leftJoin('order_products as op', 'op.order_id', '=', 'orders.id');
        }

        if ($request->dosortby == 1) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 1);
            });
        } else if ($request->dosortby == 2) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2);
            });
        } else if ($request->dosortby == 3) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 3);
            });
        } else if ($request->dosortby == 6) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 1)->where('orders.status', 6);
            });
        } else if ($request->dosortby == 7) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 7);
            });
        } else if ($request->dosortby == 8) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 8);
            });
        } else if ($request->dosortby == 9) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 9);
            });
        } else if ($request->dosortby == 10) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 10);
            });
        } else if ($request->dosortby == 11) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 3)->where('orders.status', 11);
            });
        } else if ($request->dosortby == 24) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 3)->where('orders.status', 24);
            });
        } else if ($request->dosortby == 32) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 3)->where('orders.status', 32);
            });
        } else if ($request->dosortby == 35) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 35);
            });
        } else if ($request->dosortby == 36) {
            $query = $query->where(function ($q) {
                $q->where('primary_status', 2)->where('orders.status', 36);
            });
        }

        if ($request->selecting_customer != null) {
            $query = $query->where('customer_id', $request->selecting_customer);
        }
        if ($request->selecting_customer_group != null) {
            $query = $query->whereHas('customer', function ($q) use ($request) {
                $q->where('category_id', @$request->selecting_customer_group);
            });
        }
        if ($request->selecting_sale != null) {
            $query = $query->where('user_id', $request->selecting_sale);
        } else {
            $query = $query->where('orders.dont_show', 0);
        }

        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            if ($request->dosortby == 3 || $request->dosortby == 11 || $request->dosortby == 24) {
                if ($request->date_type == '2') {
                    $query = $query->where('orders.delivery_request_date', '>=', $date);
                }
                if ($request->date_type == '1') {
                    $query = $query->where('orders.converted_to_invoice_on', '>=', $date . ' 00:00:00');
                }
            } else {
                $query = $query->where('orders.delivery_request_date', '>=', $date);
            }
        }

        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            if ($request->dosortby == 3 || $request->dosortby == 11 || $request->dosortby == 24) {
                if ($request->date_type == '1') {
                    $query = $query->where('orders.converted_to_invoice_on', '<=', $date . ' 23:59:59');
                }
                if ($request->date_type == '2') {
                    $query = $query->where('orders.delivery_request_date', '<=', $date);
                }
            } else {
                $query = $query->where('orders.delivery_request_date', '<=', $date);
            }
        }
        if (@$request->is_paid == 11 || @$request->is_paid == 24) {
            $query = $query->where('orders.status', @$request->is_paid);
        }

        if ($request->dosortby == 3) {
            $query = $query->orderBy('converted_to_invoice_on', 'DESC');
        } else {
            $query = $query->orderBy('orders.id', 'DESC');
        }

        if ($request->input_keyword != null) {
            $result = $request->input_keyword;
            if (strstr($result, '-')) {
                $query = $query->where(function ($q) use ($result) {
                    $q->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%" . $result . "%")->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%" . $result . "%");
                });
            } else {
                $resultt = preg_replace("/[^0-9]/", "", $result);
                $query = $query->where(function ($q) use ($resultt) {
                    $q->where('in_ref_id', $resultt)->orWhere('ref_id', $resultt);
                });
            }
        }
        // dd($query->get());
        $ids = $query->pluck('id')->toArray();
        $sub_total = OrderProduct::select('total_price')->whereIn('order_id', $ids)->sum('total_price');
        $total_amount = Order::select('total_amount')->whereIn('id', $ids)->sum('total_amount');

        return response()->json(['post' => $total_amount, 'sub_total' => $sub_total]);
    }

    public function notificationsAndEmails($order, $order_history, $order_product)
    {
        $notification_config = NotificationConfiguration::where('slug', 'draft_invoice_qty_change')->get();
        if ($notification_config) {
            $notification_config = $notification_config->first();
            if ($notification_config->notification_status == 1) {
                if ($notification_config->notification_type == 'notification') {
                    $this->notifications($notification_config, $order, $order_history, $order_product);
                } else if ($notification_config->notification_type == 'email') {
                    $this->emails($notification_config, $order, $order_history, $order_product);
                } else {
                    $this->notifications($notification_config, $order, $order_history, $order_product);
                    $this->emails($notification_config, $order, $order_history, $order_product);
                }
            }
        }
    }

    public function notifications($notification_config, $order, $order_history, $order_product)
    {
        $template = $notification_config->template;
        if ($template) {
            $template = $template->where('notification_type', 'notification')->first();
            $body = $template->getActualNotificationBody($template->body, $order, $order_history, $order_product);
            if ($template->to_type == 'roles') {
                $role_ids = explode(',', $template->values);
                $role_users = [];
                foreach ($role_ids as $role_id) {
                    $role_users = User::where('role_id', $role_id)->whereNull('parent_id')->get();
                }
                foreach ($role_users as $user) {
                    $user->notify(new DraftInvoiceQtyChangeNotification($template->subject, $body));
                }
            } else if ($template->to_type == 'users') {
                $user_ids = explode(',', $template->values);
                foreach ($user_ids as $user_id) {
                    $user = User::find($user_id);
                    $user->notify(new DraftInvoiceQtyChangeNotification($template->subject, $body));
                }
            }
        }
    }

    public function emails($notification_config, $order, $order_history, $order_product)
    {
        $loggedInEmail = Auth::user()->email;
        $email = config('app.mail_username');
        $template = $notification_config->template;
        if ($template) {
            $template = $template->where('notification_type', 'email')->first();
            $body = $template->getActualBody($template->body, $order, $order_history, $order_product);
            if ($template->to_type == 'roles') {
                $role_ids = explode(',', $template->values);
                $role_users = [];
                foreach ($role_ids as $role_id) {
                    $role_users = User::where('role_id', $role_id)->whereNull('parent_id')->get();
                }
                foreach ($role_users as $user) {
                    Mail::send(array(), array(), function ($message) use ($user, $template, $body, $email, $loggedInEmail) {
                        $message->to($user->email)
                            ->subject($template->subject)
                            ->from($loggedInEmail != null ? $loggedInEmail : $email, Auth::user()->name)
                            ->replyTo($email, Auth::user()->name)
                            ->setBody($body, 'text/html');
                    });
                }
            } else if ($template->to_type == 'users') {
                $user_ids = explode(',', $template->values);
                foreach ($user_ids as $user_id) {
                    $user = User::find($user_id);
                    Mail::send(array(), array(), function ($message) use ($user, $template, $body, $email, $loggedInEmail) {
                        $message->to($user->email)
                            ->subject($template->subject)
                            ->from($loggedInEmail != null ? $loggedInEmail : $email, Auth::user()->name)
                            ->replyTo($email, Auth::user()->name)
                            ->setBody($body, 'text/html');
                    });
                }
            } else {
                $custom_users = explode(',', $template->values);
                foreach ($custom_users as $user) {
                    $custom_user_email = CustomEmail::find($user);
                    Mail::send(array(), array(), function ($message) use ($custom_user_email, $template, $body, $email, $loggedInEmail) {
                        $message->to($custom_user_email->email)
                            ->subject($template->subject)
                            ->from($loggedInEmail != null ? $loggedInEmail : $email, Auth::user()->name)
                            ->replyTo($email, Auth::user()->name)
                            ->setBody($body, 'text/html');
                    });
                }
            }
        }
    }


    public function uploadQuotationExcel(Request $request)
    {
        return DraftQuotationHelper::uploadQuotationExcel($request);
    }

    public function DraftQuotExportToPDF(Request $request, $id, $page_type, $column_name, $default_sort = null, $discount = null, $bank_id = null, $vat = null)
    {
        return DraftQuotationHelper::DraftQuotExportToPDF($request, $id, $page_type, $column_name, $default_sort, $discount, $bank_id, $vat);
    }

    public function DraftQuotExportToPDFIncVat(Request $request, $id, $page_type, $column_name, $default_sort, $discount, $bank = null, $is_proforma = null)
    {
        return DraftQuotationHelper::DraftQuotExportToPDFIncVat($request, $id, $page_type, $column_name, $default_sort, $discount, $bank, $is_proforma);
    }

    public function SaveSalesPerson(Request $request)
    {
        return Order::SaveSalesPerson($request);
    }

    public function makeDraftInvoice(Request $request)
    {
        return QuotationsCommonHelper::makeDraftInvoice($request);
    }

    public function mergeDraftInvoices(Request $request){
        \DB::beginTransaction();
        try {
            //check to confirm to have only one customer
            $orders = Order::whereIn('id', $request->order_ids)->get();

            // Check that all orders have the same customer id and delivery data
            $order = $orders->first();
            $customerId = $orders->first()->customer_id;
            $deliveryDate = $orders->first()->delivery_request_date;
            $warehouseId = $orders->first()->from_warehouse_id;

            foreach ($orders as $order) {
                if ($order->customer_id != $customerId) {
                    return response()->json(['success' => false, 'msg' => 'Selected orders cannot be merged because they have different customers.']);
                }
                if ($order->delivery_request_date != $deliveryDate) {
                    return response()->json(['success' => false, 'msg' => 'Selected orders cannot be merged because they have different delivery dates.']);
                }
                if ($order->from_warehouse_id != $warehouseId) {
                    return response()->json(['success' => false, 'msg' => 'Selected orders cannot be merged because they have different warehouses.']);
                }
            }

            //generate new draft invoice number
            $quot_status     = Status::where('id',1)->first();
            $draf_status     = Status::where('id',2)->first();
            $counter_formula = $quot_status->counter_formula;
            $counter_formula = explode('-',$counter_formula);
            $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;
            $date = Carbon::now();
            $date = $date->format($counter_formula[0]); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday
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

            $new_order = new Order;
            $new_order->manual_ref_no = $order->manual_ref_no;
            $new_order->user_id = $order->user_id;
            $new_order->status_prefix         = $order->status_prefix;
            $new_order->ref_prefix            = $order->ref_prefix;
            $new_order->ref_id = $system_gen_no;
            $new_order->from_warehouse_id = $order->from_warehouse_id;
            $new_order->customer_id = $order->customer_id;
            $new_order->total_amount = // to be calculated
            $new_order->delivery_request_date = $order->delivery_request_date;
            $new_order->credit_note_date = $order->credit_note_date;
            $new_order->payment_due_date = $order->payment_due_date;
            $new_order->payment_terms_id = $order->payment_terms_id;
            $new_order->target_ship_date = $order->target_ship_date;
            $new_order->memo = $order->memo;
            $new_order->discount = $order->discount;
            $new_order->shipping = $order->shipping;
            $new_order->billing_address_id = $order->billing_address_id;
            $new_order->shipping_address_id = $order->shipping_address_id;
            $new_order->created_by = @auth()->user()->id;
            $new_order->is_vat = $order->is_vat;
            $new_order->is_manual = $order->is_manual;
            $new_order->primary_status = $order->primary_status;
            $new_order->status = //to be calculated dynamically;
            $new_order->is_processing = $order->is_processing;
            $new_order->converted_to_invoice_on = Carbon::now();
            $new_order->delivery_note = $order->delivery_note;
            $new_order->order_note_type = $order->order_note_type;
            $new_order->dont_show = $order->dont_show;
            $new_order->save();

            foreach ($orders as $order) {
                // $order->order_products()->update(['order_id' => $new_order->id]);
                OrderProduct::where('order_id', $order->id)->update(['order_id' => $new_order->id]);
                $order->previous_primary_status = $order->primary_status;
                $order->previous_status = $order->status;
                $order->primary_status = 17;
                $order->status = 18;
                $order->save();

                $status_history = new OrderStatusHistory;
                $status_history->user_id = @Auth::user()->id;
                $status_history->order_id = $order->id;
                $status_history->status = 'Draft Invoice';
                $status_history->new_status = 'Cancelled (Merge into new draft invoice '.@$new_order->status_prefix.@$new_order->ref_prefix.'-'.@$new_order->ref_id.')';
                $status_history->save();

                $order_his = new OrderHistory;
                $order_his->user_id     =   @auth()->user()->id;
                $order_his->column_name =   'Merged Draft invoice';
                $order_his->old_value   =   @$order->status_prefix.@$order->ref_prefix.'-'.@$order->ref_id;
                $order_his->new_value   =   'Merged Into '.@$new_order->status_prefix.@$new_order->ref_prefix.'-'.@$new_order->ref_id;
                $order_his->order_id    =   @$new_order->id;
                $order_his->save();
            }

            $status_history = new OrderStatusHistory;
            $status_history->user_id = @Auth::user()->id;
            $status_history->order_id = $new_order->id;
            $status_history->status = 'Draft Invoice created by merging';
            $status_history->new_status = 'Draft Invoice';
            $status_history->save();

            //to find the total of new draft invoice
            $amount = OrderProduct::where('order_id', $new_order->id)->sum('total_price_with_vat');
            //to find the min status of new draft invoice
            $status = OrderProduct::where('order_id', $new_order->id)->orderBy('status', 'ASC')->first();
            $new_order->total_amount = round($amount, 2);
            $new_order->status       = @$status->status;
            $new_order->save();

            //find purchase orders and po groups created against the merged draft invoices and update order id into the new order it
            PurchaseOrderDetail::whereIn('order_id', $request->order_ids)->update(['order_id' => $new_order->id]);
            PoGroupProductDetail::whereIn('order_id', $request->order_ids)->update(['order_id' => $new_order->id]);
            \DB::commit();
            $url = route('get-completed-draft-invoices', ['id' => $new_order->id]);
            return response()->json(['success' => true, 'msg' => 'Merged successfully', 'url' => $url]);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['success' => false, 'msg' => $e->getMessage()]);
        }
    }
}
