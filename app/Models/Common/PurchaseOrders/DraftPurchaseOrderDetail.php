<?php

namespace App\Models\Common\PurchaseOrders;

use App\Models\Common\PurchaseOrders\DraftPurchaseOrder;
use Illuminate\Database\Eloquent\Model;

class DraftPurchaseOrderDetail extends Model
{
    protected $fillable = ['po_id','customer_id','order_product_id','order_id','product_id','quantity','remarks','warehouse_id','billed_desc','is_billed','created_by','item_notes','desired_qty'];

    public function getProduct()
    {
        return $this->belongsTo('App\Models\Common\Product', 'product_id', 'id');
    }

    public function draftPo()
    {
    	return $this->belongsTo('App\Models\Common\PurchaseOrders\DraftPurchaseOrder','po_id','id');
    }
    public function get_td_reserved()
    {
      return $this->hasMany('App\TransferDocumentReservedQuantity', 'draft_pod_id', 'id');
    }

    public function notes()
    {
      return $this->hasMany('App\Models\Common\PurchaseOrders\DraftPurchaseOrderDetailNote', 'draft_po_id', 'id');
    }

    public function getProductStock()
    {
      return $this->hasMany('App\Models\Common\WarehouseProduct', 'product_id', 'product_id');
    }

    public function calculateVatToSystemCurrency($draft_po_id, $amount)
    {
        $data = array();
        $order = DraftPurchaseOrder::find($draft_po_id);
        if($order->exchange_rate == null)
        {
            $supplier_conv_rate = $order->getSupplier != null ? ($order->getSupplier->getCurrency != null ? $order->getSupplier->getCurrency->conversion_rate : 1) : 1;
        }
        else
        {
            $supplier_conv_rate = $order->exchange_rate;
        }

        $data['converted_amount'] = ($amount / $supplier_conv_rate);
        return $data;
    }

    public function calculateVat($unit_price, $vat)
    {
        $data = array();
        $vat_amount = $unit_price * ( $vat / 100 );
        $vat_amount = number_format($vat_amount,4,'.','');
        $pod_unit_price_with_vat = ($unit_price + $vat_amount);

        $data['vat_amount'] = $vat_amount;
        $data['pod_unit_price_with_vat'] = $pod_unit_price_with_vat;
        return $data;
    }

