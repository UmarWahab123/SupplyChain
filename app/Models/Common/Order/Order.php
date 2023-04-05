<?php

namespace App\Models\Common\Order;

use App\Helpers\MyHelper;
use App\Helpers\QuantityReservedHistory;
use App\Models\Common\Company;
use App\Models\Common\Order\CustomerBillingDetail;
use App\Models\Common\Order\DraftQuotation;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderNote;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\Order\OrderStatusHistory;
use App\Models\Common\Product;
use App\Models\Common\WarehouseProduct;
use App\Models\Sales\Customer;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    protected $fillable = ['dont_show', 'ecom_order', 'ecommerce_order_no', 'ecommerce_order_id', 'ecommerce_order', 'cancelled_date', 'converted_to_invoice_on', 'previous_status', 'previous_primary_status', 'created_by', 'primary_status', 'shipping_address_id', 'billing_address_id', 'memo', 'payment_terms_id', 'payment_due_date', 'delivery_request_date', 'vat', 'non_vat_total_paid', 'vat_total_paid', 'total_paid', 'from_warehouse_id', 'full_inv_no', 'in_ref_id', 'in_status_prefix', 'in_ref_prefix', 'ref_prefix', 'ref_id', 'user_id', 'customer_id', 'vat', 'target_ship_date', 'discount', 'shipping', 'total_amount', 'created_by', 'primary_status', 'status', 'status_prefix', 'is_vat', 'manual_ref_no', 'delivery_note', 'is_manual', 'payment_image', 'order_note_type', 'is_tax_order'];
    // protected $with = ["from_warehouse","customer","user","user_created","statuses"];

    public function order_products()
    {
        return $this->hasMany('App\Models\Common\Order\OrderProduct', 'order_id', 'id');
    }

    public function order_products_vat_2()
    {
        return $this->hasMany('App\Models\Common\Order\OrderProduct', 'order_id', 'id')
            ->where(function ($q1) {
                return $q1->whereNull('vat')->orWhere('vat', 0);
            })
            ->orderBy('id', 'ASC');
    }

    public function order_products_not_null()
    {
        return $this->hasMany('App\Models\Common\Order\OrderProduct', 'order_id', 'id')->whereNotNull('product_id');
    }

    public function order_attachment()
    {
        return $this->hasMany('App\Models\Common\Order\OrderAttachment', 'order_id', 'id');
    }

    public function invoice_proforma_printed()
    {
        return $this->hasOne('App\PrintHistory', 'order_id', 'id')->where('print_type', 'performa-to-pdf')->where('page_type', 'complete-invoice');
    }

    public function draft_invoice_pick_instruction_printed()
    {
        return $this->hasOne('App\PrintHistory', 'order_id', 'id')->where('print_type', 'pick-instruction')->where('page_type', 'draft_invoice');
    }

    public function from_warehouse()
    {
        return $this->belongsTo('App\Models\Common\Warehouse', 'from_warehouse_id', 'id');
    }

    public function order_notes()
    {
        return $this->hasMany('App\Models\Common\Order\OrderNote', 'order_id', 'id');
    }
    public function order_customer_note()
    {
        return $this->hasOne('App\Models\Common\Order\OrderNote', 'order_id', 'id')->where('type', 'customer');
    }
    public function order_warehouse_note()
    {
        return $this->hasOne('App\Models\Common\Order\OrderNote', 'order_id', 'id')->where('type', 'warehouse');
    }

    public function order_status_history()
    {
        return $this->hasMany('App\Models\Common\Order\OrderStatusHistory', 'order_id', 'id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Sales\Customer', 'customer_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
    public function user_created()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    public function statuses()
    {
        return $this->belongsTo('App\Models\Common\Status', 'status', 'id');
    }

    public function pre_statuses()
    {
        return $this->belongsTo('App\Models\Common\Status', 'previous_status', 'id');
    }

    public function paymentTerm()
    {
        return $this->belongsTo('App\Models\Common\PaymentTerm', 'payment_terms_id', 'id');
    }

    public function customer_billing_address()
    {
        return $this->belongsTo('App\Models\Common\Order\CustomerBillingDetail', 'billing_address_id', 'id');
    }
    public function customer_shipping_address()
    {
        return $this->belongsTo('App\Models\Common\Order\CustomerBillingDetail', 'shipping_address_id', 'id');
    }
    public function customer_billing_address_ecom()
    {
        return $this->belongsTo('App\Models\Common\Order\CustomerBillingDetail', 'billing_address_id', 'id')->where('title', 'Ecom Billing Address');
    }

    public function customer_order_ecom_address()
    {
        return $this->hasOne('App\Models\Common\Order\CustomerOrderAddressDetail', 'order_id', 'id');
    }

    public function get_order_transactions()
    {
        return $this->hasMany('App\OrderTransaction', 'order_id', 'id')->orderBy('id', 'ASC');
    }

    public function getOrderTotalVat($id, $vat)
    {
        $order = Order::find($id);
        if ($vat === 2) {
            $order_total = OrderProduct::select('total_price', 'vat')->where('order_id', @$order->id)->where(function ($q) {
                $q->whereNull('vat')->orWhere('vat', 0);
            })->get();
        } else if ($vat !== 2) {
            $order_total = OrderProduct::select('total_price', 'vat', 'total_price_with_vat')->where('order_id', @$order->id)->where('vat', '>', 0)->get();
        }

        $total = $order_total->sum('total_price');

        if ($vat === 0 || $vat === 2) {
            return number_format(floor(@$total * 100) / 100, 2, '.', '');
        } else if ($vat === 1) {
            $vat_total = 0;
            foreach ($order_total as $order) {
                // $vat = @$order->total_price_with_vat - @$order->total_price;
                $vat = @$order->vat_amount_total !== null ? round(@$order->vat_amount_total, 2) : (@$order->total_price_with_vat - @$order->total_price);
                $vat_total = $vat_total + $vat;
            }

            return number_format(@$vat_total, 2, '.', '');
        }
    }

    public function getorderTotal($id){
        $order = Order::find($id);
            $order_products = OrderProduct::where('order_id', $id)->get();
            $vat_items_total_after_discount = 0;
            $non_vat_total_after_discount = 0;
            $vat_total = 0;
            $data = array();
            foreach ($order_products as $result) {
                $qty_to_multiply = $result->is_retail == 'qty' ? $result->qty_shipped : $result->pcs_shipped;
                 $vat_items_total_after_discount += $result->vat != null ? ($result->discount == null ? $result->total_price : ($result->unit_price_with_discount != null ? $result->unit_price_with_discount : $result->unit_price) * $qty_to_multiply) : 0;

                 $non_vat_total_after_discount += $result->vat == null ? ($result->discount == null ? $result->total_price : ($result->unit_price_with_discount != null ? $result->unit_price_with_discount : $result->unit_price) * $qty_to_multiply) : 0;

                 $vat = round(@$result->total_price_with_vat-@$result->total_price,2);
                
                    $vat_total = $vat_total + ($result->vat_amount_total != null ? round($result->vat_amount_total,4) : $vat);
            }

            $data['vat_total'] = $vat_total;
            $data['vat_items_total_after_discount'] = $vat_items_total_after_discount;
            $data['non_vat_total_after_discount'] = $non_vat_total_after_discount;

            return $data;
    }

    public function getOrderTotalVatAccounting($id, $vat)
    {
        $order = Order::find($id);
        if ($vat === 2) {
            $order_total = OrderProduct::select('total_price', 'vat')->where('order_id', @$order->id)->where(function ($q) {
                $q->whereNull('vat')->orWhere('vat', 0);
            })->get();
        } else if ($vat !== 2) {
            $order_total = OrderProduct::select('total_price', 'vat', 'total_price_with_vat')->where('order_id', @$order->id)->where('vat', '>', 0)->get();
        }

        if ($vat == 0) {
            // $total = $order_total->sum('total_price_with_vat');
            $total = 0;
            foreach ($order_total as $order) {
                $total += round($order->total_price_with_vat, 4);
            }
        } else {
            $total = $order_total->sum('total_price');
        }

        if ($vat === 0 || $vat === 2) {
            // return number_format(floor(@$total*100)/100,2);
            return number_format($total, 2, '.', '');
        } else if ($vat === 1) {
            $vat_total = 0;
            foreach ($order_total as $order) {
                $vat = round(@$order->total_price_with_vat - @$order->total_price, 2);
                $vat_total = $vat_total + $vat;
            }

            // return number_format(floor(@$vat_total*100)/100,2);
            return number_format($vat_total, 2, '.', '');
        }
    }

    public function getOrderTotalVatAccountingForBilling($id, $vat)
    {
        $order = Order::find($id);
        if ($vat === 2) {
            $order_total = OrderProduct::select('total_price', 'vat')->where('order_id', @$order->id)->where(function ($q) {
                $q->whereNull('vat')->orWhere('vat', 0);
            })->get();
        } else if ($vat !== 2) {
            $order_total = OrderProduct::select('total_price', 'vat', 'total_price_with_vat')->where('order_id', @$order->id)->where('vat', '>', 0)->get();
        }

        if ($vat == 0) {
            $total = $order_total->sum('total_price_with_vat');
        } else {
            $total = $order_total->sum('total_price');
        }

        if ($vat === 0 || $vat === 2) {
            // return number_format(floor(@$total*100)/100,2);
            return number_format(preg_replace('/(\.\d\d\d\d).*/', '$1', $total), 4, '.', '');
        } else if ($vat === 1) {
            $vat_total = 0;
            foreach ($order_total as $order) {
                $vat = @$order->total_price_with_vat - @$order->total_price;
                $vat_total = $vat_total + $vat;
            }

            // return number_format(floor(@$vat_total*100)/100,2);
            return number_format(preg_replace('/(\.\d\d\d\d).*/', '$1', $vat_total), 4, '.', '');
        }
    }
    public function get_order_number_and_link($order)
    {
        if ($order->is_vat == 1) {
            if ($order->in_status_prefix !== null && $order->in_ref_id !== null) {
                $ref_no = @$order->in_status_prefix . '-' . $order->in_ref_id;
            } elseif ($order->status_prefix !== null && $order->ref_prefix !== null && $order->ref_id !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . @$order->ref_id;
            }
        } else {
            if ($order->in_status_prefix !== null && $order->in_ref_prefix !== null && $order->in_ref_id !== null) {
                $ref_no = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
            } elseif ($order->status_prefix !== null && $order->ref_prefix !== null && $order->ref_id !== null) {
                $ref_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
            } else {
                $ref_no = @$order->customer->primary_sale_person->get_warehouse->order_short_code . @$order->customer->CustomerCategory->short_code . @$order->ref_id;
            }
        }


        if (@$order->primary_status == 3) {
            $link = 'get-completed-invoices-details';
        } elseif (@$order->primary_status == 17) {
            $link = 'get-cancelled-order-detail';
        } elseif ($order->primary_status == 1) {
            $link = 'get-completed-quotation-products';
        } else {
            $link = 'get-completed-draft-invoices';
        }

        return array($ref_no, $link);
    }

    public function update_stock_card($order_product, $new_value)
    {
        // $warehouse_id = $order_product->from_warehouse_id != null ? $order_product->from_warehouse_id : ($order_product->get_order->user_created != null ? $order_product->get_order->user_created->warehouse_id : Auth::user()->warehouse_id);
        $warehouse_id = $order_product->from_warehouse_id != null ? $order_product->from_warehouse_id : ($order_product->user_warehouse_id != null ? $order_product->user_warehouse_id : ($order_product->get_order->user_created != null ? $order_product->get_order->user_created->warehouse_id : Auth::user()->warehouse_id));
        // $warehouse_product = WarehouseProduct::where('warehouse_id',$warehouse_id)->where('product_id',$order_product->product_id)->first();
        // $my_helper =  new MyHelper;
        // $res_wh_update = $my_helper->updateWarehouseProduct($warehouse_product);

        $warehouse_product = WarehouseProduct::where('warehouse_id', $warehouse_id)->where('product_id', $order_product->product_id)->first();
        if ($warehouse_product != null) {
            $diff = $order_product->quantity - $new_value;
            if ($order_product->get_order->ecommerce_order == 1) {
                if ($diff < 0) {
                    $warehouse_product->ecommerce_reserved_quantity += abs($diff);
                } else {
                    $warehouse_product->ecommerce_reserved_quantity -= abs($diff);
                }
            } else {
                if ($diff < 0) {
                    $warehouse_product->reserved_quantity += abs($diff);
                } else {
                    $warehouse_product->reserved_quantity -= abs($diff);
                }
            }

            $warehouse_product->available_quantity = ($warehouse_product->current_quantity - ($warehouse_product->reserved_quantity + $warehouse_product->ecommerce_reserved_quantity));
            $warehouse_product->save();
            $res = (new QuantityReservedHistory)->quantityReservedHistoryFun($order_product, $order_product->order_id, $new_value, 'Reserved Quantity Updated', 'Order', Auth::user()->id, $warehouse_product);

            return true;
        }
    }

    public static function SaveSalesPerson($request)
    {
        try {
            if ($request->type == 'draft_qoutation') {
                $draft_qoutation = DraftQuotation::find($request->id);
                if ($draft_qoutation != null) {
                    $draft_qoutation->user_id = $request->user_id;
                    $draft_qoutation->save();
                }
            } else {
                $order = Order::find($request->id);
                if ($order != null) {
                    $order->user_id = $request->user_id;
                    $order->save();
                }
            }
            return response()->json(['success' => true]);
        } catch (Exception $e) {
            return response()->json(['success' => false]);
        }
    }

    public static function getDataForProforma($id, $default_sort, $server = null)
    {
        $data = [];
        $order = Order::find($id);
        $data['order'] = $order;
        $address = CustomerBillingDetail::select('billing_phone')->where('customer_id', $order->customer_id)->where('is_default', 1)->first();
        $data['address'] = $address;
        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->billing_address_id)->first();
        $data['customerAddress'] = $customerAddress;
        $customerShippingAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->shipping_address_id)->first();
        $data['customerShippingAddress'] = $customerShippingAddress;
        //query for lucilla
        if($server == 'lucilla'){
            $query = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id);
            if ($default_sort != 'id_sort') {
                $query = $query->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
            } else {
                $query = $query->orderBy('id', 'ASC')->get();
            }
        }else{
            $query = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id)->whereNotNull('order_products.vat')->where('order_products.vat', '!=', 0);
            if ($default_sort != 'id_sort') {
                $query = $query->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
            } else {
                $query = $query->orderBy('id', 'ASC')->get();
            }
        }
        $data['query'] = $query;
        if($server == 'lucilla'){
            $vat_count = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id)->where(function ($z) {
                $z->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
            })->orderBy('id', 'ASC')->count();
        }
        else
        {
            $vat_count = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id)->where('vat', '!=', 0)->where(function ($z) {
                $z->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
            })->orderBy('id', 'ASC')->count();
        }
        $data['vat_count'] = $vat_count;
        if($server == 'lucilla'){
            $vat_count_notes = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->select('id')->WhereHas('get_order_product_notes', function ($q) {
                $q->where('show_on_invoice', 1);
            })->where('order_id', $id)->orderBy('id', 'ASC')->count();
        }else{
            $vat_count_notes = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->select('id')->WhereHas('get_order_product_notes', function ($q) {
                $q->where('show_on_invoice', 1);
            })->where(function ($q) {
                $q->where('vat', '!=', 0);
            })->where('order_id', $id)->orderBy('id', 'ASC')->count();
        }
        $data['vat_count_notes'] = $vat_count_notes;

        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $data['arr'] = $arr;

        $query_count = $query->count() / 6;
        $data['query_count'] = $query_count;

        $query2 = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where(function ($q) {
            $q->where('order_products.vat', 0)->orWhereNull('order_products.vat');
        })->where('order_id', $id);

        if ($default_sort != 'id_sort') {
            $query2 = $query2->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->select('order_products.*')->get();
        } else {
            $query2 = $query2->orderBy('id', 'ASC')->get();
        }
        $data['query2'] = $query2;


        $non_vat_count = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where(function ($q) {
            $q->where('vat', 0)->orWhereNull('vat');
        })->where('order_id', $id)->where(function ($p) {
            $p->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
        })->orderBy('id', 'ASC')->count();
        $data['non_vat_count'] = $non_vat_count;

        #To find notes
        $query2_notes = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->select('id')->WhereHas('get_order_product_notes', function ($q) {
            $q->where('show_on_invoice', 1);
        })->where(function ($q) {
            $q->where('vat', 0)->orWhereNull('vat');
        })->where('order_id', $id)->orderBy('id', 'ASC')->count();
        $data['query2_notes'] = $query2_notes;

        #To find discounted items
        $query2_discounts = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->select('id')->where(function ($q) {
            $q->where('vat', 0)->orWhereNull('vat');
        })->where('order_id', $id)->where('qty_shipped', '>', 0)->where('discount', '>', '0')->orderBy('id', 'ASC')->count();
        $data['query2_discounts'] = $query2_discounts;

        $inv_note = OrderNote::where('order_id', $id)->where('type', 'customer')->first();
        $data['inv_note'] = $inv_note;

        if (($non_vat_count + @$query2_notes + $query2_discounts) > 16) {
            $query_count2 = ceil((@$non_vat_count + @$query2_notes + $query2_discounts) / 16);
        } else {
            $query_count2 = 1;
        }
        $data['query_count2'] = $query_count2;
        // dd($non_vat_count + @$query2_notes + $query2_discounts);

        if (($vat_count + $vat_count_notes) > 10) {
            $query_count = ceil(($vat_count + $vat_count_notes) / 10);
        } else {
            $query_count = 1;
        }
        $data['query_count'] = $query_count;

        $all_orders_count = OrderProduct::where('order_id', $id)->where(function ($q) {
            $q->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
        })->orderBy('id', 'ASC')->count();
        $data['all_orders_count'] = $all_orders_count;

        if ($all_orders_count <= 12) {
            $do_pages_count = ceil($all_orders_count / 12);
            $final_pages = $all_orders_count % 12;
            if ($final_pages == 0) {
                // $do_pages_count++;
            }
        }
        elseif ($all_orders_count <= 8) {
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
        $data['do_pages_count'] = $do_pages_count;

        return $data;
    }

    // public static function doSortForReports($default_sort, $column_name, $order_products) {
    //   if ($column_name == 'reference_code' && $default_sort !== 'id_sort') {
    //     $order_products = $order_products->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
    //   } elseif($column_name == 'short_desc' && $default_sort !== 'id_sort') {
    //     $order_products = $order_products->orderBy('short_desc', $default_sort)->get();
    //   } elseif($column_name == 'supply_from' && $default_sort !== 'id_sort') {
    //     $order_products = $order_products->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
    //   } elseif($column_name == 'type_id' && $default_sort !== 'id_sort') {
    //     $order_products = $order_products->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $default_sort)->get();
    //   } elseif($column_name == 'brand' && $default_sort !== 'id_sort') {
    //     $order_products = $order_products->orderBy($column_name, $default_sort)->get();
    //   }
    //   else{
    //     $order_products = $order_products->orderBy('id', 'ASC')->get();
    //   }
    // }

    public static function getDataForInvExcVat($id, $default_sort, $column_name)
    {
        $data = [];
        $order = Order::find($id);
        $data['order'] = $order;

        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $data['arr'] = $arr;

        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->billing_address_id)->first();
        $data['customerAddress'] = $customerAddress;

        $customerShippingAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->shipping_address_id)->first();
        $data['customerShippingAddress'] = $customerShippingAddress;

        $order_products = OrderProduct::select('order_products.*')->with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id);

        // if ($default_sort != 'id_sort') {
        //   $order_products = $order_products->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
        // }
        // else{
        //   $order_products = $order_products->orderBy('id', 'ASC')->get();
        // }

        if ($column_name == 'reference_code' && $default_sort !== 'id_sort') {
            $order_products = $order_products->select('order_products.*')->leftJoin('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
        } elseif ($column_name == 'short_desc' && $default_sort !== 'id_sort') {
            $order_products = $order_products->orderBy('short_desc', $default_sort)->get();
        } elseif ($column_name == 'supply_from' && $default_sort !== 'id_sort') {
            $order_products = $order_products->select('order_products.*')->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
        } elseif ($column_name == 'type_id' && $default_sort !== 'id_sort') {
            $order_products = $order_products->select('order_products.*')->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $default_sort)->get();
        } elseif ($column_name == 'brand' && $default_sort !== 'id_sort') {
            $order_products = $order_products->orderBy($column_name, $default_sort)->get();
        } else {
            $order_products = $order_products->orderBy('id', 'ASC')->get();
        }

        // if ($column_name == 'reference_code' && $default_sort !== 'id_sort') {
        //   $order_products = $order_products->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
        // } elseif($column_name == 'short_desc' && $default_sort !== 'id_sort') {
        //   $order_products = $order_products->orderBy('short_desc', $default_sort)->get();
        // } elseif($column_name == 'supply_from' && $default_sort !== 'id_sort') {
        //   $order_products = $order_products->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
        // } elseif($column_name == 'type_id' && $default_sort !== 'id_sort') {
        //   $order_products = $order_products->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $default_sort)->get();
        // } elseif($column_name == 'brand' && $default_sort !== 'id_sort') {
        //   $order_products = $order_products->orderBy($column_name, $default_sort)->get();
        // }
        // else{
        //   $order_products = $order_products->orderBy('id', 'ASC')->get();
        // }

        // $label = $item->from_warehouse_id != null ? @$item->from_warehouse->warehouse_title: (@$item->from_supplier->reference_name != null ? $item->from_supplier->reference_name;



        $data['order_products'] = $order_products;

        $discount_1 = 0;
        $discount_2 = 0;
        //to find discounted items
        if ($order->primary_status == 2 || $order->primary_status == 1) {
            $total_products_count_qty = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('quantity', '>', 0)->orWhereHas('get_order_product_notes');
            })->where('is_retail', 'qty')->orderBy('id', 'ASC')->count();
            $total_products_count_pieces = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('number_of_pieces', '>', 0)->orWhereHas('get_order_product_notes');
            })->where('is_retail', 'pieces')->orderBy('id', 'ASC')->count();

            $discount_1 = OrderProduct::select('id', 'order_id', 'is_retail', 'discount', 'quantity')->where('order_id', $id)->where('is_retail', 'qty')->where('discount', '>', 0)->where('quantity', '>', 0)->orderBy('id', 'ASC')->count();
            $discount_2 = OrderProduct::select('id', 'order_id', 'is_retail', 'discount', 'number_of_pieces')->where('order_id', $id)->where('is_retail', 'pieces')->where('discount', '>', 0)->where('number_of_pieces', '>', 0)->orderBy('id', 'ASC')->count();
        } else if ($order->primary_status == 3) {
            $total_products_count_qty = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('qty_shipped', '>', 0)->orWhereHas('get_order_product_notes');
            })->orderBy('id', 'ASC')->count();
            // dd($total_products_count_qty);
            $total_products_count_pieces = OrderProduct::where('order_id', $id)->where(function ($q) {
                $q->where('pcs_shipped', '>', 0)->orWhereHas('get_order_product_notes');
            })->where('is_retail', 'pieces')->orderBy('id', 'ASC')->count();

            $discount_1 = OrderProduct::select('id', 'order_id', 'is_retail', 'discount', 'qty_shipped')->where('order_id', $id)->where('is_retail', 'qty')->where('discount', '>', 0)->where('qty_shipped', '>', 0)->orderBy('id', 'ASC')->count();
            $discount_2 = OrderProduct::select('id', 'order_id', 'is_retail', 'discount', 'pcs_shipped')->where('order_id', $id)->where('is_retail', 'pieces')->where('discount', '>', 0)->where('pcs_shipped', '>', 0)->orderBy('id', 'ASC')->count();
        }
        $data['total_products_count_qty'] = $total_products_count_qty;
        $data['total_products_count_pieces'] = $total_products_count_pieces;
        $data['discount_1'] = $discount_1;
        $data['discount_2'] = $discount_2;

        $notes_count = 0;
        foreach ($order->order_products as $prod) {
            $notes_count += $prod->get_order_product_notes->count();
        }
        $pages = ceil(($total_products_count_qty + $total_products_count_pieces + @$notes_count + $discount_1 + $discount_2) / 13);
        // dd(intval($pages));

        if ($pages == 0) {
            $pages = 1;
        }
        $data['pages'] = $pages;

        return $data;
    }

    public static function getDataForInvIncVat($id, $column_name, $default_sort)
    {
        $data = [];
        $order = Order::find($id);
        $data['order'] = $order;

        $arr = explode("\r\n", @$order->user->getCompany->bank_detail);
        $data['arr'] = $arr;

        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->billing_address_id)->first();
        $data['customerAddress'] = $customerAddress;

        $customerShippingAddress = CustomerBillingDetail::where('customer_id', $order->customer->id)->where('id', $order->shipping_address_id)->first();
        $data['customerShippingAddress'] = $customerShippingAddress;

        $order_products = OrderProduct::with('product', 'get_order', 'product.def_or_last_supplier', 'product.units', 'product.supplier_products')->where('order_id', $id);
        // if ($default_sort != 'id_sort')
        // {
        //     $order_products = $order_products->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->select('order_products.*')->get();
        // }
        // else
        // {
        //     $order_products = $order_products->orderBy('id', 'ASC')->get();
        // }
        // dd($column_name, $default_sort);

        if ($column_name == 'reference_code' && $default_sort !== 'id_sort') {
            // dd('works');
            $order_products = $order_products->select('order_products.*')->leftJoin('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->get();
            // dd($order_products);
        } elseif ($column_name == 'short_desc' && $default_sort != 'id_sort') {
            $order_products = $order_products->orderBy('short_desc', $default_sort)->get();
        } elseif ($column_name == 'supply_from' && $default_sort !== 'id_sort') {
            $order_products = $order_products->select('order_products.*')->leftJoin('suppliers', 'suppliers.id', '=', 'order_products.supplier_id')->orderBy('suppliers.reference_name', $default_sort)->get();
        } elseif ($column_name == 'type_id' && $default_sort !== 'id_sort') {
            $order_products = $order_products->select('order_products.*')->leftJoin('types', 'types.id', '=', 'order_products.type_id')->orderBy('types.title', $default_sort)->get();
        } elseif ($column_name == 'brand' && $default_sort !== 'id_sort') {
            $order_products = $order_products->orderBy($column_name, $default_sort)->get();
        } else {
            $order_products = $order_products->orderBy('id', 'ASC')->get();
        }

        $data['order_products'] = $order_products;

        $inv_note = OrderNote::where('order_id', $id)->where('type', 'customer')->first();
        $data['inv_note'] = $inv_note;

        $discount_1 = 0;
        $discount_2 = 0;
        //to find discounted items
        if ($order->primary_status == 2 || $order->primary_status == 1) {
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
                    // $do_pages_count++;
                }
            } else {
                $do_pages_count = ceil($all_orders_count / 8);
                $final_pages = $all_orders_count % 8;
                if ($final_pages == 0) {
                    $do_pages_count++;
                }
            }

            $discount_1 = OrderProduct::select('id', 'order_id', 'is_retail', 'discount', 'quantity')->where('order_id', $id)->where('is_retail', 'qty')->where('discount', '>', 0)->where('quantity', '>', 0)->orderBy('id', 'ASC')->count();
            $discount_2 = OrderProduct::select('id', 'order_id', 'is_retail', 'discount', 'number_of_pieces')->where('order_id', $id)->where('is_retail', 'pieces')->where('discount', '>', 0)->where('number_of_pieces', '>', 0)->orderBy('id', 'ASC')->count();
        } else if ($order->primary_status == 3) {
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
                $do_pages_count = ceil($all_orders_count / 8);
                $final_pages = $all_orders_count % 8;
                if ($final_pages == 0) {
                    $do_pages_count++;
                }
            }

            $discount_1 = OrderProduct::select('id', 'order_id', 'is_retail', 'discount', 'qty_shipped')->where('order_id', $id)->where('is_retail', 'qty')->where('discount', '>', 0)->where('qty_shipped', '>', 0)->orderBy('id', 'ASC')->count();
            $discount_2 = OrderProduct::select('id', 'order_id', 'is_retail', 'discount', 'pcs_shipped')->where('order_id', $id)->where('is_retail', 'pieces')->where('discount', '>', 0)->where('pcs_shipped', '>', 0)->orderBy('id', 'ASC')->count();
        }
        $data['total_products_count_qty'] = $total_products_count_qty;
        $data['total_products_count_pieces'] = $total_products_count_pieces;
        $data['all_orders_count'] = $all_orders_count;
        $data['do_pages_count'] = $do_pages_count;
        $data['final_pages'] = $final_pages;

        $notes_count = 0;
        foreach ($order->order_products as $prod) {
            $notes_count += $prod->get_order_product_notes->count();
        }
        $pages = ceil(($total_products_count_qty + $total_products_count_pieces + @$notes_count + $discount_1 + $discount_2) / 13);
        // dd(intval($pages));

        if ($pages == 0) {
            $pages = 1;
        }
        $data['pages'] = $pages;

        return $data;
    }

    public static function getDataForQuoTexica($id, $default_sort)
    {
        $data = [];
        $order = Order::find($id);
        $data['order'] = $order;
        $order_products = OrderProduct::where('order_id', $id);
        if ($default_sort != 'id_sort') {
            $order_products = $order_products->join('products', 'products.id', '=', 'order_products.product_id')->orderBy('products.refrence_code', $default_sort)->select('order_products.*')->get();
        } else {
            $order_products = $order_products->orderBy('id', 'desc')->get();
        }
        $data['order_products'] = $order_products;

        $company_info = Company::where('id', $order->user->company_id)->first();
        $data['company_info'] = $company_info;
        // dd($order);
        $address = CustomerBillingDetail::select('billing_phone')->where('customer_id', $order->customer_id)->where('is_default', 1)->first();
        $data['address'] = $address;

        $customerAddress = CustomerBillingDetail::where('customer_id', $order->customer_id)->where('id', $order->billing_address_id)->first();
        $data['customerAddress'] = $customerAddress;

        $inv_note = OrderNote::where('order_id', $order->id)->where('type', 'customer')->first();
        $data['inv_note'] = $inv_note;
        $warehouse_note = OrderNote::where('order_id', $order->id)->where('type', 'warehouse')->first();
        $data['warehouse_note'] = $warehouse_note;

        $all_orders_count = OrderProduct::where('order_id', $id)->where(function ($q) {
            $q->where('quantity', '>', 0)->orWhereHas('get_order_product_notes');
        })->orderBy('id', 'ASC')->count();
        // getting count on all order products
        $do_pages_count = ceil($all_orders_count / 3);
        $data['do_pages_count'] = $do_pages_count;

        return $data;
    }

    public static function createManualOrder($stock, $order_id = null, $msg = null )
    {
        $customer = Customer::where('manual_customer', 1)->first();
        if ($customer != null) {
            $address  = CustomerBillingDetail::where('customer_id', $customer->id)->first();
            $order                        = new Order;
            $order->status_prefix         = 'M';
            $order->ref_prefix            = 'A';
            $order->customer_id           = $customer->id;
            $order->total_amount          = 0;
            $order->target_ship_date      = $stock->created_at;
            $order->memo                  = $order_id;
            $order->discount              = 0;
            $order->from_warehouse_id     = $stock->warehouse_id;
            $order->shipping              = 0;
            $order->payment_due_date      = $stock->created_at;
            $order->delivery_request_date = $stock->created_at;
            $order->billing_address_id    = $address != null ? $address->id : null;
            $order->shipping_address_id   = $address != null ? $address->id : null;
            $order->user_id               = @auth()->user()->id;
            $order->converted_to_invoice_on = Carbon::now();
            $order->manual_ref_no         = null;
            $order->is_vat                = 0;
            $order->created_by            = @auth()->user()->id;
            $order->primary_status        = 37;
            $order->status                = 38;
            $order->save();
            $order->ref_id = $order->id;
            $order->full_inv_no = 'Manual-' . $order->id;
            $order->save();

            $cust_note = new OrderNote;
			$cust_note->order_id = $order->id;
			$cust_note->note = $msg;
			$cust_note->type = 'customer';
			$cust_note->save();

            $product = Product::find($stock->product_id);

            $new_order_product = OrderProduct::create([
                'order_id'             => $order->id,
                'product_id'           => $product->id,
                'category_id'          => $product->category_id,
                'short_desc'           => $product->short_desc,
                'brand'                => $product->brand,
                'type_id'              => $product->type_id,
                'number_of_pieces'     => null,
                'quantity'             => abs($stock->quantity_out),
                'qty_shipped'          => abs($stock->quantity_out),
                'selling_unit'         => $product->selling_unit,
                'margin'               => $product->margin,
                'vat'                  => null,
                'vat_amount_total'     => null,
                'unit_price'           => null,
                'last_updated_price_on' => null,
                'unit_price_with_vat'  => null,
                'unit_price_with_discount'  => null,
                'is_mkt'               => $product->is_mkt,
                'total_price'          => null,
                'total_price_with_vat' => null,
                'supplier_id'          => $product->supplier_id,
                'from_warehouse_id'    => $stock->warehouse_id,
                'user_warehouse_id'    => $stock->warehouse_id,
                'warehouse_id'         => $stock->warehouse_id,
                'is_warehouse'         => $product->is_warehouse,
                'status'               => 38,
                'is_billed'            => 'Product',
                'default_supplier'     => null,
                'remarks'     => $stock->title,
                'created_by'           => @auth()->user()->id,
                'discount'             => 0,
                'is_retail'            => 'qty',
                'import_vat_amount'    => null,
                'actual_cost'          => $product->selling_price,
                'locked_actual_cost'          => $product->selling_price,
            ]);
            $stock->order_id = $order->id;
            $stock->order_product_id = $new_order_product->id;
            $stock->save();

            $order_history = new OrderStatusHistory;
            $order_history->user_id = @auth()->user()->id;
            $order_history->order_id = @$order->id;
            $order_history->status = 'Created';
            $order_history->new_status = 'Manual Adjustment Document';
            $order_history->save();
        }
        return true;
    }


    public static function doSort($request, $query)
    {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';

        //For Pick Instruction dashboard

        if ($request->sortbyparam == 'purchase_date') {
            $query->orderBy('updated_at', $sort_order);
        } elseif ($request->sortbyparam == 'purchase_date') {
            $query->orderBy('updated_at', $sort_order);
        } elseif ($request->sortbyparam == 'remark') {
            $query->leftJoin('order_notes', 'order_notes.order_id', '=', 'orders.id')->select('orders.*')->where('type', '=', 'customer')->orderBy('order_notes.note', $sort_order);
        } elseif ($request->sortbyparam == 'comment') {
            $query->leftJoin('order_notes', 'order_notes.order_id', '=', 'orders.id')->select('orders.*')->where('type', '=', 'wearhouse')->orderBy('order_notes.note', $sort_order);
        } elseif ($request->sortbyparam == '') {
            $query = $query->orderBy('orders.id', 'DESC');
        }


        //The end

        // For accounting dashboard
        if ($request->sortbyparam == 'order_no') {
            $query->orderBy('ref_id', $sort_order);
        }

        if ($request->sortbyparam == 'sales_person') {
            $query->select('orders.*')->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')->leftJoin('users', 'customers.primary_sale_id', '=', 'users.id')->orderBy('users.name', $sort_order);
        }

        if ($request->sortbyparam == 'customer_no') {
            $query->select('orders.*')->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')->orderBy('customers.reference_number', $sort_order);
        }

        if ($request->sortbyparam == 'customer_reference_name') {
            $query->select('orders.*')->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')->orderBy('customers.reference_name', $sort_order);
        }

        if ($request->sortbyparam == 'order_total') {
            $query->orderBy(\DB::Raw('total_amount+0'), $sort_order);
        }

        if ($request->sortbyparam == 'delivery_date') {
            $query->orderBy('credit_note_date', $sort_order);
        }

        if ($request->sortbyparam == 'memo') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }

        if ($request->sortbyparam == 'created_at') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }

        if ($request->sortbyparam == 'draft_delivery_date' || $request->sortbyparam == 'date') {
            $query->orderBy('delivery_request_date', $sort_order);
        }
        // End for accounting dashboard


        if ($request->sortbyparam == 'recv_date') {
            $query->leftJoin('order_transactions', 'order_transactions.order_id', '=', 'orders.id')->orderBy('order_transactions.received_date', $sort_order);
        }

        if ($request->sortbyparam == 'inv_date') {
            $sort_variable  = 'converted_to_invoice_on';
            $query->orderBy($sort_variable, $sort_order);
        }

        if ($request->sortbyparam == 'payment_reference') {
            $query->leftJoin('order_transactions', 'order_transactions.order_id', '=', 'orders.id')->orderBy('order_transactions.payment_reference_no', $sort_order);
        }

        if ($request->sortbyparam == 'in_ref_id') {
            $query->orderBy($request->sortbyparam, $sort_order);
        } else if ($request->sortbyparam == 'in_ref_id') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }

        if ($request->sortbyparam == 'ref_id') {
            $query->orderBy($request->sortbyparam, $sort_order);
        } else if ($request->sortbyparam == 'ref_id') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }

        if ($request->sortbyparam == 'user_id') {
            $query->join('users', 'users.id', '=', 'orders.user_id')->orderBy('users.name', $sort_order);
        } else if ($request->sortbyparam == 'user_id') {
            $query->join('users', 'users.id', '=', 'orders.user_id')->orderBy('users.name', $sort_order);
        }

        if ($request->sortbyparam == 'customer_id') {
            $query->join('customers', 'customers.id', '=', 'orders.customer_id')->orderBy('customers.reference_number', $sort_order);
        }


        if ($request->sortbyparam == 'customer_name') {
            $query->select('orders.*')->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')->orderBy('customers.reference_name', $sort_order);
        }


        if ($request->sortbyparam == 'company') {
            $query->join('customers', 'customers.id', '=', 'orders.customer_id')->orderBy('customers.company', $sort_order);
        }


        if ($request->sortbyparam == 'discount') {
            $sort_variable  = 'all_discount';
            $query->orderBy($sort_variable, $sort_order);
        }



        if ($request->sortbyparam == 'sub_total_amount') {
            $query->join('order_products', 'order_products.order_id', '=', 'orders.id')->orderBy(\DB::raw('SUM(order_products.total_price)+0'), $sort_order);
        }


        if ($request->sortbyparam == 'total_amount') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }


        if ($request->sortbyparam == 'delivery_date') {
            $sort_variable  = 'delivery_request_date';
            $query->orderBy($sort_variable, $sort_order);
        }


        if ($request->sortbyparam == 'due_date') {
            $sort_variable  = 'payment_due_date';
            $query->orderBy($sort_variable, $sort_order);
        }


        if ($request->sortbyparam == 'status') {
            $query->orderBy($request->sortbyparam, $sort_order);
        }

        if ($request->sortbyparam == 'tax_id') {
            $query->select('orders.*')->leftJoin('customers', 'customers.id', '=', 'orders.customer_id')->join('customer_billing_details as cbd', 'cbd.customer_id', '=', 'customers.id')->orderBy('cbd.tax_id', $sort_order);
        }
        if ($request->sortbyparam == 'target_ship_date') {
            $query->orderBy('target_ship_date', $sort_order);
        }
        if ($request->sortbyparam == 'printed') {
            $new_or = @$sort_order == 'ASC' ? 'DESC' : 'ASC';
            
            if($request->dosortby != 3){
                // $query->leftJoin('print_histories', 'orders.id', '=', 'print_histories.order_id')->where('print_type', 'pick-instruction')->where('page_type', 'draft_invoice')
                // ->select('orders.*', DB::raw('IF((print_histories.print_type = "pick-instruction" AND page_type = "draft_invoice"), 1, 0) as printed'));
                // $query->orderBy('printed', $sort_order);
                  // $query->orderByRaw('ISNULL(printed), printed '.@$new_or);

                $query->leftJoin('print_histories', function($join) {
                $join->on('orders.id', '=', 'print_histories.order_id')
                     ->where('print_type', 'pick-instruction')->where('page_type', 'draft_invoice');
                })
                ->select('orders.*', DB::raw('IF(print_histories.id IS NULL, 0, 1) as printed'))->orderBy('printed', $sort_order);

            }else{
                
                // $query->leftJoin('print_histories', 'orders.id', '=', 'print_histories.order_id')
                // ->select('orders.*', DB::raw('IF((print_histories.print_type = "performa-to-pdf" AND page_type = "complete-invoice"), 1, 0) as printed'));
                // $query->orderBy('printed', $sort_order)
                //   ->orderByRaw('ISNULL(printed), printed '.@$new_or);

                $query->leftJoin('print_histories', function($join) {
                $join->on('orders.id', '=', 'print_histories.order_id')
                     ->where('print_type', 'performa-to-pdf')->where('page_type', 'complete-invoice');
                })
                ->select('orders.*', DB::raw('IF(print_histories.id IS NULL, 0, 1) as printed'))->orderBy('printed', $sort_order);

                // $query->leftJoin('print_histories', 'orders.id', '=', 'print_histories.order_id')->where('print_type', 'performa-to-pdf')->where('page_type', 'complete-invoice')->orderBy('print_histories.id', $sort_order);
            }
        }

    }

    public static function returnAddColumn($column, $item)
    {
        switch ($column) {
            case 'action':
                $html_string = '';
                if ($item->primary_status == 1 &&  Auth::user()->role_id != 7) {
                    $html_string .= '<a href="javascript:void(0);" class="actionicon deleteIcon text-center deleteOrder" data-id="' . $item->id . '" title="Delete"><i class="fa fa-trash"></i></a>';
                }
                return $html_string;
                break;

            case 'sub_total_amount':
                return number_format(floor($item->sub_total_price * 100) / 100, 2, '.', ',');
                break;

            case 'total_amount':
                return number_format(floor($item->total_amount * 100) / 100, 2, '.', ',');
                break;

            case 'discount':
                return $item->all_discount !== null ? number_format(floor($item->all_discount * 100) / 100, 2, '.', ',') : '0.00';
                break;

            case 'due_date':
                return @$item->payment_due_date != null ? Carbon::parse(@$item->payment_due_date)->format('d/m/Y') : '--';
                break;

            case 'invoice_date':
                return Carbon::parse(@$item->converted_to_invoice_on)->format('d/m/Y');
                break;

            case 'sub_total_2':
                return $item->not_vat_total_amount !== null ? number_format($item->not_vat_total_amount, 2, '.', ',') : '0.00';
                break;

            case 'reference_id_vat_2':
                return @$item->in_status_prefix . '-' . @$item->in_ref_prefix . $item->in_ref_id . '-2';
                break;

            case 'vat_1':
                return $item->vat_amount_price !== null ? number_format($item->vat_amount_price, 2, '.', ',') : '0.00';
                break;

            case 'sub_total_1':
                return $item->vat_total_amount !== null ? number_format($item->vat_total_amount, 2, '.', ',') : '0.00';
                break;

            case 'reference_id_vat':
                return $item->in_status_prefix . '-' . @$item->in_ref_prefix . $item->in_ref_id . '-1';
                break;

            case 'ref_id':
                if ($item->status_prefix !== null || $item->ref_prefix !== null) {
                    $ref_no = @$item->status_prefix . '-' . $item->ref_prefix . $item->ref_id;
                } else {
                    $ref_no = '';
                }
                $html_string = '';
                if ($item->primary_status == 2) {
                    $html_string .= '<a href="' . route('get-completed-draft-invoices', ['id' => $item->id]) . '" title="View Products" id="draf_invoice_' . $item->id . '"><b>' . $ref_no . '</b></a>';
                } elseif ($item->primary_status == 3) {
                    if ($item->ref_id == null) {
                        $ref_no = '-';
                    }
                    $html_string .= $ref_no;
                } elseif ($item->primary_status == 1) {
                    $html_string = '<a href="' . route('get-completed-quotation-products', ['id' => $item->id]) . '" title="View Products"  id="draf_invoice_' . $item->id . '"><b>' . $ref_no . '</b></a>';
                }
                return $html_string;
                break;

            case 'received_date':
                if (!$item->get_order_transactions->isEmpty()) {
                    $count = count($item->get_order_transactions);
                    $html = Carbon::parse(@$item->get_order_transactions[$count - 1]->received_date)->format('d/m/Y');
                    return $html;
                } else {
                    return '--';
                }
                return 'Date';
                break;

            case 'payment_reference_no':
                if (!$item->get_order_transactions->isEmpty()) {
                    $html = '';
                    foreach ($item->get_order_transactions as $key => $ot) {
                        if ($ot->get_payment_ref != null) {
                            if ($key == 0) {
                                $html .= $ot->get_payment_ref->payment_reference_no;
                            } else {
                                $html .= ',' . $ot->get_payment_ref->payment_reference_no;
                            }
                        }
                    }
                    return $html;
                } else {
                    return '--';
                }
                break;

            case 'sales_person':
                return $item->user_id !== null ? @$item->user->name : '--';
                break;

            case 'number_of_products':
                $html_string = $item->order_products->count();
                return $html_string;
                break;

            case 'status':
                $html = '<span class="sentverification">' . @$item->statuses->title . '</span>';
                return $html;
                break;

            case 'memo':
                return @$item->memo != null ? @$item->memo : '--';
                break;

            case 'comment_to_warehouse':
                $warehouse_note = $item->order_warehouse_note;
                return @$warehouse_note != null ? '<span id="short_desc" title="' . @$warehouse_note->note . '">' . @$warehouse_note->note . '</span>' : '--';
                break;

            case 'delivery_date':
                return @$item->delivery_request_date != null ?  Carbon::parse($item->delivery_request_date)->format('d/m/Y') : '--';
                break;

            case 'target_ship_date':
                return @$item->target_ship_date != null ?  Carbon::parse($item->target_ship_date)->format('d/m/Y') : '--';
                break;

            case 'customer_company':
                $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . @$item->customer->company . '</b></a>';
                return $html_string;
                break;

            case 'customer_ref_no':
                $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . @$item->customer_id) . '"><b>' . @$item->customer->reference_number . '</b></a>';
                return $html_string;
                break;

            case 'customer':
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
                break;

            case 'inv_no':
                if ($item->in_status_prefix !== null || $item->in_ref_prefix !== null) {
                    $ref_no = @$item->in_status_prefix . '-' . $item->in_ref_prefix . $item->in_ref_id;
                } else {
                    $ref_no = '';
                }
                $html_string = '<a href="' . route('get-completed-invoices-details', ['id' => $item->id]) . '" title="View Detail"><b>' . $ref_no . '</b></a>';
                return $html_string;
                break;

            case 'checkbox':
                $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                                    <input type="checkbox" class="custom-control-input check1" value="' . $item->id . '" id="quot_' . $item->id . '">
                                    <label class="custom-control-label" for="quot_' . $item->id . '"></label>
                                </div>';
                return $html_string;
                break;

            case 'remark':
                $customer_note = $item->order_customer_note;
                return @$customer_note != null ? '<span id="short_desc" title="' . @$customer_note->note . '">' . @$customer_note->note . '</span>' : '--';
                break;
            case 'tax_id':
                $customer_billing = $item->customer->getbilling->first();
                if ($customer_billing) {
                    return $customer_billing->tax_id != null ? $customer_billing->tax_id : '--';
                }
                break;
            case 'reference_address':
                $customer_billing = $item->customer->getbilling->first();
                if ($customer_billing) {
                    return $customer_billing->title != null ? $customer_billing->title : '--';
                }
                break;
            case 'printed':
                return $item->primary_status == 2 ? (@$item->draft_invoice_pick_instruction_printed != null ? 'Yes' : 'No')
                                                    : (@$item->invoice_proforma_printed != null ? 'Yes' : 'No');
                break;
        }
    }

    public static function returnFilterColumn($column, $item, $keyword)
    {
        switch ($column) {
            case 'ref_id':
                if (strstr($keyword, '-')) {
                    $item->where(function ($q) use ($keyword) {
                        $q->where(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_prefix`,`in_ref_id`)"), 'LIKE', "%" . $keyword . "%");
                        $q->orWhere(DB::raw("CONCAT(`in_status_prefix`,'-',`in_ref_id`)"), 'LIKE', "%" . $keyword . "%")
                        ->orWhere("in_ref_id", 'LIKE', "%" . $keyword . "%")
                        ->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_prefix`,`ref_id`)"), 'LIKE', "%" . $keyword . "%")
                        ->orWhere(DB::raw("CONCAT(`status_prefix`,'-',`ref_id`)"), 'LIKE', "%" . $keyword . "%")
                        ->orWhere("ref_id", 'LIKE', "%" . $keyword . "%");
                    });
                } else {
                    $resultt = preg_replace("/[^0-9]/", "", $keyword);
                    $item->where(function ($q) use ($resultt) {
                        $q->where('in_ref_id', 'LIKE', "%$resultt%")->orWhere('ref_id', 'LIKE', "%$resultt%");
                    });
                }
                break;
            case 'customer':
                $item->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('reference_name', 'LIKE', "%$keyword%");
                });
                break;
            case 'customer_ref_no':
                $item->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('reference_number', 'LIKE', "%$keyword%");
                });
                break;
            case 'sales_person':
                $item->whereHas('user', function ($q) use ($keyword) {
                    $q->where('name', 'LIKE', "%$keyword%");
                });
                break;

            case 'customer_company':
                $item->whereHas('customer', function ($q) use ($keyword) {
                    $q->where('company', 'LIKE', "%$keyword%");
                });
                break;
            case 'sub_total_amount':
                $item->whereHas('order_products', function ($q) use ($keyword) {
                    $q->where('total_price', 'LIKE', "%$keyword%");
                });
                break;
            case 'remark':
                $item->whereHas('order_customer_note', function ($q) use ($keyword) {
                    $q->where('note', 'LIKE', "%$keyword%");
                });
                break;
            case 'comment_to_warehouse':
                $item->whereHas('order_warehouse_note', function ($q) use ($keyword) {
                    $q->where('note', 'LIKE', "%$keyword%");
                });
                break;
            case 'total_amount':
                $item->where('total_amount', 'LIKE', "%$keyword%");
                break;
            case 'memo':
                $item->where('memo', 'LIKE', "%$keyword%");
                break;
            case 'tax_id':
                $item->whereHas('customer', function ($q) use ($keyword) {
                    $q->whereHas('getbilling', function ($q1) use ($keyword) {
                        $q1->where('tax_id', 'LIKE', "%$keyword%");
                    });
                });
                break;
        }
    }

    public static function returnAddColumnAccountReceivable($column, $item)
    {
        switch ($column) {
            case 'delivery_date':
                if (@$item->primary_status == 25) {
                    return 'N.A';
                } else {
                    return $item->delivery_request_date != null ? Carbon::parse(@$item->delivery_request_date)->format('d/m/Y') : 'N.A';
                }
                break;

            case 'invoice_date':
                return $item->converted_to_invoice_on != NULL ? Carbon::parse($item->converted_to_invoice_on)->format('d/m/Y') : "N.A";
                break;

            case 'payment_due_date':
                return $item->payment_due_date != NULL ? Carbon::parse($item->payment_due_date)->format('d/m/Y') : "N.A";
                break;

            case 'amount_due':
                $amount_paid = $item->get_order_transactions->sum('total_received');
                $amount_due = $item->total_amount - $amount_paid;
                return number_format(preg_replace('/(\.\d\d).*/', '$1', $amount_due), 2, '.', ',');
                break;

            case 'amount_paid':
                $amount_paid = $item->get_order_transactions->sum('total_received');
                return number_format(preg_replace('/(\.\d\d).*/', '$1', $amount_paid), 2, '.', ',');
                break;

            case 'total_received_non_vat':
                $due = $item->total_amount - $item->total_paid;
                $due = round($due, 2);
                $vat_total = 0;
                if ($item->order_products != null) {
                    $vat_total = $item->order_products_vat_2->sum('total_price');
                }
                $total = (@$item->primary_status == 25 ? '-' : '') . floatval(preg_replace('/[^\d.]/', '', $vat_total)) - @$item->non_vat_total_paid;
                $total = round($total, 2);
                $disabled = $total == 0 ? "disabled" : "";
                $html = '<input type="number" class="form-control" name="oi_total_received" id="oi_total_received_non_vat_' . $item->id . '" value="' . $total . '" ' . $disabled . ' >';
                return $html;
                break;

            case 'total_received':
                $due = $item->total_amount - $item->total_paid;
                $due = round($due, 2);
                //vat items total
                $vat_total = 0;
                $vat_amount = 0;
                if ($item->order_products != null) {
                    $vat_total = $item->order_products->where('vat', '>', 0)->sum('total_price_with_vat');
                    foreach ($item->order_products as $order) {
                        $vat = round(@$order->total_price_with_vat - @$order->total_price, 2);
                        $vat_amount = $vat_amount + $vat;
                    }
                }
                $total = (@$item->primary_status == 25 ? '-' : '') . (floatval(preg_replace('/[^\d.]/', '', $vat_total))) - @$item->vat_total_paid;
                $total = round($total, 2);
                $disabled = $total == 0 ? "disabled" : "";
                $html = '<input type="number" class="form-control" name="oi_total_received" id="oi_total_received_' . $item->id . '" value="' . $total . '" ' . $disabled . '>';
                return $html;
                break;

            case 'sales_person':
                return $item->user != null ? @$item->user->name : 'N.A';
                break;

            case 'sub_total_amount':
                $sub_amount = $item->order_products ? $item->order_products->sum('total_price') : 0;
                return number_format($sub_amount, 2, '.', ',');
                break;

            case 'invoice_total':
                return $item->total_amount != null ? (@$item->primary_status == 25 ? '-' : '') . number_format(preg_replace('/(\.\d\d).*/', '$1', $item->total_amount), 2, '.', ',') : 'N.A';
                break;

            case 'sub_total_2':
                $total = 0;
                if ($item->order_products != null) {
                    $total = $item->order_products_vat_2->sum('total_price');
                }
                return @$item->order_products != null ? number_format(preg_replace('/(\.\d\d).*/', '$1', $total), 2, '.', ',')  : '--';
                break;

            case 'reference_id_vat_2':
                if (@$item->in_status_prefix !== null) {
                    return $item->in_status_prefix . '-' . @$item->in_ref_prefix . $item->in_ref_id . '-2';
                } elseif (@$item->status_prefix !== null && @$item->primary_status == 25) {
                    return $item->status_prefix . $item->ref_id . '-2';
                } else {
                    return @$item->ref_id != null ? @$item->customer->primary_sale_person->get_warehouse->order_short_code . @$item->customer->CustomerCategory->short_code . @$item->ref_id . '-2' : '--';
                }
                break;

            case 'vat_1':
                $vat_total = 0;
                if ($item->order_products != null) {
                    foreach ($item->order_products as $order) {
                        $vat = round(@$order->total_price_with_vat - @$order->total_price, 2);
                        $vat_total = $vat_total + $vat;
                    }
                }
                return @$item->order_products != null ? number_format(preg_replace('/(\.\d\d).*/', '$1', $vat_total), 2, '.', ',')  : '--';
                break;

            case 'sub_total_1':
                $total = 0;
                $vat_total = 0;
                if ($item->order_products != null) {
                    $total = $item->order_products->where('vat', '>', 0)->sum('total_price_with_vat');
                    foreach ($item->order_products as $order) {
                        $vat = round(@$order->total_price_with_vat - @$order->total_price, 2);
                        $vat_total = $vat_total + $vat;
                    }
                }
                return @$item->order_products != null ? number_format(preg_replace('/(\.\d\d).*/', '$1', round(($total - $vat_total), 2)), 2, '.', ',')  : '--';
                break;

            case 'reference_id_vat':
                if (@$item->in_status_prefix !== null) {
                    return $item->in_status_prefix . '-' . @$item->in_ref_prefix . $item->in_ref_id . '-1';
                } elseif (@$item->status_prefix !== null && @$item->primary_status == 25) {
                    return $item->status_prefix . $item->ref_id . '-1';
                } else {
                    return @$item->ref_id != null ? @$item->customer->primary_sale_person->get_warehouse->order_short_code . @$item->customer->CustomerCategory->short_code . @$item->ref_id . '-1' : '--';
                }
                break;

            case 'customer_company':
                $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . $item->customer->id) . '" title="View Detail"><b>' . ($item->customer !== null ? $item->customer->company : "N.A") . '</b></a>';
                return $html_string;
                break;

            case 'customer_reference_name':
                $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . $item->customer->id) . '" title="View Detail"><b>' . ($item->customer !== null ? $item->customer->reference_name : "N.A") . '</b></a>';
                return $html_string;
                break;

            case 'ref_no':
                if ($item->primary_status == 3) {
                    if ($item->in_status_prefix !== null || $item->in_ref_prefix !== null) {
                        $ref_no = @$item->in_status_prefix . '-' . $item->in_ref_prefix . $item->in_ref_id;
                    } else {
                        $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code . @$item->customer->CustomerCategory->short_code . @$item->ref_id;
                    }
                    $html_string = '<a target="_blank" href="' . route('get-completed-invoices-details', ['id' => $item->id]) . '" title="View Products" ><b>' . @$ref_no . '</b></a>';
                } else if ($item->primary_status == 25) {
                    if ($item->status_prefix !== null || $item->ref_id !== null) {
                        $ref_no = @$item->status_prefix . $item->ref_id;
                    } else {
                        $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code . @$item->customer->CustomerCategory->short_code . @$item->ref_id;
                    }
                    $html_string = '<a target="_blank" href="' . route('get-credit-note-detail', ['id' => $item->id]) . '" title="View Products" ><b>' . @$ref_no . '</b></a>';
                } else {
                    if ($item->status_prefix !== null || $item->ref_prefix !== null) {
                        $ref_no = @$item->in_status_prefix . '-' . $item->in_ref_prefix . $item->in_ref_id;
                    } else {
                        $ref_no = @$item->customer->primary_sale_person->get_warehouse->order_short_code . @$item->customer->CustomerCategory->short_code . @$item->ref_id;
                    }
                    $html_string = '<a target="_blank" href="' . route('get-completed-invoices-details', ['id' => $item->id]) . '" title="View Products" ><b>' . @$ref_no . '</b></a>';
                }

                return $html_string;
                break;

            case 'checkbox':
                $html_string = '<div class="custom-control custom-checkbox custom-checkbox1 d-inline-block">
                            <input type="checkbox" class="custom-control-input check1" value="' . $item->id . '" id="oi_' . $item->id . '">
                            <label class="custom-control-label" for="oi_' . $item->id . '"></label>
                        </div>';
                return $html_string;
                break;
        }
    }
}
