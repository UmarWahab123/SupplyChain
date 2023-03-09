<?php
namespace App\Helpers;

class POGroupSortingHelper
{
  public static function ProductReceivingRecordsSorting($request, $query)
  {
    $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';
    if ($request['column_name'] == 'po_no')
    {
      $query->leftJoin('purchase_orders as po', 'po.id', '=', 'po_group_product_details.po_id')->orderBy('po.ref_id', $sort_order);
    }
    elseif ($request['column_name'] == 'order_warehouse')
    {
      $query->leftJoin('orders as o', 'o.id', '=', 'po_group_product_details.order_id')->leftJoin('users as u', 'u.id', '=', 'o.user_id')->leftJoin('warehouses as w', 'w.id', '=', 'u.warehouse_id')->orderBy('w.warehouse_title', $sort_order);
    }
    // elseif ($request['column_name'] == 'suppliers_product_reference_no')
    // {
    //   $query->leftJoin('products as p', 'p.id', '=', 'po_group_product_details.product_id')->leftJoin('supplier_products as sp', 'sp.product_id', '=', 'p.id')->where('sp.supplier_id', 'po_group_product_details.supplier_id')->orderBy('sp.product_supplier_reference_no', $sort_order);
    // }
    elseif ($request['column_name'] == 'our_reference_number')
    {
      $query->leftJoin('products as p', 'p.id', '=', 'po_group_product_details.product_id')->orderBy('p.refrence_code', $sort_order);
    }
    elseif ($request['column_name'] == 'brand')
    {
      $query->leftJoin('products as p', 'p.id', '=', 'po_group_product_details.product_id')->orderBy('p.brand', $sort_order);
    }
    elseif ($request['column_name'] == 'product_description')
    {
      $query->leftJoin('products as p', 'p.id', '=', 'po_group_product_details.product_id')->orderBy('p.short_desc', $sort_order);
    }
    elseif ($request['column_name'] == 'type')
    {
      $query->leftJoin('products as p', 'p.id', '=', 'po_group_product_details.product_id')->leftJoin('types as t', 't.id', '=', 'p.type_id')->orderBy('t.title', $sort_order);
    }
    elseif ($request['column_name'] == 'customer')
    {
      $query->leftJoin('orders as o', 'o.id', '=', 'po_group_product_details.order_id')->leftJoin('customers as c', 'c.id', '=', 'o.customer_id')->orderBy('c.reference_name', $sort_order);
    }
    elseif ($request['column_name'] == 'buying_unit')
    {
      $query->leftJoin('products as p', 'p.id', '=', 'po_group_product_details.product_id')->leftJoin('units as u', 'u.id', '=', 'p.buying_unit')->orderBy('u.title', $sort_order);
    }
    elseif ($request['column_name'] == 'qty_ordered')
    {
      $query->orderBy(\DB::Raw('quantity_ordered+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'qty')
    {
      $query->leftJoin('purchase_order_details as pod', 'pod.id', '=', 'po_group_product_details.pod_id')->leftJoin('order_products as op', 'op.id', '=', 'pod.order_product_id')->orderBy(\DB::Raw('op.quantity+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'qty_inv')
    {
      $query->orderBy(\DB::Raw('quantity_inv+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'gross_weight')
    {
      $query->orderBy(\DB::Raw('case when unit_gross_weight != null then total_gross_weight+0/quantity_inv+0 else unit_gross_weight end'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_gross_weight')
    {
      $query->orderBy(\DB::Raw('total_gross_weight+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'extra_cost')
    {
      $query->orderBy(\DB::Raw('unit_extra_cost+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_extra_cost')
    {
      $query->orderBy(\DB::Raw('total_extra_cost+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'extra_tax')
    {
      $query->orderBy(\DB::Raw('unit_extra_tax+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_extra_tax')
    {
      $query->orderBy(\DB::Raw('total_extra_tax+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'purchasing_price_f_wo_vat')
    {
      $query->orderBy(\DB::Raw('unit_price+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'purchasing_price_f')
    {
      $query->orderBy(\DB::Raw('unit_price_with_vat+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'discount')
    {
      $query->orderBy(\DB::Raw('discount+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_purchasing_price_f_wo_vat')
    {
      $query->orderBy(\DB::Raw('total_unit_price+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_purchasing_price_f')
    {
      $query->orderBy(\DB::Raw('total_unit_price_with_vat+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'purchasing_price_thb_f_wo_vat')
    {
      $query->orderBy(\DB::Raw('unit_price_in_thb+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'purchasing_price_thb_f')
    {
      $query->orderBy(\DB::Raw('unit_price_in_thb_with_vat+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_purchasing_price_thb_f_wo_vat')
    {
      $query->orderBy(\DB::Raw('total_unit_price_in_thb+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_purchasing_price_thb_f')
    {
      $query->orderBy(\DB::Raw('total_unit_price_in_thb_with_vat+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_purchasing_price_thb_f')
    {
      $query->orderBy(\DB::Raw('total_unit_price_in_thb_with_vat+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'book_vat')
    {
      $query->orderBy(\DB::Raw('pogpd_vat_actual+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'import_tax_book')
    {
      $query->orderBy(\DB::Raw('import_tax_book+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'freight')
    {
      $query->orderBy(\DB::Raw('freight+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_freight')
    {
      $query->orderBy(\DB::Raw('freight+0 * quantity_inv+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'landing')
    {
      $query->orderBy(\DB::Raw('landing+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_landing')
    {
      $query->orderBy(\DB::Raw('landing+0 * quantity_inv+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'import_weighted_percent')
    {
      $query->orderBy(\DB::Raw('actual_tax_price+0 * quantity_inv+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'cogs')
    {
      $query->orderBy(\DB::Raw('product_cost+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'total_cogs')
    {
      $query->orderBy(\DB::Raw('(product_cost*quantity_inv)+0'), $sort_order);
    }
    return $query;
  }

  public static function ReceivingQueueSorting($request, $query)
  {
    $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';
    if ($request['column_name'] == 'group_no')
    {
      $query->orderBy('ref_id', $sort_order);
    }
    elseif ($request['column_name'] == 'quantity')
    {
      $query->leftJoin('po_group_product_details as pogd', 'pogd.po_group_id', '=', 'po_groups.id')->orderBy(\DB::Raw('SUM(pogd.quantity_inv)+0'), $sort_order)->groupBy('po_groups.id');
    }
    elseif ($request['column_name'] == 'net_weight')
    {
      $query->orderBy('po_group_total_gross_weight', $sort_order);
    }
    elseif ($request['column_name'] == 'issue_date')
    {
      $query->orderBy('created_at', $sort_order);
    }
    elseif ($request['column_name'] == 'po_total')
    {
      $query->orderBy(\DB::Raw('total_buying_price_in_thb+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'target_receive_date')
    {
      $query->orderBy('target_receive_date', $sort_order);
    }
    elseif ($request['column_name'] == 'warehouse')
    {
      $query->leftJoin('warehouses as w', 'w.id', '=', 'po_groups.warehouse_id')->orderBy('w.warehouse_title', $sort_order);
    }
    elseif ($request['column_name'] == 'courier')
    {
      $query->leftJoin('couriers as c', 'c.id', '=', 'po_groups.courier')->orderBy('c.title', $sort_order);
    }
    else
    {
      $query->orderBy('id', 'DESC');
    }
    return $query;
  }

  public static function WarehouseProductRecevingRecordsSorting($request, $query)
  {
    $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';
    if ($request['column_name'] == 'po_no')
    {
      $query->leftJoin('purchase_orders as po', 'po.id', '=', 'po_group_product_details.po_id')->orderBy('po.ref_id', $sort_order);
    }
    elseif ($request['column_name'] == 'our_reference_number')
    {
      $query->leftJoin('products as p', 'p.id', '=', 'po_group_product_details.product_id')->orderBy('p.refrence_code', $sort_order);
    }
    elseif ($request['column_name'] == 'warehouse')
    {
      $query->leftJoin('orders as o', 'o.id', '=', 'po_group_product_details.order_id')->leftJoin('users as u', 'u.id', '=', 'o.user_id')->leftJoin('warehouses as w', 'w.id', '=', 'u.warehouse_id')->orderBy('w.warehouse_title', $sort_order);
    }
    elseif ($request['column_name'] == 'supplier_description')
    {
      $query->leftJoin('products as p', 'p.id', '=', 'po_group_product_details.product_id')->orderBy('p.short_desc', $sort_order);
    }
    elseif ($request['column_name'] == 'customer')
    {
      $query->leftJoin('orders as o', 'o.id', '=', 'po_group_product_details.order_id')->leftJoin('customers as c', 'c.id', '=', 'o.customer_id')->orderBy('c.reference_name', $sort_order);
    }
    elseif ($request['column_name'] == 'qty_ordered')
    {
      $query->orderBy(\DB::Raw('quantity_ordered+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'qty_inv')
    {
      $query->orderBy(\DB::Raw('quantity_inv+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'billed_unit')
    {
      $query->leftJoin('products as p', 'p.id', '=', 'po_group_product_details.product_id')->leftJoin('units as u', 'u.id', '=', 'p.buying_unit')->orderBy('u.title', $sort_order);
    }
    elseif ($request['column_name'] == 'qty_receive')
    {
      $query->orderBy(\DB::Raw('quantity_received_1+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'expiration_date')
    {
      $query->orderBy('expiration_date_1', $sort_order);
    }
    elseif ($request['column_name'] == 'quantity_received_2')
    {
      $query->orderBy(\DB::Raw('quantity_received_2+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'expiration_date_2')
    {
      $query->orderBy('expiration_date_2', $sort_order);
    }
    elseif ($request['column_name'] == 'goods_condition')
    {
      $query->orderBy('good_condition', $sort_order);
    }
    elseif ($request['column_name'] == 'results')
    {
      $query->orderBy('result', $sort_order);
    }
    elseif ($request['column_name'] == 'temprature_c')
    {
      $query->orderBy(\DB::Raw('temperature_c+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'checker')
    {
      $query->orderBy('checker', $sort_order);
    }
    elseif ($request['column_name'] == 'problem_found')
    {
      $query->orderBy('problem_found', $sort_order);
    }
    elseif ($request['column_name'] == 'solution')
    {
      $query->orderBy('solution', $sort_order);
    }
    elseif ($request['column_name'] == 'authorized_changes')
    {
      $query->orderBy('authorized_changes', $sort_order);
    }
    else
    {
      $query->orderBy('id','desc');
    }
    return $query;
  }

  public static function TransferReceivingQueueSorting($request, $query)
  {
    $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';
    if ($request['column_name'] == 'group_no')
    {
      $query->orderBy('ref_id', $sort_order);
    }
    elseif ($request['column_name'] == 'quantity')
    {
      $query->leftJoin('po_group_details as pgd', 'pgd.po_group_id', '=', 'po_groups.id')->leftJoin('purchase_orders as po', 'po.id', '=', 'pgd.purchase_order_id')->orderBy(\DB::Raw('sum(po.total_quantity+0)'), $sort_order)->groupBy('po_groups.id');
    }
    elseif ($request['column_name'] == 'net_weight')
    {
      $query->leftJoin('po_group_details as pgd', 'pgd.po_group_id', '=', 'po_groups.id')->leftJoin('purchase_orders as po', 'po.id', '=', 'pgd.purchase_order_id')->orderBy(\DB::Raw('sum(po.total_gross_weight+0)'), $sort_order)->groupBy('po_groups.id');
    }
    elseif ($request['column_name'] == 'issue_date')
    {
      $query->orderBy('created_at', $sort_order);
    }
    elseif ($request['column_name'] == 'target_receive_date')
    {
      $query->orderBy('target_receive_date', $sort_order);
    }
    elseif ($request['column_name'] == 'to_warehouse')
    {
      $query->leftJoin('warehouses as w', 'w.id', '=', 'po_groups.warehouse_id')->orderBy('w.warehouse_title', $sort_order);
    }
    else
    {
      $query->orderBy('id', 'DESC');
    }
    return $query;
  }

  public static function TDReceivingRecordsSorting($request, $query)
  {
    $sort_order = $request['sort_order'] == 1 ? 'DESC' : 'ASC';
    if ($request['column_name'] == 'to_no')
    {
      $query->orderBy('purchase_orders.ref_id', $sort_order);
    }
    elseif ($request['column_name'] == 'our_reference_number')
    {
      $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->orderBy('p.refrence_code', $sort_order);
    }
    elseif ($request['column_name'] == 'product_description')
    {
      $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->orderBy('p.short_desc', $sort_order);
    }
    elseif ($request['column_name'] == 'selling_unit')
    {
      $query->join('products as p', 'p.id', '=', 'purchase_order_details.product_id')->join('units as u', 'u.id', '=', 'p.selling_unit')->orderBy('u.title', $sort_order);
    }
    elseif ($request['column_name'] == 'qty_ordered')
    {
      $query->orderBy(\DB::Raw('quantity+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'qty_inv')
    {
      $query->orderBy(\DB::Raw('trasnfer_qty_shipped+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'qty_receive')
    {
      $query->orderBy(\DB::Raw('quantity_received+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'expiration_date')
    {
      $query->orderBy('expiration_date', $sort_order);
    }
    elseif ($request['column_name'] == 'quantity_received_2')
    {
      $query->orderBy(\DB::Raw('quantity_received_2+0'), $sort_order);
    }
    elseif ($request['column_name'] == 'expiration_date_2')
    {
      $query->orderBy('expiration_date_2', $sort_order);
    }
    return $query;
  }
}