    /*grand calculation on each event for Draft PO/TD*/
    public function grandCalculationForDraftPoTD($draft_po_id)
    {
        $data = array();
        $total_gross_weight            = 0;
        $sub_total                     = 0;
        $total_item_product_quantities = 0;
        $total_import_tax_book         = 0;
        $total_import_tax_book_price   = 0;
        $total_vat_actual              = 0;
        $total_vat_actual_price        = 0;
        $pod_total_vat_amount          = 0;
        $pod_total_unit_price_with_vat = 0;
        $total_vat_actual_price_in_thb = 0;

        $query = DraftPurchaseOrderDetail::where('po_id',$draft_po_id)->get();
        foreach ($query as  $value)
        {
            $convert_vals = $this->calculateVatToSystemCurrency($draft_po_id, $value->pod_vat_actual_price);
            $value->pod_vat_actual_price_in_thb = $convert_vals['converted_amount'];

            $convert_vals = $this->calculateVatToSystemCurrency($draft_po_id, $value->pod_vat_actual_total_price);
            $value->pod_vat_actual_total_price_in_thb = $convert_vals['converted_amount'];
            $value->save();

            $pod_unit_price  = $value->pod_unit_price;
            $unit_price_wd   = $value->quantity * $pod_unit_price - (($value->quantity * $pod_unit_price) * (@$value->discount / 100));
            $value->pod_total_unit_price = $unit_price_wd;

            if($value->pod_unit_price_with_vat != null)
            {
                $pod_unit_price_with_vat  = $value->pod_unit_price_with_vat;
            }
            else
            {
                $value->pod_unit_price_with_vat = $value->pod_unit_price + ($value->pod_unit_price * ($value->pod_vat_actual / 100));
                $value->save();

                $pod_unit_price_with_vat = $value->pod_unit_price_with_vat;
            }
            $total_unit_price_wv_wd   = $value->quantity * $pod_unit_price_with_vat - (($value->quantity * $pod_unit_price_with_vat) * (@$value->discount / 100));
            $value->pod_total_unit_price_with_vat = $total_unit_price_wv_wd;

            $value->save();

            $sub_total                     += $value->pod_total_unit_price;
            $pod_total_unit_price_with_vat += $value->pod_total_unit_price_with_vat;
            $total_gross_weight            = ($value->quantity * $value->pod_gross_weight) + $total_gross_weight;
            $total_item_product_quantities = $total_item_product_quantities + $value->quantity;
            $total_import_tax_book         = $total_import_tax_book + $value->pod_import_tax_book;
            $total_import_tax_book_price   = $total_import_tax_book_price + $value->pod_import_tax_book_price;
            $total_vat_actual              = $total_vat_actual + $value->pod_vat_actual;
            $total_vat_actual_price        = $total_vat_actual_price + $value->pod_vat_actual_price;
            $pod_total_vat_amount          = $pod_total_vat_amount + $value->pod_vat_actual_total_price;
            $total_vat_actual_price_in_thb = $total_vat_actual_price_in_thb + $value->pod_vat_actual_price_in_thb;
        }

        $po_totals_update                                = DraftPurchaseOrder::find($draft_po_id);
        $po_totals_update->total_gross_weight            = $total_gross_weight;
        $po_totals_update->total_quantity                = $total_item_product_quantities;
        $po_totals_update->total_import_tax_book         = $total_import_tax_book;
        $po_totals_update->total_import_tax_book_price   = $total_import_tax_book_price;
        $po_totals_update->total_vat_actual              = $total_vat_actual;
        $po_totals_update->total_vat_actual_price        = $total_vat_actual_price;
        $po_totals_update->total_vat_actual_price_in_thb = $total_vat_actual_price_in_thb;
        $po_totals_update->total                         = number_format($sub_total, 3, '.', '');
        $po_totals_update->vat_amount_total              = number_format($pod_total_vat_amount, 3, '.', '');
        $po_totals_update->total_with_vat                = number_format($pod_total_unit_price_with_vat, 3, '.', '');
        $po_totals_update->save();

        $data['sub_total'] = $sub_total;
        $data['total_qty'] = $total_item_product_quantities;
        $data['vat_amout'] = $pod_total_vat_amount;
        $data['total_w_v'] = $pod_total_unit_price_with_vat;
        return $data;
    }

    // calculate columns
    public function calColumns($updateRow)
    {
        $decimals = $updateRow->getProduct != null ? ($updateRow->getProduct->units != null ? $updateRow->getProduct->units->decimal_places : 0) : 0;

        $data = array();
        //item level calculations
        $total_amount_wo_vat = number_format((float)$updateRow->pod_total_unit_price,3,'.',''); 
        $total_amount_w_vat = number_format((float)$updateRow->pod_total_unit_price_with_vat,3,'.',''); 
 
        $unit_price = $updateRow->pod_unit_price != null ?  number_format($updateRow->pod_unit_price,3,'.','') : '--'; 
        $unit_price_w_vat = $updateRow->pod_unit_price_with_vat != null ?  number_format($updateRow->pod_unit_price_with_vat,3,'.','') : '--';

        $unit_gross_weight = $updateRow->pod_gross_weight != null ?  number_format($updateRow->pod_gross_weight,3,'.','') : '--'; 
        $total_gross_weight = $updateRow->pod_total_gross_weight != null ?  number_format($updateRow->pod_total_gross_weight,3,'.','') : '--'; 

        $desired_qty = ($updateRow->desired_qty != null ? number_format($updateRow->desired_qty,$decimals,'.','') : "--" );
        $quantity = ($updateRow->quantity != null ? number_format($updateRow->quantity,$decimals,'.','') : "--" );

        $data['id'] = $updateRow->id;
        $data['unit_price'] = $unit_price;
        $data['unit_price_w_vat'] = $unit_price_w_vat;
        $data['total_amount_wo_vat'] = $total_amount_wo_vat;
        $data['total_amount_w_vat'] = $total_amount_w_vat;
        $data['unit_gross_weight'] = $unit_gross_weight;
        $data['total_gross_weight'] = $total_gross_weight;
        $data['desired_qty'] = $desired_qty;
        $data['quantity'] = $quantity;

        return $data;        
    }

