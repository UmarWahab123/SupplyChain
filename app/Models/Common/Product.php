<?php

namespace App\Models\Common;

use App\Models\Common\Currency;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\QuotationConfig;
use Illuminate\Database\Eloquent\Concerns\HasEvents;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\ExportStatus;
use Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Product extends Model
{
    use HasEvents;
    use Notifiable;
    protected $table = 'products';
    protected $with = ["sellingUnits"];

    protected $fillable = ['refrence_code', 'refrence_no', 'hs_code', 'long_desc', 'short_desc', 'buying_unit', 'supplier_id', 'selling_price', 'primary_category', 'category_id', 'type_id', 'brand_id', 'brand', 'product_temprature_c', 'quantity', 'name', 'weight', 'unit_conversion_rate', 'landing', 'freight', 'import_tax_actual', 'vat', 'average_unit_price', 'selling_unit', 'status', 'created_by', 'shipping_address_id', 'billing_address_id', 'product_notes', 'is_cogs_updated', 'last_date_import', 'ecommerce_enabled'];


    public function prouctImages()
    {
        return $this->hasMany('App\Models\Common\ProductImage', 'product_id', 'id');
    }

    public function ecomProuctImage()
    {
        return $this->hasOne('App\Models\Common\ProductImage', 'product_id', 'id')->where('is_enabled', 1);
    }

    public function getDraftPoDetailProduct()
    {
        return $this->belongsTo('App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetail', 'order_product_id', 'id');
    }

    public function def_or_last_supplier()
    {
        return $this->belongsTo('App\Models\Common\Supplier', 'supplier_id', 'id');
    }

    public function supplier_products()
    {
        return $this->hasMany('App\Models\Common\SupplierProducts', 'product_id', 'id')->whereNotNull('supplier_id')->where('is_deleted',0);
    }

    public function warehouse_products()
    {
        return $this->hasMany('App\Models\Common\WarehouseProduct', 'product_id', 'id');
    }

    private static function get_ecom_config_warehouse()
    {
        $checkConfig = QuotationConfig::where('section','ecommerce_configuration')->first();
        $settings = unserialize($checkConfig->print_prefrences);
        $warehouse_id = $settings['status'][5];
        return $warehouse_id;
    }

    public function ecom_warehouse()
    {
        $warehouse_id = Product::get_ecom_config_warehouse();
        return $this->hasMany('App\Models\Common\WarehouseProduct', 'product_id', 'id')->where('warehouse_id', $warehouse_id);
    }
    public function ecom_warehouse_stock()
    {
        $warehouse_id = Product::get_ecom_config_warehouse();
        return $this->hasOne('App\Models\Common\WarehouseProduct', 'product_id', 'id')->where('warehouse_id', $warehouse_id);
    }

    public function default_supplier_products()
    {
        return $this->belongsTo('App\Models\Common\SupplierProducts', 'supplier_id', 'supplier_id')->where('is_deleted',0);
    }

    public function productType()
    {
        return $this->belongsTo('App\Models\Common\ProductType', 'type_id', 'id');
    }
    public function productType2()
    {
        return $this->belongsTo('App\Models\Common\ProductSecondaryType', 'type_id_2', 'id');
    }
    public function productType3()
    {
        return $this->belongsTo('App\ProductTypeTertiary', 'type_id_3', 'id');
    }
    public function productOrigin()
    {
        return $this->belongsTo('App\Models\Common\ProductSecondaryType', 'type_id_2', 'id');
    }

    public function productBrand()
    {
        return $this->belongsTo('App\Models\Common\Brand', 'brand_id', 'id');
    }

    public function productCategory()
    {
        return $this->belongsTo('App\Models\Common\ProductCategory', 'primary_category', 'id');
    }
    public function productRetailCategory()
    {
        return $this->hasOne('App\Models\Common\ProductCategory', 'primary_category', 'id');
    }

    public function productSubCategory()
    {
        return $this->belongsTo('App\Models\Common\ProductCategory', 'category_id', 'id');
    }

    public function customer_type_category_margins()
    {
        return $this->hasMany('App\Models\Common\CustomerTypeCategoryMargin', 'category_id', 'category_id');
    }

    public function customer_type_product_margins()
    {
        return $this->hasMany('App\Models\Common\CustomerTypeProductMargin', 'product_id', 'id');
    }

    public function product_fixed_price()
    {
        return $this->hasMany('App\Models\Common\ProductFixedPrice', 'product_id', 'id');
    }
    public function restaurant_fixed_price()
    {
        return $this->hasOne('App\Models\Common\ProductFixedPrice', 'product_id', 'id')->where('customer_type_id', 1);
    }
    public function hotel_fixed_price()
    {
        return $this->hasOne('App\Models\Common\ProductFixedPrice', 'product_id', 'id')->where('customer_type_id', 2);
    }

    public function units()
    {
        return $this->belongsTo('App\Models\Common\Unit', 'buying_unit', 'id');
    }

    public function stockUnit()
    {
        return $this->belongsTo('App\Models\Common\Unit', 'stock_unit', 'id');
    }

    public function sellingUnits()
    {
        return $this->belongsTo('App\Models\Common\Unit', 'selling_unit', 'id');
    }

    public function buyingUnits()
    {
        return $this->belongsTo('App\Models\Common\Unit', 'buying_unit', 'id');
    }

    public function ecomSellingUnits()
    {
        return $this->belongsTo('App\Models\Common\Unit', 'ecom_selling_unit', 'id');
    }


    public function added_by()
    {
        return $this->belongsTo('App\User', 'created_by', 'id');
    }

    public function get_order_product()
    {
        return $this->hasOne('App\Models\Common\Order\OrderProduct', 'product_id', 'id');
    }

    public function check_import_or_not()
    {
        return $this->hasOne('App\Models\Common\PoGroupProductDetail', 'product_id', 'id')->where('is_review', 1);
    }

    public function order_products()
    {
        return $this->hasMany('App\Models\Common\Order\OrderProduct', 'product_id', 'id');
    }
    public function invoice_products()
    {
        return $this->hasMany('App\Models\Common\Order\OrderProduct', 'product_id', 'id');
    }

    public function product_margins()
    {
        return $this->hasMany('App\Models\Common\CustomerTypeProductMargin', 'product_id', 'id');
    }

    public function po_group_product_detail()
    {
        return $this->hasMany('App\Models\Common\PoGroupProductDetail', 'product_id', 'id');
    }

    public function productCustomerFixedPrice()
    {
        return $this->hasMany('App\Models\Common\ProductCustomerFixedPrice', 'product_id', 'id');
    }

    public function purchaseOrderDetail()
    {
        return $this->hasMany('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'order_product_id', 'id');
    }
    public function getPoData()
    {
        return $this->hasMany('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'product_id', 'id')->whereHas('PurchaseOrder', function ($q) {
            $q->where('status', 14);
        });
    }


    public function stock_out()
    {
        return $this->hasMany('App\Models\Common\StockManagementOut', 'product_id', 'id');
    }

    public function stock_in()
    {
        return $this->hasMany('App\Models\Common\StockManagementIn', 'product_id', 'id')->orderBy('expiration_date', 'asc');
    }

    public function stock_out_start()
    {
        return $this->hasMany('App\Models\Common\StockManagementOut', 'product_id', 'id');
    }
    public function manual_adjustment()
    {
        return $this->hasMany('App\Models\Common\StockManagementOut', 'product_id', 'id')->whereNull('order_id')->whereNull('order_product_id')->whereNull('po_id')->whereNull('p_o_d_id')->whereNull('po_group_id')->whereNull('supplier_id')->whereNull('quantity_in')->whereNotNull('cost');
    }

    public function price_calculate($product, $order)
    {
        $today_date = date('Y-m-d');
        // dd($today_date);
        $ProductCustomerFixedPrice =  ProductCustomerFixedPrice::where('product_id', $product->id)->where('customer_id', $order->customer->id);
        $ProductCustomerFixedPrice = $ProductCustomerFixedPrice->where(function($q) use ($today_date){
            $q->whereDate('expiration_date', '>=', $today_date)->orWhereNull('expiration_date');
        })->first();
        // dd($ProductCustomerFixedPrice);
        if ($ProductCustomerFixedPrice != null && $ProductCustomerFixedPrice->fixed_price > 0) {
            $unit_price = $ProductCustomerFixedPrice->fixed_price;
            $price_date = $product->last_price_updated_date;
            $discount = $ProductCustomerFixedPrice->discount;
            $price_after_discount = $ProductCustomerFixedPrice->price_after_discount;
            return array($unit_price, "Customer Price", $price_date, $discount, $price_after_discount);
        }

        $ProductFixedPrice = ProductFixedPrice::where('product_id', $product->id)->where('customer_type_id', $order->customer->category_id)->first();
        if ($ProductFixedPrice != null && $ProductFixedPrice->fixed_price > 0) {
            $unit_price = $ProductFixedPrice->fixed_price;
            $price_date = $product->last_price_updated_date;
            $discount   = @$order->customer->discount;
            $price_after_discount = $discount != 0 && $discount != null ? round($unit_price * ( (100 - $discount) / 100 ) , 2) : null; 
            return array($unit_price, "Fixed Price", $price_date, $discount, $price_after_discount);
        }

        $CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id', $product->id)->where('customer_type_id', $order->customer->category_id)->first();
        if ($CustomerTypeProductMargin != null) {
            $margin      = $CustomerTypeProductMargin->default_value;
            $marginValue = (($margin / 100) * $product->selling_price);
            $unit_price  = $marginValue + ($product->selling_price);
            $price_date = $product->last_price_updated_date;
            $discount   = @$order->customer->discount;
            $price_after_discount = $discount != 0 && $discount != null ? round($unit_price * ( (100 - $discount) / 100 ) , 2) : null; 
            return array($unit_price, "Reference Price", $price_date, $discount, $price_after_discount);
        }
    }


    public function ecom_price_calculate($product, $order)
    {
        $unit_price = 0;
        $today_date = date("d-m-Y");
        if ($product->ecom_selling_unit) {
            $products_cogs = $product->total_buy_unit_cost * $product->selling_unit_conversion_rate;
        } else {
            $products_cogs = $product->total_buy_unit_cost * $product->unit_conversion_rate;
        }

        if ($product->ecom_selling_unit) {
            $sell_unit = $product->ecom_selling_unit;
        } else {
            $sell_unit = $product->selling_unit;
        }

        $quotation_qry = QuotationConfig::where('section', 'ecommerce_configuration')->first();
        $quotation_config =  unserialize($quotation_qry->print_prefrences);
        if ($quotation_config['status'][8]) {
            $default_currency = $quotation_config['status'][8];
        } else {
            $default_currency = 1;
        }
        $currency = Currency::where('id', $default_currency)->first();

        $ProductCustomerFixedPrice =  ProductCustomerFixedPrice::where('product_id', $product->id)->where('customer_id', $order->customer->id)->first();
        if ($ProductCustomerFixedPrice != null && $ProductCustomerFixedPrice->fixed_price > 0) {
            $unit_price = $ProductCustomerFixedPrice->fixed_price;
            if ($product->discount && $today_date <= $product->discount_expiry_date) {
                $product_price = $ProductCustomerFixedPrice->fixed_price;
                $unit_price = $unit_price - (($product->discount / 100) * $unit_price);
            }
            $price_date  = $product->last_price_updated_date;
            return array($unit_price, "Customer Price", $price_date);
        }

        $ProductFixedPrice = ProductFixedPrice::where('product_id', $product->id)->where('customer_type_id', $order->customer->category_id)->first();
        if ($ProductFixedPrice != null && $ProductFixedPrice->fixed_price > 0) {
            $unit_price = $ProductFixedPrice->fixed_price;
            if ($product->discount && $today_date <= $product->discount_expiry_date) {
                $product_price =  $ProductFixedPrice->fixed_price;
                $unit_price = $unit_price - (($product->discount / 100) * $unit_price);
            }
            $price_date  = $product->last_price_updated_date;
            return array($unit_price, "Fixed Price", $price_date);
        }

        $CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id', $product->id)->where('customer_type_id', $order->customer->category_id)->first();
        if ($CustomerTypeProductMargin != null) {
            if ($product->ecom_selling_unit) {
                $selling_price = $product->unit_price * $product->selling_unit_conversion_rate;
            } else {
                $selling_price = $product->unit_price * $product->unit_conversion_rate;
            }
            $margin      = $CustomerTypeProductMargin->default_value;
            $marginValue = (($margin / 100) * $selling_price);
            $unit_price  = $marginValue + $selling_price;
            if ($product->discount && $today_date <= $product->discount_expiry_date) {
                $product_price = $marginValue + $selling_price;
                $unit_price = $unit_price - (($product->discount / 100) * $unit_price);
            }

            $price_date  = $product->last_price_updated_date;
            return array($unit_price, "Reference Price", $price_date);
        }
    }

    public function buyingUnitCalculation($buying_unit, $selling_unit)
    {
        // checking one by one
        if ($buying_unit == 'Gram' && $selling_unit == 'Gram') {
            $unit_conversion_rate = 1;
        } elseif ($buying_unit == 'Kg' && $selling_unit == 'Kg') {
            $unit_conversion_rate = 1;
        } elseif ($buying_unit == 'Gram' && $selling_unit == 'Kg') {
            $unit_conversion_rate = 1 * 1000;
        } elseif ($buying_unit == 'Kg' && $selling_unit == 'Gram') {
            $unit_conversion_rate = (1 / 1000);
        } else {
            $unit_conversion_rate = 0;
        }

        return $unit_conversion_rate;
    }

    public function sellingUnitCalculation($selling_unit, $buying_unit)
    {
        // checking one by one
        if ($buying_unit == 'Gram' && $selling_unit == 'Gram') {
            $unit_conversion_rate = 1;
        } elseif ($buying_unit == 'Kg' && $selling_unit == 'Kg') {
            $unit_conversion_rate = 1;
        } elseif ($buying_unit == 'Gram' && $selling_unit == 'Kg') {
            $unit_conversion_rate = 1 * 1000;
        } elseif ($buying_unit == 'Kg' && $selling_unit == 'Gram') {
            $unit_conversion_rate = (1 / 1000);
        } else {
            $unit_conversion_rate = 0;
        }

        return $unit_conversion_rate;
    }

    public function checkProductMktForResturant($product_id)
    {
        $product_c_t_p_m_f_rest = CustomerTypeProductMargin::where('product_id', $product_id)->where('is_mkt', 1)->where('customer_type_id', 1)->first();

        if ($product_c_t_p_m_f_rest != null) {
            return true;
        } else {
            return false;
        }
    }

    public function checkProductMktForHotel($product_id)
    {
        $product_c_t_p_m_f_hotel = CustomerTypeProductMargin::where('product_id', $product_id)->where('is_mkt', 1)->where('customer_type_id', 2)->first();

        if ($product_c_t_p_m_f_hotel != null) {
            return true;
        } else {
            return false;
        }
    }

    public function updateOrderProductPriceForMkt($ops, $id, $customer_type_id)
    {
        // dd($customer_type_id);
        $product = Product::where('id', $id)->first();

        if (is_numeric($ops->margin)) {
            $updateOrderProduct = OrderProduct::find($ops->id);
            $updateOrderProduct->exp_unit_cost = $product->selling_price;

            // calculation
            $CustomerTypeProductMargin = CustomerTypeProductMargin::where('product_id', $id)->where('customer_type_id', $customer_type_id)->first();

            if ($CustomerTypeProductMargin != null) {
                $margin      = $CustomerTypeProductMargin->default_value;
                $marginValue = (($margin / 100) * $product->selling_price);
                $unit_price  = $marginValue + ($product->selling_price);
                $total_price = $updateOrderProduct->quantity * $unit_price;
                $total_price_with_vat  = (($product->vat / 100) * $total_price) + $total_price;
            }

            $updateOrderProduct->unit_price           = @$unit_price;
            $updateOrderProduct->total_price          = @$total_price;
            $updateOrderProduct->total_price_with_vat = @$total_price_with_vat;

            $updateOrderProduct->save();

            $subTotalOrder = OrderProduct::where('order_id', $updateOrderProduct->order_id)->get();
            $grand_total = 0;
            foreach ($subTotalOrder as $subtotal) {
                $grand_total = $grand_total + $subtotal->total_price;
            }
            $updateOrderProduct->get_order->update(['total_amount' => $grand_total]);
        }
    }
    public function getDataOfProductMargins($product_id, $category_id, $type)
    {
        if ($type == "custCatMargin") {
            $product = Product::find($product_id);
            $customerTypeCatMargin = CustomerTypeCategoryMargin::where('category_id', $product->category_id)->where('customer_type_id', $category_id)->first();

            if ($customerTypeCatMargin !== null) {
                return $customerTypeCatMargin;
            } else {
                return "N.A";
            }
        } elseif ($type == "custProdMargin") {
            $customerTypeProdMargin = CustomerTypeProductMargin::where('product_id', $product_id)->where('customer_type_id', $category_id)->first();

            if ($customerTypeProdMargin !== null) {
                return $customerTypeProdMargin;
            } else {
                return "N.A";
            }
        } elseif ($type == "prodRefPrice") {
            $prodRefPrice = CustomerTypeProductMargin::where('product_id', $product_id)->where('customer_type_id', $category_id)->first();
            if ($prodRefPrice) {
                $product = Product::find($product_id);
                $calculatedValue = number_format($product->selling_price + ($product->selling_price * ($prodRefPrice->default_value / 100)), 3, '.', ',');
                if ($calculatedValue !== null) {
                    return $calculatedValue;
                } else {
                    return "N.A";
                }
            } else {
                return "N.A";
            }
        } elseif ($type == "last_updated") {
            $prodRefPrice = CustomerTypeProductMargin::where('product_id', $product_id)->where('customer_type_id', $category_id)->first();
            if ($prodRefPrice != null) {
                return $prodRefPrice->last_updated;
            } else {
                return null;
            }
        } elseif ($type == "prodFixPrice") {
            $product_fixed_price = ProductFixedPrice::where('product_id', $product_id)->where('customer_type_id', $category_id)->first();

            if ($product_fixed_price !== null) {
                return $product_fixed_price;
            } else {
                return "N.A";
            }
        } elseif ($type == "mktCheck") {
            $mktCheck = CustomerTypeProductMargin::where('product_id', $product_id)->where('customer_type_id', $category_id)->first();

            if ($mktCheck !== null) {
                return $mktCheck;
            } else {
                return "N.A";
            }
        } elseif ($type == "ecomCogs") {
            $prodRefPrice = CustomerTypeProductMargin::where('product_id', $product_id)->where('customer_type_id', $category_id)->first();
            if ($prodRefPrice) {
                $product = Product::find($product_id);
                $t_b_u_c_p = $product->total_buy_unit_cost_price * floatval($product->selling_unit_conversion_rate);
                $calculatedValue = number_format($t_b_u_c_p + ($t_b_u_c_p * ($prodRefPrice->default_value / 100)), 3, '.', ',');
                if ($calculatedValue !== null) {
                    return $calculatedValue;
                } else {
                    return "N.A";
                }
            } else {
                return "N.A";
            }
        }
    }

    public function getProductSellingPrice($product, $purchasing_thb, $extra_cost_thb, $freight_cost_thb, $landing_cost_thb, $import_tax)
    {
        $total_buying_price = (($import_tax / 100) * $purchasing_thb) + $purchasing_thb;
        $total_buying_price = ($freight_cost_thb) + ($landing_cost_thb) + ($extra_cost_thb) + ($total_buying_price);

        $product->total_buy_unit_cost_price = $total_buying_price;
        // this is selling price
        $total_selling_price = $total_buying_price * $product->unit_conversion_rate;
        return $total_selling_price;
    }

    public function checkItemImportExistance($product_id)
    {
        $checkItemPo = PoGroupProductDetail::join('po_groups', 'po_group_product_details.po_group_id', '=', 'po_groups.id')->where('po_group_product_details.product_id', $product_id)->where('po_groups.is_review', 1)->count('po_group_product_details.id');
        return $checkItemPo;
    }

    public function get_stock($product_id, $warehouse_id)
    {
        $warehouse_product = WarehouseProduct::where('product_id', $product_id)->where('warehouse_id', $warehouse_id)->first();

        return $warehouse_product->available_quantity != null ? number_format($warehouse_product->available_quantity, 3, '.', '') : 0;
    }

    public function purchaseOrderDetailVatIn()
    {
        return $this->hasMany('App\Models\Common\PurchaseOrders\PurchaseOrderDetail', 'product_id', 'id')->whereNotNull('pod_vat_actual_total_price');
    }

    public function getOnWater($id)
    {
        $on_water = Product::join('purchase_order_details', 'products.id', '=', 'purchase_order_details.product_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->where('purchase_order_details.product_id', $id)
            ->where('purchase_orders.status', 14)
            ->sum('purchase_order_details.quantity');

        return $on_water;
    }

    public static function getOnSupplier($id)
    {
        $on_supplier = Product::join('purchase_order_details', 'products.id', '=', 'purchase_order_details.product_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->where('purchase_order_details.product_id', $id)
            ->where('purchase_orders.status', 13)
            ->sum('purchase_order_details.quantity');

        return $on_supplier;
    }

    // public static function getQtyOrdered($id) {
    //     $qty_ordered = Product::join('purchase_order_details','products.id','=','purchase_order_details.product_id')
    //     ->join('purchase_orders','purchase_orders.id','=','purchase_order_details.po_id')
    //     ->where('purchase_order_details.product_id',$id)
    //     ->whereIn('purchase_orders.status', [12,13])
    //     ->sum('purchase_order_details.quantity');

    //     return $qty_ordered;
    // }

    public function getOnAirplane($id)
    {
        $on_airplane = Product::join('purchase_order_details', 'products.id', '=', 'purchase_order_details.product_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->join('po_groups', 'po_groups.id', '=', 'purchase_orders.po_group_id')
            ->join('couriers', 'couriers.id', '=', 'po_groups.courier')
            ->join('courier_types', 'courier_types.id', '=', 'couriers.courier_type_id')
            ->where('purchase_order_details.product_id', $id)
            ->where('purchase_orders.status', 14)
            ->where('courier_types.type', 'Airplane')
            ->sum('purchase_order_details.quantity');

        return $on_airplane;
    }

    public function getOnDomestic($id)
    {
        $on_domestic = Product::join('purchase_order_details', 'products.id', '=', 'purchase_order_details.product_id')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_details.po_id')
            ->join('po_groups', 'po_groups.id', '=', 'purchase_orders.po_group_id')
            ->join('couriers', 'couriers.id', '=', 'po_groups.courier')
            ->join('courier_types', 'courier_types.id', '=', 'couriers.courier_type_id')
            ->where('purchase_order_details.product_id', $id)
            ->where('purchase_orders.status', 14)
            ->where('courier_types.type', 'Domestic Vehicle')
            ->sum('purchase_order_details.quantity');

        return $on_domestic;
    }

    public static function doSort($request, $products)
    {
        if ($request->sortbyvalue == 1) {
            $sort_order     = 'DESC';
        } else {
            $sort_order     = 'ASC';
        }

        if ($request->sortbyparam == 'pf') {
            $sort_variable  = 'refrence_code';
            $products->orderBy($sort_variable, $sort_order);
        }

        if ($request->sortbyparam == 'description') {
            $sort_variable  = 'short_desc';
            $products->orderBy($sort_variable, $sort_order);
        }

        if ($request->sortbyparam == 'supplier') {
            $products->join('suppliers', 'suppliers.id', '=', 'products.supplier_id')->orderBy('suppliers.reference_name', $sort_order);
        }

        if ($request->sortbyparam == 'vat_out') {
            $products->orderBy('vat_amount_total', $sort_order);
        }

        if ($request->sortbyparam == 'sale%') {
            $to_get_totals = (clone $products)->get();
            $total_items_sales = $to_get_totals->sum('sales');
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END /' . $total_items_sales . ')*100'), $sort_order);
        }

        if ($request->sortbyparam == 'vat_in') {
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_vat_in) END)'), $sort_order);
        }
    }

    public static function doSortby($request, $products)
    {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $sort_variable = null;
        if ($request['sortbyparam'] == 1) {
            $sort_variable  = 'refrence_code';
        } elseif ($request['sortbyparam'] == 2) {
            $sort_variable  = 'short_desc';
        } elseif ($request['sortbyparam'] == 5) {
            $sort_variable  = 'QuantityText';
        } elseif ($request['sortbyparam'] == 6) {
            $sort_variable  = 'PiecesText';
        } elseif ($request['sortbyparam'] == 7) {
            $sort_variable  = 'TotalAmount';
        } elseif ($request['sortbyparam'] == 'type') {
            $products->leftJoin('types as t', 't.id', '=', 'products.type_id')->orderBy('t.title', $sort_order);
        } elseif ($request['sortbyparam'] == 'type_2') {
            $products->leftJoin('product_secondary_types as t', 't.id', '=', 'products.type_id_2')->orderBy('t.title', $sort_order);
        }  elseif ($request['sortbyparam'] == 'type_3') {
            $products->leftJoin('product_type_tertiaries as t', 't.id', '=', 'products.type_id_3')->orderBy('t.title', $sort_order);
        } elseif ($request['sortbyparam'] == 'brand') {
            $sort_variable  = 'brand';
        } elseif ($request['sortbyparam'] == 'selling_unit') {
            $products->leftJoin('units as u', 'u.id', '=', 'products.selling_unit')->orderBy('u.title', $sort_order);
        } elseif ($request['sortbyparam'] == 'sub_total') {
            $sort_variable  = 'totalPriceSub';
        } elseif ($request['sortbyparam'] == 'vat_thb') {
            $sort_variable  = 'VatTotalAmount';
        } elseif ($request['sortbyparam'] == 'net_price') {
            $sort_variable  = 'selling_price';
        } elseif ($request['sortbyparam'] == 'total_net_price') {
            $sort_variable  = 'totalCogs';
        } elseif ($request['sortbyparam'] == 'total_net_price') {
            $sort_variable  = 'totalCogs';
        }

        if ($sort_variable != NULL) {
            $products->orderBy($sort_variable, $sort_order);
        }
        return $products;
    }

    public static function MarginReportByProductNameSorting($request, $products, $total_items_sale, $total_items_gp)
    {
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $sort_variable = null;
        if ($request['sortbyparam'] == 1) {
            $sort_variable  = 'sales';
        } elseif ($request['sortbyparam'] == 2) {
            $sort_variable  = 'products_total_cost';
        } elseif ($request['sortbyparam'] == 'margin') {
            $sort_variable  = 'marg';
        } elseif ($request['sortbyparam'] == 'pf') {
            $sort_variable  = 'refrence_code';
        } elseif ($request['sortbyparam'] == 'description') {
            $sort_variable  = 'short_desc';
        } elseif ($request['sortbyparam'] == 'supplier') {
            $products->leftJoin('suppliers as s', 's.id', '=', 'products.supplier_id')->orderBy('s.reference_name', $sort_order);
        } elseif ($request['sortbyparam'] == 'vat_out') {
            $sort_variable  = 'vat_amount_total';
        } elseif ($request['sortbyparam'] == 'unit_cogs') {
            $products->orderBy(\DB::raw('products_total_cost / qty'), $sort_order);
        } elseif ($request['sortbyparam'] == 'qty') {
            $sort_variable  = 'qty';
        }
         elseif ($request['sortbyparam'] == 'percent_sales') {
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END /' . $total_items_sale . ')*100'), $sort_order);
        } elseif ($request['sortbyparam'] == 'vat_in') {
            $sort_variable  = 'vat_in';
        }
        elseif($request['sortbyparam'] == 'gp')
        {
            $products->orderBy(\DB::raw('(CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END) - (SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped) END))'), $sort_order);
        }
        elseif($request['sortbyparam'] == 'percent_gp')
        {
            $products->orderBy(\DB::raw('((CASE WHEN o.primary_status="3" THEN SUM(op.total_price) END) - (SUM(CASE
            WHEN o.primary_status="3" THEN (op.actual_cost * op.qty_shipped) END))) /'.$total_items_gp.'*100'), $sort_order);
        }

        if ($sort_variable != null) {
            $products->orderBy($sort_variable, $sort_order);
        }
        return $products;
    }

    public static function ProductListingSorting($request, $query, $getWarehouses, $getCategories, $getCategoriesSuggested, $not_visible_arr)
    {
        $query->getQuery()->orders = null;
        $sort_order = $request['sortbyvalue'] == 1 ? 'DESC' : 'ASC';
        $inc = strpos($request['sortbyparam'], '-') == true ? explode('-', $request['sortbyparam'])[2] : $request['sortbyparam'];

        if ($request['sortbyparam'] == 2) {
            $query->join('types', 'types.id', '=', 'products.type_id')->orderBy('refrence_code', $sort_order)->orderBy('types.title', 'ASC');
        } elseif ($request['sortbyparam'] == 5) {
            $query->join('types', 'types.id', '=', 'products.type_id')->orderBy('short_desc', $sort_order)->orderBy('products.brand', $sort_order)->orderBy('types.title', 'ASC');
        } elseif ($request['sortbyparam'] == 6) {
            $query->orderBy('product_notes', $sort_order);
        } elseif ($request['sortbyparam'] == 7) {
            $query->orderBy('product_note_3', $sort_order);
        } elseif ($request['sortbyparam'] == 9) {
            $query->join('types', 'types.id', '=', 'products.type_id')->orderBy('types.title', $sort_order);
        } elseif ($request['sortbyparam'] == 11) {
            $query->join('product_secondary_types', 'product_secondary_types.id', '=', 'products.type_id_2')->orderBy('product_secondary_types.title', $sort_order);
        } elseif ($request['sortbyparam'] == 12) {
            $query->join('product_type_tertiaries', 'product_type_tertiaries.id', '=', 'products.type_id_3')->orderBy('product_type_tertiaries.title', $sort_order);
        } elseif ($request['sortbyparam'] == 10) {
            $query->join('types', 'types.id', '=', 'products.type_id')->orderBy('brand', $sort_order)->orderBy('products.short_desc', $sort_order)->orderBy('types.title', 'ASC');
        } elseif ($request['sortbyparam'] == 14) {
            $query->join('suppliers', 'suppliers.id', '=', 'products.supplier_id')->join('types', 'types.id', '=', 'products.type_id')->orderBy('suppliers.reference_name', $sort_order)->orderBy('types.title', 'ASC');
        } elseif ($request['sortbyparam'] == 15) {
            $query->leftJoin('supplier_products', function ($join) {
                $join->on('supplier_products.supplier_id', '=', 'products.supplier_id');
                $join->on('supplier_products.product_id', '=', 'products.id');
            })->orderBy('supplier_products.supplier_description', $sort_order);
        } elseif ($request['sortbyparam'] == 'supplier_country') {
            $query->leftJoin('suppliers as s', 's.id', '=', 'products.supplier_id')->join('countries as c', 'c.id', '=', 's.country')->orderBy('c.name', $sort_order);
        } elseif ($request['sortbyparam'] == 'hs_description') {
            $query->orderBy('products.hs_description', $sort_order);
        } elseif ($request['sortbyparam'] == 'category_id') {
            $query->leftJoin('product_categories as pc', 'pc.id', '=', 'products.primary_category')->orderBy('pc.title', $sort_order);
        } elseif ($request['sortbyparam'] == 'billed_unit') {
            $query->leftJoin('units as u', 'u.id', '=', 'products.buying_unit')->orderBy('u.title', $sort_order);
        } elseif ($request['sortbyparam'] == 'selling_unit') {
            $query->leftJoin('units as u', 'u.id', '=', 'products.selling_unit')->orderBy('u.title', $sort_order);
        } elseif ($request['sortbyparam'] == 'temprature_c') {
            $query->orderBy(\DB::Raw('products.product_temprature_c+0'), $sort_order);
        }elseif ($request['sortbyparam'] == 'extra_tax') {
            $query->leftJoin('supplier_products', function ($join) {
                $join->on('supplier_products.supplier_id', '=', 'products.supplier_id');
                $join->on('supplier_products.product_id', '=', 'products.id');
            })->orderBy('supplier_products.extra_tax', $sort_order);
        }elseif ($request['sortbyparam'] == 'import_tax_book') {
            $query->orderBy('products.import_tax_book', $sort_order);
        } elseif ($request['sortbyparam'] == 'vat') {
            $query->orderBy('products.vat', $sort_order);
        } elseif ($request['sortbyparam'] == 'supplier_ref_no') {
            $query->leftJoin('supplier_products', function ($join) {
                $join->on('supplier_products.supplier_id', '=', 'products.supplier_id');
                $join->on('supplier_products.product_id', '=', 'products.id');
            })->orderBy('supplier_products.product_supplier_reference_no', $sort_order);
        } elseif ($request['sortbyparam'] == 'purchasing_price_eur') {
            $query->leftJoin('supplier_products', function ($join) {
                $join->on('supplier_products.supplier_id', '=', 'products.supplier_id');
                $join->on('supplier_products.product_id', '=', 'products.id');
            })->orderBy('supplier_products.buying_price', $sort_order);
        } elseif ($request['sortbyparam'] == 'purchasing_price_thb') {
            $query->leftJoin('supplier_products', function ($join) {
                $join->on('supplier_products.supplier_id', '=', 'products.supplier_id');
                $join->on('supplier_products.product_id', '=', 'products.id');
            })->orderBy('supplier_products.buying_price_in_thb', $sort_order);
        } elseif ($request['sortbyparam'] == 'freight') {
            $query->leftJoin('supplier_products', function ($join) {
                $join->on('supplier_products.supplier_id', '=', 'products.supplier_id');
                $join->on('supplier_products.product_id', '=', 'products.id');
            })->orderBy('supplier_products.freight', $sort_order);
        } elseif ($request['sortbyparam'] == 'landing') {
            $query->leftJoin('supplier_products', function ($join) {
                $join->on('supplier_products.supplier_id', '=', 'products.supplier_id');
                $join->on('supplier_products.product_id', '=', 'products.id');
            })->orderBy('supplier_products.landing', $sort_order);
        } elseif ($request['sortbyparam'] == 'cost_price') {
            $query->orderBy('products.total_buy_unit_cost_price', $sort_order);
        } elseif ($request['sortbyparam'] == 'unit_conversion_rate') {
            $query->orderBy('products.unit_conversion_rate', $sort_order);
        } elseif ($request['sortbyparam'] == 'net_price_per_unit') {
            $query->orderBy('products.selling_price', $sort_order);
        } elseif ($request['sortbyparam'] == 'weight') {
            $query->orderBy(\DB::Raw('products.weight+0'), $sort_order);
        } elseif ($request['sortbyparam'] == 'lead_time') {
            $query->leftJoin('supplier_products', function ($join) {
                $join->on('supplier_products.supplier_id', '=', 'products.supplier_id');
                $join->on('supplier_products.product_id', '=', 'products.id');
            })->orderBy('supplier_products.leading_time', $sort_order);
        } elseif ($request['sortbyparam'] == 'last_update_price') {
            $query->orderBy('products.last_price_updated_date', $sort_order);
        } elseif ($request['sortbyparam'] == 'total_visible_stock') {
            $warehouse_ids = [];
            $start = 49;
            foreach ($getWarehouses as $warehouse) {
                if (!in_array($start, $not_visible_arr)) {
                    array_push($warehouse_ids, $warehouse->id);
                }
                $start += 3;
            }
            $query->leftJoin('warehouse_products', function ($join) {
                $join->on('warehouse_products.product_id', '=', 'products.id');
            })->whereIn('warehouse_products.warehouse_id', $warehouse_ids)->orderBy(\DB::Raw('SUM(warehouse_products.current_quantity)+0'), $sort_order)->groupBy('products.id');
        } elseif ($request['sortbyparam'] == 'on_water') {
            $query->leftJoin('purchase_order_details', function ($join) use ($sort_order) {
                $join->on('products.id', '=', 'purchase_order_details.product_id')
                    ->join('purchase_orders', function ($join) {
                        $join->on('purchase_orders.id', '=', 'purchase_order_details.po_id')->where('status', 14);
                    });
            })->orderBy(\DB::Raw('SUM(purchase_order_details.quantity)+0'), $sort_order)->groupBy('products.id');

            // $query->leftJoin('purchase_order_details','products.id','=','purchase_order_details.product_id')
            // ->leftJoin('purchase_orders','purchase_orders.id','=','purchase_order_details.po_id')
            // ->where('purchase_orders.status', 14)
            // ->orderBy(\DB::Raw('SUM(purchase_order_details.quantity)+0'), $sort_order)
            // ->groupBy('products.id');
        } elseif ($request['sortbyparam'] == 'on_supplier') {
            $query->leftJoin('purchase_order_details', function ($join) use ($sort_order) {
                $join->on('products.id', '=', 'purchase_order_details.product_id')
                    ->join('purchase_orders', function ($join) {
                        $join->on('purchase_orders.id', '=', 'purchase_order_details.po_id')->where('status', 13);
                    });
            })->orderBy(\DB::Raw('SUM(purchase_order_details.quantity)+0'), $sort_order)->groupBy('products.id');
            // $query->leftJoin('purchase_order_details','products.id','=','purchase_order_details.product_id')
            // ->leftJoin('purchase_orders','purchase_orders.id','=','purchase_order_details.po_id')
            // ->where('purchase_orders.status', 13)->orWhereNull('purchase_orders.status')
            // ->orderBy(\DB::Raw('SUM(purchase_order_details.quantity)+0'), $sort_order)
            // ->groupBy('products.id');

            // $query->whereHas('purchaseOrderDetail',function($pod) use ($sort_order){
            //     $pod->whereHas('PurchaseOrder',function($po) use ($sort_order){
            //         $po->where('status',13);
            //     })->orderBy(\DB::Raw('SUM(purchase_order_details.quantity)+0'), $sort_order);
            // });
        } elseif ($inc >= 49) {
            if ($request['sortbyparam'] == $inc) {
                $query->leftJoin('warehouse_products', 'warehouse_products.product_id', '=', 'products.id')->where('warehouse_id', $request['data_id'])->orderBy(\DB::Raw('round(warehouse_products.current_quantity,3)'), $sort_order);
            } elseif ($request['sortbyparam'] == 'available-qty-' . $inc) {
                $query->leftJoin('warehouse_products', 'warehouse_products.product_id', '=', 'products.id')->where('warehouse_id', $request['data_id'])->orderBy(\DB::Raw('round(warehouse_products.available_quantity,3)'), $sort_order);
            } elseif ($request['sortbyparam'] == 'reserve-qty-' . $inc) {
                $query->leftJoin('warehouse_products', 'warehouse_products.product_id', '=', 'products.id')->where('warehouse_id', $request['data_id'])->orderBy(\DB::Raw('round(warehouse_products.reserved_quantity,3)'), $sort_order);
            } elseif ($request['sortbyparam'] == 'fixed-price-' . $inc) {
                $query->leftJoin('product_fixed_prices as pfp', 'pfp.product_id', '=', 'products.id')->where('customer_type_id', $request['data_id'])->orderBy(\DB::Raw('round(pfp.fixed_price,3)'), $sort_order);
            } elseif ($request['sortbyparam'] == 'suggested-price-' . $inc) {
                $query->leftJoin('customer_type_product_margins as c', 'c.product_id', '=', 'products.id')->where('customer_type_id', $request['data_id'])->orderBy(\DB::Raw('round(products.selling_price+(products.selling_price * (c.default_value/100)),3)'), $sort_order);
            }
        } else {
            $query->orderBy('products.short_desc', 'ASC');
            $query->orderBy('products.brand', 'ASC');
        }
        return $query;
    }

    public static function returnAddColumn($column, $item, $not_visible_arr, $getWarehouses)
    {
        switch ($column) {
            case 'checkbox':
                $html_string = '<div class="custom-control custom-checkbox d-inline-block">
                <input type="checkbox" class="custom-control-input check" value="' . $item->id . '" id="product_check_' . $item->id . '">
                <label class="custom-control-label" for="product_check_' . $item->id . '"></label>
                </div>';
                return $html_string;
                break;

            case 'action':
                $html_string = '
                <a href="' . url('get-product-detail/' . $item->id) . '" class="actionicon editIcon text-center" title="View Detail"><i class="fa fa-eye"></i></a>
                ';
                return $html_string;
                break;

            case 'category_id':
                if (in_array('5', $not_visible_arr))
                    return '--';
                $html_string = '<span class="m-l-15 inputDoubleClick" id="category_id" data-fieldvalue="' . @$item->category_id . '" data-id="cat ' . @$item->category_id . ' ' . @$item->id . '"> ';
                $html_string .= ($item->primary_category != null) ? $item->productCategory->title . ' / ' . $item->productSubCategory->title : "--";
                $html_string .= '</span>';

                $html_string .= '<div class="incomplete-filter d-none inc-fil-cat">
                <select class="font-weight-bold form-control-lg form-control js-states state-tags select-common category_id categories_select' . @$item->id . '" name="category_id" required="true">';
                $html_string .= '</select></div>';
                return $html_string;
                break;

            case 'buying_unit':
                if (in_array('10', $not_visible_arr))
                    return '--';
                $text_color = $item->buying_unit == null ? 'color: red;' : '';
                $buy_unit = $item->buying_unit != null ? @$item->units->title : 'Select';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="buying_unit" style="' . $text_color . '"  data-fieldvalue="' . @$item->units->title . '" data-id="buying_unit ' . @$item->buying_unit . ' ' . @$item->id . '">';
                $html_string .= @$buy_unit;
                $html_string .= '</span>';

                $html_string .= '<select name="buying_unit" class="select-common form-control buying-unit d-none buying_select' . @$item->id . '">';
                $html_string .= '</select>';
                $html_string .= '<input type="text" name="buying_unit" class="fieldFocus d-none" value="' . @$item->units->title . '">';
                return $html_string;
                break;

            case 'selling_unit':
                if (in_array('11', $not_visible_arr))
                    return '--';
                $text_color = $item->selling_unit == null ? 'color: red;' : '';
                $sell_unit = $item->selling_unit != null ? @$item->sellingUnits->title : 'Select';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="selling_unit" style="' . $text_color . '" data-fieldvalue="' . @$item->sellingUnits->title . ' ' . @$item->id . '" data-id="selling_unit ' . @$item->selling_unit . ' ' . @$item->id . '">';
                $html_string .= $sell_unit;
                $html_string .= '</span>';

                $html_string .= '<select name="selling_unit" class="select-common form-control selling_unit' . @$item->id . ' buying-unit d-none">';
                $html_string .= '</select>';
                $html_string .= '<input type="text" name="selling_unit" class="fieldFocus d-none" value="' . @$item->sellingUnits->title . '">';
                return $html_string;
                break;

            case 'import_tax_book':
                if (in_array('18', $not_visible_arr))
                    return '--';
                $impot_tax_book_item = $item->import_tax_book == null ? '--' : $item->import_tax_book;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="import_tax_book" data-fieldvalue="' . @$item->import_tax_book . '">';
                $html_string .= $impot_tax_book_item;
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="import_tax_book" style="width:100%;" class="fieldFocus d-none" value="' . $item->import_tax_book . '">';
                return $html_string;
                break;

            case 'vat':
                if (in_array('19', $not_visible_arr))
                    return '0';
                $vat = $item->vat !== null ? $item->vat . ' %' : "0 %";
                return $vat;
                break;

            case 'on_water':
                if (in_array('34', $not_visible_arr))
                    return '--';
                $on_water = $item->getOnWater($item->id);
                $on_water = ($on_water != null) ? $on_water : 0;
                $decimal = $item->units->decimal_places;
                if ($decimal != null) {
                    $on_water = number_format($on_water, $decimal, '.', '');
                } else {
                    $on_water = number_format($on_water, 3, '.', '');
                }
                $request_array = '';
                $request_array .= $item->id;
                $request_array .= ',' . 'on-water';
                $type = 'list';
                if ($on_water != 0 && $on_water != null && $on_water != '0') {
                    $html_string = '<a target="_blank" href="' . url('purchasing-report/' . $request_array . '/' . $type) . '" style="cursor:pointer" title="View Detail"><b>' . $on_water . '</b></a>';
                } else {
                    $html_string = $on_water;
                }
                return $html_string;
                break;

            case 'image':
                if (in_array('9', $not_visible_arr))
                    return '--';
                $product_images = $item->prouctImages->count();
                $html_string = '<div class="d-flex justify-content-center text-center">';
                if ($product_images > 0) {
                    $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#images-modal" data-id="' . $item->id . '" class="fa fa-eye d-block show-prod-image mr-2" title="View Images"></a> ';
                }
                if ($product_images < 4) {
                    $html_string .= '<a href="javascript:void(0)" data-toggle="modal" data-target="#productImagesModal" class="img-uploader fa fa-plus d-block" title="Add Images"></a><input type="hidden" id="images_count_' . $item->id . '" value="' . $product_images . '"></div>';
                }
                return $html_string;
                break;

            case 'supplier_id':
                if (in_array('20', $not_visible_arr))
                    return '--';
                return (@$item->supplier_id != null) ? @$item->def_or_last_supplier->reference_name : '--';
                break;

            case 'p_s_reference_number':
                if (in_array('4', $not_visible_arr))
                    return '--';
                $getProductDefaultSupplier = $item->supplier_products->where('supplier_id', @$item->supplier_id)->first();
                $html_string = '';
                if ($getProductDefaultSupplier) {
                    $prod_def_sup = $getProductDefaultSupplier->product_supplier_reference_no == null ? '--' : $getProductDefaultSupplier->product_supplier_reference_no;
                    $html_string .= '
                    <span class="m-l-15 inputDoubleClick" id="product_supplier_reference_no"  data-fieldvalue="' . @$getProductDefaultSupplier->product_supplier_reference_no . '">';
                    $html_string .= $prod_def_sup;
                    $html_string .= '</span>';

                    $html_string .= '<input type="text" style="width:100%;" name="product_supplier_reference_no" class="fieldFocus d-none" value="' . $getProductDefaultSupplier->product_supplier_reference_no . '">';
                }
                return $html_string;
                break;

            case 'supplier_description':
                if (in_array('22', $not_visible_arr))
                    return '--';
                if ($item->supplier_id !== 0) {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id', @$item->supplier_id)->first();
                    $html_string = '';
                    if ($getProductDefaultSupplier) {
                        $html_string = '<span class="m-l-15 inputDoubleClick" id="supplier_description"  data-fieldvalue="' . @$getProductDefaultSupplier->supplier_description . '">' . ($getProductDefaultSupplier->supplier_description != NULL ? $getProductDefaultSupplier->supplier_description : "--") . '</span>';
                        $desc = htmlspecialchars($getProductDefaultSupplier->supplier_description);
                        $html_string .= '<input type="text" style="width:100%;" name="supplier_description" class="fieldFocus d-none" value="' . $desc . '">';
                    }
                    return $html_string;
                } else {
                    return "--";
                }
                break;
            case 'supplier_country':
                if (in_array('21', $not_visible_arr))
                    return '--';
                if ($item->supplier_id !== 0) {
                    $country = $item->def_or_last_supplier->getcountry;
                    $html_string = '';
                    if ($item->def_or_last_supplier->getcountry) {
                        $html_string = '<span class="m-l-15" id="supplier_country"  data-fieldvalue="' . @$country->name . '">' . ($country->name != NULL ? $country->name : "--") . '</span>';
                        $desc = htmlspecialchars($country->name);
                    }
                    return $html_string;
                } else {
                    return "--";
                }
                break;

            case 'freight':
                if (in_array('25', $not_visible_arr))
                    return '--';
                if ($item->supplier_id != 0) {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id', $item->supplier_id)->first();
                    $html_string = '';
                    if ($getProductDefaultSupplier) {
                        $html_string = '<span class="m-l-15 inputDoubleClick" id="freight"  data-fieldvalue="' . @$getProductDefaultSupplier->freight . '">' . ($getProductDefaultSupplier->freight != NULL ? $getProductDefaultSupplier->freight : "--") . '</span>
                    <input type="text" style="width:100%;" name="freight" class="fieldFocus d-none" value="' . @$getProductDefaultSupplier->freight . '">';
                    }
                    return $html_string;
                } else {
                    return "--";
                }
                break;

            case 'landing':
                if (in_array('26', $not_visible_arr))
                    return '--';
                if ($item->supplier_id != 0) {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id', $item->supplier_id)->first();
                    $html_string = '';
                    if ($getProductDefaultSupplier) {
                        $html_string = '<span class="m-l-15 inputDoubleClick" id="landing"  data-fieldvalue="' . @$getProductDefaultSupplier->landing . '">' . ($getProductDefaultSupplier->landing != NULL ? $getProductDefaultSupplier->landing : "--") . '</span>
                    <input type="text" style="width:100%;" name="landing" class="fieldFocus d-none" value="' . @$getProductDefaultSupplier->landing . '">';
                    }
                    return $html_string;
                } else {
                    return "--";
                }
                break;

            case 'vendor_price':
                if (in_array('23', $not_visible_arr))
                    return '--';
                if ($item->supplier_id != 0) {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id', @$item->supplier_id)->first();
                    $html_string = '';
                    if ($getProductDefaultSupplier) {
                        if ($getProductDefaultSupplier->buying_price !== null) {
                            $supplier_currency_logo = @$getProductDefaultSupplier->supplier->getCurrency->currency_symbol;
                        } else {
                            $supplier_currency_logo = '';
                        }

                        $formated_value = number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '');

                        if ($getProductDefaultSupplier->buying_price !== null) {
                            $text_color = '';
                        } else {
                            $text_color = 'color: red;';
                        }

                        $html_string = '<span class="m-l-15 inputDoubleClick" style="' . $text_color . '" id="buying_price"  data-fieldvalue="' . @$getProductDefaultSupplier->buying_price . '">' . ($getProductDefaultSupplier->buying_price !== NULL ?  ' <b>' . @$supplier_currency_logo . '</b> ' . number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '') : "--") . '</span>
                    <input type="text" style="width:100%;" name="buying_price" class="fieldFocus d-none" value="' . number_format((float)@$getProductDefaultSupplier->buying_price, 3, '.', '') . '">';
                    }
                    return $html_string;
                } else {
                    return "--";
                }
                break;

            case 'vendor_price_in_thb':
                if (in_array('24', $not_visible_arr))
                    return '--';
                if ($item->supplier_id != 0) {
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id', $item->supplier_id)->first();
                    $formated_value = number_format((float)@$getProductDefaultSupplier->buying_price_in_thb, 3, '.', '');
                    return (@$getProductDefaultSupplier->buying_price_in_thb != null) ? $formated_value : '--';
                }
                break;

            case 'total_buy_unit_cost_price':
                if (in_array('27', $not_visible_arr))
                    return '--';
                return (@$item->total_buy_unit_cost_price != null) ? number_format((float)@$item->total_buy_unit_cost_price, 3, '.', '') : '--';
                break;

            case 'total_visible_stock':
                if (in_array('33', $not_visible_arr))
                    return '--';
                $visible = 0;
                $start = 49;
                foreach ($getWarehouses as $warehouse) {
                    if (!in_array($start, $not_visible_arr)) {
                        $warehouse_product = $item->warehouse_products->where('warehouse_id', $warehouse->id)->first();
                        $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity : 0;
                        $visible += $qty;
                    }

                    $start += 3;
                }

                $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
                return number_format($visible, $decimal_places, '.', ',');
                break;

            case 'last_price_history':
                if (in_array('32', $not_visible_arr))
                    return '--';
                return $item->last_price_updated_date == null ? '--' : Carbon::parse($item->last_price_updated_date)->format('d/m/Y');
                break;

            case 'unit_conversion_rate':
                if (in_array('28', $not_visible_arr))
                    return '--';
                $text_color = $item->unit_conversion_rate == null ? 'color: red;' : '';
                $ucr = $item->unit_conversion_rate == null ? '--' : $item->unit_conversion_rate;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="unit_conversion_rate" style="' . $text_color . '" data-fieldvalue="' . number_format((float)@$item->unit_conversion_rate, 3, '.', '') . '">';
                $html_string .= ($ucr == '--' ? $ucr : number_format((float)@$ucr, 3, '.', ''));
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="unit_conversion_rate" style="width: 80%;" class="fieldFocus d-none" value="' . number_format((float)@$item->unit_conversion_rate, 3, '.', '') . '">';
                return $html_string;
                break;

            case 'selling_unit_cost_price':
                if (in_array('29', $not_visible_arr))
                    return '--';
                $html_string = '
                    <span class="m-l-15" id="selling_price"  data-fieldvalue="' . @$item->selling_price . '">';
                $html_string .= ($item->selling_price == null ? '--' : number_format((float)@$item->selling_price, 3, '.', ''));
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="selling_price" class="fieldFocus d-none" value="' . number_format((float)@$item->selling_price, 3, '.', '') . '">';
                return $html_string;
                break;

            case 'weight':
                if (in_array('30', $not_visible_arr))
                    return '--';
                $weight = $item->weight == null ? '--' : $item->weight;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="weight"  data-fieldvalue="' . @$item->weight . '">';
                $html_string .= $weight;
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="weight" style="width: 100%;" class="fieldFocus d-none" value="' . $item->weight . '">';
                return $html_string;
                break;

            case 'lead_time':
                if (in_array('31', $not_visible_arr))
                    return '--';
                if ($item->supplier_id == 0)
                    return "--";
                $getProductDefaultSupplier = $item->supplier_products->where('supplier_id', $item->supplier_id)->first();
                $html_string = '';
                if ($getProductDefaultSupplier) {
                    if ($getProductDefaultSupplier->leading_time !== null) {
                        $text_color = '';
                    } else {
                        $text_color = 'color: red;';
                    }

                    $html_string = '<span class="m-l-15 inputDoubleClick" style="' . $text_color . '" id="leading_time"  data-fieldvalue="' . @$getProductDefaultSupplier->leading_time . '">' . ($getProductDefaultSupplier->leading_time != NULL ? $getProductDefaultSupplier->leading_time : "--") . '</span>
                  <input type="text" style="width:100%;" name="leading_time" class="fieldFocus d-none" value="' . @$getProductDefaultSupplier->leading_time . '">';
                }
                return $html_string;
                break;

            case 'product_type':
                if (in_array('12', $not_visible_arr))
                    return '--';
                $text_color = $item->type_id == null ? 'color: red;' : '';
                $prod_type = $item->type_id != null ? @$item->productType->title : '--';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type" style="' . $text_color . '" data-fieldvalue="' . @$item->type_id . '" data-id="type ' . @$item->type_id . ' ' . @$item->id . '">';
                $html_string .= @$prod_type;
                $html_string .= '</span>';
                $html_string .= '<select name="type_id" class="select-common form-control product_type d-none type_select' . @$item->id . '">';
                $html_string .= '</select>';
                return $html_string;
                break;

            case 'product_type_2':
                if (in_array('13', $not_visible_arr))
                    return '--';
                $text_color = $item->type_id_2 == null ? '' : '';
                $prod_type = $item->type_id_2 != null ? @$item->productType2->title : '--';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type_2" style="' . $text_color . '" data-fieldvalue="' . @$item->type_id_2 . '" data-id="type_2 ' . @$item->type_id_2 . ' ' . @$item->id . '">';
                $html_string .= @$prod_type;
                $html_string .= '</span>';
                $html_string .= '<select name="type_id_2" class="select-common form-control product_type d-none type_select_2' . @$item->id . '">';
                $html_string .= '</select>';
                return $html_string;
                break;

            case 'product_type_3':
                if (in_array('14', $not_visible_arr))
                    return 'avsd';
                $text_color = $item->type_id_3 == null ? '' : '';
                $prod_type = $item->type_id_3 != null ? $item->productType3->title : '--';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_type_3" style="' . $text_color . '" data-fieldvalue="' . @$item->type_id_3 . '" data-id="type_3 ' . @$item->type_id_3 . ' ' . @$item->id . '">';
                $html_string .= @$prod_type;
                $html_string .= '</span>';
                $html_string .= '<select name="type_id_3" class="select-common form-control product_type d-none type_select_2' . @$item->id . '">';
                $html_string .= '</select>';
                return $html_string;
                break;

            case 'brand':
                if (in_array('15', $not_visible_arr))
                    return '--';
                $brand = $item->brand == null ? '--' : $item->brand;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="brand"  data-fieldvalue="' . @$item->brand . '">';
                $html_string .= $brand;
                $html_string .= '</span>';

                $html_string .= '<input type="text" style="width:100%;" name="brand" class="fieldFocus d-none" value="' . $item->brand . '">';
                return $html_string;
                break;

            case 'product_temprature_c':
                if (in_array('16', $not_visible_arr))
                    return '--';
                $prod_temp_c = $item->product_temprature_c == null ? '--' : $item->product_temprature_c;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_temprature_c"  data-fieldvalue="' . @$item->product_temprature_c . '">';
                $html_string .= $prod_temp_c;
                $html_string .= '</span>';

                $html_string .= '<input type="number" style="width:100%;" name="product_temprature_c" class="fieldFocus d-none" value="' . $item->product_temprature_c . '">';
                return $html_string;
                break;

            case 'extra_tax':
                if (in_array('17', $not_visible_arr))
                    return '--';
                    $getProductDefaultSupplier = $item->supplier_products->where('supplier_id', @$item->supplier_id)->first();
                    if ($getProductDefaultSupplier) {
                        return $getProductDefaultSupplier->extra_tax != null ? number_format((float)@$getProductDefaultSupplier->extra_tax,3,'.','') : '--';
                    }
                    return '--';
                break;

            case 'title':
                if (in_array('36', $not_visible_arr))
                    return '--';
                $name = $item->name == null ? '--' : $item->name;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="short_desc"  data-fieldvalue="' . @$item->name . '">';
                $html_string .= $name;
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="name" style="width: 100%;" class="fieldFocus d-none" value="' . $item->name . '">';
                return $html_string;
                break;

            case 'min_order_qty':
                if (in_array('37', $not_visible_arr))
                    return '--';
                $min_o_qty = $item->min_o_qty == null ? '--' : $item->min_o_qty;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="min_o_qty' . $item->id . '"  data-fieldvalue="' . @$item->min_o_qty . '">';
                $html_string .= $min_o_qty;
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="min_o_qty" style="width: 100%;" class="fieldFocus d-none" value="' . $item->min_o_qty . '">';
                return $html_string;
                break;

            case 'max_order_qty':
                if (in_array('38', $not_visible_arr))
                    return '--';
                $max_o_qty = $item->max_o_qty == null ? '--' : $item->max_o_qty;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="max_o_qty' . $item->id . '"  data-fieldvalue="' . @$item->max_o_qty . '">';
                $html_string .= $max_o_qty;
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="max_o_qty" style="width: 100%;" class="fieldFocus d-none" value="' . $item->max_o_qty . '">';
                return $html_string;
                break;

            case 'dimension':
                if (in_array('39', $not_visible_arr))
                    return '--';
                $length = $item->length != null ? $item->length : '--';
                $length_html = '<span id="short_desc">
                <span class="m-l-15 inputDoubleClick"  data-fieldvalue="' . @$item->length . '">';
                $length_html .= $length;
                $length_html .= '</span>';

                $length_html .= '<input type="text"  name="length" style="width: 20%;" class="fieldFocus d-none" value="' . $item->length . '"> cm';

                $length_html .= '<span class="ml-2 mr-2"> x </span>';
                $width = $item->width != null ? $item->width : '--';
                $length_html .= '
                <span class="m-l-15 inputDoubleClick"  data-fieldvalue="' . @$item->width . '">';
                $length_html .= $width;
                $length_html .= '</span>';

                $length_html .= '<input type="text"  name="width" style="width: 20%;" class="fieldFocus d-none" value="' . $item->width . '"> cm';

                $length_html .= '<span class="ml-2 mr-2"> x </span>';
                $height = $item->height != null ? $item->height : '--';
                $length_html .= '
                <span class="m-l-15 inputDoubleClick"  data-fieldvalue="' . @$item->height . '">';
                $length_html .= $height;
                $length_html .= '</span>';

                $length_html .= '<input type="text"  name="height" style="width: 20%;" class="fieldFocus d-none" value="' . $item->height . '"> cm';
                $html = $length_html . '</span>';
                return $html;
                break;

            case 'ecom_product_weight_per_unit':
                if (in_array('40', $not_visible_arr))
                    return '--';
                $ecom_product_weight_per_unit = $item->ecom_product_weight_per_unit == null ? '--' : $item->ecom_product_weight_per_unit;
                $html_string = '
                <span class="m-l-15 inputDoubleClick"  data-fieldvalue="' . @$item->ecom_product_weight_per_unit . '">';
                $html_string .= $ecom_product_weight_per_unit;
                $html_string .= '</span>';
                $html_string .= '<input type="text"  name="ecom_product_weight_per_unit" style="width: 80%;" class="fieldFocus d-none" value="' . $item->ecom_product_weight_per_unit . '">';
                return $html_string . ' kg';
                break;

            case 'long_desc':
                if (in_array('41', $not_visible_arr))
                    return '--';
                $long_desc = $item->long_desc == null ? '--' : $item->long_desc;
                $html_string = '
                <span style="max-width:300px;" class="m-l-15 inputDoubleClick" id="short_desc"  data-fieldvalue="' . @$item->long_desc . '">';
                $html_string .= $long_desc;
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="long_desc" style="width: 100%;" class="fieldFocus d-none" value="' . $item->long_desc . '">';
                return $html_string;
                break;

            case 'selling_price':
                if (in_array('42', $not_visible_arr))
                    return '--';
                $ecommerce_price = $item->ecommerce_price == null ? '--' : number_format($item->ecommerce_price, 2);
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="ecommerce_price' . $item->id . '"  data-fieldvalue="' . @$item->ecommerce_price . '">';
                $html_string .= $ecommerce_price;
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="ecommerce_price" style="width: 100%;" class="fieldFocus d-none" value="' . $item->ecommerce_price . '">';
                return $html_string;
                break;

            case 'discount_price':
                if (in_array('43', $not_visible_arr))
                    return '--';
                $discount_price = $item->discount_price == null ? '--' : $item->discount_price;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="discount_price' . $item->id . '"  data-fieldvalue="' . @$item->discount_price . '">';
                $html_string .= $discount_price;
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="discount_price" style="width: 100%;" class="fieldFocus d-none" value="' . $item->discount_price . '">';
                return $html_string;
                break;

            case 'discount_expiry':
                if (in_array('44', $not_visible_arr))
                    return '--';
                $discount_expiry_date = $item->discount_expiry_date == null ? '--' : $item->discount_expiry_date;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="discount_expiry_date' . $item->id . '"  data-fieldvalue="' . @$item->discount_expiry_date . '">';
                $html_string .= $discount_expiry_date;
                $html_string .= '</span>';

                $html_string .= '<input type="date"  name="discount_expiry_date" style="width: 100%;" class="fieldFocus d-none" value="' . $item->discount_expiry_date . '">';
                return $html_string;
                break;

            case 'ecom_selling_unit':
                if (in_array('45', $not_visible_arr))
                    return '--';
                return @$item->ecomSellingUnits != null ? @$item->ecomSellingUnits->title : '--';
                break;

            case 'ecom_selling_conversion_rate':
                if (in_array('46', $not_visible_arr))
                    return '--';
                $selling_unit_conversion_rate = $item->selling_unit_conversion_rate == null ? '--' : $item->selling_unit_conversion_rate;
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="selling_unit_conversion_rate' . $item->id . '"  data-fieldvalue="' . @$item->selling_unit_conversion_rate . '">';
                $html_string .= $selling_unit_conversion_rate;
                $html_string .= '</span>';

                $html_string .= '<input type="number"  name="selling_unit_conversion_rate" style="width: 100%;" class="fieldFocus d-none" value="' . $item->selling_unit_conversion_rate . '">';
                return $html_string;
                break;

            case 'ecom_cogs_price':
                if (in_array('47', $not_visible_arr))
                    return '--';
                return number_format($item->selling_unit_conversion_rate * $item->selling_price, 3, '.', ',');
                break;

            case 'ecom_status':
                if(in_array('48', $not_visible_arr))
                return '--';
                return $item->ecommerce_enabled == 0 ? "disabled" : "enabled";
                break;

            case 'on_supplier':
                if (in_array('34', $not_visible_arr))
                    return '--';
                $on_supplier = $item::getOnSupplier($item->id);
                $on_supplier = ($on_supplier != null) ? $on_supplier : 0;

                $decimal = $item->units->decimal_places;
                if ($decimal != null) {
                    $on_supplier = number_format($on_supplier, $decimal, '.', '');
                } else {
                    $on_supplier = number_format($on_supplier, 3, '.', '');
                }
                $request_array = '';
                $request_array .= $item->id;
                $request_array .= ',' . 'on-supplier';
                $type = 'list';
                if ($on_supplier != 0 && $on_supplier != null && $on_supplier != '0') {
                    $html_string = '<a target="_blank" href="' . url('purchasing-report/' . $request_array . '/' . $type) . '" style="cursor:pointer" title="View Detail"><b>' . $on_supplier . '</b></a>';
                } else {
                    $html_string = $on_supplier;
                }
                return $html_string;
                break;
        }
    }

    public static function returnEditColumn($column, $item, $not_visible_arr)
    {
        switch ($column) {
            case 'refrence_code':
                if (in_array('2', $not_visible_arr))
                    return '--';
                $refrence_code = $item->refrence_code != null ? $item->refrence_code : "--";
                $html_string = '
                <a target="_blank" href="' . url('get-product-detail/' . $item->id) . '" title="View Detail"><b>' . $refrence_code . '</b></a>
                ';
                return $html_string;
                break;

            case 'hs_code':
                $hs_code = $item->hs_code != null ? $item->hs_code : "--";
                return $hs_code;
                break;

            case 'hs_description':
                if (in_array('3', $not_visible_arr))
                    return '--';
                $html_string = '<span class="m-l-15 inputDoubleClick" id="hs_description" data-fieldvalue="' . @$item->hs_description . '">';
                $html_string .= ($item->hs_description != null ? $item->hs_description : "--");
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="hs_description" style="width:100%;" class="fieldFocus d-none" value="' . $item->hs_description . '">';

                return $html_string;
                break;

            case 'short_desc':
                if (in_array('6', $not_visible_arr))
                    return '--';
                $desc_prod = $item->short_desc != null ? $item->short_desc : '--';
                $text_color = $item->short_desc != null ? '' : 'color: red;';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="short_desc" style="' . $text_color . '" data-fieldvalue="' . @$item->short_desc . '">';
                $html_string .= $desc_prod;
                $html_string .= '</span>';
                $desc = $item->short_desc != null ? htmlspecialchars($item->short_desc) : '';
                $html_string .= '<input type="text"  name="short_desc" style="width:100%;" class="fieldFocus d-none" value="' . $desc . '">';
                return $html_string;
                break;

            case 'product_notes':
                if (in_array('7', $not_visible_arr))
                    return '--';
                $note = $item->product_notes != null ? $item->product_notes : '--';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_notes" data-fieldvalue="' . @$item->product_notes . '">';
                $html_string .= $note;
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="product_notes" style="width:100%;" class="fieldFocus d-none" value="' . $item->product_notes . '">';
                return $html_string;
                break;

            case 'product_note_3':
                if (in_array('8', $not_visible_arr))
                    return '--';

                // dd($item->product_note_3);
                $note = $item->product_note_3 != null ? $item->product_note_3 : '--';
                $html_string = '
                <span class="m-l-15 inputDoubleClick" id="product_note_3" data-fieldvalue="' . @$item->product_note_3 . '">';
                $html_string .= $note;
                $html_string .= '</span>';

                $html_string .= '<input type="text"  name="product_note_3" style="width:100%;" class="fieldFocus d-none" value="' . $item->product_note_3 . '">';
                return $html_string;
                break;
        }
    }

    public static function returnFilterColumn($column, $item, $keyword)
    {
        switch ($column) {
            case 'p_s_reference_number':
                $query = $item->whereIn('products.id', SupplierProducts::select('product_id')->where('product_supplier_reference_no', 'LIKE', "%$keyword%")->pluck('product_id'));
                return $query;
                break;
            case 'short_desc':
                $query = $item->where('short_desc', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'supplier_description':
                $query = $item->whereIn('products.id', SupplierProducts::select('product_id')->where('supplier_description', 'LIKE', "%$keyword%")->pluck('product_id'));
                return $query;
                break;
            case 'refrence_code':
                $query = $item->where('refrence_code', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'buying_unit':
                $query = $item->whereIn('buying_unit', Unit::select('id')->where('title', 'LIKE', "%$keyword%")->pluck('id'));
                return $query;
                break;
            case 'selling_unit':
                $query = $item->whereIn('selling_unit', Unit::select('id')->where('title', 'LIKE', "%$keyword%")->pluck('id'));
                return $query;
                break;
            case 'category_id':
                $query = $item->whereIn('category_id', ProductCategory::select('id')->where('title', 'LIKE', "%$keyword%")->pluck('id'))->orWhereIn('primary_category', ProductCategory::select('id')->where('title', 'LIKE', "%$keyword%")->pluck('id'));
                return $query;
                break;
            case 'product_notes':
                $query = $item->where('product_notes', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'hs_description':
                $query = $item->where('hs_description', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'brnad':
                $query = $item->where('brand', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'product_temprature_c':
                $query = $item->where('product_temprature_c', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'vat':
                $query = $item->where('vat', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'import_tax_book':
                $query = $item->where('import_tax_book', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'total_buy_unit_cost_price':
                $query = $item->where('total_buy_unit_cost_price', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'unit_conversion_rate':
                $query = $item->where('unit_conversion_rate', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'selling_unit_cost_price':
                $query = $item->where('selling_price', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'weight':
                $query = $item->where('weight', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'product_type':
                $query = $item->whereIn('type_id', ProductType::select('id')->where('title', 'LIKE', "%$keyword%")->pluck('id'));
                return $query;
                break;
            case 'product_type_2':
                $query = $item->whereIn('type_id_2', ProductSecondaryType::select('id')->where('title', 'LIKE', "%$keyword%")->pluck('id'));
                return $query;
                break;
            case 'supplier_id':
                $query = $item->whereIn('supplier_id', Supplier::select('id')->where('reference_name', 'LIKE', "%$keyword%")->pluck('id'));
                return $query;
                break;
            case 'vendor_price':
                $query = $item->whereIn('products.id', SupplierProducts::select('product_id')->where('buying_price', 'LIKE', "%$keyword%")->pluck('product_id'));
                return $query;
                break;
            case 'vendor_price_in_thb':
                $query = $item->whereIn('products.id', SupplierProducts::select('product_id')->where('buying_price_in_thb', 'LIKE', "%$keyword%")->pluck('product_id'));
                return $query;
                break;
            case 'freight':
                $query = $item->whereIn('products.id', SupplierProducts::select('product_id')->where('freight', 'LIKE', "%$keyword%")->pluck('product_id'));
                return $query;
                break;
            case 'landing':
                $query = $item->whereIn('products.id', SupplierProducts::select('product_id')->where('landing', 'LIKE', "%$keyword%")->pluck('product_id'));
                return $query;
                break;
            case 'lead_time':
                $query = $item->whereIn('products.id', SupplierProducts::select('product_id')->where('leading_time', 'LIKE', "%$keyword%")->pluck('product_id'));
                return $query;
                break;
            case 'name':
                $query = $item->where('name', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'min_order_qty':
                $query = $item->where('min_o_qty', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'max_order_qty':
                $query = $item->where('max_o_qty', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'ecom_product_weight_per_unit':
                $query = $item->where('ecom_product_weight_per_unit', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'long_desc':
                $query = $item->where('long_desc', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'selling_price':
                $query = $item->where('ecommerce_price', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'discount_price':
                $query = $item->where('discount_price', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'ecom_selling_unit':
                $query = $item->where('ecom_selling_unit', 'LIKE', "%$keyword%");
                return $query;
                break;
            case 'ecom_selling_conversion_rate':
                $query = $item->where('selling_unit_conversion_rate', 'LIKE', "%$keyword%");
                return $query;
                break;
        }
    }

    public static function returnMarginReportAddColumn($column, $item, $total_items_gp, $total_items_sales)
    {
        switch ($column) {
            case 'margins':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $adjustment_out = 0;
                if ($sales != 0) {
                    $total = ($sales - $cogs - abs($adjustment_out)) / $sales;
                } else {
                    $total = 0;
                }
                if ($total == 0) {
                    $formated = "-100.00";
                } else {
                    $formated = number_format($total * 100, 2);
                }
                return $formated . " %";
                break;

            case 'percent_gp':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $adjustment_out = 0;
                $total = $sales - $cogs - abs($adjustment_out);
                $formated = number_format($total, 2, '.', '');
                if ($total_items_gp !== 0) {
                    $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
                } else {
                    $formated = 0;
                }
                return $formated . ' %';
                break;

            case 'gp':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $adjustment_out = 0;
                $total = $sales - $cogs - abs($adjustment_out);
                $formated = number_format($total, 2, '.', ',');
                return $formated;
                break;

            case 'cogs':
                $adjustment_out = 0;
                $total = number_format($item->products_total_cost + abs($adjustment_out), 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-wid=' . $item->wid . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'percent_sales':
                $total = number_format($item->sales, 2, '.', '');
                if ($total_items_sales !== 0) {
                    $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
                } else {
                    $total = 0;
                }
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-wid=' . $item->wid . ' title="View Detail"><b>' . $total . ' %</b></a>';
                return $html_string;
                break;

            case 'sales':
                $total = number_format($item->sales, 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-wid=' . $item->wid . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'vat_in':
                return $item->import_vat_amount != null ? number_format($item->import_vat_amount, 2) : '--';
                break;

            case 'vat_out':
                return $item->vat_amount_total != null ? number_format($item->vat_amount_total, 2) : '--';
                break;

            case 'short_desc':
                return $item->short_desc;
                break;
        }
    }

    public static function returnMarginReportEditColumn($column, $item)
    {
        switch ($column) {
            case 'warehouse_title':
                $html_string = '<a href="' . route('margin-report-2') . '"><span class="font-weight-bold">' . $item->warehouse_title . '</span></a>';
                return $html_string;
                break;
        }
    }


    public static function returnAddColumnMargin2($column, $item, $total_items_gp, $total_items_sales)
    {
        switch ($column) {
            case 'margins':
                $adjustment_out = $item->manual_adjustment != null ? $item->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                if ($sales != 0) {
                    $total = ($sales - $cogs - abs($adjustment_out)) / $sales;
                } else {
                    $total = 0;
                }
                if ($total == 0) {
                    $formated = "-100.00";
                } else {
                    $formated = number_format($total * 100, 2);
                }
                return $formated . " %";
                break;

            case 'percent_gp':
                $adjustment_out = $item->manual_adjustment != null ? $item->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs - abs($adjustment_out);
                $formated = number_format($total, 2, '.', '');
                if ($total_items_gp !== 0) {
                    $formated = number_format(($formated / $total_items_gp) * 100, 2, '.', ',');
                } else {
                    $formated = 0;
                }
                return $formated . ' %';
                break;

            case 'gp':
                $adjustment_out = $item->manual_adjustment != null ? $item->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs - abs($adjustment_out);
                $formated = number_format($total, 2, '.', ',');
                return $formated;
                break;

            case 'cogs':
                $adjustment_out = $item->manual_adjustment != null ? $item->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
                $total = number_format($item->products_total_cost + abs($adjustment_out), 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-id=' . $item->product_id . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'brand':
                return @$item->brand != null ? @$item->brand : '--';
                break;

            case 'percent_sales':
                $total = number_format($item->sales, 2, '.', '');
                if ($total_items_sales !== 0) {
                    $total = number_format(($total / $total_items_sales) * 100, 2, '.', ',');
                } else {
                    $total = 0;
                }
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-id=' . $item->product_id . ' title="View Detail"><b>' . $total . ' %</b></a>';
                return $html_string;
                break;

            case 'sales':
                $total = number_format($item->sales, 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-id=' . $item->product_id . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'vat_in':
                return $item->vat_in != null ? number_format($item->vat_in, 2) : '--';
                $total_pos_count = $item->purchaseOrderDetailVatIn->sum('quantity');
                $total_pos_vat = $item->purchaseOrderDetailVatIn->sum('pod_vat_actual_total_price');
                if ($total_pos_count > 0) {
                    $total_pos_vat = $total_pos_vat / $total_pos_count;
                }
                if ($total_pos_count > $item->totalQuantity) {
                    $quantity_to_multiply = $item->totalQuantity;
                } else {
                    $quantity_to_multiply = $total_pos_count;
                }
                return $item->purchaseOrderDetailVatIn != null ? number_format($total_pos_vat * $quantity_to_multiply, 2) : '--';
                break;

            case 'vat_out':
                return $item->vat_amount_total != null ? number_format($item->vat_amount_total, 2) : '--';
                break;
            case 'unit_cogs':
                $adjustment_out = $item->manual_adjustment != null ? $item->manual_adjustment()->sum(\DB::raw('quantity_out * cost')) : 0;
                $total = round($item->products_total_cost + abs($adjustment_out), 2);
                return $item->qty != null && $item->qty != 0 && $total != null ? number_format(($total / $item->qty), 4) : '--';
                break;
            case 'qty':
                return $item->qty != null ? number_format($item->qty, 2) : '--';
                break;
        }
    }

    public static function returnEditColumnMargin2($column, $item)
    {
        switch ($column) {
            case 'default_supplier':
                $html_string = '<span id="short_desc"><a href="' . url('get-product-detail/' . $item->product_id) . '" target="_blank" title="View Detail"><b>' . @$item->def_or_last_supplier->reference_name . '</b></a></span>';
                return $html_string;
                break;

            case 'short_desc':
                $html_string = '<span id="short_desc"><a href="' . url('get-product-detail/' . $item->product_id) . '" target="_blank" title="View Detail"><b>' . $item->short_desc . '</b></a></span>';
                return $html_string;
                break;

            case 'refrence_code':
                $brand = @$item->brand != null ? @$item->brand : '';
                $html_string = '<span id=""><a href="' . url('get-product-detail/' . $item->product_id) . '" target="_blank" title="View Detail"><b>' . $item->refrence_code . '</b></a></span>';
                return $html_string;
                break;
        }
    }

    public static function returnFilterColumnMargin2($column, $item, $keyword)
    {
        switch ($column) {
            case 'default_supplier':
                $item->whereHas('def_or_last_supplier', function ($q) use ($keyword) {
                    $q->where('reference_name', 'LIKE', "%$keyword%");
                });
                $item->orWhere('products.short_desc', 'LIKE', "%$keyword%");
                break;

            case 'refrence_code':
                $item->where('products.refrence_code', 'LIKE', "%$keyword%");
                $item->orWhere('products.short_desc', 'LIKE', "%$keyword%");
                break;
        }
    }


    public static function returnAddColumnMargin3($column, $item, $total_items_gp, $total_items_sales)
    {
        switch ($column) {
            case 'margins':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales != 0 ? $item->marg : $total = 0;
                $formated = $total == 0 ? "-100.00" : number_format($total * 100, 2);
                return $formated . " %";
                break;

            case 'percent_gp':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs;
                $formated = number_format($total, 2, '.', '');
                $formated = $total_items_gp !== 0 ? number_format(($formated / $total_items_gp) * 100, 2, '.', ',') : 0;
                return $formated . ' %';
                break;

            case 'gp':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs;
                $formated = number_format($total, 2, '.', ',');
                return $formated;
                break;

            case 'cogs':
                $total = number_format($item->products_total_cost, 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-saleid=' . $item->sale_id . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'brand':
                return @$item->brand != null ? @$item->brand : '--';
                break;

            case 'percent_sales':
                $total = number_format($item->sales, 2, '.', '');
                $total = $total_items_sales !== 0 ? number_format(($total / $total_items_sales) * 100, 2, '.', ',') : 0;
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-saleid=' . $item->sale_id . ' title="View Detail"><b>' . $total . ' %</b></a>';
                return $html_string;
                break;

            case 'sales':
                $total = number_format($item->sales, 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-saleid=' . $item->sale_id . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'vat_in':
                return $item->vat_in != null ? number_format($item->vat_in, 2) : '--';
                break;
            case 'vat_out':
                return $item->vat_amount_total != null ? number_format($item->vat_amount_total, 2) : '--';
                break;
        }
    }

    public static function returnEditColumnMargin3($column, $item)
    {
        switch ($column) {
            case 'name':
                $html_string = '<a href="' . route('margin-report-5', ['sale_id' => $item->sale_id, 'secondary_filter' => 'yes']) . '" target="_blank" title="View Report"><b>' . $item->name . '</b></a>';
                return $html_string;
                break;

            case 'short_desc':
                return $item->short_desc;
                break;
        }
    }

    public static function returnAddColumnMargin4($column, $item, $total_items_gp, $total_items_sales, $request)
    {
        switch ($column) {
            case 'margins':
                $stock = (new ProductCategory)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales != 0 ? ($sales - $cogs - abs($total_man)) / $sales : 0;
                $formated = $total == 0 ? "-100.00" : number_format($total * 100, 2);
                return $formated . " %";
                break;

            case 'percent_gp':
                $stock = (new ProductCategory)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs - abs($total_man);
                $formated = number_format($total, 2, '.', '');
                $formated = $total_items_gp !== 0 ? number_format(($formated / $total_items_gp) * 100, 2, '.', ',') : 0;
                return $formated . ' %';
                break;

            case 'gp':
                $stock = (new ProductCategory)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs - abs($total_man);
                $formated = number_format($total, 2, '.', ',');
                return $formated;
                break;

            case 'cogs':
                $stock = (new ProductCategory)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $total = number_format($item->products_total_cost + abs($total_man), 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-catid=' . $item->category_id . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'brand':
                return @$item->brand != null ? @$item->brand : '--';
                break;

            case 'percent_sales':
                $total = number_format($item->sales, 2, '.', '');
                $total = $total_items_sales !== 0 ? number_format(($total / $total_items_sales) * 100, 2, '.', ',') : 0;
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-catid=' . $item->category_id . ' title="View Detail"><b>' . $total . ' %</b></a>';
                return $html_string;
                break;
            case 'sales':
                $total = number_format($item->sales, 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-catid=' . $item->category_id . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'vat_in':
                return $item->vat_in != null ? number_format($item->vat_in, 2) : '--';
                break;
            case 'vat_out':
                return $item->vat_amount_total != null ? number_format($item->vat_amount_total, 2) : '--';
                break;
        }
    }

    public static function returnEditColumnMargin4($column, $item)
    {
        switch ($column) {
            case 'title':
                $html_string = '<a href="' . route('margin-report-8', ['id' => $item->category_id]) . '" target="_blank" title="View Report"><b>' . $item->title . '</b></a>';
                return $html_string;
                break;

            case 'short_desc':
                return $item->short_desc;
                break;
        }
    }


    public static function returnAddColumnMargin5($column, $item, $total_items_gp, $total_items_sales, $request)
    {
        switch ($column) {
            case 'reference_code':
                $html_string = '<a href="' . route('margin-report-2', ['customer_id' => $item->customer_id, 'from_margin_report' => 'yes', 'customer' => $item->customer_id]) . '" target="_blank" title="View Report"><b>' . $item->reference_number . '</b></a>';
                return $html_string;
                break;
            case 'margins':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales != 0 ? $item->marg : 0;
                $formated = $total == 0 ? "-100.00" : number_format($total * 100, 2);
                return $formated . " %";
                break;

            case 'percent_gp':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs;
                $formated = number_format($total, 2, '.', '');
                $formated = $total_items_gp !== 0 ? number_format(($formated / $total_items_gp) * 100, 2, '.', ',') : 0;
                return $formated . ' %';
                break;

            case 'gp':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs;
                $formated = number_format($total, 2, '.', ',');
                return $formated;
                break;

            case 'cogs':
                $total = number_format($item->products_total_cost, 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-custid=' . $item->customer_id . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'brand':
                return @$item->brand != null ? @$item->brand : '--';
                break;

            case 'percent_sales':
                $total = number_format($item->sales, 2, '.', '');
                $total = $total_items_sales !== 0 ? number_format(($total / $total_items_sales) * 100, 2, '.', ',') : 0;
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-custid=' . $item->customer_id . ' title="View Detail"><b>' . $total . ' %</b></a>';
                return $html_string;
                break;
            case 'sales':
                $total = number_format($item->sales, 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-custid=' . $item->customer_id . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'vat_in':
                return $item->vat_in != null ? number_format($item->vat_in, 2) : '--';
                break;
            case 'vat_out':
                return $item->vat_amount_total != null ? number_format($item->vat_amount_total, 2) : '--';
                break;
        }
    }

    public static function returnEditColumnMargin5($column, $item)
    {
        switch ($column) {
            case 'reference_name':
                $html_string = '<a href="' . route('margin-report-2', ['customer_id' => $item->customer_id, 'from_margin_report' => 'yes', 'customer' => $item->customer_id]) . '" target="_blank" title="View Report"><b>' . $item->reference_name . '</b></a>';
                return $html_string;
                break;

            case 'short_desc':
                return $item->short_desc;
                break;
        }
    }

    public static function returnAddColumnMargin6($column, $item, $total_items_gp, $total_items_sales)
    {
        switch ($column) {
            case 'margins':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales != 0 ? $item->marg : 0;
                $formated = $total == 0 ? "-100.00" : number_format($total * 100, 2);
                return $formated . " %";
                break;

            case 'percent_gp':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs;
                $formated = number_format($total, 2, '.', '');
                $formated = $total_items_gp !== 0 ? number_format(($formated / $total_items_gp) * 100, 2, '.', ',') : 0;
                return $formated . ' %';
                break;

            case 'gp':
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs;
                $formated = number_format($total, 2, '.', ',');
                return $formated;
                break;

            case 'cogs':
                $total = number_format($item->products_total_cost, 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-ctype=' . $item->customer_type_id . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'brand':
                return @$item->brand != null ? @$item->brand : '--';
                break;

            case 'percent_sales':
                $total = number_format($item->sales, 2, '.', '');
                $total = $total_items_sales !== 0 ? number_format(($total / $total_items_sales) * 100, 2, '.', ',') : 0;
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-ctype=' . $item->customer_type_id . ' title="View Detail"><b>' . $total . ' %</b></a>';
                return $html_string;
                break;
            case 'sales':
                $total = number_format($item->sales, 2);
                $html_string = '<a href="javascript:void(0)" class="s_p_report_w_pm" data-ctype=' . $item->customer_type_id . ' title="View Detail"><b>' . $total . '</b></a>';
                return $html_string;
                break;

            case 'vat_in':
                return $item->vat_in != null ? number_format($item->vat_in, 2) : '--';
                break;
            case 'vat_out':
                return $item->vat_amount_total != null ? number_format($item->vat_amount_total, 2) : '--';
                break;
        }
    }

    public static function returnEditColumnMargin6($column, $item)
    {
        switch ($column) {
            case 'title':
                $html_string = '<a href="' . route('margin-report-5', ['customer_type_id' => $item->customer_type_id, 'secondary_filter' => 'yes']) . '" target="_blank" title="View Report"><b>' . $item->title . '</b></a>';
                return $html_string;
                break;

            case 'short_desc':
                return $item->short_desc;
                break;
        }
    }


    public static function returnAddColumnMargin9($column, $item, $total_items_gp, $total_items_sales, $request)
    {
        switch ($column) {
            case 'margins':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales != 0 ? ($sales - $cogs - abs($total_man)) / $sales : 0;
                $formated = $total == 0 ? "-100.00" : number_format($total * 100, 2);
                return $formated . " %";
                break;

            case 'percent_gp':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs - abs($total_man);
                $formated = number_format($total, 2, '.', '');
                $formated = $total_items_gp !== 0 ? number_format(($formated / $total_items_gp) * 100, 2, '.', ',') : 0;
                return $formated . ' %';
                break;

            case 'gp':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs - abs($total_man);
                $formated = number_format($total, 2, '.', ',');
                return $formated;
                break;

            case 'cogs':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $total = number_format($item->products_total_cost + abs($total_man), 2);
                $html_string = $total;
                return $html_string;
                break;

            case 'brand':
                return @$item->brand != null ? @$item->brand : '--';
                break;

            case 'percent_sales':
                $total = number_format($item->sales, 2, '.', '');
                $total = $total_items_sales !== 0 ? number_format(($total / $total_items_sales) * 100, 2, '.', ',') : 0;
                $html_string = $total;
                return $html_string;
                break;
            case 'sales':
                $total = number_format($item->sales, 2);
                $html_string = $total;
                return $html_string;
                break;

            case 'vat_in':
                return $item->vat_in != null ? number_format($item->vat_in, 2) : '--';
                break;
            case 'vat_out':
                return $item->vat_amount_total != null ? number_format($item->vat_amount_total, 2) : '--';
                break;
        }
    }

    public static function returnEditColumnMargin9($column, $item)
    {
        switch ($column) {
            case 'title':
                $html_string = $item->title;
                return $html_string;
                break;

            case 'short_desc':
                return $item->short_desc;
                break;
        }
    }

    public static function returnAddColumnMargin11($column, $item, $total_items_gp, $total_items_sales, $request)
    {
        switch ($column) {
            case 'margins':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales != 0 ? ($sales - $cogs - abs($total_man)) / $sales : 0;
                $formated = $total == 0 ? "-100.00" : number_format($total * 100, 2);
                return $formated . " %";
                break;

            case 'percent_gp':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs - abs($total_man);
                $formated = number_format($total, 2, '.', '');
                $formated = $total_items_gp !== 0 ? number_format(($formated / $total_items_gp) * 100, 2, '.', ',') : 0;
                return $formated . ' %';
                break;

            case 'gp':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs - abs($total_man);
                $formated = number_format($total, 2, '.', ',');
                return $formated;
                break;

            case 'cogs':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $total = number_format($item->products_total_cost + abs($total_man), 2);
                $html_string = $total;
                return $html_string;
                break;
            case 'brand':
                return @$item->brand != null ? @$item->brand : '--';
                break;

            case 'percent_sales':
                $total = number_format($item->sales, 2, '.', '');
                $total = $total_items_sales !== 0 ? number_format(($total / $total_items_sales) * 100, 2, '.', ',') : 0;
                $html_string = $total;
                return $html_string;
                break;
            case 'sales':
                $total = number_format($item->sales, 2);
                $html_string = $total;
                return $html_string;
                break;

            case 'vat_in':
                return $item->import_vat_amount != null ? number_format($item->import_vat_amount, 2) : '--';
                break;
            case 'vat_out':
                return $item->vat_amount_total != null ? number_format($item->vat_amount_total, 2) : '--';
                break;
        }
    }

    public static function returnEditColumnMargin11($column, $item)
    {
        switch ($column) {
            case 'title':
                $html_string = $item->title;
                return $html_string;
                break;

            case 'short_desc':
                return $item->short_desc;
                break;
        }
    }

    public static function returnAddColumnMarginType3($column, $item, $total_items_gp, $total_items_sales, $request)
    {
        switch ($column) {
            case 'margins':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales != 0 ? ($sales - $cogs - abs($total_man)) / $sales : 0;
                $formated = $total == 0 ? "-100.00" : number_format($total * 100, 2);
                return $formated . " %";
                break;

            case 'percent_gp':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs - abs($total_man);
                $formated = number_format($total, 2, '.', '');
                $formated = $total_items_gp !== 0 ? number_format(($formated / $total_items_gp) * 100, 2, '.', ',') : 0;
                return $formated . ' %';
                break;

            case 'gp':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $sales = $item->sales;
                $cogs  = $item->products_total_cost;
                $total = $sales - $cogs - abs($total_man);
                $formated = number_format($total, 2, '.', ',');
                return $formated;
                break;

            case 'cogs':
                $stock = (new ProductType)->get_manual_adjustments($request, $item->category_id);
                $total_man = (clone $stock)->sum(\DB::raw('cost * quantity_out'));
                $total = number_format($item->products_total_cost + abs($total_man), 2);
                $html_string = $total;
                return $html_string;
                break;
            case 'brand':
                return @$item->brand != null ? @$item->brand : '--';
                break;

            case 'percent_sales':
                $total = number_format($item->sales, 2, '.', '');
                $total = $total_items_sales !== 0 ? number_format(($total / $total_items_sales) * 100, 2, '.', ',') : 0;
                $html_string = $total;
                return $html_string;
                break;
            case 'sales':
                $total = number_format($item->sales, 2);
                $html_string = $total;
                return $html_string;
                break;

            case 'vat_in':
                return $item->import_vat_amount != null ? number_format($item->import_vat_amount, 2) : '--';
                break;
            case 'vat_out':
                return $item->vat_amount_total != null ? number_format($item->vat_amount_total, 2) : '--';
                break;
        }
    }

    public static function returnEditColumnMarginType3($column, $item)
    {
        switch ($column) {
            case 'title':
                $html_string = $item->title;
                return $html_string;
                break;

            case 'short_desc':
                return $item->short_desc;
                break;
        }
    }

    public static function returnAddColumnMargin10($column, $item, $total_items_gp, $total_items_sales, $request)
    {
        switch ($column) {
            case 'supplier_name':
                $name = $item->supplier != null ? $item->supplier->reference_name : '--';
                $html_string = '<a href="' . route('sold-products-report', ['from_supplier_margin' => true, 'supplier_id' => $item->supplier_id, 'from_date' => $request->from_date, 'to_date' => $request->to_date]) . '" target="_blank" title="View Report"><b>' . $name . '</b></a>';
                return $html_string;
                break;
            case 'margins':
                $adjustment_out = 0;
                $sales = $item->sales_total;
                $cogs  = $item->total_cost_c;
                $total = $sales != 0 ? ($sales - $cogs - abs($adjustment_out)) / $sales : 0;
                $formated = $total == 0 ? "00.00" : number_format($total * 100, 2);
                return $formated . " %";
                break;

            case 'percent_gp':
                $sales = $item->sales_total;
                $cogs  = $item->total_cost_c;
                $total = $sales - $cogs;
                $formated = number_format($total, 2, '.', '');
                $formated = $total_items_gp !== 0 ? number_format(($formated / $total_items_gp) * 100, 2, '.', ',') : 0;
                return $formated . ' %';
                break;

            case 'gp':
                $sales = $item->sales_total;
                $cogs  = $item->total_cost_c;
                $total = $sales - $cogs;
                $formated = number_format($total, 2, '.', ',');
                return $formated;
                break;

            case 'cogs':
                $html_string = '<a href="' . route('sold-products-report', ['from_supplier_margin' => true, 'supplier_id' => $item->supplier_id, 'from_date' => $request->from_date, 'to_date' => $request->to_date]) . '" target="_blank" title="View Report"><b>' . number_format($item->total_cost_c, 2, '.', ',') . '</b></a>';
                return $html_string;
                break;

            case 'percent_sales':
                $total = number_format($item->sales_total, 2, '.', '');
                $total = $total_items_sales != 0 ? number_format(($total / $total_items_sales) * 100, 2, '.', ',') : 0;
                $html_string = '<a href="' . route('sold-products-report', ['from_supplier_margin' => true, 'supplier_id' => $item->supplier_id, 'from_date' => $request->from_date, 'to_date' => $request->to_date]) . '" class="" data-wid=' . $item->wid . ' title="View Detail"><b>' . $total . ' %</b></a>';
                return $html_string;
                break;
            case 'sales':
                $html_string = '<a href="' . route('sold-products-report', ['from_supplier_margin' => true, 'supplier_id' => $item->supplier_id, 'from_date' => $request->from_date, 'to_date' => $request->to_date]) . '" target="_blank" title="View Report"><b>' . number_format($item->sales_total, 2) . '</b></a>';
                return $html_string;
                break;

            case 'vat_in':
                return number_format($item->vat_in_total, 2, '.', ',');
                break;
            case 'vat_out':
                return number_format($item->vat_out_total, 2);
                break;
        }
    }

    public static function returnEditColumnMargin10($column, $item, $keyword)
    {
        switch ($column) {
            case 'supplier_name':
                $item->whereHas('supplier', function ($q) use ($keyword) {
                    $q->where('suppliers.reference_name', 'LIKE', "%$keyword%");
                });
                return $item;
                break;
        }
    }

    public static function returnAddColumnStockReport($column, $item, $from_date, $to_date, $warehouse_id = null)
    {
        $stock_query = StockManagementOut::where('product_id', $item->id);
        if($warehouse_id != null && $warehouse_id != ''){
            $stock_query = $stock_query->where('warehouse_id', $warehouse_id);
        }
        $Start_count_out = (clone $stock_query)->where('created_at', '<', $from_date)->sum('quantity_out');


        $Start_count_in = (clone $stock_query)->where('created_at', '<', $from_date)->sum('quantity_in');
        // dd($Start_count_out, $Start_count_in);
        switch ($column) {
            case 'history':
                $html_string = '
                <a class="" target="_blank" href="' . route('stock-detail-report', ['id' => $item->id, 'warehouse_id' => '', 'from_date' => $from_date, 'to_date' => $to_date]) . '" style="cursor:pointer" title="View history" ><i class="fa fa-history"></i></a>';
                return $html_string;
                break;

            case 'cogs':
                $unit_conversion_rate = ($item->unit_conversion_rate != null) ? $item->unit_conversion_rate : 1;
                $cogs = $item->total_buy_unit_cost_price * $unit_conversion_rate;
                return number_format($cogs, 3, '.', ',');
                break;

            case 'stock_balance':
                // $Start_count_out = $item->stock_out();
                // if($warehouse_id != null && $warehouse_id != ''){
                //     $Start_count_out = $Start_count_out->where('warehouse_id', $warehouse_id);
                // }
                // $Start_count_out = $Start_count_out->whereDate('created_at', '<', $from_date)->sum('quantity_out');

                // $Start_count_in = $item->stock_out();
                // if($warehouse_id != null && $warehouse_id != ''){
                //     $Start_count_in = $Start_count_in->where('warehouse_id', $warehouse_id);
                // }
                // $Start_count_in = $Start_count_in->where('warehouse_id', $warehouse_id)->whereDate('created_at', '<', $from_date)->sum('quantity_in');

                $INS = $item->stock_out->sum('quantity_in');
                $OUTs = $item->stock_out->sum('quantity_out');
                $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
                return number_format($Start_count_out + $Start_count_in + $INS + $OUTs, $decimal_places, '.', ',');
                break;

            case 'stock_out':
                $OUTs = $item->stock_out->sum('quantity_out');
                $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
                return number_format($OUTs, $decimal_places, '.', ',');
                break;

            case 'out_transfer_document':
                $out_transfer_document = $item->stock_out->where('title', '!=', Null)->where('title', 'TD')->where('quantity_in', NULL)->sum('quantity_out');
                return number_format($out_transfer_document, 2, '.', ',');
                break;

            case 'out_manual_adjustment':
                $out_manual_adjustment = $item->stock_out->where('title', '!=', Null)->where('title', '!=', 'TD')->where('quantity_in', NULL)->sum('quantity_out');
                return number_format($out_manual_adjustment, 2, '.', ',');
                break;

            case 'out_order':
                $out_order = $item->stock_out->where('order_id', '!=', Null)->where('quantity_in', NULL)->sum('quantity_out');
                return number_format($out_order, 2, '.', ',');
                break;

            case 'stock_in':
                $INS = $item->stock_out->sum('quantity_in');
                $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
                return number_format($INS, $decimal_places, '.', ',');
                break;

            case 'in_orderUpdate':
                $in_orderUpdate = $item->stock_out->where('order_id', '!=', Null)->where('quantity_out', NULL)->sum('quantity_in');
                return number_format($in_orderUpdate, 2, '.', ',');
                break;

            case 'in_transferDocument':
                $in_transferDocument = $item->stock_out->where('title', '!=', Null)->where('title', 'TD')->where('quantity_out', NULL)->sum('quantity_in');
                return number_format($in_transferDocument, 2, '.', ',');
                break;

            case 'in_manualAdjusment':
                $in_manualAdjusment = $item->stock_out->where('title', '!=', Null)->where('title', '!=', 'TD')->where('quantity_out', NULL)->sum('quantity_in');
                return number_format($in_manualAdjusment, 2, '.', ',');
                break;

            case 'in_purchase':
                $purchase_in = $item->stock_out->where('po_group_id', '!=', Null)->sum('quantity_in');
                return number_format($purchase_in, 2, '.', ',');
                break;

            case 'start_count':

                $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
                return number_format($Start_count_out + $Start_count_in, $decimal_places, '.', ',');
                break;

            case 'selling_unit':
                return @$item->sellingUnits->title;
                break;

            case 'min_stock':
                return @$item->min_stock != null ? @$item->min_stock : '--';
                break;

            // case 'product_type_2':
            //     return @$item->productType2 != null ? $item->productType2->title : '--';
            //     break;

            case 'supplier_country':
                return @$item->def_or_last_supplier->getcountry != null ? @$item->def_or_last_supplier->getcountry->name : '--';
                break;

            case 'product_type_3':
                return @$item->productType3 != null ? $item->productType3->title : '--';
                break;

            case 'product_type':
                return @$item->productType != null ? $item->productType->title : '--';
                break;

            case 'brand':
                return @$item->brand != null ? $item->brand : '--';
                break;
            case 'supplier':
                return @$item->def_or_last_supplier != null ? @$item->def_or_last_supplier->reference_name : '--';
                break;
        }
    }


    public static function returnEditColumnStockReport($column, $item)
    {
        switch ($column) {
            case 'short_desc':
                return $item->short_desc != null ? $item->short_desc : '--';
                break;

            case 'refrence_code':
                $html_string = '
                 <a href="' . url('get-product-detail/' . $item->id) . '" target="_blank" title="View Detail"><b>' . $item->refrence_code . '</b></a>
                 ';
                return $html_string;
                break;
        }
    }


    public static function returnAddColumnProductSaleReport($column, $item, $not_visible_arr, $from_date, $to_date, $supplier_id, $customer_id, $date_type, $getWarehouses)
    {
        switch ($column) {
            case 'view':
                $customer_id == '' ? $customer_id = 'NA' : '';
                $supplier_id == '' ? $supplier_id = 'NA' : '';
                $from_date == '' ? $from_date = 'NoDate' : '';
                $to_date == '' ? $to_date = 'NoDate' : '';
                $date_type == '' ? $date_type = '1' : '';
                $html_string = '<a target="_blank" href="' . url('get-product-sales-report-detail/' . $customer_id . '/' . $supplier_id . '/' . $item->product_id . '/' . $from_date . '/' . $to_date . '/' . $date_type) . '" class="actionicon" style="cursor:pointer" title="View history" data-id=' . $item->product_id . '><i class="fa fa-history"></i></a>';
                return $html_string;
                break;

            case 'selling_unit':
                if (in_array('7', $not_visible_arr))
                    return '--';
                return @$item->sellingUnits->title;
                break;

            case 'brand':
                if (in_array('5', $not_visible_arr))
                    return '--';
                return @$item->brand != null ? @$item->brand : '--';
                break;

            case 'product_type':
                if (in_array('2', $not_visible_arr))
                    return '--';
                return @$item->productType != null ? @$item->productType->title : '--';
                break;

            case 'product_type_2':
                if (in_array('3', $not_visible_arr))
                    return '--';
                return @$item->productType2 != null ? @$item->productType2->title : '--';
                break;

            case 'product_type_3':
                if (in_array('4', $not_visible_arr))
                    return '--';
                return @$item->productType3 != null ? @$item->productType3->title : '--';
                break;

            case 'total_quantity':
                if (in_array('8', $not_visible_arr))
                    return '--';
                return number_format($item->QuantityText, 2);
                break;

            case 'total_pieces':
                if (in_array('9', $not_visible_arr))
                    return '--';
                return number_format($item->PiecesText, 2);
                break;

            case 'total_cost':
                return number_format($item->TotalAverage, 2);
                break;

            case 'total_amount':
                if (in_array('11', $not_visible_arr))
                    return '--';
                return number_format($item->TotalAmount, 2);
                break;

            case 'total_stock':
                if (in_array('13', $not_visible_arr))
                    return '--';
                return number_format($item->warehouse_products->sum('current_quantity'), 2);
                break;

            case 'sub_total':
                if (in_array('10', $not_visible_arr))
                    return '--';
                return number_format($item->totalPriceSub, 2);
                break;

            case 'vat_thb':
                if (in_array('12', $not_visible_arr))
                    return '--';
                return $item->VatTotalAmount != null ? number_format($item->VatTotalAmount, 2) : '--';
                break;

            case 'avg_unit_price':
                return number_format($item->avg_unit_price, 2);
                break;

            case 'total_visible_stock':
                if (in_array('14', $not_visible_arr))
                    return '--';
                $visible = 0;
                $start = 17;
                foreach ($getWarehouses as $warehouse) {
                    if (!in_array($start, $not_visible_arr)) {
                        $warehouse_product = $item->warehouse_products->where('warehouse_id', $warehouse->id)->first();
                        $qty = ($warehouse_product->current_quantity != null) ? $warehouse_product->current_quantity : 0;
                        $visible += $qty;
                    }
                    $start += 1;
                }
                return round($visible, 3);
                break;

            case 'cogs':
                if (in_array('15', $not_visible_arr))
                    return '--';
                if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11) {
                    $html = (@$item->selling_price != null) ? number_format((float)@$item->selling_price, 3, '.', '') : '--';
                    $html .= ' / ' . @$item->sellingUnits->title;
                    return $html;
                } else {
                    return '--';
                }
                break;

            case 'total_cogs':
                if (in_array('16', $not_visible_arr))
                    return '--';
                if (Auth::user()->role_id == 1 || Auth::user()->role_id == 2 || Auth::user()->role_id == 7 || Auth::user()->role_id == 11) {
                    return number_format($item->totalCogs, 2);
                } else {
                    return '--';
                }
                break;
        }
    }

    public static function returnEditColumnProductSaleReport($column, $item, $not_visible_arr)
    {
        switch ($column) {
            case 'refrence_code':
                if (in_array('1', $not_visible_arr))
                    return '--';
                $html_string = '<a href="' . url('get-product-detail/' . $item->product_id) . '" target="_blank" title="View Detail"><b>' . $item->refrence_code . '</b></a>';
                return $html_string;
                break;

            case 'short_desc':
                if (in_array('6', $not_visible_arr))
                    return '--';
                return '<span id="short_desc">' . $item->short_desc . '</span>';
                break;
        }
    }

    public static function returnAddColumnProductSaleReportByMonth($column, $item, $not_visible_arr)
    {
        switch ($column) {
            case 'Jan':
                if (in_array('4', $not_visible_arr))
                    return '--';
                return $item->jan_totalAmount != null ? number_format($item->jan_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Dec':
                if (in_array('15', $not_visible_arr))
                    return '--';
                return $item->dec_totalAmount != null ? number_format($item->dec_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Feb':
                if (in_array('5', $not_visible_arr))
                    return '--';
                return $item->feb_totalAmount != null ? number_format($item->feb_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Mar':
                if (in_array('6', $not_visible_arr))
                    return  '--';
                return $item->mar_totalAmount != null ? number_format($item->mar_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Apr':
                if (in_array('7', $not_visible_arr))
                    return '--';
                return $item->apr_totalAmount != null ? number_format($item->apr_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'May':
                if (in_array('8', $not_visible_arr))
                    return '--';
                return $item->may_totalAmount != null ? number_format($item->may_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Jun':
                if (in_array('9', $not_visible_arr))
                    return '--';
                return $item->jun_totalAmount != null ? number_format($item->jun_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Jul':
                if (in_array('10', $not_visible_arr))
                    return '--';
                return $item->jul_totalAmount != null ? number_format($item->jul_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Aug':
                if (in_array('11', $not_visible_arr))
                    return '--';
                return $item->aug_totalAmount != null ? number_format($item->aug_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Sep':
                if (in_array('12', $not_visible_arr))
                    return '--';
                return $item->sep_totalAmount != null ? number_format($item->sep_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Oct':
                if (in_array('13', $not_visible_arr))
                    return '--';
                return $item->oct_totalAmount != null ? number_format($item->oct_totalAmount, 2, '.', ',') : '0.00';
                break;

            case 'Nov':
                if (in_array('14', $not_visible_arr))
                    return '--';
                return $item->nov_totalAmount != null ? number_format($item->nov_totalAmount, 2, '.', ',') : '0.00';
                break;
            case 'total':
                    if (in_array('15', $not_visible_arr))
                        return '--';
                        // calculate total by adding up all the month values
                       $total = ($item->jan_totalAmount ?? 0) +
                                 ($item->feb_totalAmount ?? 0) +
                                 ($item->mar_totalAmount ?? 0) +
                                 ($item->apr_totalAmount ?? 0) +
                                 ($item->may_totalAmount ?? 0) +
                                 ($item->jun_totalAmount ?? 0) +
                                 ($item->jul_totalAmount ?? 0) +
                                 ($item->aug_totalAmount ?? 0) +
                                 ($item->sep_totalAmount ?? 0) +
                                 ($item->oct_totalAmount ?? 0) +
                                 ($item->nov_totalAmount ?? 0);
            
                        return number_format($total, 2, '.', ',');
                        break;
        }
    }

    public static function returnEditColumnProductSaleReportByMonth($column, $item, $not_visible_arr)
    {
        switch ($column) {
            case 'refrence_code':
                if (in_array('0', $not_visible_arr))
                    return '--';
                $html_string = '<a target="_blank" href="' . url('get-product-detail/' . $item->product_id) . '"><b>' . $item->product->refrence_code . '</b></a>';
                return @$html_string;
                break;

            case 'brand':
                if (in_array('1', $not_visible_arr))
                    return '--';
                return ($item->product->brand != null) ? $item->product->brand : '--';
                break;

            case 'short_desc':
                if (in_array('2', $not_visible_arr))
                    return '--';
                return ($item->product->short_desc != null) ? $item->product->short_desc : '--';
                break;

            case 'selling_unit':
                if (in_array('3', $not_visible_arr))
                    return '--';
                return ($item->product && $item->product->sellingUnits->title != null) ? $item->product->sellingUnits->title : '--';
                break;
        }
    }

    public static function sortPSRByMonth($products, $request)
    {
        $sort_order = $request->sortbyvalue == 1 ? 'DESC' : 'ASC';
        $sort_variable = null;
        $product_sort = null;
        if($request->sortbyparam == 1)
        {
            $product_sort  = 'p.refrence_code';
        }
        else if($request->sortbyparam == 2)
        {
            $product_sort  = 'p.brand';
        }
        else if($request->sortbyparam == 3)
        {
            $product_sort  = 'p.short_desc';
        }
        else if($request->sortbyparam == 4)
        {
            $product_sort  = 'u.title';
        }
        else if($request->sortbyparam == 'Jan')
        {
            $sort_variable  = 'jan_totalAmount';
        }
        else if($request->sortbyparam == 'Feb')
        {
            $sort_variable  = 'feb_totalAmount';
        }
        else if($request->sortbyparam == 'Mar')
        {
            $sort_variable  = 'mar_totalAmount';
        }
        else if($request->sortbyparam == 'Apr')
        {
            $sort_variable  = 'apr_totalAmount';
        }
        else if($request->sortbyparam == 'May')
        {
            $sort_variable  = 'may_totalAmount';
        }
        else if($request->sortbyparam == 'Jun')
        {
            $sort_variable  = 'jun_totalAmount';
        }
        else if($request->sortbyparam == 'Jul')
        {
            $sort_variable  = 'jul_totalAmount';
        }
        else if($request->sortbyparam == 'Aug')
        {
            $sort_variable  = 'aug_totalAmount';
        }
        else if($request->sortbyparam == 'Sep')
        {
            $sort_variable  = 'sep_totalAmount';
        }
        else if($request->sortbyparam == 'Oct')
        {
            $sort_variable  = 'oct_totalAmount';
        }
        else if($request->sortbyparam == 'Nov')
        {
            $sort_variable  = 'nov_totalAmount';
        }
        else if($request->sortbyparam == 'Dec')
        {
            $sort_variable  = 'dec_totalAmount';
        }
        else
        {
            $products->orderBy('op.product_id', 'asc');
        }

        if($product_sort != null)
        {
            $products = $products->join('products as p', 'p.id', '=', 'op.product_id');
            if ($product_sort == 'u.title') {
                $products = $products->join('units as u', 'p.selling_unit', '=', 'u.id');
            }
            $products = $products->orderBy($product_sort, $sort_order);
        }
        if($sort_variable)
        {
            $products->orderBy($sort_variable, $sort_order);
        }
        return $products;
    }
}
