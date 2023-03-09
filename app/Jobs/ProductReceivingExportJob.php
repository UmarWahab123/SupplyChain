<?php

namespace App\Jobs;

use App\ExportStatus;
use App\Exports\ImportingProductReceivingRecord;
use App\FailedJobException;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Product;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Warehouse;
use App\Models\Common\PoGroup;
use App\Models\Common\Order\Order;
use App\ProductReceivingExportLog;
use App\ProductsReceivingRecordImporting;
use DB;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Queue\SerializesModels;
use App\Helpers\POGroupSortingHelper;
use Illuminate\Http\Request;


class ProductReceivingExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $request;
    protected $user_id;
    protected $status;
    public $tries = 1;
    public $timout = 500;
    public $final_book_percent_of_group = 0;
    public $final_vat_actual_percent_of_group = 0;
    protected $sort_order;
    protected $column_name;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $user_id, $status, $sort_order, $column_name)
    {
        $this->request = $request;
        $this->user_id = $user_id;
        $this->status  = $status;
        $this->sort_order  = $sort_order;
        $this->column_name  = $column_name;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {


        try {
            //code...        $request=$this->request;
            $user_id = $this->user_id;
            $request = $this->request;
            $status  = $this->status;
            $sort_order  = $this->sort_order;
            $column_name  = $this->column_name;

            $query = PoGroupProductDetail::where('po_group_product_details.status', 1)->where('po_group_product_details.po_group_id', $request)->with('product.supplier_products', 'get_supplier.getCurrency', 'get_supplier.getCurrency', 'product.units', 'product.sellingUnits', 'purchase_order', 'order.user.get_warehouse', 'product.productType', 'order.customer')->select('po_group_product_details.*');

            $request_data = new Request();
            $request_data->replace(['sort_order' => $sort_order, 'column_name' => $column_name]);
            $query = POGroupSortingHelper::ProductReceivingRecordsSorting($request_data, $query);
            $query = $query->get();
            $current_date = date("Y-m-d");
            $data = [];

            DB::table('products_receiving_record_importings')->truncate();
            $data = [];
            $final_book_percent_of_group = 0;
            $final_vat_actual_percent_of_group = 0;
            foreach ($query as $value) {
                if ($value->import_tax_book != null && $value->import_tax_book != 0) {
                    $final_book_percent_of_group = $final_book_percent_of_group + (($value->import_tax_book / 100) * $value->total_unit_price_in_thb);
                }

                if ($value->pogpd_vat_actual != null && $value->pogpd_vat_actual != 0) {
                    $final_vat_actual_percent_of_group = $final_vat_actual_percent_of_group + (($value->pogpd_vat_actual / 100) * $value->total_unit_price_in_thb);
                }
            }

            foreach ($query as $item) {
                $pogpd_id = $item->id;
                $occurrence = $item->occurrence;
                $discount = $item->discount;

                if ($occurrence == 1) {
                    $purchase_orders_ids =  PurchaseOrder::where('po_group_id', $item->po_group_id)->where('supplier_id', $item->supplier_id)->pluck('id')->toArray();
                    $pod = PurchaseOrderDetail::select('po_id', 'id')->whereIn('po_id', $purchase_orders_ids)->where('product_id', $item->product_id)->get();

                    if ($pod[0]->PurchaseOrder->ref_id !== null) {
                        $po_number = $pod[0]->PurchaseOrder->ref_id;
                        $po_id = $pod[0]->PurchaseOrder->id;
                        $pod_id = $pod[0]->id;
                    } else {
                        $po_number = "--";
                        $po_id = "--";
                        $pod_id = "--";
                    }
                } else {
                    $po_number = '--';
                    $po_id = '--';
                    $pod_id = '--';
                }

                if ($occurrence == 1) {
                    $purchase_orders_ids =  PurchaseOrder::where('po_group_id', $item->po_group_id)->where('supplier_id', $item->supplier_id)->pluck('id')->toArray();
                    $pod = PurchaseOrderDetail::select('po_id', 'order_id')->whereIn('po_id', $purchase_orders_ids)->where('product_id', $item->product_id)->get();
                    $order = Order::find($pod[0]->order_id);
                    $order_warehouse = $order !== null ? $order->user->get_warehouse->warehouse_title : "N.A";
                } else {
                    $order_warehouse = '--';
                }

                if ($occurrence == 1) {
                    $purchase_orders_ids =  PurchaseOrder::where('po_group_id', $item->po_group_id)->where('supplier_id', $item->supplier_id)->pluck('id')->toArray();
                    $pod = PurchaseOrderDetail::select('po_id', 'order_id')->whereIn('po_id', $purchase_orders_ids)->where('product_id', $item->product_id)->get();
                    $order = Order::find($pod[0]->order_id);
                    if ($order != null) {
                        $order_no = null;
                        if ($order->primary_status == 3) {
                            $order_no = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
                        } elseif ($order->primary_status == 2) {
                            $order_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
                        } elseif ($order->primary_status == 17) {
                            $order_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
                        } else {
                            $order_no = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
                        }
                    } else {
                        $order_no = "N.A";
                    }
                } else {
                    $order_no = '--';
                }


                if ($item->supplier_id !== NULL) {
                    $sup_name = SupplierProducts::where('supplier_id', $item->supplier_id)->where('product_id', $item->product_id)->first();
                    $reference_number = $sup_name != null && $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no : "--";
                } else {
                    $reference_number = "N.A";
                }

                if ($item->supplier_id !== NULL) {
                    $sup_name = Supplier::where('id', $item->supplier_id)->first();
                    $supplier = $sup_name->reference_name;
                } else {
                    $sup_name = Warehouse::where('id', $item->from_warehouse_id)->first();
                    $supplier = $sup_name->warehouse_title;
                    // return $sup_name->company != null ? $sup_name->company :"--" ;
                }

                $product = Product::where('id', $item->product_id)->first();
                $prod_reference_number = $product->refrence_code;

                $brand = $product->brand != null ? $product->brand : '--';

                $desc = $product->short_desc != null ? $product->short_desc : '--';

                $avg_weight = $product->weight != null ? $product->weight : '--';

                $type = $product->productType != null ? $product->productType->title : '--';

                $unit =  $product->units->title != null ? $product->units->title : '--';

                $qty_ordered = number_format($item->quantity_ordered, 2, '.', '');

                $qty = number_format($item->quantity_inv, 2, '.', '');

                $total_gross_weight = $item->total_gross_weight != null ? $item->total_gross_weight : 0;
                $pod_total_gross_weight = number_format($total_gross_weight, 3, '.', '');

                $total_extra_cost = $item->total_extra_cost != null ? $item->total_extra_cost : 0;
                $pod_total_extra_cost = number_format($total_extra_cost, 2, '.', '');

                $total_extra_tax = $item->total_extra_tax != null ? $item->total_extra_tax : 0;
                $pod_total_extra_tax = number_format($total_extra_tax, 2, '.', '');

                $currency_code = @$item->get_supplier != null ? $item->get_supplier->getCurrency->currency_code : '';
                // $buying_price = $item->unit_price != null ?number_format($item->unit_price,2,'.','').' '.$currency_code: '' ;
                $buying_price = $item->unit_price != null ? round($item->unit_price, 2) : '';

                $currency_code = @$item->get_supplier != null ? $item->get_supplier->getCurrency->currency_code : '';
                $total_buying_price = $item->total_unit_price != null ? $item->total_unit_price : number_format($item->unit_price * $item->quantity_inv, 2, '.', '');

                if ($item->currency_conversion_rate != null && $item->currency_conversion_rate != 0) {
                    $currency_conversion_rate = $item->currency_conversion_rate != null ? number_format((1 / $item->currency_conversion_rate), 2, '.', '') : 0;
                } else {
                    $currency_conversion_rate = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0;
                }

                $buying_price_in_thb = $item->unit_price_in_thb != null ? number_format($item->unit_price_in_thb, 2, '.', '') : '';

                // $total_buying_price_in_thb = $item->unit_price_in_thb != null ? number_format(($item->unit_price_in_thb*$item->quantity_inv),2,'.',''): '' ;
                $total_buying_price_in_thb = null;
                if ($item->occurrence > 0) {
                    // $ccr = $item->po_group->purchase_orders()->where('supplier_id',$item->supplier_id)->pluck('id')->toArray();

                    // $total_occr = $item->averageCurrency($ccr,$item->product_id,'total_buying_price_in_thb');
                    // $total_buying_price_in_thb= round($total_occr / $item->occurrence,3);

                    $total_buying_price_in_thb_item = $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb, 2, '.', '') : '';
                } else {
                    $total_buying_price_in_thb_item = $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb, 2, '.', '') : '';
                }
                $import_tax_book = number_format($item->import_tax_book, 2, '.', '');

                $freight = $item->freight;
                $freight = number_format($freight, 2, '.', '');

                $original_freight_without_rounding = $item->freight;
                $original_landing_without_rounding = $item->landing;

                $total_freight = number_format($item->freight * $item->quantity_inv, 2, '.', '');

                $landing = $item->landing;
                $landing = number_format($landing, 2, '.', '');

                $total_landing = number_format($item->landing * $item->quantity_inv, 2, '.', '');

                $po_group_import_tax_book = $item->po_group->total_import_tax_book_percent;
                //$total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;

                $import_tax = $item->import_tax_book;
                $total_price = $item->total_unit_price_in_thb;
                $book_tax = (($import_tax / 100) * $total_price);
                $check_book_tax = (($po_group_import_tax_book * $item->po_group->total_buying_price_in_thb) / 100);
                if ($check_book_tax != 0) {
                    $book_tax = number_format($book_tax, 2, '.', '');
                } else {
                    $count = PoGroupProductDetail::where('status', 1)->where('po_group_id', $item->po_group_id)->count();
                    $book_tax = (1 / $count) * $item->total_unit_price_in_thb;
                    $book_tax = number_format($book_tax, 2, '.', '');
                }

                $total_import_tax = $item->po_group->po_group_import_tax_book;
                $po_group_import_tax_book = $item->po_group->total_import_tax_book_percent;
                // $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;
                $import_tax = $item->import_tax_book;
                $total_price = $item->total_unit_price_in_thb;
                $book_tax = (($import_tax / 100) * $total_price);
                $check_book_tax = (($po_group_import_tax_book * $item->po_group->total_buying_price_in_thb) / 100);
                if ($check_book_tax != 0) {
                    $book_tax = round($book_tax, 2);
                } else {
                    $count = PoGroupProductDetail::where('status', 1)->where('po_group_id', $item->po_group_id)->count();
                    $book_tax = (1 / $count) * $item->total_unit_price_in_thb;
                    $book_tax = round($book_tax, 2);
                }
                if ($total_import_tax != 0) {
                    $weighted = (($book_tax / $total_import_tax) * 100);
                    $weighted = number_format($weighted, 2, '.', '') . '%';
                } else {
                    $weighted = '0%';
                }



                $total_import_tax = $item->po_group->po_group_import_tax_book;
                $po_group_import_tax_book = $item->po_group->total_import_tax_book_percent;
                // $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;
                $import_tax = $item->import_tax_book;
                $total_price = $item->total_unit_price_in_thb;
                $book_tax = (($import_tax / 100) * $total_price);


                $check_book_tax = (($po_group_import_tax_book * $item->po_group->total_buying_price_in_thb) / 100);


                if ($check_book_tax != 0) {
                    $book_tax = round($book_tax, 2);
                } else {
                    // $count = PurchaseOrderDetail::whereIn('po_id',PoGroupDetail::where('po_group_id',$item->po_group_id)->pluck('purchase_order_id'))->count();
                    $count = PoGroupProductDetail::where('status', 1)->where('po_group_id', $item->po_group_id)->count();
                    $book_tax = (1 / $count) * $item->total_unit_price_in_thb;
                    $book_tax = round($book_tax, 2);
                }
                if ($total_import_tax != 0) {
                    $weighted_e = ($book_tax / $total_import_tax);
                } else {
                    $weighted_e = 0;
                }
                $tax = $item->po_group->tax;
                $actual_tax =  number_format(($weighted_e * $tax), 2, '.', '');

                $final_book_percent = 0;
                if ($item->actual_tax_price == NULL) {
                    $group_tax = $item->po_group->tax;
                    $find_item_tax_value = $item->import_tax_book / 100 * $item->total_unit_price_in_thb;
                    if ($final_book_percent != 0) {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;
                        if ($item->quantity_inv == 0) {
                            $actual_tax = 0;
                        } else {
                            $actual_tax = number_format(round($find_item_tax * $group_tax, 2) / $item->quantity_inv, 2, '.', '');
                        }
                    } else {
                        $total_import_tax = $item->po_group->po_group_import_tax_book;
                        $po_group_import_tax_book = $item->po_group->total_import_tax_book_percent;
                        $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;

                        $import_tax = $item->import_tax_book;
                        $total_price = $item->total_unit_price_in_thb;
                        $book_tax = (($import_tax / 100) * $total_price);


                        $check_book_tax = (($po_group_import_tax_book * $total_buying_price_in_thb) / 100);


                        if ($check_book_tax != 0) {
                            $book_tax = round($book_tax, 2);
                        } else {
                            $book_tax = (1 / $count) * $item->total_unit_price_in_thb;
                            $book_tax = round($book_tax, 2);
                        }
                        if ($total_import_tax != 0) {
                            $weighted = ($book_tax / $total_import_tax);
                        } else {
                            $weighted = 0;
                        }
                        $tax = $item->po_group->tax;
                        $actual_tax = number_format(($weighted * $tax), 2, '.', '');
                    }
                } else {
                    $actual_tax = number_format($item->actual_tax_price, 2, '.', '');
                }


                $actual_tax_percent = $item->actual_tax_percent;
                $actual_tax_percent = number_format($actual_tax_percent, 2, '.', '') . '%';


                if ($occurrence == 1) {
                    $purchase_orders_ids =  PurchaseOrder::where('po_group_id', $item->po_group_id)->where('supplier_id', $item->supplier_id)->pluck('id')->toArray();
                    $pod = PurchaseOrderDetail::select('po_id', 'order_id')->whereIn('po_id', $purchase_orders_ids)->where('product_id', $item->product_id)->get();
                    $order = Order::find($pod[0]->order_id);
                    if ($order !== null) {
                        $customer = $order->customer->reference_name;
                    } else {
                        $customer = "N.A";
                    }
                } else {
                    $customer = '--';
                }

                if ($item->unit_gross_weight == NULL) {
                    $total_gross_weight = $item->total_gross_weight != null ? $item->total_gross_weight : 0;
                    $qty_inv = $item->quantity_inv != null ? $item->quantity_inv : 0;

                    if ($qty_inv != 0) {
                        $u_g_weight = ($total_gross_weight / $qty_inv);
                    } else {
                        $u_g_weight = 0;
                    }
                } else {
                    $u_g_weight = $item->unit_gross_weight != null ? $item->unit_gross_weight : 0;
                }

                $product_note = $item->product != null ? $item->product->product_notes : null;
                $unit_extra_cost = $item->unit_extra_cost != null ? $item->unit_extra_cost : 0;
                $unit_extra_tax = $item->unit_extra_tax != null ? $item->unit_extra_tax : 0;

                $purchasing_price_eur_with_vat = $item->unit_price_with_vat != null ? number_format($item->unit_price_with_vat, 2, '.', '') : 0;
                $total_purchasing_price_with_vat = $item->total_unit_price_with_vat != null ? number_format($item->total_unit_price_with_vat, 2, '.', '') : 0;

                $purchasing_price_in_thb_with_vat = $item->unit_price_in_thb_with_vat != null ? number_format($item->unit_price_in_thb_with_vat, 2, '.', '') : 0;
                $total_purchasing_price_thb_with_vat = $item->total_unit_price_in_thb_with_vat != null ? number_format($item->total_unit_price_in_thb_with_vat, 2, '.', '') : 0;

                //new column data starts here

                $pogpd_vat_actual     = number_format($item->pogpd_vat_actual, 2, '.', '');
                $import_tax_book_col  = number_format($item->import_tax_book, 2, '.', '');

                $all_pgpd = PoGroupProductDetail::where('status', 1)->where('po_group_id', $item->po_group_id)->count();
                $po_group_vat_actual_percent = $item->po_group->po_group_vat_actual_percent;
                $total_buying_price_in_thb_of_group = $item->po_group->total_buying_price_in_thb;
                $import_tax_of_item = $item->pogpd_vat_actual;
                $total_price_of_item = $item->total_unit_price_in_thb;
                $book_tax_of_item = (($import_tax_of_item / 100) * $total_price_of_item);

                $check_book_tax = (($po_group_vat_actual_percent * $total_buying_price_in_thb_of_group) / 100);

                if ($check_book_tax != 0) {
                    $book_vat_actual = number_format($book_tax_of_item, 2, '.', '');
                } else {
                    $book_tax_of_item = (1 / $all_pgpd) * $item->total_unit_price_in_thb;
                    $book_vat_actual = number_format($book_tax_of_item, 2, '.', '');
                }

                if ($item->vat_weighted_percent == null) {
                    $group_tax = $item->po_group->vat_actual_tax;
                    $find_item_tax_value = $item->pogpd_vat_actual / 100 * $item->total_unit_price_in_thb;

                    $import_tax = $item->pogpd_vat_actual;
                    $total_price = $item->total_unit_price_in_thb;
                    $book_tax = (($import_tax / 100) * $total_price);

                    if ($book_tax != 0) {
                        $vat_weighted_per = ($final_vat_actual_percent_of_group / $book_tax) * 100;
                    } else {
                        $vat_weighted_per = 0;
                    }

                    $vat_weighted_percent = $vat_weighted_per . " %";

                    // $find_item_tax_value = $item->pogpd_vat_actual/100 * $item->total_unit_price_in_thb_with_vat;
                    if ($final_vat_actual_percent_of_group != 0 && $group_tax != 0) {
                        $find_item_tax = $find_item_tax_value / $final_vat_actual_percent_of_group;
                        $cost = $find_item_tax * $group_tax;
                        if ($group_tax != 0) {
                            $vat_weighted_percent = number_format(($cost / $group_tax) * 100, 4, '.', '') . " %";
                        } else {
                            $vat_weighted_percent = "0" . " %";
                        }
                    } else {
                        $po_group_vat_actual = $item->po_group->po_group_vat_actual;
                        $po_group_vat_percent = $item->po_group->po_group_vat_actual_percent;
                        $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb_with_vat;

                        $vat_actual = $item->pogpd_vat_actual;
                        $total_price = $item->total_unit_price_in_thb_with_vat;
                        // $total_price = $item->total_unit_price_in_thb;
                        $vat_tax = (($vat_actual / 100) * $total_price);


                        $check_book_tax = (($po_group_vat_percent * $total_buying_price_in_thb) / 100);


                        if ($check_book_tax != 0) {
                            $vat_tax = round($vat_tax, 2);
                        } else {
                            $vat_tax = (1 / $all_pgpd) * $item->total_unit_price_in_thb_with_vat;
                            // $vat_tax = (1/$all_pgpd)* $item->total_unit_price_in_thb;
                            $vat_tax = round($vat_tax, 2);
                        }
                        if ($po_group_vat_actual != 0) {
                            $vat_weighted = (($vat_tax / $po_group_vat_actual) * 100);
                        } else {
                            $vat_weighted = 0;
                        }

                        $vat_weighted_percent = number_format($vat_weighted, 4, '.', '') . '%';
                    }
                } else {
                    $vat_weighted_percent = number_format($item->vat_weighted_percent, 4, '.', '') . '%';
                }

                if ($item->pogpd_vat_actual_price == NULL) {
                    $group_tax = $item->po_group->vat_actual_tax;
                    $find_item_tax_value = $item->pogpd_vat_actual / 100 * $item->total_unit_price_in_thb;

                    $pogpd_vat_actual_price_of_item = number_format($find_item_tax_value, 2, '.', '');
                } else {
                    $pogpd_vat_actual_price_of_item = number_format($item->pogpd_vat_actual_price, 2, '.', '');
                }

                if ($item->occurrence > 1) {
                    $all_ids = PurchaseOrder::where('po_group_id', $item->po_group_id)->where('supplier_id', $item->supplier_id)->pluck('id');
                    $all_record_vat_tax = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $item->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->sum('pod_vat_actual_total_price_in_thb');
                    $total_pogpd_vat_actual_price = number_format($all_record_vat_tax, 2, '.', '');
                } else {
                    $total_pogpd_vat_actual_price = number_format(($item->pogpd_vat_actual_price * $item->quantity_inv), 2, '.', '');
                }

                if ($item->pogpd_vat_actual_percent_val == NULL) {
                    $find_item_tax_value = (($item->pogpd_vat_actual / 100) * $item->total_unit_price_in_thb) * $item->quantity_inv;
                    if ($item->total_unit_price_in_thb != 0) {
                        $p_vat_percent = ($find_item_tax_value / $item->total_unit_price_in_thb) * 100;
                    } else {
                        $p_vat_percent = 0;
                    }

                    $pogpd_vat_actual_percent_val = $p_vat_percent . ' %';
                } else {
                    $pogpd_vat_actual_percent_val = number_format($item->pogpd_vat_actual_percent_val, 2, '.', '') . '%';
                }

                $total_import_tax_thb =  number_format(($item->actual_tax_price * $item->quantity_inv), 2, '.', '');
                $or_qty = $item->purchase_order_detail != null ? ($item->purchase_order_detail->order_product != null ? round($item->purchase_order_detail->order_product->quantity, 3) : null) : null;
                $supplier_product = $item->product->supplier_products->where('supplier_id', $item->supplier_id)->first();
                $supplier_description = $supplier_product->supplier_description != null ? $supplier_product->supplier_description : '--';



                $data[] = [
                    'po_no'                      => $po_number,
                    'po_id'                      => $po_id,
                    'pod_id'                     => $pod_id,
                    'pogpd_id'                   => $pogpd_id,
                    'discount'                   => $discount,
                    'order_warehouse'            => $order_warehouse,
                    'order_no'                   => $order_no,
                    'sup_ref_no'                 => $reference_number,
                    'supplier'                   => $supplier,
                    'supplier_description'       => $supplier_description,
                    'pf_no'                      => $prod_reference_number,
                    'brand'                      => $brand,
                    'description'                => $desc,
                    'avg_weight'                 => $avg_weight,
                    'type'                       => $type,
                    'customer'                   => $customer,
                    'buying_unit'                => $unit,
                    'qty_ordered'                => $qty_ordered,
                    'customer_qty'                => $or_qty,
                    'qty_inv'                    => $qty,
                    'total_gross_weight'         => number_format($pod_total_gross_weight, 3, '.', ''),
                    'total_extra_cost_thb'       => $pod_total_extra_cost,
                    'total_extra_tax_thb'        => $pod_total_extra_tax,
                    'purchasing_price_eur'       => $buying_price,
                    'purchasing_price_eur_with_vat' => $purchasing_price_eur_with_vat,
                    'total_purchasing_price'     => $total_buying_price,
                    'total_purchasing_price_with_vat' => $total_purchasing_price_with_vat,
                    'currency_conversion_rate'   => $currency_conversion_rate,
                    'purchasing_price_thb'       => $buying_price_in_thb,
                    'purchasing_price_thb_with_vat'   => $purchasing_price_in_thb_with_vat,
                    'total_purchasing_price_thb' => $total_buying_price_in_thb_item,
                    'total_purchasing_price_thb_with_vat' => $total_purchasing_price_thb_with_vat,
                    'import_tax_book_percent'    => $import_tax_book,
                    'freight_thb'                => $freight,
                    'total_freight'                => $total_freight,
                    'landing_thb'                => $landing,
                    'total_landing'                => $total_landing,
                    'book_percent_tax'           => $book_tax,
                    'weighted_percent'           => $item->po_group->tax != null ? $weighted : '--',
                    'actual_tax'                 => $actual_tax,
                    'actual_tax_percent'         => $actual_tax_percent,
                    'sub_row'                    => 0,
                    'product_note'               => $product_note,
                    'gross_weight'               => number_format($u_g_weight, 3, '.', ''),
                    'extra_cost'                 => number_format($unit_extra_cost, 2, '.', ''),
                    'extra_tax'                  => number_format($unit_extra_tax, 2, '.', ''),
                    'pogpd_vat_actual'  => $pogpd_vat_actual,
                    'book_vat_total'    => $book_vat_actual,
                    'vat_weighted_percent'  => $vat_weighted_percent,
                    'pogpd_vat_actual_price' => $pogpd_vat_actual_price_of_item,
                    'total_pogpd_vat_actual_price'  => $total_pogpd_vat_actual_price,
                    'pogpd_vat_actual_percent_val'  => $pogpd_vat_actual_percent_val,
                    'total_import_tax_thb'          => $total_import_tax_thb,
                    'cogs'          => number_format($item->product_cost, 3, '.', ''),
                    'total_cogs'          => number_format($item->product_cost * $qty, 3, '.', ''),
                ];


                if ($item->occurrence > 1) {
                    $po_group_for_export = $item->po_group;
                    $po_group_detail_item = $item;
                    $all_ids = PurchaseOrder::where('po_group_id', $item->po_group_id)->where('supplier_id', $item->supplier_id)->pluck('id');

                    $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $item->product_id)->get();
                    $total_extra_cost = $item->total_extra_cost != null ? $item->total_extra_cost : 0;
                    $pod_total_extra_cost = number_format($total_extra_cost, 2, '.', '');

                    $total_extra_tax = $item->total_extra_tax != null ? $item->total_extra_tax : 0;
                    // $pod_total_extra_tax = number_format($total_extra_tax,2,'.','');
                    //To find the unit extra cost
                    $uni_ex_cost = $item->unit_extra_cost != null ? $item->unit_extra_cost : 0;
                    foreach ($all_record as $item) {
                        // $pod_total_extra_cost = number_format($uni_ex_cost * $item->quantity,2,'.','');
                        $pod_total_extra_cost = $item->total_extra_cost != null ?  number_format($item->total_extra_cost, 2, '.', '') : 0;
                        $pod_total_extra_tax = $item->total_extra_tax != null ? number_format($item->total_extra_tax, 2, '.', '') : 0;

                        $unit_extra_cost = $item->unit_extra_cost != null ? number_format($item->unit_extra_cost, 2, '.', '') : 0;
                        $unit_extra_tax = $item->unit_extra_tax != null ? number_format($item->unit_extra_tax, 2, '.', '') : 0;
                        $discount = $item->discount;
                        //return $item->PurchaseOrder->ref_id !== null ? $item->PurchaseOrder->ref_id : "--" ;
                        if ($item->PurchaseOrder->ref_id !== null) {
                            $po_nos = $item->PurchaseOrder->ref_id;
                            $po_id = $item->PurchaseOrder->id;
                            $pod_id = $item->id;
                        } else {
                            $po_nos = "--";
                            $pod_id = "--";
                            $po_id = "--";
                        }

                        $order = Order::find(@$item->order_id);
                        $order_warehouses = $order !== null ? $order->user->get_warehouse->warehouse_title : "--";

                        $order = Order::find(@$item->order_id);
                        //return $order !== null ? $order->ref_id : "--" ;
                        if ($order !== null) {
                            $order_nos = null;
                            if ($order->primary_status == 3) {
                                $order_nos = @$order->in_status_prefix . '-' . $order->in_ref_prefix . $order->in_ref_id;
                            } elseif ($order->primary_status == 2) {
                                $order_nos = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
                            } elseif ($order->primary_status == 17) {
                                $order_nos = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
                            } else {
                                $order_nos = @$order->status_prefix . '-' . $order->ref_prefix . $order->ref_id;
                            }
                        } else {
                            $order_nos = "N.A";
                        }


                        if ($item->PurchaseOrder->supplier_id !== NULL) {
                            $sup_name = Supplier::select('id', 'reference_name')->where('id', $item->PurchaseOrder->supplier_id)->first();
                            $supplier_ref_names = $sup_name->reference_name;
                        } else {
                            $sup_name = Warehouse::where('id', $item->PurchaseOrder->from_warehouse_id)->first();
                            $supplier_ref_names = $sup_name->warehouse_title;
                        }


                        if ($item->PurchaseOrder->supplier_id !== NULL) {
                            $sup_name = SupplierProducts::select('product_supplier_reference_no')->where('supplier_id', $item->PurchaseOrder->supplier_id)->where('product_id', $item->product_id)->first();
                            $supplier_ref_nos = $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no : "--";
                        } else {
                            $supplier_ref_nos = "N.A";
                        }

                        $product_ref_nos = $item->product->refrence_code;
                        $brand = $item->product->brand != null ? $item->product->brand : '--';
                        $short_descs = $item->product->short_desc != null ? $item->product->short_desc : '--';
                        $avg_weight_col = $item->product->weight != null ? $item->product->weight : '--';
                        $type = $item->product->productType != null ? $item->product->productType->title : '--';
                        $buying_units = $item->product->units->title != null ? $item->product->units->title : '--';

                        // if($item->order_product_id != null)
                        // {
                        //   $sup_name = OrderProduct::select('quantity')->where('id',$item->order_product_id)->first();
                        //   $quantity_ordereds = $sup_name->quantity;
                        // }
                        // else
                        // {
                        //   $quantity_ordereds = '--';
                        // }
                        $decimals = $item->product != null ? ($item->product->units != null ? $item->product->units->decimal_places : 0) : 0;
                        $quantity_ordereds = $item->desired_qty !== null ? number_format(@$item->desired_qty, $decimals, '.', ',') : "--";
                        $quantity_invs = $item->quantity;
                        $pod_total_gross_weights = $item->pod_total_gross_weight != null ? number_format($item->pod_total_gross_weight, 3, '.', '') : '';

                        // $total_extra_costs =  "--" ;

                        // $total_extra_tax  =  "--" ;

                        $buying_prices = $item->pod_unit_price != null ? number_format($item->pod_unit_price, 3, '.', '') : '--';

                        $total_buying_price_os = $item->pod_total_unit_price != null ? number_format($item->pod_total_unit_price, 3, '.', '') : '--';
                        if ($item->currency_conversion_rate != null && $item->currency_conversion_rate != 0) {
                            $currency_conversion_rates = $item->currency_conversion_rate != null ? number_format((1 / $item->currency_conversion_rate), 2, '.', '') : 0;
                        } else {
                            $currency_conversion_rates = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0;
                        }

                        // $currency_conversion_rates = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0 ;

                        $unit_price_in_thbs = $item->unit_price_in_thb != null ? number_format($item->unit_price_in_thb, 3, '.', '') : '--';

                        $total_buying_prices = $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb, 3, '.', '') : '--';

                        $import_tax_books =  number_format($item->pod_import_tax_book, 2, '.', '');

                        $freights = number_format($item->pod_freight, 2, '.', '');

                        $landings = number_format($item->pod_landing, 2, '.', '');

                        $book_taxs = number_format($item->pod_import_tax_book_price, 2, '.', '');

                        $total_freight = number_format($original_freight_without_rounding * $item->quantity, 2, '.', '');
                        $total_landing = number_format($original_landing_without_rounding * $item->quantity, 2, '.', '');

                        $weighteds =   "--";

                        $actual_taxs =   "--";

                        $actual_tax_percents =  number_format($item->pod_actual_tax_percent, 2, '.', '') . '%';

                        $order = Order::find($item->order_id);
                        if ($order !== null) {
                            $customer = $order->customer->reference_name;
                        } else {
                            $customer = "N.A";
                        }

                        $purchasing_price_eur_with_vat = $item->pod_unit_price_with_vat != null ? number_format($item->pod_unit_price_with_vat, 2, '.', '') : 0;
                        $total_purchasing_price_with_vat = $item->pod_total_unit_price_with_vat != null ? number_format($item->pod_total_unit_price_with_vat, 2, '.', '') : 0;

                        $purchasing_price_in_thb_with_vat = $item->unit_price_with_vat_in_thb != null ? number_format($item->unit_price_with_vat_in_thb, 2, '.', '') : 0;
                        $total_purchasing_price_thb_with_vat = $item->total_unit_price_with_vat_in_thb != null ? number_format($item->total_unit_price_with_vat_in_thb, 2, '.', '') : 0;

                        //new column data starts here

                        $pogpd_vat_actual     = number_format($po_group_detail_item->pogpd_vat_actual, 2, '.', '');
                        $import_tax_book_col  = number_format($po_group_detail_item->import_tax_book, 2, '.', '');

                        $all_pgpd = PoGroupProductDetail::where('status', 1)->where('po_group_id', $po_group_for_export->id)->count();
                        $po_group_vat_actual_percent = $po_group_for_export->po_group_vat_actual_percent;
                        $total_buying_price_in_thb_of_group = $po_group_for_export->total_buying_price_in_thb;
                        $import_tax_of_item = $po_group_detail_item->pogpd_vat_actual;
                        $total_price_of_item = $po_group_detail_item->total_unit_price_in_thb;
                        $book_tax_of_item = (($import_tax_of_item / 100) * $total_price_of_item);

                        $check_book_tax = (($po_group_vat_actual_percent * $total_buying_price_in_thb_of_group) / 100);
                        if ($item->pod_vat_actual_total_price_in_thb != null) {
                            $book_vat_actual = number_format($item->pod_vat_actual_total_price_in_thb, 2, '.', '');
                        } else {
                            if ($check_book_tax != 0) {
                                $book_vat_actual = number_format($book_tax_of_item, 2, '.', '');
                            } else {
                                $book_tax_of_item = (1 / $all_pgpd) * $po_group_detail_item->total_unit_price_in_thb;
                                $book_vat_actual = number_format($book_tax_of_item, 2, '.', '');
                            }
                        }

                        if ($po_group_detail_item->vat_weighted_percent == null) {
                            $group_tax = $po_group_for_export->vat_actual_tax;
                            $find_item_tax_value = $po_group_detail_item->pogpd_vat_actual / 100 * $po_group_detail_item->total_unit_price_in_thb;

                            $import_tax = $po_group_detail_item->pogpd_vat_actual;
                            $total_price = $po_group_detail_item->total_unit_price_in_thb;
                            $book_tax = (($import_tax / 100) * $total_price);

                            if ($book_tax != 0) {
                                $vat_weighted_per = ($final_vat_actual_percent_of_group / $book_tax) * 100;
                            } else {
                                $vat_weighted_per = 0;
                            }

                            $vat_weighted_percent = $vat_weighted_per . " %";

                            // $find_item_tax_value = $po_group_detail_item->pogpd_vat_actual/100 * $po_group_detail_item->total_unit_price_in_thb_with_vat;
                            if ($final_vat_actual_percent_of_group != 0 && $group_tax != 0) {
                                $find_item_tax = $find_item_tax_value / $final_vat_actual_percent_of_group;
                                $cost = $find_item_tax * $group_tax;
                                if ($group_tax != 0) {
                                    $vat_weighted_percent = number_format(($cost / $group_tax) * 100, 4, '.', '') . " %";
                                } else {
                                    $vat_weighted_percent = "0" . " %";
                                }
                            } else {
                                $po_group_vat_actual = $po_group_for_export->po_group_vat_actual;
                                $po_group_vat_percent = $po_group_for_export->po_group_vat_actual_percent;
                                $total_buying_price_in_thb = $po_group_for_export->total_buying_price_in_thb_with_vat;

                                $vat_actual = $po_group_detail_item->pogpd_vat_actual;
                                $total_price = $po_group_detail_item->total_unit_price_in_thb_with_vat;
                                // $total_price = $po_group_detail_item->total_unit_price_in_thb;
                                $vat_tax = (($vat_actual / 100) * $total_price);


                                $check_book_tax = (($po_group_vat_percent * $total_buying_price_in_thb) / 100);


                                if ($check_book_tax != 0) {
                                    $vat_tax = round($vat_tax, 2);
                                } else {
                                    $vat_tax = (1 / $all_pgpd) * $po_group_detail_item->total_unit_price_in_thb_with_vat;
                                    // $vat_tax = (1/$all_pgpd)* $po_group_detail_item->total_unit_price_in_thb;
                                    $vat_tax = round($vat_tax, 2);
                                }
                                if ($po_group_vat_actual != 0) {
                                    $vat_weighted = (($vat_tax / $po_group_vat_actual) * 100);
                                } else {
                                    $vat_weighted = 0;
                                }

                                $vat_weighted_percent = number_format($vat_weighted, 4, '.', '') . '%';
                            }
                        } else {
                            $vat_weighted_percent = number_format($po_group_detail_item->vat_weighted_percent, 4, '.', '') . '%';
                        }

                        if ($po_group_detail_item->pogpd_vat_actual_price == NULL) {
                            $group_tax = $po_group_for_export->vat_actual_tax;
                            $find_item_tax_value = $po_group_detail_item->pogpd_vat_actual / 100 * $po_group_detail_item->total_unit_price_in_thb;

                            $pogpd_vat_actual_price_of_item = number_format($find_item_tax_value, 2, '.', '');
                        } else {
                            $pogpd_vat_actual_price_of_item = number_format($po_group_detail_item->pogpd_vat_actual_price, 2, '.', '');
                        }
                        if ($po_group_detail_item->occurrence > 1) {
                            $all_ids = PurchaseOrder::where('po_group_id', $po_group_for_export->id)->where('supplier_id', $po_group_detail_item->supplier_id)->pluck('id');
                            $all_record_vat_tax = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $po_group_detail_item->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->sum('pod_vat_actual_total_price_in_thb');

                            $total_pogpd_vat_actual_price = number_format($all_record_vat_tax, 2, '.', '');
                        } else {
                            $total_pogpd_vat_actual_price = number_format(($po_group_detail_item->pogpd_vat_actual_price * $po_group_detail_item->quantity_inv), 2, '.', '');
                        }

                        if ($po_group_detail_item->pogpd_vat_actual_percent_val == NULL) {
                            $find_item_tax_value = (($po_group_detail_item->pogpd_vat_actual / 100) * $po_group_detail_item->total_unit_price_in_thb) * $po_group_detail_item->quantity_inv;
                            if ($po_group_detail_item->total_unit_price_in_thb != 0) {
                                $p_vat_percent = ($find_item_tax_value / $po_group_detail_item->total_unit_price_in_thb) * 100;
                            } else {
                                $p_vat_percent = 0;
                            }

                            $pogpd_vat_actual_percent_val = $p_vat_percent . ' %';
                        } else {
                            $pogpd_vat_actual_percent_val = number_format($po_group_detail_item->pogpd_vat_actual_percent_val, 2, '.', '') . '%';
                        }

                        $total_import_tax_thb =  number_format(($po_group_detail_item->actual_tax_price * $po_group_detail_item->quantity_inv), 2, '.', '');
                        if ($item->order_product_id != null) {

                            $html_string = ($item->order_product_id != null ? ($item->order_product->quantity != null ? number_format($item->order_product->quantity, 3, '.', '') : null) : null);
                            $or_qty = $html_string;
                        } else {
                            $or_qty = null;
                        }

                        $supplier_product = $item->product->supplier_products->where('supplier_id', $item->PurchaseOrder->supplier_id)->first();
                        $supplier_description = $supplier_product->supplier_description != null ? $supplier_product->supplier_description : '--';
                        $data[] = [
                            'po_no'                      => $po_nos,
                            'po_id'                      => $po_id,
                            'pod_id'                     => $pod_id,
                            'pogpd_id'                   => $pogpd_id,
                            'discount'                   => $discount,
                            'order_warehouse'            => $order_warehouses,
                            'order_no'                   => $order_nos,
                            'sup_ref_no'                 => $supplier_ref_nos,
                            'supplier'                   => $supplier_ref_names,
                            'supplier_description'       => $supplier_description,
                            'pf_no'                      => $product_ref_nos,
                            'brand'                      => $brand,
                            'description'                => $short_descs,
                            'avg_weight'                 => $avg_weight_col,
                            'type'                       => $type,
                            'customer'                   => $customer,
                            'buying_unit'                => $buying_units,
                            'qty_ordered'                => $quantity_ordereds,
                            'customer_qty'                => $or_qty,
                            'qty_inv'                    => $quantity_invs,
                            'total_gross_weight'         => number_format($pod_total_gross_weights, 3, '.', ''),
                            'total_extra_cost_thb'       => $pod_total_extra_cost,
                            'total_extra_tax_thb'        => $pod_total_extra_tax,
                            'purchasing_price_eur'       => $buying_prices,
                            'purchasing_price_eur_with_vat' => $purchasing_price_eur_with_vat,
                            'total_purchasing_price'     => $total_buying_price_os,
                            'total_purchasing_price_with_vat' => $total_purchasing_price_with_vat,
                            'currency_conversion_rate'   => $currency_conversion_rates,
                            'purchasing_price_thb'       => $unit_price_in_thbs,
                            'purchasing_price_thb_with_vat' => $purchasing_price_in_thb_with_vat,
                            'total_purchasing_price_thb' => $total_buying_prices,
                            'total_purchasing_price_thb_with_vat' => $total_purchasing_price_thb_with_vat,
                            'import_tax_book_percent'    => $import_tax_books,
                            'freight_thb'                => $freight,
                            'total_freight'                => $total_freight,
                            'landing_thb'                => $landing,
                            'total_landing'                => $total_landing,
                            'book_percent_tax'           => $book_taxs,
                            'weighted_percent'           => $po_group_detail_item->po_group->tax != null ? $weighted : '--',
                            'actual_tax'                 => $actual_tax,
                            'actual_tax_percent'         => $actual_tax_percent,
                            'sub_row'                    => 1,
                            'product_note'               => $product_note,
                            'gross_weight'               => number_format($u_g_weight, 3, '.', ''),
                            'extra_cost'                 => number_format($unit_extra_cost, 2, '.', ''),
                            'extra_tax'                  => number_format($unit_extra_tax, 2, '.', ''),
                            'pogpd_vat_actual'  => $pogpd_vat_actual,
                            'book_vat_total'    => $book_vat_actual,
                            'vat_weighted_percent'  => $vat_weighted_percent,
                            'pogpd_vat_actual_price' => $pogpd_vat_actual_price_of_item,
                            'total_pogpd_vat_actual_price'  => $total_pogpd_vat_actual_price,
                            'pogpd_vat_actual_percent_val'  => $pogpd_vat_actual_percent_val,
                            'total_import_tax_thb'          => $total_import_tax_thb,
                            'cogs'          => number_format($item->product->selling_price, 3, '.', ''),
                            'total_cogs'          => number_format($item->product->selling_price * $quantity_invs, 3, '.', ''),
                        ];
                        # code...
                    }
                }

                // $data[] = [
                //   'pogpd_vat_actual'  => $pogpd_vat_actual,
                //   'book_vat_total'    => $book_vat_actual,
                //   'vat_weighted_percent'  => $vat_weighted_percent,
                //   'pogpd_vat_actual_price'=> $pogpd_vat_actual_price_of_item,
                //   'total_pogpd_vat_actual_price'  => $total_pogpd_vat_actual_price,
                //   'pogpd_vat_actual_percent_val'  => $pogpd_vat_actual_percent_val,
                //   'total_import_tax_thb'          => $total_import_tax_thb,
                // ];

                // $data['pogpd_vat_actual'] = $pogpd_vat_actual;
                // $data['book_vat_total'] = $book_vat_actual;
                // $data['vat_weighted_percent'] = $vat_weighted_percent;
                // $data['pogpd_vat_actual_price'] = $pogpd_vat_actual_price_of_item;
                // $data['total_pogpd_vat_actual_price'] = $total_pogpd_vat_actual_price;
                // $data['pogpd_vat_actual_percent_val'] = $pogpd_vat_actual_percent_val;
                // $data['total_import_tax_thb'] = $total_import_tax_thb;

            }

            foreach (array_chunk($data, 1500) as $t) {
                DB::table('products_receiving_record_importings')->insert($t);
            }

            if ($status == "OPEN") {
                $typeCol = "importing_open_product_receiving";
            } elseif ($status == "CLOSE") {
                // $typeCol = "importing_closed_product_receiving";
                $typeCol = "importing_open_product_receiving";
            }

            $not_visible_arr = [];
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', $typeCol)->where('user_id', $user_id)->first();

            if ($not_visible_columns != null) {
                $not_visible_arr = explode(',', $not_visible_columns->hide_columns);
            }

            $col_display_pref = [];
            $col_display_cols = ColumnDisplayPreference::select('display_order')->where('type', $typeCol)->where('user_id', $user_id)->first();


            if ($col_display_cols != null) {
                if ($col_display_cols->display_order != NULL && $col_display_cols->display_order != '') {
                    $col_display_pref = explode(',', $col_display_cols->display_order);
                } else {
                    $col_orders = "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51";
                    $col_display_pref = explode(',', $col_orders);
                }
            } else {
                $col_orders = "0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30,31,32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51";
                $col_display_pref = explode(',', $col_orders);
            }

            if (count($col_display_pref) <= 48) {
                $count = count($col_display_pref);
                for ($i = $count + 1; $i < 49; $i++) {
                    array_push($col_display_pref, $i);
                }
            }

            $records = ProductsReceivingRecordImporting::get();
            $group_ref = PoGroup::find($request);
            $group_ref = $group_ref->ref_id;
            $return = \Excel::store(new ImportingProductReceivingRecord($records, $not_visible_arr, $col_display_pref), 'Importing-Product-Receiving-' . $group_ref . '.xlsx');
            if ($return) {
                $exportLog = ProductReceivingExportLog::where('group_id', $request)->first();
                if ($exportLog == null) {
                    $exportLog = new ProductReceivingExportLog();
                    $exportLog->user_id = $user_id;
                    $exportLog->group_id = $request;
                    $exportLog->last_downloaded = date('Y-m-d H:i:s');
                    $exportLog->save();
                } else {
                    ProductReceivingExportLog::where('group_id', $request)->update(['last_downloaded' => date('Y-m-d H:i:s')]);
                }

                ExportStatus::where('type', 'products_receiving_importings')->update(['status' => 0, 'last_downloaded' => date('Y-m-d')]);

                return response()->json(['msg' => 'File Saved']);
            }
        } catch (Exception $e) {
            dd($e);
            $this->failed($e);
        } catch (MaxAttemptsExceededException $e) {
            $this->failed($e);
        }
    }
    public function failed($exception)
    {
        ExportStatus::where('type', 'products_receiving_importings')->update(['status' => 2, 'exception' => $exception->getMessage()]);
        $failedJobException = new FailedJobException();
        $failedJobException->type = "Products Receiving Importings";
        $failedJobException->exception = $exception->getMessage();
        $failedJobException->save();
    }
}