    public static function DraftPOSorting($request, $query)
    {
        $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';

        if ($request['column_name'] == 1)
        {
            $query->leftJoin('products', 'products.id', '=', 'draft_purchase_order_details.product_id')->orderBy('products.refrence_code', $sort_order);
        }
        elseif ($request['column_name'] == 'brand')
        {
            $query->leftJoin('products', 'products.id', '=', 'draft_purchase_order_details.product_id')->orderBy('products.brand', $sort_order);
        }
        elseif ($request['column_name'] == 'type')
        {
            $query->leftJoin('products', 'products.id', '=', 'draft_purchase_order_details.product_id')->leftJoin('types', 'types.id', '=', 'products.type_id')->orderBy('types.title', $sort_order);
        }
        elseif ($request['column_name'] == 'selling_unit')
        {
            $query->leftJoin('products', 'products.id', '=', 'draft_purchase_order_details.product_id')->leftJoin('units', 'units.id', '=', 'products.selling_unit')->orderBy('units.title', $sort_order);
        }
        elseif ($request['column_name'] == 'gross_weight')
        {
            $query->orderBy(\DB::raw('draft_purchase_order_details.pod_gross_weight+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'order_qty')
        {
            $query->orderBy(\DB::raw('draft_purchase_order_details.desired_qty+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'qty_inv')
        {
            $query->orderBy(\DB::raw('draft_purchase_order_details.quantity+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'order_qty_unit')
        {
            $query->orderBy(\DB::raw('draft_purchase_order_details.billed_unit_per_package+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'unit_price')
        {
            $query->orderBy(\DB::raw('draft_purchase_order_details.pod_unit_price+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'purchasing_vat')
        {
            $query->orderBy(\DB::raw('draft_purchase_order_details.pod_vat_actual+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'unit_price_plus_vat')
        {
            $query->orderBy(\DB::raw('draft_purchase_order_details.pod_unit_price_with_vat+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'price_date')
        {
            $query->orderBy('draft_purchase_order_details.last_updated_price_on', $sort_order);
        }
        elseif ($request['column_name'] == 'total_amount_wo_vat')
        {
            $amount = '(draft_purchase_order_details.pod_unit_price * draft_purchase_order_details.quantity)';
            $query->orderBy(\DB::Raw($amount.'-('.$amount.'*(draft_purchase_order_details.discount/100))'), $sort_order);
        }
        elseif ($request['column_name'] == 'total_amount_w_vat')
        {
            $amount = '(draft_purchase_order_details.pod_unit_price_with_vat * draft_purchase_order_details.quantity)';
            $query->orderBy(\DB::Raw($amount.'-('.$amount.'*(draft_purchase_order_details.discount/100))'), $sort_order);
        }
        elseif ($request['column_name'] == 'total_gross_weight')
        {
            $query->orderBy(\DB::raw('draft_purchase_order_details.pod_total_gross_weight+0'), $sort_order);
        }
        elseif ($request['column_name'] == 'avg_units_for_sales')
        {
            $query->leftJoin('products', 'products.id', '=', 'draft_purchase_order_details.product_id')->orderBy(\DB::raw('products.weight+0'), $sort_order);
        }
        else
        {
            $query->orderBy('id', 'ASC');
        }
        return $query;
    }
}
