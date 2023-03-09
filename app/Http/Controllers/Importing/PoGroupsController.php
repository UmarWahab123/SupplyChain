<?php

namespace App\Http\Controllers\Importing;

use App\ExportStatus;
use App\Exports\ImportingProductReceivingRecord;
use App\FiltersForImportingReceivingProducts;
use App\Helpers\Datatables\PoGroupDatatable;
use App\Helpers\POGroupSortingHelper;
use App\Http\Controllers\Controller;
use App\ImportFileHistory;
use App\Imports\BulkProductImportInGroupDetail;
use App\Jobs\Importing\ConfirmGroupImportData;
use App\Jobs\ProductReceivingExportJob;
use App\Jobs\ProductsReceivingImportJob;
use App\Jobs\UpdateOldRecord;
use App\Models\Common\ColumnDisplayPreference;
use App\Models\Common\Configuration;
use App\Models\Common\Courier;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use App\Models\Common\PoGroup;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\PoGroupProductDetail;
use App\Models\Common\Product;
use App\Models\Common\ProductReceivingHistory;
use App\Models\Common\PurchaseOrders\PoGroupStatusHistory;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\StockManagementOut;
use App\Models\Common\StockOutHistory;
use App\Models\Common\Supplier;
use App\Models\Common\SupplierProducts;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Warehouse;
use App\Models\ProductReceivingImportTemp;
use App\Notification;
use App\PoGroupProductHistory;
use App\ProductHistory;
use App\ProductReceivingExportLog;
use App\QuotationConfig;
use App\StatusCheckForCompleteProductsExport;
use App\User;
use App\Variable;
use Auth;
use Carbon\Carbon;
use DB;
use Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Yajra\Datatables\Datatables;

class PoGroupsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $vairables = Variable::select('slug', 'standard_name', 'terminology')->get();
        $global_terminologies = [];
        foreach ($vairables as $variable) {
            if ($variable->terminology != null) {
                $global_terminologies[$variable->slug] = $variable->terminology;
            } else {
                $global_terminologies[$variable->slug] = $variable->standard_name;
            }
        }

        $config = Configuration::first();
        $sys_name = $config->company_name;
        $sys_color = $config;
        $sys_logos = $config;
        $part1 = explode("#", $config->system_color);
        $part1 = array_filter($part1);
        $value = implode(",", $part1);
        $num1 = hexdec($value);
        $num2 = hexdec('001500');
        $sum = $num1 + $num2;
        $sys_border_color = "#";
        $sys_border_color .= dechex($sum);
        $part1 = explode("#", $config->btn_hover_color);
        $part1 = array_filter($part1);
        $value = implode(",", $part1);
        $number = hexdec($value);
        $sum = $number + $num2;
        $btn_hover_border = "#";
        $btn_hover_border .= dechex($sum);
        $current_version = '3.8';

        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies, 'sys_name' => $sys_name, 'sys_logos' => $sys_logos, 'sys_color' => $sys_color, 'sys_border_color' => $sys_border_color, 'btn_hover_border' => $btn_hover_border, 'current_version' => $current_version]);
    }

    public function receivingQueue()
    {
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        return $this->render('importing.po-groups.new-incompleted-groups', compact('dummy_data'));
    }

    public function getImportingReceivingPoGroups(Request $request)
    {

        $is_con = $request->dosortby;
        if ($request->dosortby == 3) {
            $query = PoGroup::whereHas('FromWarehouse', function ($q) {
                $q->where('is_bonded', 1);
            })->where('is_confirm', 0);
        } else {
            $query = PoGroup::where('po_groups.from_warehouse_id', NULL);
        }

        if ($request->dosortby == 2) {
            $query = $query->where('is_cancel', $request->dosortby);
        } else if ($request->dosortby == 3) {
        } else {
            $query = $query->where('po_groups.is_review', $request->dosortby)->where('is_cancel', NULL);
        }

        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            $query->where('po_groups.target_receive_date', '>=', $date . ' 00:00:00');
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            $query->where('po_groups.target_receive_date', '<=', $date . ' 23:59:00');
        }
        $query->with('ToWarehouse', 'po_courier', 'po_group_detail.purchase_order.PoSupplier', 'po_group_product_details_one.get_supplier', 'po_group_product_details_one.get_warehouse')->withCount('po_group_detail');
        $query = PoGroup::POGroupSorting($request, $query);
        $couriers = Courier::select('id', 'title')->get();

        $dt = Datatables::of($query);
        $add_columns = ['warehouse', 'note', 'target_receive_date', 'po_total', 'issue_date', 'net_weight', 'landing', 'freight', 'tax', 'quantity', 'supplier_ref_no', 'courier', 'po_number', 'id'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column, $couriers) {
                return PoGroupDatatable::returnAddColumn($column, $item, $couriers);
            });
        }

        $edit_columns = ['bill_of_landing_or_airway_bill'];

        foreach ($edit_columns as $column) {
            $dt->editColumn($column, function ($item) use ($column) {
                return PoGroupDatatable::returnEditColumn($column, $item);
            });
        }

        $filter_columns = ['courier', 'po_number', 'id'];

        foreach ($filter_columns as $column) {
            $dt->filterColumn($column, function ($item, $keyword) use ($column) {
                return PoGroupDatatable::returnFilterColumn($column, $item, $keyword);
            });
        }

        $dt->rawColumns(['po_number', 'bill_of_lading', 'airway_bill', 'target_receive_date', 'tax', 'freight', 'landing', 'bill_of_landing_or_airway_bill', 'courier', 'vendor', 'vendor_ref_no', 'supplier_ref_no', 'id', 'issue_date']);

        return $dt->make(true);
    }

    public function viewPoNumbers(Request $request)
    {
        $i = 1;
        $po_group = PoGroup::find($request->id);
        $po_group_detail = $po_group->po_group_detail;
        $html_string = '';
        foreach ($po_group_detail as $p_g_d) {
            $link = '<a target="_blank" href="' . route('get-purchase-order-detail', ['id' => $p_g_d->purchase_order->id]) . '" title="View Detail"><b>' . $p_g_d->purchase_order->ref_id . '</b></a>';
            $html_string .= '<tr><td style="text-align:center">' . $i . '</td><td style="text-align:center">' . @$link . '</td></tr>';
            $i++;
        }
        return $html_string;
    }

    public function viewSupplierName(Request $request)
    {
        $i = 1;
        $po_group = PoGroup::find($request->id);
        $po_group_detail = $po_group->po_group_detail;
        $html_string = '';
        foreach ($po_group_detail as $p_g_d) {
            if ($p_g_d->purchase_order->supplier_id != null) {
                $ref_no = $p_g_d->purchase_order->PoSupplier->reference_number;
                $name = $p_g_d->purchase_order->PoSupplier->reference_name;
            } else {
                $ref_no = $p_g_d->purchase_order->PoWarehouse->location_code;
                $name = $p_g_d->purchase_order->PoWarehouse->warehouse_title;
            }
            $html_string .= '<tr><td style="text-align:center">' . $i . '</td><td style="text-align:center">' . @$ref_no . '</td><td style="text-align:center">' . @$name . '</td></tr>';
            $i++;
        }
        return $html_string;
    }

    public function receivingQueueDetail($id)
    {
        $po_group = PoGroup::find($id);
        $product_receiving_history = ProductReceivingHistory::with('get_user')->where('updated_by', auth()->user()->id)->where('po_group_id', $id)->orderBy('id', 'DESC')->get();
        $status_history = PoGroupStatusHistory::with('get_user')->where('po_group_id', $id)->get();
        $statusCheck = StatusCheckForCompleteProductsExport::where('id', 2)->first();
        $exportLog = ProductReceivingExportLog::where('group_id', $id)->first();
        $last_downloaded = null;
        if ($exportLog != null) {
            $last_downloaded = $exportLog->last_downloaded;
        }

        if ($po_group->from_warehouse_id != null) {
            $check_bond = $po_group->FromWarehouse->is_bonded;
        } else {
            $check_bond = 0;
        }

        //to find to warehouse is bonded or not
        if ($po_group->warehouse_id != null) {
            $check_to_bond = $po_group->ToWarehouse->is_bonded;
        } else {
            $check_to_bond = 0;
        }

        #to find the po in the group
        $pos = PoGroupDetail::where('po_group_id', $po_group->id)->pluck('purchase_order_id')->toArray();
        $pos_supplier_invoice_no = PurchaseOrder::select('id', 'invoice_number')->whereNotNull('invoice_number')->whereIn('id', $pos)->get();
        $getCouriers = Courier::where('is_deleted', 0)->get();
        $table_hide_columns = TableHideColumn::select('hide_columns')->where('type', 'importing_open_product_receiving')->where('user_id', Auth::user()->id)->first();
        $display_prods = ColumnDisplayPreference::where('type', 'importing_open_product_receiving')->where('user_id', Auth::user()->id)->first();

        $allow_custom_invoice_number = '';
        $show_custom_line_number = '';
        $show_supplier_invoice_number = '';
        $globalAccessConfig4 = QuotationConfig::where('section', 'groups_management_page')->first();
        if ($globalAccessConfig4) {
            $globalaccessForGroups = unserialize($globalAccessConfig4->print_prefrences);
            foreach ($globalaccessForGroups as $val) {
                if ($val['slug'] === "show_custom_invoice_number") {
                    $allow_custom_invoice_number = $val['status'];
                }
                if ($val['slug'] === "show_custom_line_number") {
                    $show_custom_line_number = $val['status'];
                }
                if ($val['slug'] === "supplier_invoice_number") {
                    $show_supplier_invoice_number = $val['status'];
                }
            }
        }

        return $this->render('importing.po-groups.products-receiving', compact('po_group', 'id', 'product_receiving_history', 'status_history', 'statusCheck', 'last_downloaded', 'pos_supplier_invoice_no', 'getCouriers', 'table_hide_columns', 'display_prods', 'check_bond', 'check_to_bond', 'allow_custom_invoice_number', 'show_custom_line_number', 'show_supplier_invoice_number'));
    }

    public function savePoGroupInfoData(Request $request)
    {
        $po_group = PoGroup::find($request->po_group_id);
        foreach ($request->except('po_group_id') as $key => $value) {
            if ($key == 'target_receive_date') {
                $value = str_replace("/", "-", $value);
                $value = date('Y-m-d', strtotime($value));
                $po_group->$key = $value;
            } else {
                $po_group->$key = $value;
            }
        }
        $po_group->save();

        return response()->json(['success' => true]);
    }

    public function getPoGroupProductDetails(Request $request, $id)
    {
        $group = PoGroup::find($id);
        $all_record = PoGroupProductDetail::where('po_group_product_details.status', 1)->where('po_group_product_details.po_group_id', $id);
        $all_record = $all_record->with('product.supplier_products', 'get_supplier.getCurrency', 'get_supplier.getCurrency', 'product.units', 'product.sellingUnits', 'purchase_order', 'order.user.get_warehouse', 'product.productType', 'order.customer')->select('po_group_product_details.*');

        $all_record = POGroupSortingHelper::ProductReceivingRecordsSorting($request, $all_record);
        $all_pgpd =  PoGroupProductDetail::where('status', 1)->where('po_group_id', $id)->count();

        $final_book_percent = 0;
        $final_vat_actual_percent = 0;
        foreach ($all_record->get() as $value) {
            if ($value->import_tax_book != null && $value->import_tax_book != 0) {
                $final_book_percent = $final_book_percent + (($value->import_tax_book / 100) * $value->total_unit_price_in_thb);
            }

            if ($value->pogpd_vat_actual != null && $value->pogpd_vat_actual != 0) {
                $final_vat_actual_percent = $final_vat_actual_percent + (($value->pogpd_vat_actual / 100) * $value->total_unit_price_in_thb);
            }
        }

        $not_visible_arr = [];
        $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', 'importing_open_product_receiving')->where('user_id', Auth::user()->id)->first();
        if ($not_visible_columns != null) {
            $not_visible_arr = explode(',', $not_visible_columns->hide_columns);
        }
        $dt = Datatables::of($all_record);

        $dt->addColumn('occurrence', function ($item) {
            return $item->occurrence;
        });

        if (!in_array('1', $not_visible_arr)) {
            $dt->addColumn('po_number', function ($item) {
                $occurrence = $item->occurrence;
                if ($occurrence == 1) {
                    // $id = $item->po_group_id;
                    // $pod = PurchaseOrderDetail::select('po_id')->where('product_id',$item->product_id)->whereHas('PurchaseOrder',function($q) use ($id,$item){
                    //     $q->where('po_group_id',$id);
                    //     $q->where('supplier_id',$item->supplier_id);
                    // })->get();
                    // $po = $pod[0]->PurchaseOrder;
                    // if($po->ref_id !== null)
                    // {
                    //     $html_string = '<a target="_blank" href="'.route('get-purchase-order-detail', ['id' => $po->id]).'" title="View Detail"><b>'.$po->ref_id.'</b></a>';
                    //     return $html_string;
                    // }
                    // else
                    // {
                    //     return "--";
                    // }

                    $po = $item->purchase_order;
                    if ($po) {
                        if ($po->ref_id !== null) {
                            $html_string = '<a target="_blank" href="' . route('get-purchase-order-detail', ['id' => $po->id]) . '" title="View Detail"><b>' . $po->ref_id . '</b></a>';
                            return $html_string;
                        } else {
                            return "--";
                        }
                    } else {
                        return "--";
                    }
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('po_number', function ($item) {
                return '--';
            });
        }

        if (!in_array('2', $not_visible_arr)) {
            $dt->addColumn('order_warehouse', function ($item) {
                $occurrence = $item->occurrence;
                if ($occurrence == 1) {
                    // $id = $item->po_group_id;
                    // $pod = PurchaseOrderDetail::select('po_id','order_id')->where('product_id',$item->product_id)->whereHas('PurchaseOrder',function($q) use ($id,$item){
                    //     $q->where('po_group_id',$id);
                    //     $q->where('supplier_id',$item->supplier_id);
                    // })->get();
                    // $order = Order::find($pod[0]->order_id);

                    $order = $item->order;
                    return $order !== null ? $order->user->get_warehouse->warehouse_title : "N.A";
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('order_warehouse', function ($item) {
                return '--';
            });
        }

        if (!in_array('3', $not_visible_arr)) {
            $dt->addColumn('order_no', function ($item) {
                $occurrence = $item->occurrence;
                if ($occurrence == 1) {
                    // $id = $item->po_group_id;
                    // $pod = PurchaseOrderDetail::select('po_id','order_id')->where('product_id',$item->product_id)->whereHas('PurchaseOrder',function($q) use ($id,$item){
                    //     $q->where('po_group_id',$id);
                    //     $q->where('supplier_id',$item->supplier_id);
                    // })->get();
                    // $order = Order::find($pod[0]->order_id);

                    $order = $item->order;
                    if ($order != null) {
                        $ret = $order->get_order_number_and_link($order);
                        $ref_no = $ret[0];
                        $link = $ret[1];

                        $html_string = '<a target="_blank" href="' . route($link, ['id' => $order->id]) . '" title="View Detail"><b>' . $ref_no . '</b></a>';

                        return $html_string;
                    } else {
                        return "N.A";
                    }
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('order_no', function ($item) {
                return '--';
            });
        }

        if (!in_array('4', $not_visible_arr)) {
            $dt->addColumn('reference_number', function ($item) {
                if ($item->supplier_id !== NULL) {
                    // $sup_name = SupplierProducts::where('supplier_id',$item->supplier_id)->where('product_id',$item->product_id)->first();
                    $sup_name = $item->product != null ? $item->product->supplier_products->where('supplier_id', $item->supplier_id)->first() : null;
                    if ($sup_name != null) {
                        $ref_no   = $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no : "--";
                        $ref_no1   = $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no : "";

                        $html_string = '<span class="m-l-15 inputDoubleClicks" id="hs_description" data-fieldvalue="' . $ref_no1 . '">';
                        $html_string .= $ref_no;
                        $html_string .= '</span>';

                        $html_string .= '<input type="text" autocomplete="off" name="sup_ref_no" data-id="' . $item->id . '" class="fieldFocuss  d-none" value="' . $ref_no1 . '" style="width:100%">';
                        return $html_string;
                    } else {
                        return 'N.A';
                    }
                } else {
                    return "N.A";
                }
            });
        } else {
            $dt->addColumn('reference_number', function ($item) {
                return '--';
            });
        }
        $dt->filterColumn('reference_number', function ($query, $keyword) {
            $query = $query->whereIn('supplier_id', SupplierProducts::select('supplier_id')->where('product_supplier_reference_no', 'LIKE', "%$keyword%")->pluck('supplier_id'))->whereIn('product_id', SupplierProducts::select('product_id')->where('product_supplier_reference_no', 'LIKE', "%$keyword%")->pluck('product_id'));
        }, true);

        if (!in_array('5', $not_visible_arr)) {
            $dt->addColumn('supplier', function ($item) {
                if ($item->supplier_id !== NULL) {
                    return  $html_string = '<a target="_blank" href="' . url('get-supplier-detail/' . $item->supplier_id) . '"  ><b>' . $item->get_supplier->reference_name . '</b></a>';
                } else {
                    $sup_name = Warehouse::where('id', $item->from_warehouse_id)->first();
                    return  $html_string = $sup_name->warehouse_title;
                }
            });
        } else {
            $dt->addColumn('supplier', function ($item) {
                return '--';
            });
        }
        $dt->filterColumn('supplier', function ($query, $keyword) {
            $query = $query->whereIn('supplier_id', Supplier::select('id')->where('reference_name', 'LIKE', "%$keyword%")->pluck('id'));
        }, true);

        if (!in_array('6', $not_visible_arr)) {
            $dt->addColumn('supplier_description', function ($item) {
                if ($item->supplier_id !== NULL) {
                    $supplier_product = $item->product->supplier_products->where('supplier_id', $item->supplier_id)->first();
                    if ($supplier_product) {
                        return $supplier_product->supplier_description != null ? $supplier_product->supplier_description : '--';
                    }
                    return '--';
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('supplier_description', function ($item) {
                return '--';
            });
        }

        if (!in_array('7', $not_visible_arr)) {
            $dt->addColumn('prod_reference_number', function ($item) {
                return  $html_string = '<a target="_blank" href="' . url('get-product-detail/' . $item->product_id) . '"><b>' . @$item->product->refrence_code . '</b></a>';
            });
        } else {
            $dt->addColumn('prod_reference_number', function ($item) {
                return '--';
            });
        }

        $dt->filterColumn('prod_reference_number', function ($query, $keyword) {
            $query = $query->whereIn('product_id', Product::select('id')->where('refrence_code', 'LIKE', "%$keyword%")->pluck('id'));
        }, true);
        if (!in_array('8', $not_visible_arr)) {
            $dt->addColumn('brand', function ($item) {
                return $item->product_id != null ? ($item->product->brand != '' ? $item->product->brand : '--') : '--';
            });
        } else {
            $dt->addColumn('brand', function ($item) {
                return '--';
            });
        }
        if (!in_array('9', $not_visible_arr)) {
            $dt->addColumn('desc', function ($item) {
                return $item->product_id != null ? $item->product->short_desc : '';
            });
        } else {
            $dt->addColumn('desc', function ($item) {
                return '--';
            });
        }

        $dt->filterColumn('desc', function ($query, $keyword) {
            $query = $query->whereIn('product_id', Product::select('id')->where('short_desc', 'LIKE', "%$keyword%")->pluck('id'));
        }, true);
        if (!in_array('10', $not_visible_arr)) {
            $dt->addColumn('type', function ($item) {
                return $item->product_id != null ? $item->product->productType->title : '';
            });
        } else {
            $dt->addColumn('type', function ($item) {
                return '--';
            });
        }
        if (!in_array('11', $not_visible_arr)) {
            $dt->addColumn('customer', function ($item) {
                $occurrence = $item->occurrence;
                if ($occurrence == 1) {
                    // $id = $item->po_group_id;
                    // $pod = PurchaseOrderDetail::select('po_id','order_id')->where('product_id',$item->product_id)->whereHas('PurchaseOrder',function($q) use ($id,$item){
                    //     $q->where('po_group_id',$id);
                    //     $q->where('supplier_id',$item->supplier_id);
                    // })->get();
                    // $order = Order::find($pod[0]->order_id);
                    $order = $item->order;
                    if ($order !== null) {
                        $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . $order->customer_id) . '"><b>' . $order->customer->reference_name . '</b></a>';
                        return $html_string . "<span style='visibility:hidden;'>abcabcabcabaaaaaa</span>";
                    } else {
                        return "N.A <span style='visibility:hidden;'>abcabcabcabaaaaaa</span>";
                    }
                } else {
                    return "-- <span style='visibility:hidden;'>abcabcabcabaaaaaa</span>";
                }
            });
        } else {
            $dt->addColumn('customer', function ($item) {
                return '--';
            });
        }
        if (!in_array('12', $not_visible_arr)) {
            $dt->addColumn('unit', function ($item) {
                return $item->product_id != null ? $item->product->units->title : '';
            });
        } else {
            $dt->addColumn('unit', function ($item) {
                return '--';
            });
        }

        if (!in_array('13', $not_visible_arr)) {
            $dt->addColumn('qty_ordered', function ($item) {
                return number_format($item->quantity_ordered, 2, '.', '');
            });
        } else {
            $dt->addColumn('qty_ordered', function ($item) {
                return '--';
            });
        }

        if (!in_array('14', $not_visible_arr)) {
            $dt->addColumn('original_qty', function ($item) {
                if ($item->occurrence == 1) {
                    $or_qty = $item->purchase_order_detail != null ? ($item->purchase_order_detail->order_product != null ? number_format($item->purchase_order_detail->order_product->quantity, 3) : 'Stock') : 'Stock';
                    return $or_qty;
                }
                return '--';
            });
        } else {
            $dt->addColumn('original_qty', function ($item) {
                return '--';
            });
        }
        if (!in_array('15', $not_visible_arr)) {
            $dt->addColumn('qty', function ($item) {

                $ref_id = $item->po_group->ref_id;
                $old_value = $item->po_group->po_group_history($ref_id, 'Quantity Inv', $item->quantity_inv)->first();
                if($item->quantity_inv != $item->quantity_inv_old) {
                    return '<span style="color:red">' .number_format($item->quantity_inv, 3, '.', ''). '</span>'  . ' / ' . $item->quantity_inv_old;
                } else {
                    return number_format($item->quantity_inv, 3, '.', '');
                }

            });
        } else {
            $dt->addColumn('qty', function ($item) {
                return '--';
            });
        }

        if (!in_array('16', $not_visible_arr)) {
            $dt->addColumn('product_notes', function ($item) {
                return  $html_string = $item->product->product_notes != NULL ? $item->product->product_notes : "N.A";
            });
        } else {
            $dt->addColumn('product_notes', function ($item) {
                return '--';
            });
        }
        if (!in_array('17', $not_visible_arr)) {
            $dt->addColumn('pod_unit_gross_weight', function ($item) {
                if ($item->unit_gross_weight == NULL) {
                    $total_gross_weight = $item->total_gross_weight != null ? $item->total_gross_weight : 0;
                    $qty_inv = $item->quantity_inv != null ? $item->quantity_inv : 0;

                    if ($qty_inv != 0) {
                        $u_g_weight = ($total_gross_weight / $qty_inv);
                    } else {
                        $u_g_weight = 0;
                    }

                    $html_string = '<input type="number"  name="unit_gross_weight" data-id="' . $item->id . '" data-fieldvalue="' . $u_g_weight . '" class="fieldFocus" value="' . number_format($u_g_weight, 3, '.', '') . '" style="width:100%">';
                    return $html_string;
                } else {
                    $unit_gross_weight = $item->unit_gross_weight != null ? $item->unit_gross_weight : 0;

                    $html_string = '<input type="number"  name="unit_gross_weight" data-id="' . $item->id . '" data-fieldvalue="' . $unit_gross_weight . '" class="fieldFocus" value="' . number_format($unit_gross_weight, 3, '.', '') . '" style="width:100%">';
                    return $html_string;
                }
            });
        } else {
            $dt->addColumn('pod_unit_gross_weight', function ($item) {
                return '--';
            });
        }

        if (!in_array('18', $not_visible_arr)) {
            $dt->addColumn('pod_total_gross_weight', function ($item) {
                $total_gross_weight = $item->total_gross_weight != null ? $item->total_gross_weight : 0;

                $html_string = '<input type="number"  name="total_gross_weight" data-id="' . $item->id . '" data-fieldvalue="' . $total_gross_weight . '" class="fieldFocus" value="' . number_format($total_gross_weight, 3, '.', '') . '" style="width:100%">';
                return $html_string;
            });
        } else {
            $dt->addColumn('pod_total_gross_weight', function ($item) {
                return '--';
            });
        }

        if (!in_array('19', $not_visible_arr)) {
            $dt->addColumn('pod_unit_extra_cost', function ($item) {
                $unit_extra_cost = $item->unit_extra_cost != null ? $item->unit_extra_cost : 0;
                $occurrence = $item->occurrence;
                if ($occurrence > 1) {
                    $html_string = '<span id="pod_unit_extra_cost_avg_' . $item->id . '">' . number_format($unit_extra_cost, 2, '.', ',') . '</span>';
                } else {
                    $html_string = '<input type="number" name="unit_extra_cost" data-id="' . $item->id . '" data-fieldvalue="' . $unit_extra_cost . '" class="fieldFocus" value="' . number_format($unit_extra_cost, 2, '.', '') . '" style="width:100%">';
                }
                return $html_string;
            });
        } else {
            $dt->addColumn('pod_unit_extra_cost', function ($item) {
                return '--';
            });
        }

        if (!in_array('20', $not_visible_arr)) {
            $dt->addColumn('pod_total_extra_cost', function ($item) {
                $total_extra_cost = $item->total_extra_cost != null ? $item->total_extra_cost : 0;
                $occurrence = $item->occurrence;
                if ($occurrence > 1) {
                    $html_string = '<span id="pod_total_extra_cost_avg_' . $item->id . '">' . number_format($total_extra_cost, 2, '.', ',') . '</span>';
                } else {
                    $html_string = '<input type="number" name="total_extra_cost" data-id="' . $item->id . '" data-fieldvalue="' . $total_extra_cost . '" class="fieldFocus" value="' . number_format($total_extra_cost, 2, '.', '') . '" style="width:100%">';
                }
                return $html_string;
            });
        } else {
            $dt->addColumn('pod_total_extra_cost', function ($item) {
                return '--';
            });
        }

        if (!in_array('21', $not_visible_arr)) {
            $dt->addColumn('pod_unit_extra_tax', function ($item) {
                $unit_extra_tax = $item->unit_extra_tax != null ? $item->unit_extra_tax : 0;
                $occurrence = $item->occurrence;
                if ($occurrence > 1) {
                    $html_string = '<span id="pod_unit_extra_tax_avg_' . $item->id . '">' . number_format($unit_extra_tax, 2, '.', ',') . '</span>';
                } else {
                    $html_string = '<input type="number" name="unit_extra_tax" data-id="' . $item->id . '" data-fieldvalue="' . $unit_extra_tax . '" class="fieldFocus" value="' . number_format($unit_extra_tax, 2, '.', '') . '" style="width:100%">';
                }
                return $html_string;
            });
        } else {
            $dt->addColumn('pod_unit_extra_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('22', $not_visible_arr)) {
            $dt->addColumn('pod_total_extra_tax', function ($item) {
                $total_extra_tax = $item->total_extra_tax != null ? $item->total_extra_tax : 0;
                $occurrence = $item->occurrence;
                if ($occurrence > 1) {
                    $html_string = '<span id="pod_total_extra_tax_avg_' . $item->id . '">' . number_format($total_extra_tax, 2, '.', ',') . '</span>';
                } else {
                    $html_string = '<input type="number" name="total_extra_tax" data-id="' . $item->id . '" data-fieldvalue="' . $total_extra_tax . '" class="fieldFocus" value="' . number_format($total_extra_tax, 2, '.', '') . '" style="width:100%">';
                }
                return $html_string;
            });
        } else {
            $dt->addColumn('pod_total_extra_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('23', $not_visible_arr)) {
            $dt->addColumn('buying_price_wo_vat', function ($item) use ($group) {
                if ($item->unit_price != null) {
                    $buying_price_wo_vat = $item->unit_price;
                    $currency_code = @$item->get_supplier->getCurrency->currency_code;

                    $ref_id = $item->po_group->ref_id;
                    $old_value = $item->po_group->po_group_history($ref_id, 'Unit Price', $item->unit_price)->first();
                    if($old_value['old_value']) {
                        return '<span style="color:red">'. number_format($buying_price_wo_vat, 2, '.', ','). '</span>'  . ' ' . $currency_code . ' / ' . $old_value['old_value'] . ' ' . $currency_code;
                    }
                    return number_format($buying_price_wo_vat, 2, '.', ',') . ' ' . $currency_code;

                } else {
                    $all_items_buying_price = 0;
                    $occurrence = 0;
                    $final_unit_price = 0;
                    $all_pos = $group->purchase_orders->where('supplier_id', $item->supplier_id);
                    foreach ($all_pos as $po) {

                        $same_items = $po->PurchaseOrderDetail->where('product_id', $item->product_id);
                        $all_items_buying_price += $same_items !== null ? $same_items->sum('pod_unit_price') : 0;

                        $occurrence += $same_items->count();
                    }
                    if ($occurrence != 0) {
                        $final_unit_price = $all_items_buying_price / $occurrence;
                    } else {
                        $occurrence = 0;
                    }
                    $currency_code = @$item->get_supplier->getCurrency->currency_code;

                    return $final_unit_price != null ? number_format($final_unit_price, 2, '.', ',') . ' ' . $currency_code : '';
                }
            });
        } else {
            $dt->addColumn('buying_price_wo_vat', function ($item) {
                return '--';
            });
        }
        if (!in_array('24', $not_visible_arr)) {
            $dt->addColumn('buying_price', function ($item) use ($group) {

                if ($item->unit_price != null) {
                    $buying_price = $item->unit_price_with_vat;
                    $currency_code = @$item->get_supplier->getCurrency->currency_code;
                    return number_format($buying_price, 2, '.', ',') . ' ' . $currency_code;
                } else {
                    $all_items_buying_price = 0;
                    $occurrence = 0;
                    $final_unit_price = 0;
                    $all_pos = $group->purchase_orders->where('supplier_id', $item->supplier_id);
                    foreach ($all_pos as $po) {

                        $same_items = $po->PurchaseOrderDetail->where('product_id', $item->product_id);
                        $all_items_buying_price += $same_items !== null ? $same_items->sum('pod_unit_price_with_vat') : 0;
                        // $all_items_buying_price += $same_items !== null ? $same_items->sum('pod_unit_price') : 0;

                        $occurrence += $same_items->count();
                    }
                    if ($occurrence != 0) {
                        $final_unit_price = $all_items_buying_price / $occurrence;
                    } else {
                        $occurrence = 0;
                    }
                    $currency_code = @$item->get_supplier->getCurrency->currency_code;
                    return $final_unit_price != null ? number_format($final_unit_price, 2, '.', ',') . ' ' . $currency_code : '';
                }
            });
        } else {
            $dt->addColumn('buying_price', function ($item) {
                return '--';
            });
        }

        if (!in_array('25', $not_visible_arr)) {
            $dt->addColumn('discount', function ($item) {
                $discount = $item->discount !== null ? $item->discount . ' %' : 0;
                return $discount;
            });
        } else {
            $dt->addColumn('discount', function ($item) {
                return '--';
            });
        }

        if (!in_array('26', $not_visible_arr)) {
            $dt->addColumn('total_buying_price_wo_vat', function ($item) use ($group) {
                if ($item->total_unit_price != null) {
                    $currency_code = @$item->get_supplier->getCurrency->currency_code;
                    return $item->total_unit_price != null ? number_format($item->total_unit_price, 2, '.', ',') . ' ' . $currency_code : '';
                } else {
                    $all_items_total_buying_price = 0;
                    $occurrence = 0;
                    $all_pos = $group->purchase_orders->where('supplier_id', $item->supplier_id);

                    foreach ($all_pos as $po) {

                        $same_items = $po->PurchaseOrderDetail->where('product_id', $item->product_id);
                        $all_items_total_buying_price += $same_items !== null ? $same_items->sum('pod_total_unit_price') : 0;

                        $occurrence += $same_items->count();
                    }
                    if ($occurrence != 0) {
                        $final_unit_price = $all_items_total_buying_price;
                    } else {
                        $occurrence = 0;
                    }
                    $currency_code = @$item->get_supplier->getCurrency->currency_code;
                    return $final_unit_price != null ? number_format($final_unit_price, 2, '.', ',') . ' ' . $currency_code : '';
                }
            });
        } else {
            $dt->addColumn('total_buying_price_wo_vat', function ($item) {
                return '--';
            });
        }

        if (!in_array('27', $not_visible_arr)) {
            $dt->addColumn('total_buying_price', function ($item) use ($group) {
                if ($item->total_unit_price_with_vat != null) {
                    $currency_code = @$item->get_supplier->getCurrency->currency_code;
                    return $item->total_unit_price_with_vat != null ? number_format($item->total_unit_price_with_vat, 2, '.', ',') . ' ' . $currency_code : '';
                } else {
                    $all_items_total_buying_price = 0;
                    $occurrence = 0;
                    $all_pos = $group->purchase_orders->where('supplier_id', $item->supplier_id);

                    foreach ($all_pos as $po) {

                        $same_items = $po->PurchaseOrderDetail->where('product_id', $item->product_id);
                        $all_items_total_buying_price += $same_items !== null ? $same_items->sum('pod_total_unit_price_with_vat') : 0;
                        // $all_items_total_buying_price += $same_items !== null ? $same_items->sum('pod_total_unit_price') : 0;

                        $occurrence += $same_items->count();
                    }
                    if ($occurrence != 0) {
                        $final_unit_price = $all_items_total_buying_price;
                    } else {
                        $occurrence = 0;
                    }
                    $currency_code = @$item->get_supplier->getCurrency->currency_code;
                    return $final_unit_price != null ? number_format($final_unit_price, 2, '.', ',') . ' ' . $currency_code : '';
                }
            });
        } else {
            $dt->addColumn('total_buying_price', function ($item) {
                return '--';
            });
        }

        if (!in_array('28', $not_visible_arr)) {
            $dt->addColumn('currency_conversion_rate', function ($item) use ($group) {
                if ($item->occurrence > 1) {
                    $ccr = $group->purchase_orders->where('supplier_id', $item->supplier_id)->pluck('id')->toArray();

                    $total_occr = $item->averageCurrency($ccr, $item->product_id, 'currency_conversion_rate');

                    $currency_conversion_rate = $total_occr / $item->occurrence;
                } else {
                    $currency_conversion_rate = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0;
                    if ($currency_conversion_rate != 0) {
                        $currency_conversion_rate = (1 / $currency_conversion_rate);
                    } else {
                        $currency_conversion_rate = $currency_conversion_rate;
                    }
                }

                $html_string = '<input type="number"  name="currency_conversion_rate" data-id="' . $item->id . '" data-fieldvalue="' . number_format($currency_conversion_rate, 2, '.', '') . '" ' . ($item->status == 0 ? 'disabled' : "") . ' class="fieldFocus" value="' . number_format($currency_conversion_rate, 2, '.', '') . '" style="width:100%">';
                return $html_string;
            });
        } else {
            $dt->addColumn('currency_conversion_rate', function ($item) {
                return '--';
            });
        }

        if (!in_array('29', $not_visible_arr)) {
            $dt->addColumn('buying_price_in_thb_wo_vat', function ($item) {
                // if($item->occurrence > 1)
                // {
                //     $ccr = $group->purchase_orders->where('supplier_id',$item->supplier_id)->pluck('id')->toArray();

                //     $total_occr = $item->averageCurrency($ccr,$item->product_id,'buying_price_in_thb_wo_vat');
                //     return round($total_occr / $item->occurrence,3);
                // }
                // else
                // {
                // }
                $buying_price_in_thb_wo_vat = $item->unit_price_in_thb;
                return $item->unit_price_in_thb != null ? number_format($buying_price_in_thb_wo_vat, 2, '.', ',') : '';
            });
        } else {
            $dt->addColumn('buying_price_in_thb_wo_vat', function ($item) {
                return '--';
            });
        }

        if (!in_array('30', $not_visible_arr)) {
            $dt->addColumn('buying_price_in_thb', function ($item) {
                // if($item->occurrence > 1)
                // {
                //     $ccr = $group->purchase_orders->where('supplier_id',$item->supplier_id)->pluck('id')->toArray();

                //     $total_occr = $item->averageCurrency($ccr,$item->product_id,'buying_price_in_thb');
                //     return round($total_occr / $item->occurrence,3);
                // }
                // else
                // {
                // return $item->unit_price_in_thb != null ? number_format($item->unit_price_in_thb,2,'.',','): '' ;
                // }

                $buying_price_in_thb = $item->unit_price_in_thb_with_vat;
                return $item->unit_price_in_thb_with_vat != null ? number_format($item->unit_price_in_thb_with_vat, 2, '.', ',') : '';
            });
        } else {
            $dt->addColumn('buying_price_in_thb', function ($item) {
                return '--';
            });
        }

        if (!in_array('31', $not_visible_arr)) {
            $dt->addColumn('total_buying_price_in_thb_wo_vat', function ($item) {
                // if($item->occurrence > 1)
                // {
                //     $ccr = $group->purchase_orders->where('supplier_id',$item->supplier_id)->pluck('id')->toArray();

                //     $total_occr = $item->averageCurrency($ccr,$item->product_id,'total_buying_price_in_thb_wo_vat');
                //     return number_format($total_occr,3,'.',',');
                // }
                // else
                // {
                return $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb, 2, '.', ',') : '';
                // }
            });
        } else {
            $dt->addColumn('total_buying_price_in_thb_wo_vat', function ($item) {
                return '--';
            });
        }

        if (!in_array('32', $not_visible_arr)) {
            $dt->addColumn('total_buying_price_in_thb', function ($item) {
                // if($item->occurrence > 1)
                // {
                //     $ccr = $group->purchase_orders->where('supplier_id',$item->supplier_id)->pluck('id')->toArray();

                //     $total_occr = $item->averageCurrency($ccr,$item->product_id,'total_buying_price_in_thb');
                //     return number_format($total_occr,3,'.',',');
                // }
                // else
                // {
                return $item->total_unit_price_in_thb_with_vat != null ? number_format($item->total_unit_price_in_thb_with_vat, 2, '.', ',') : '';
                // return $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb,2,'.',','): '' ;
                // }
            });
        } else {
            $dt->addColumn('total_buying_price_in_thb', function ($item) {
                return '--';
            });
        }

        if (!in_array('33', $not_visible_arr)) {
            $dt->addColumn('vat_act', function ($item) {
                $html_string = '<input type="number"  name="pogpd_vat_actual" data-id="' . $item->id . '" data-fieldvalue="' . number_format($item->pogpd_vat_actual, 2, '.', '') . '" class="fieldFocus" value="' . number_format($item->pogpd_vat_actual, 2, '.', '') . '" style="width:100%">';
                return $html_string;
            });
        } else {
            $dt->addColumn('vat_act', function ($item) {
                return '--';
            });
        }

        if (!in_array('34', $not_visible_arr)) {
            $dt->addColumn('import_tax_book', function ($item) {
                $html_string = '<input type="number"  name="import_tax_book" data-id="' . $item->id . '" data-fieldvalue="' . number_format($item->import_tax_book, 2, '.', '') . '" class="fieldFocus" value="' . number_format($item->import_tax_book, 2, '.', '') . '" style="width:100%">';
                return $html_string;
            });
        } else {
            $dt->addColumn('import_tax_book', function ($item) {
                return '--';
            });
        }

        if (!in_array('35', $not_visible_arr)) {
            $dt->addColumn('freight', function ($item) {
                $freight = $item->freight;
                return number_format($freight, 2, '.', ',');
            });
        } else {
            $dt->addColumn('freight', function ($item) {
                return '--';
            });
        }

        if (!in_array('36', $not_visible_arr)) {
            $dt->addColumn('total_freight', function ($item) {
                $freight = $item->freight * $item->quantity_inv;
                return number_format($freight, 2, '.', ',');
            });
        } else {
            $dt->addColumn('total_freight', function ($item) {
                return '--';
            });
        }

        if (!in_array('37', $not_visible_arr)) {
            $dt->addColumn('landing', function ($item) {
                $landing = $item->landing;
                return number_format($landing, 2, '.', ',');
            });
        } else {
            $dt->addColumn('landing', function ($item) {
                return '--';
            });
        }

        if (!in_array('38', $not_visible_arr)) {
            $dt->addColumn('total_landing', function ($item) {
                $landing = $item->landing * $item->quantity_inv;
                return number_format($landing, 2, '.', ',');
            });
        } else {
            $dt->addColumn('total_landing', function ($item) {
                return '--';
            });
        }


        if (!in_array('39', $not_visible_arr)) {
            $dt->addColumn('vat_percent_tax', function ($item) use ($all_pgpd, $group) {
                $po_group_vat_actual_percent = $group->po_group_vat_actual_percent;
                $total_buying_price_in_thb = $group->total_buying_price_in_thb;
                // $total_buying_price_in_thb = $group->total_buying_price_in_thb_with_vat;

                $import_tax = $item->pogpd_vat_actual;
                $total_price = $item->total_unit_price_in_thb;
                // $total_price = $item->total_unit_price_in_thb;
                $book_tax = (($import_tax / 100) * $total_price);

                $check_book_tax = (($po_group_vat_actual_percent * $total_buying_price_in_thb) / 100);

                if ($check_book_tax != 0) {
                    return number_format($book_tax, 2, '.', ',');
                } else {
                    // $book_tax = (1/$all_pgpd)* $item->total_unit_price_in_thb;
                    // $book_tax = (1/$all_pgpd)* $item->total_unit_price_in_thb;
                    // return number_format($book_tax,2,'.',',');
                    return '0.00';
                }
            });
        } else {
            $dt->addColumn('vat_percent_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('40', $not_visible_arr)) {
            $dt->addColumn('vat_weighted_percent', function ($item) use ($all_pgpd, $final_vat_actual_percent, $group) {
                if ($item->vat_weighted_percent == null) {
                    if ($group->vat_actual_tax == 0 || $group->vat_actual_tax == '') {
                        return number_format(0, 2, '.', '') . ' %';
                    }
                    $group_tax = $group->vat_actual_tax;
                    $find_item_tax_value = $item->pogpd_vat_actual / 100 * $item->total_unit_price_in_thb;

                    $import_tax = $item->pogpd_vat_actual;
                    $total_price = $item->total_unit_price_in_thb;
                    $book_tax = (($import_tax / 100) * $total_price);

                    if ($book_tax != 0) {
                        $vat_weighted_per = ($final_vat_actual_percent / $book_tax) * 100;
                    } else {
                        $vat_weighted_per = 0;
                    }

                    return number_format($vat_weighted_per, 2, '.', '') . " %";
                } else {
                    return number_format($item->vat_weighted_percent, 4, '.', ',') . '%';
                }
            });
        } else {
            $dt->addColumn('vat_weighted_percent', function ($item) {
                return '--';
            });
        }

        if (!in_array('41', $not_visible_arr)) {
            $dt->addColumn('vat_act_tax', function ($item) use ($all_pgpd, $final_vat_actual_percent, $group) {
                //unit purchasing vat
                if ($item->pogpd_vat_actual_price == NULL) {
                    $group_tax = $group->vat_actual_tax;
                    // $find_item_tax_value = $item->pogpd_vat_actual/100 * $item->total_unit_price_in_thb_with_vat;
                    $find_item_tax_value = $item->pogpd_vat_actual / 100 * $item->total_unit_price_in_thb;

                    return number_format($find_item_tax_value, 2, '.', ',');
                } else {
                    return number_format($item->pogpd_vat_actual_price, 2, '.', ',');
                }
            });
        } else {
            $dt->addColumn('vat_act_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('42', $not_visible_arr)) {
            $dt->addColumn('total_vat_act_tax', function ($item) use ($all_pgpd, $final_vat_actual_percent, $group) {
                //total purchasing vat
                if ($item->occurrence > 1) {
                    // $all_ids = PurchaseOrder::where('po_group_id',$item->po_group_id)->where('supplier_id',$item->supplier_id)->pluck('id');
                    $all_ids = $group->purchase_orders->where('supplier_id', $item->supplier_id)->pluck('id');
                    $all_record_vat_tax = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $item->product_id)->sum('pod_vat_actual_total_price_in_thb');
                    return number_format($all_record_vat_tax, 2, '.', ',');

                    // $group_res = $group->where("purchase_orders",function($po) use ($item){
                    //     $po->where('supplier_id',$item->supplier_id)->whereHas('PurchaseOrderDetail',function($pod){
                    //         $pod->sum('pod_vat_actual_total_price_in_thb');
                    //     })->first();
                    // });
                    // dd($group_res);
                    // return number_format((float)$group_res,2,'.',',');
                } else {
                    return number_format(($item->pogpd_vat_actual_price * $item->quantity_inv), 2, '.', ',');
                }
            });
        } else {
            $dt->addColumn('total_vat_act_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('43', $not_visible_arr)) {
            $dt->addColumn('vat_act_tax_percent', function ($item) use ($final_vat_actual_percent) {
                //tax allocation
                if ($item->pogpd_vat_actual_percent_val == NULL) {
                    $find_item_tax_value = (($item->pogpd_vat_actual / 100) * $item->total_unit_price_in_thb);
                    if ($item->total_unit_price_in_thb != 0) {
                        $p_vat_percent = ($find_item_tax_value / $item->total_unit_price_in_thb) * 100;
                    } else {
                        $p_vat_percent = 0;
                    }

                    return $p_vat_percent . ' %';
                } else {
                    return number_format($item->pogpd_vat_actual_percent_val, 2, '.', ',') . '%';
                }
            });
        } else {
            $dt->addColumn('vat_act_tax_percent', function ($item) {
                return '--';
            });
        }

        // if(!in_array('47', $not_visible_arr))
        // {
        $dt->addColumn('custom_line_number', function ($item) {
            $html_string = '<input type="text"  name="custom_line_number" data-id="' . $item->id . '" data-fieldvalue="' . @$item->custom_line_number . '" class="fieldFocus" value="' . @$item->custom_line_number . '" style="width:100%">';
            return $html_string;
        });
        // }
        // else
        // {
        //     $dt->addColumn('custom_line_number',function($item){
        //         return '--';
        //     });
        // }

        if(!in_array('44', $not_visible_arr))
        {
            $dt->addColumn('book_tax',function($item) use ($all_pgpd,$group){
                return number_format($item->import_tax_book_price,2,'.',',');
                if($item->occurrence > 1)
                {
                    // $all_ids = PurchaseOrder::where('po_group_id',$item->po_group_id)->where('supplier_id',$item->supplier_id)->pluck('id');
                    $all_ids = $group->purchase_orders->where('supplier_id', $item->supplier_id)->pluck('id');
                    $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $item->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->sum('pod_import_tax_book_price');

                    return number_format($all_record, 2, '.', ',');
                } else {
                    $po_group_import_tax_book = $group->total_import_tax_book_percent;
                    // $total_buying_price_in_thb = $group->total_buying_price_in_thb_with_vat;
                    $total_buying_price_in_thb = $group->total_buying_price_in_thb;

                    $import_tax = $item->import_tax_book;
                    $total_price = $item->total_unit_price_in_thb;
                    // $total_price = $item->total_unit_price_in_thb;
                    $book_tax = (($import_tax / 100) * $total_price);

                    $check_book_tax = (($po_group_import_tax_book * $total_buying_price_in_thb) / 100);

                    if ($check_book_tax != 0) {
                        return number_format($book_tax, 2, '.', ',');
                    } else {
                        // $book_tax = (1/$all_pgpd)* $item->total_unit_price_in_thb;
                        $book_tax = (1 / $all_pgpd) * $item->total_unit_price_in_thb_with_vat;
                        return number_format($book_tax, 2, '.', ',');
                    }
                }
            });
        } else {
            $dt->addColumn('book_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('45', $not_visible_arr)) {
            $dt->addColumn('weighted', function ($item) use ($all_pgpd, $final_book_percent) {
                if ($item->weighted_percent == null) {
                    return '--';
                } else {
                    return number_format($item->weighted_percent, 4, '.', ',') . '%';
                }
            });
        } else {
            $dt->addColumn('weighted', function ($item) {
                return '--';
            });
        }

        if (!in_array('46', $not_visible_arr)) {
            $dt->addColumn('actual_tax', function ($item) use ($all_pgpd, $final_book_percent, $group) {
                if ($item->actual_tax_price == NULL) {
                    $group_tax = $group->tax;
                    $find_item_tax_value = $item->import_tax_book / 100 * $item->total_unit_price_in_thb_with_vat;
                    // $find_item_tax_value = $item->import_tax_book/100 * $item->total_unit_price_in_thb;
                    if ($final_book_percent != 0) {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;
                        if ($item->quantity_inv == 0) {
                            return 0;
                        } else {
                            return number_format(round($find_item_tax * $group_tax, 2) / $item->quantity_inv, 2, '.', ',');
                        }
                    } else {
                        $total_import_tax = $group->po_group_import_tax_book;
                        $po_group_import_tax_book = $group->total_import_tax_book_percent;
                        $total_buying_price_in_thb = $group->total_buying_price_in_thb_with_vat;
                        // $total_buying_price_in_thb = $group->total_buying_price_in_thb;

                        $import_tax = $item->import_tax_book;
                        $total_price = $item->total_unit_price_in_thb_with_vat;
                        // $total_price = $item->total_unit_price_in_thb;
                        $book_tax = (($import_tax / 100) * $total_price);


                        $check_book_tax = (($po_group_import_tax_book * $total_buying_price_in_thb) / 100);


                        if ($check_book_tax != 0) {
                            $book_tax = round($book_tax, 2);
                        } else {
                            // $book_tax = (1/$all_pgpd)* $item->total_unit_price_in_thb;
                            $book_tax = (1 / $all_pgpd) * $item->total_unit_price_in_thb_with_vat;
                            $book_tax = round($book_tax, 2);
                        }
                        if ($total_import_tax != 0) {
                            $weighted = ($book_tax / $total_import_tax);
                        } else {
                            $weighted = 0;
                        }
                        $tax = $group->tax;
                        return number_format(($weighted * $tax), 2, '.', ',');
                    }
                } else {
                    return number_format($item->actual_tax_price, 2, '.', ',');
                }
            });
        } else {
            $dt->addColumn('actual_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('47', $not_visible_arr)) {
            $dt->addColumn('total_actual_tax', function ($item) use ($all_pgpd, $final_book_percent) {
                return number_format(($item->actual_tax_price * $item->quantity_inv), 2, '.', ',');
            });
        } else {
            $dt->addColumn('total_actual_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('48', $not_visible_arr)) {
            $dt->addColumn('actual_tax_percent', function ($item) use ($final_book_percent, $group) {
                if ($item->actual_tax_percent == NULL) {
                    $group_tax = $group->tax;
                    $find_item_tax_value = $item->import_tax_book / 100 * $item->total_unit_price_in_thb_with_vat;
                    // $find_item_tax_value = $item->import_tax_book/100 * $item->total_unit_price_in_thb;
                    if ($final_book_percent != 0) {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;
                        if ($item->quantity_inv == 0) {
                            return 0;
                        } else {
                            $actual_tax_per_quantity = number_format($find_item_tax * $group_tax, 2, '.', '') / $item->quantity_inv;
                            if ($item->unit_price_in_thb_with_vat != 0) {
                                return number_format(($actual_tax_per_quantity / $item->unit_price_in_thb_with_vat) * 100, 2, '.', ',') . ' %';
                            } else {
                                return 0;
                            }
                        }
                    } else {
                        return 0;
                    }
                } else {
                    return number_format($item->actual_tax_percent, 2, '.', ',') . '%';
                }
            });
        } else {
            $dt->addColumn('actual_tax_percent', function ($item) {
                return '--';
            });
        }

        if (!in_array('50', $not_visible_arr)) {
            $dt->addColumn('product_cost', function ($item) {
                return $item->product_cost != null ? number_format($item->product_cost, 2, '.', ',') : 0;
            });
        } else {
            $dt->addColumn('product_cost', function ($item) {
                return '--';
            });
        }
        if (!in_array('51', $not_visible_arr)) {
            $dt->addColumn('total_product_cost', function ($item) {
                $total_cogs = $item->product_cost * $item->quantity_inv;
                return number_format($total_cogs, 2, '.', ',');
            });
        } else {
            $dt->addColumn('total_product_cost', function ($item) {
                return '--';
            });
        }


        // ->addColumn('buying_currency',function($item){
            // if($item->supplier_id !== NULL)
            // {
                //     $supplier = Supplier::where('id',$item->supplier_id)->first();
                //     return $supplier->getCurrency->currency_code != null ? $supplier->getCurrency->currency_code : '';
                // }
                // else
                // {
                    //     return "N.A";
                    // }

                    // })


        $dt->setRowClass(function ($item) {
            if ($item->status != 0) {
                return '';
            } else {
                return 'yellowRow';
            }
        });

        $dt->rawColumns(['po_number', 'order_no', 'supplier', 'reference_number', 'import_tax_book', 'desc', 'kg', 'pod_total_gross_weight', 'pod_total_extra_cost', 'pod_total_extra_tax', 'currency_conversion_rate', 'qty', 'prod_reference_number', 'customer', 'discount', 'custom_line_number', 'pod_unit_extra_cost', 'pod_unit_extra_tax', 'pod_unit_gross_weight', 'vat_act', 'vat_percent_tax', 'vat_weighted_percent', 'vat_act_tax', 'vat_act_tax_percent', 'total_freight', 'total_landing', 'original_qty', 'buying_price_wo_vat']);

        return $dt->make(true);
    }

    public function getPoGroupProductDetailsFooterValues($id)
    {
        $all_record = PoGroupProductDetail::where('status', 1)->where('po_group_id', $id);
        $all_record = $all_record->with('po_group', 'product', 'get_supplier', 'get_supplier.getCurrency', 'product.units', 'product.sellingUnits');
        $all_pgpd = PoGroupProductDetail::where('status', 1)->where('po_group_id', $id)->count();

        $final_book_percent = 0;
        foreach ($all_record->get() as $value) {
            if ($value->import_tax_book != null && $value->import_tax_book != 0) {
                $final_book_percent = $final_book_percent + (($value->import_tax_book / 100) * $value->total_unit_price_in_thb_with_vat);
            }
        }




        $qty_ordered_sum    = (clone $all_record)->sum('quantity_ordered');
        $qty_inv_sum        = (clone $all_record)->sum('quantity_inv');
        $total_gross_weight = (clone $all_record)->sum('total_gross_weight');
        $total_extra_cost   = (clone $all_record)->sum('total_extra_cost');
        $total_extra_tax    = (clone $all_record)->sum('total_extra_tax');
        $buying_price       = (clone $all_record)->sum('unit_price_with_vat');
        $buying_price_thb   = (clone $all_record)->sum(\DB::raw('ROUND(unit_price_in_thb_with_vat,2)'));
        $t_buying_price_thb = (clone $all_record)->sum(\DB::raw('ROUND(total_unit_price_in_thb_with_vat,2)'));
        $freight            = (clone $all_record)->sum('freight');
        $landing            = (clone $all_record)->sum('landing');
        $book_per_tax       = (clone $all_record)->sum(\DB::raw('ROUND(import_tax_book_price,2)'));
        $total_unit_price_in_thb               = (clone $all_record)->sum('total_unit_price_in_thb');


        $final_unit_price = 0;
        $all_items_total_buying_price = 0;
        $occurrence = 0;
        $weighted_column_sum = 0;
        $actual_tax_column_sum = 0;
        $actual_tax_per_sum = 0;
        $book_tax_final = 0;
        $total_import_tax_cal = 0;
        $total_freight = 0;
        $total_landing = 0;
        foreach ($all_record->get() as $value) {
            $all_pos = $value->po_group->purchase_orders()->where('supplier_id', $value->supplier_id)->get();
            foreach ($all_pos as $po) {

                $same_items = $po->PurchaseOrderDetail()->where('product_id', $value->product_id)->get();
                $all_items_total_buying_price += $same_items !== null ? $same_items->sum('pod_total_unit_price_with_vat') : 0;

                $occurrence += $same_items->count();
            }
            if ($occurrence != 0) {
                $final_unit_price = $all_items_total_buying_price;
            } else {
                $final_unit_price = 0;
            }

            $total_import_tax_cal += number_format(($value->actual_tax_price * $value->quantity_inv), 4, '.', '');
            //total calculate total landing and total freight
            $total_freight += number_format($value->freight * $value->quantity_inv, 2, '.', '');
            $total_landing += number_format($value->landing * $value->quantity_inv, 2, '.', '');

            // weighted column total
            if ($value->weighted_percent == null) {
                $group_tax = $value->po_group->tax;
                $find_item_tax_value = $value->import_tax_book / 100 * $value->total_unit_price_in_thb_with_vat;
                if ($final_book_percent != 0 && $group_tax != 0) {
                    $find_item_tax = $find_item_tax_value / $final_book_percent;
                    $cost = $find_item_tax * $group_tax;
                    if ($group_tax != 0) {
                        $weighted_column_sum += number_format(($cost / $group_tax) * 100, 4, '.', '');
                    } else {
                        $weighted_column_sum += 0;
                    }
                } else {
                    $total_import_tax = $value->po_group->po_group_import_tax_book;
                    $po_group_import_tax_book = $value->po_group->total_import_tax_book_percent;
                    $total_buying_price_in_thb = $value->po_group->total_buying_price_in_thb_with_vat;

                    $import_tax = $value->import_tax_book;
                    $total_price = $value->total_unit_price_in_thb_with_vat;
                    $book_tax = (($import_tax / 100) * $total_price);


                    $check_book_tax = (($po_group_import_tax_book * $total_buying_price_in_thb) / 100);


                    if ($check_book_tax != 0) {
                        $book_tax = round($book_tax, 2);
                    } else {
                        $book_tax = (1 / $all_pgpd) * $value->total_unit_price_in_thb_with_vat;
                        $book_tax = round($book_tax, 2);
                    }
                    if ($total_import_tax != 0) {
                        $weighted = (($book_tax / $total_import_tax) * 100);
                    } else {
                        $weighted = 0;
                    }

                    $weighted_column_sum += number_format($weighted, 4, '.', '');
                }
            } else {
                $weighted_column_sum += number_format($value->weighted_percent, 4, '.', '');
            }

            // actual tax column total
            if ($value->actual_tax_price == NULL) {
                $group_tax = $value->po_group->tax;
                $find_item_tax_value = $value->import_tax_book / 100 * $value->total_unit_price_in_thb_with_vat;
                if ($final_book_percent != 0) {
                    $find_item_tax = $find_item_tax_value / $final_book_percent;
                    if ($value->quantity_inv == 0) {
                        $actual_tax_column_sum += 0;
                    } else {
                        $actual_tax_column_sum += number_format(round($find_item_tax * $group_tax, 2) / $value->quantity_inv, 2, '.', '');
                    }
                } else {
                    $total_import_tax = $value->po_group->po_group_import_tax_book;
                    $po_group_import_tax_book = $value->po_group->total_import_tax_book_percent;
                    $total_buying_price_in_thb = $value->po_group->total_buying_price_in_thb_with_vat;

                    $import_tax = $value->import_tax_book;
                    $total_price = $value->total_unit_price_in_thb_with_vat;
                    $book_tax = (($import_tax / 100) * $total_price);


                    $check_book_tax = (($po_group_import_tax_book * $total_buying_price_in_thb) / 100);


                    if ($check_book_tax != 0) {
                        $book_tax = round($book_tax, 2);
                    } else {
                        $book_tax = (1 / $all_pgpd) * $value->total_unit_price_in_thb_with_vat;
                        $book_tax = round($book_tax, 2);
                    }
                    if ($total_import_tax != 0) {
                        $weighted = ($book_tax / $total_import_tax);
                    } else {
                        $weighted = 0;
                    }
                    $tax = $value->po_group->tax;
                    $actual_tax_column_sum += number_format(($weighted * $tax), 2, '.', '');
                }
            } else {
                $actual_tax_column_sum += number_format($value->actual_tax_price, 2, '.', '');
            }

            // actual percent column total
            if ($value->actual_tax_percent == NULL) {
                $actual_tax_per_sum += 0;
                // $group_tax = $value->po_group->tax;
                // $find_item_tax_value = $value->import_tax_book/100 * $value->total_unit_price_in_thb_with_vat;
                // if($final_book_percent != 0)
                // {
                //     $find_item_tax = $find_item_tax_value / $final_book_percent;
                //     if($value->quantity_inv == 0)
                //     {
                //         $actual_tax_per_sum += 0;
                //     }
                //     else
                //     {
                //         $actual_tax_per_quantity = number_format($find_item_tax * $group_tax,2,'.','') / $value->quantity_inv;
                //         if($value->unit_price_in_thb_with_vat != 0)
                //         {
                //             $actual_tax_per_sum += number_format(($actual_tax_per_quantity / $value->unit_price_in_thb_with_vat)* 100,2,'.','');
                //         }
                //         else
                //         {
                //             $actual_tax_per_sum += 0;
                //         }
                //     }
                // }
                // else
                // {
                //     $actual_tax_per_sum += 0;
                // }
            } else {
                $actual_tax_per_sum += number_format($value->actual_tax_percent, 2, '.', '');
            }
            if ($value->occurrence > 1) {
                $all_ids = PurchaseOrder::where('po_group_id', $value->po_group_id)->where('supplier_id', $value->supplier_id)->pluck('id');
                $all_record_book = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $value->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->sum('pod_import_tax_book_price');

                $book_tax_final += number_format($all_record_book, 2, '.', '');
            } else {
                // book per tax column total
                $po_group_import_tax_book = $value->po_group->total_import_tax_book_percent;
                $total_buying_price_in_thb = $value->po_group->total_buying_price_in_thb;

                $import_tax = $value->import_tax_book;
                $total_price = $value->total_unit_price_in_thb;
                $book_tax = (($import_tax / 100) * $total_price);

                $check_book_tax = (($po_group_import_tax_book * $total_buying_price_in_thb) / 100);

                if ($check_book_tax != 0) {
                    $book_tax_final += number_format($book_tax, 2, '.', '');
                } else {
                    $book_tax_final += number_format((1 / $all_pgpd) * $value->total_unit_price_in_thb, 2, '.', '');
                }
            }
        }
        $book_tax_final = 0;
        foreach ($all_record->get() as $value) {
            if ($value->import_tax_book != null && $value->import_tax_book != 0) {
                $book_tax_final = $book_tax_final + (($value->import_tax_book / 100) * $value->total_unit_price_in_thb);
            }
        }

        return response()->json([
            "qty_ordered_sum"    => $qty_ordered_sum,
            "qty_inv_sum"        => $qty_inv_sum,
            "total_gross_weight" => $total_gross_weight,
            "total_extra_cost"   => $total_extra_cost,
            "total_extra_tax"    => $total_extra_tax,
            "buying_price"       => $buying_price,
            "total_buying_price" => $final_unit_price,
            "buying_price_thb"   => $buying_price_thb,
            "t_buying_price_thb" => $t_buying_price_thb,
            "freight"            => $freight,
            "landing"            => $landing,
            "book_per_tax"       => number_format($book_tax_final, 2),
            "total_import_tax_cal" => number_format($total_import_tax_cal, 2),
            "total_freight" => number_format($total_freight, 2),
            "total_landing" => number_format($total_landing, 2),
            "weighted_sum"       => number_format($weighted_column_sum, 2) . ' %',
            "actual_tax_col_sum" => number_format($actual_tax_column_sum, 2),
            "actual_tax_per_sum" => number_format($actual_tax_per_sum, 2) . ' %',
            "total_unit_price_in_thb" => number_format($total_unit_price_in_thb, 2)
        ]);
    }

    public function exportImportingProductReceivingRecord(Request $request)
    {
        $status = ExportStatus::where('type', 'products_receiving_importings')->where('user_id', Auth::user()->id)->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->type = 'products_receiving_importings';
            $new->user_id = Auth::user()->id;
            $new->status = 1;
            $new->save();
            ProductReceivingExportJob::dispatch($request['id'], Auth::user()->id, $request['status'], $request['sort_order'], $request['column_name']);
            return response()->json(['status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['status' => 2, 'recursive' => false]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'products_receiving_importings')->update(['status' => 1, 'user_id' => Auth::user()->id]);
            ProductReceivingExportJob::dispatch($request['id'], Auth::user()->id, $request['status'], $request['sort_order'], $request['column_name']);
            return response()->json(['msg' => "File is getting ready!", 'status' => 1, 'recursive' => true]);
        }
    }

    public function recursiveExportStatusImportingPeceivingProducts()
    {
        $status = ExportStatus::where('type', 'products_receiving_importings')->first();
        return response()->json(['msg' => "File Created!", 'status' => $status->status, 'exception' => $status->exception]);
    }

    public function recursiveExportStatusImportingPeceivingBulkProducts()
    {
        $status = ExportStatus::where('type', 'products_receiving_importings_bulk_job')->first();
        return response()->json(['msg' => "File Created!", 'status' => $status->status, 'exception' => $status->exception]);
    }
    public function checkStatusFirstTime()
    {
        $status = ExportStatus::where('type', 'products_receiving_importings')->where('user_id', Auth::user()->id)->first();
        if ($status != null) {
            return response()->json(['status' => $status->status]);
        } else {
            return response()->json(['status' => 0]);
        }
    }
    public function editPoGroupProductDetails(Request $request)
    {
        // dd('controller');
        DB::beginTransaction();
        try {
            $po_detail = PoGroupProductDetail::where('id', $request->pod_id)->first();
            $po_group_product_detail_id = $request->pod_id;
            if ($request->has('user_id')) {
                $user = User::find($request->user_id);
            } else {
                $user = null;
            }

            foreach ($request->except('pod_id', 'po_group_id', 'old_value', 'user_id') as $key => $value) {
                if ($po_detail) {
                    $PoGroupProduct_history = new PoGroupProductHistory;
                    $PoGroupProduct_history->user_id = $user != null ? $user->id : Auth::user()->id;
                    $PoGroupProduct_history->ref_id = $request->pod_id;
                    $PoGroupProduct_history->order_product_id = $po_detail->product_id;
                    $PoGroupProduct_history->old_value = $request->old_value;
                    $PoGroupProduct_history->column_name = $key;
                    $PoGroupProduct_history->po_group_id = $request->po_group_id;
                    $PoGroupProduct_history->new_value = $value;
                    $PoGroupProduct_history->save();
                }


                if ($value == '') {
                    // $supp_detail->$key = null;
                } elseif ($key == 'quantity_received') {
                    if ($value > $po_detail->quantity) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'extra_quantity' => $value - $po_detail->quantity]);
                    } else {
                        $params['term_key']  = $key;
                        $params['old_value'] = $po_detail->$key;
                        $params['new_value'] = $value;
                        $params['ip_address'] = $request->ip();
                        $this->saveProductReceivingHistory($params, $po_detail->id, $request->po_group_id);
                        $po_detail->$key = $value;
                    }
                } elseif ($key == 'total_gross_weight') {
                    #To update gross weight on PO level as well
                    $po_ids = $po_detail->po_group->po_group_detail()->pluck('purchase_order_id')->toArray();
                    $pods = PurchaseOrderDetail::whereIn('po_id', $po_ids)->whereHas('PurchaseOrder', function ($po) use ($po_detail) {
                        $po->where('supplier_id', $po_detail->supplier_id);
                    })->where('product_id', $po_detail->product_id)->get();
                    // dd($pod);
                    if ($po_detail->quantity_inv != 0) {
                        $unit_gross_weight = $value / $po_detail->quantity_inv;
                    } else {
                        $unit_gross_weight = 0;
                    }

                    $po_detail->unit_gross_weight = $unit_gross_weight;
                    $po_detail->save();

                    if ($pods->count() > 0) {
                        foreach ($pods as $pod) {
                            $pod->pod_gross_weight = $unit_gross_weight;
                            $pod->pod_total_gross_weight = $unit_gross_weight * $pod->quantity;
                            $pod->save();

                            #Update Product gross weight
                            $purchase_order_detail = $pod;
                            if ($purchase_order_detail->PurchaseOrder->supplier_id == NULL && $purchase_order_detail->PurchaseOrder->from_warehouse_id != NULL) {
                                $supplier_id = $purchase_order_detail->product->supplier_id;
                            } else {
                                $supplier_id = $purchase_order_detail->PurchaseOrder->PoSupplier->id;
                            }

                            $getProductSupplier = SupplierProducts::where('product_id', @$purchase_order_detail->product_id)->where('supplier_id', @$supplier_id)->first();

                            $product_detail = Product::find($purchase_order_detail->product_id);

                            if ($getProductSupplier !== null && $product_detail->supplier_id == $supplier_id) {
                                if ($unit_gross_weight != 0) {
                                    $getProductSupplier->gross_weight = $unit_gross_weight;
                                    $getProductSupplier->save();
                                }
                            } elseif ($getProductSupplier !== null) {
                                if ($unit_gross_weight != 0) {
                                    $getProductSupplier->gross_weight = $unit_gross_weight;
                                    $getProductSupplier->save();
                                }
                            }
                            #End Product update
                        }

                        $all_pos = PurchaseOrder::whereIn('id', $po_ids)->get();
                        foreach ($all_pos as $po) {
                            $po_total_gross_weight = 0;
                            $po_items = $po->PurchaseOrderDetail;
                            foreach ($po_items as $detail) {
                                $po_total_gross_weight += $detail->pod_total_gross_weight;
                            }

                            $po->total_gross_weight = $po_total_gross_weight;
                            $po->save();
                        }
                    }
                    #Here we store the pod total gross weight
                    #which will update the purchase order's total_gross_weight
                    #which at the end will update the po group's po_group_total_gross_weight
                    $po_detail->$key = $value;
                    $po_detail->save();

                    $po_group_total_gross_weight = 0;
                    $po_details = $po_detail->po_group->po_group_product_details;
                    foreach ($po_details as $detail) {
                        $po_group_total_gross_weight += $detail->total_gross_weight;
                    }

                    $po_detail->po_group->po_group_total_gross_weight = $po_group_total_gross_weight;
                    $po_detail->po_group->save();

                    #and at the end we will calculate the freight and landing of pod
                    $po_details = PoGroupProductDetail::where('status', 1)->where('po_group_id', $po_detail->po_group_id)->where('quantity_inv', '!=', 0)->get();
                    foreach ($po_details as $group_detail) {

                        $item_gross_weight = $group_detail->total_gross_weight;
                        $total_gross_weight = $po_detail->po_group->po_group_total_gross_weight;
                        $total_quantity = $group_detail->quantity_inv;

                        $total_freight = $po_detail->po_group->freight;
                        $total_landing = $po_detail->po_group->landing;

                        $freight = (($total_freight * ($item_gross_weight / $total_gross_weight)) / $total_quantity);
                        $landing = (($total_landing * ($item_gross_weight / $total_gross_weight)) / $total_quantity);

                        $group_detail->freight = $freight;
                        $group_detail->landing = $landing;
                        $group_detail->save();
                    }
                    if ($po_detail->po_group->is_review == 1) {
                        $po_group_product__detail = PoGroupProductDetail::where('status', 1)->where('id', $po_group_product_detail_id)->first();
                        $po_group = PoGroup::find($po_group_product__detail->po_group_id);

                        //to find to warehouse is bonded or not
                        if ($po_group->warehouse_id != null) {
                            $check_to_bond = $po_group->ToWarehouse->is_bonded;
                        } else {
                            $check_to_bond = 0;
                        }
                        if ($po_group_product__detail) {
                            if ($po_group_product__detail->supplier_id != null) {
                                $supplier_product = SupplierProducts::where('supplier_id', $po_group_product__detail->supplier_id)->where('product_id', $po_group_product__detail->product_id)->first();
                            } else {
                                $check_product = Product::find($po_group_product__detail->product_id);
                                if ($check_product) {
                                    $supplier_product = SupplierProducts::where('supplier_id', $check_product->supplier_id)->where('product_id', $check_product->id)->first();
                                }
                            }

                            $supplier_conv_rate_thb = @$po_group_product__detail->currency_conversion_rate != 0 ? $po_group_product__detail->currency_conversion_rate : 1;
                            $buying_price_in_thb    = $po_group_product__detail->unit_price_in_thb;

                            $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                            if ($po_group_product__detail->supplier_id != null) {
                                $supplier_product->freight         = $po_group_product__detail->freight;
                                $supplier_product->landing         = $po_group_product__detail->landing;
                            }
                            $supplier_product->extra_cost          = $po_group_product__detail->unit_extra_cost;
                            $supplier_product->extra_tax           = $po_group_product__detail->unit_extra_tax;
                            $supplier_product->vat_actual          = $po_group_product__detail->pogpd_vat_actual_percent_val;
                            if ($check_to_bond == 0) {
                                $supplier_product->import_tax_actual   = $po_group_product__detail->actual_tax_percent;
                            }
                            $supplier_product->gross_weight        = $po_group_product__detail->total_gross_weight / $po_group_product__detail->quantity_inv;
                            $supplier_product->save();

                            $product = Product::find($po_group_product__detail->product_id);

                            // this is the price of after conversion for THB
                            $importTax              = $supplier_product->import_tax_actual;
                            $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                            $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);

                            $product->total_buy_unit_cost_price = $total_buying_price;
                            $product->t_b_u_c_p_of_supplier     = $total_buying_price * $supplier_conv_rate_thb;
                            $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                            // creating a history on a product detail page which shipment updated the product COGS
                            $product_history              = new ProductHistory;
                            $product_history->user_id     = $user != null ? $user->id : Auth::user()->id;
                            $product_history->product_id  = $product->id;
                            $product_history->column_name = 'COGS Updated through ' . @$po_group->ref_id . ' by edition total gross weight For ' . @$supplier_product->supplier->reference_name;
                            $product_history->old_value   = @$product->selling_price;
                            $product_history->new_value   = @$total_selling_price;
                            $product_history->save();

                            $product->selling_price           = $total_selling_price;
                            $product->supplier_id             = $supplier_product->supplier_id;
                            $product->last_price_updated_date = Carbon::now();
                            $product->last_date_import        = Carbon::now();
                            $product->save();

                            $po__ids = $po_group->po_group_detail != null ? $po_group->po_group_detail()->pluck('purchase_order_id')->toArray() : [];
                            $po_detail_products = PurchaseOrderDetail::where('product_id', $product->id)->whereNotNull('order_product_id')->whereIn('po_id', $po__ids)->get();
                            if ($po_detail_products->count() > 0) {
                                foreach ($po_detail_products as $pod) {
                                    if ($pod->order_product) {
                                        $pod->order_product->actual_cost = $product->selling_price;
                                        $pod->order_product->save();
                                    }
                                }
                            }
                        }
                    }
                    DB::commit();
                    return response()->json(['gross_weight' => true, 'po_group' => $group_detail->po_group]);
                } elseif ($key == 'unit_gross_weight') {
                    #To update gross weight on PO level as well
                    $po_ids = $po_detail->po_group->po_group_detail()->pluck('purchase_order_id')->toArray();
                    $pods = PurchaseOrderDetail::whereIn('po_id', $po_ids)->where('product_id', $po_detail->product_id)->get();

                    if ($po_detail->quantity_inv != 0) {
                        $total_gross_weight = $value * $po_detail->quantity_inv;
                    } else {
                        $total_gross_weight = 0;
                    }

                    $po_detail->total_gross_weight = $total_gross_weight;
                    $po_detail->save();

                    if ($pods->count() > 0) {
                        foreach ($pods as $pod) {
                            $pod->pod_gross_weight = $value;
                            $pod->pod_total_gross_weight = $value * $pod->quantity;
                            $pod->save();

                            #Update Product gross weight
                            $purchase_order_detail = $pod;
                            if ($purchase_order_detail->PurchaseOrder->supplier_id == NULL && $purchase_order_detail->PurchaseOrder->from_warehouse_id != NULL) {
                                $supplier_id = $purchase_order_detail->product->supplier_id;
                            } else {
                                $supplier_id = $purchase_order_detail->PurchaseOrder->PoSupplier->id;
                            }

                            $getProductSupplier = SupplierProducts::where('product_id', @$purchase_order_detail->product_id)->where('supplier_id', @$supplier_id)->first();

                            $product_detail = Product::find($purchase_order_detail->product_id);

                            if ($getProductSupplier !== null && $product_detail->supplier_id == $supplier_id) {
                                if ($value != 0) {
                                    $getProductSupplier->gross_weight = $value;
                                    $getProductSupplier->save();
                                }
                            } elseif ($getProductSupplier !== null) {
                                if ($value != 0) {
                                    $getProductSupplier->gross_weight = $value;
                                    $getProductSupplier->save();
                                }
                            }
                            #End Product update
                        }

                        $all_pos = PurchaseOrder::whereIn('id', $po_ids)->get();
                        foreach ($all_pos as $po) {
                            $po_total_gross_weight = 0;
                            $po_items = $po->PurchaseOrderDetail;
                            foreach ($po_items as $detail) {
                                $po_total_gross_weight += $detail->pod_total_gross_weight;
                            }

                            $po->total_gross_weight = $po_total_gross_weight;
                            $po->save();
                        }
                    }
                    #Here we store the pod total gross weight
                    #which will update the purchase order's total_gross_weight
                    #which at the end will update the po group's po_group_total_gross_weight
                    $po_detail->$key = $value;
                    $po_detail->save();

                    $po_group_total_gross_weight = 0;
                    $po_details = $po_detail->po_group->po_group_product_details;
                    foreach ($po_details as $detail) {
                        $po_group_total_gross_weight += $detail->total_gross_weight;
                    }

                    $po_detail->po_group->po_group_total_gross_weight = $po_group_total_gross_weight;
                    $po_detail->po_group->save();

                    #and at the end we will calculate the freight and landing of pod
                    $po_details = PoGroupProductDetail::where('status', 1)->where('po_group_id', $po_detail->po_group_id)->where('quantity_inv', '!=', 0)->get();
                    foreach ($po_details as $group_detail) {

                        $item_gross_weight = $group_detail->total_gross_weight;
                        $total_gross_weight = $po_detail->po_group->po_group_total_gross_weight;
                        $total_quantity = $group_detail->quantity_inv;

                        $total_freight = $po_detail->po_group->freight;
                        $total_landing = $po_detail->po_group->landing;

                        $freight = (($total_freight * ($item_gross_weight / $total_gross_weight)) / $total_quantity);
                        $landing = (($total_landing * ($item_gross_weight / $total_gross_weight)) / $total_quantity);

                        $group_detail->freight = $freight;
                        $group_detail->landing = $landing;
                        $group_detail->save();
                    }
                    if ($po_detail->po_group->is_review == 1) {
                        $po_group_product__detail = PoGroupProductDetail::where('status', 1)->where('id', $po_group_product_detail_id)->first();
                        $po_group = PoGroup::find($po_group_product__detail->po_group_id);

                        //to find to warehouse is bonded or not
                        if ($po_group->warehouse_id != null) {
                            $check_to_bond = $po_group->ToWarehouse->is_bonded;
                        } else {
                            $check_to_bond = 0;
                        }
                        if ($po_group_product__detail) {
                            if ($po_group_product__detail->supplier_id != null) {
                                $supplier_product = SupplierProducts::where('supplier_id', $po_group_product__detail->supplier_id)->where('product_id', $po_group_product__detail->product_id)->first();
                            } else {
                                $check_product = Product::find($po_group_product__detail->product_id);
                                if ($check_product) {
                                    $supplier_product = SupplierProducts::where('supplier_id', $check_product->supplier_id)->where('product_id', $check_product->id)->first();
                                }
                            }

                            $supplier_conv_rate_thb = @$po_group_product__detail->currency_conversion_rate != 0 ? $po_group_product__detail->currency_conversion_rate : 1;
                            $buying_price_in_thb    = $po_group_product__detail->unit_price_in_thb;

                            $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                            if ($po_group_product__detail->supplier_id != null) {
                                $supplier_product->freight         = $po_group_product__detail->freight;
                                $supplier_product->landing         = $po_group_product__detail->landing;
                            }
                            $supplier_product->extra_cost          = $po_group_product__detail->unit_extra_cost;
                            $supplier_product->extra_tax           = $po_group_product__detail->unit_extra_tax;
                            $supplier_product->vat_actual          = $po_group_product__detail->pogpd_vat_actual_percent_val;
                            if ($check_to_bond == 0) {
                                $supplier_product->import_tax_actual   = $po_group_product__detail->actual_tax_percent;
                            }
                            $supplier_product->gross_weight        = $po_group_product__detail->total_gross_weight / $po_group_product__detail->quantity_inv;
                            $supplier_product->save();

                            $product = Product::find($po_group_product__detail->product_id);

                            // this is the price of after conversion for THB
                            $importTax              = $supplier_product->import_tax_actual;
                            $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                            $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);

                            $product->total_buy_unit_cost_price = $total_buying_price;
                            $product->t_b_u_c_p_of_supplier     = $total_buying_price * $supplier_conv_rate_thb;
                            $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                            // creating a history on a product detail page which shipment updated the product COGS
                            $product_history              = new ProductHistory;
                            $product_history->user_id     = $user != null ? $user->id : Auth::user()->id;
                            $product_history->product_id  = $product->id;
                            $product_history->group_id    = @$po_group->id;
                            $product_history->column_name = 'COGS Updated through ' . @$po_group->ref_id . ' by edition unit gross weight For ' . @$supplier_product->supplier->reference_name;
                            $product_history->old_value   = @$product->selling_price;
                            $product_history->new_value   = @$total_selling_price;
                            $product_history->save();

                            $product->selling_price           = $total_selling_price;
                            $product->supplier_id             = $supplier_product->supplier_id;
                            $product->last_price_updated_date = Carbon::now();
                            $product->last_date_import        = Carbon::now();
                            $product->save();

                            $po__ids = $po_group->po_group_detail != null ? $po_group->po_group_detail()->pluck('purchase_order_id')->toArray() : [];
                            $po_detail_products = PurchaseOrderDetail::where('product_id', $product->id)->whereNotNull('order_product_id')->whereIn('po_id', $po__ids)->get();
                            if ($po_detail_products->count() > 0) {
                                foreach ($po_detail_products as $pod) {
                                    if ($pod->order_product) {
                                        $pod->order_product->actual_cost = $product->selling_price;
                                        $pod->order_product->save();
                                    }
                                }
                            }
                        }
                    }
                    DB::commit();
                    return response()->json(['gross_weight' => true, 'po_group' => $group_detail->po_group]);
                } elseif ($key == 'import_tax_book') {
                    $po_detail->$key = $value;
                    $po_detail->import_tax_book_price = ($value / 100) * $po_detail->total_unit_price_in_thb;
                    $po_detail->save();

                    $all_ids = PurchaseOrder::where('po_group_id', @$po_detail->po_group_id)->where('supplier_id', $po_detail->supplier_id)->pluck('id');

                    $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $po_detail->product_id)->get();

                    foreach ($all_record as $value) {
                        $value->pod_import_tax_book = $po_detail->import_tax_book;
                        $import_tax_book_total = ($po_detail->import_tax_book/100) * $value->total_unit_price_in_thb;
                        $value->pod_import_tax_book_price = $import_tax_book_total;
                        $value->save();
                    }

                    $po_group = PoGroup::where('id', $request->po_group_id)->first();

                    $total_import_tax_book_price = 0;
                    $total_import_tax_book_percent = 0;
                    $po_group_details = $po_group->po_group_product_details;

                    foreach ($po_group_details as $po_group_detail) {
                        $total_import_tax_book_price += ($po_group_detail->import_tax_book_price);
                        $total_import_tax_book_percent += ($po_group_detail->import_tax_book);
                    }
                    if ($total_import_tax_book_price == 0) {
                        foreach ($po_group_details as $po_group_detail) {
                            $count = PoGroupProductDetail::where('status', 1)->where('po_group_id', $po_group_detail->po_group_id)->count();
                            $book_tax = (1 / $count) * $po_group_detail->total_unit_price_in_thb;
                            $total_import_tax_book_price += $book_tax;
                        }
                    }
                    // dd($total_import_tax_book_price);
                    $po_group->po_group_import_tax_book = $total_import_tax_book_price;
                    $po_group->total_import_tax_book_percent = $total_import_tax_book_percent;
                    $po_group->save();

                    foreach ($po_group->po_group_product_details as $group_detail) {

                        $tax = $po_group->tax;
                        $total_import_tax = $po_group->po_group_import_tax_book;
                        $import_tax = $group_detail->import_tax_book;
                        if ($total_import_tax != 0) {
                            $actual_tax_percent = ($tax / $total_import_tax * $import_tax);
                            $group_detail->actual_tax_percent = $actual_tax_percent;
                        }
                        $group_detail->save();
                    }
                    DB::commit();
                    return response()->json(['import_tax' => true, 'po_group' => $po_group]);
                } elseif ($key == 'pogpd_vat_actual') {
                    $po_detail->$key = $value;
                    $po_detail->pogpd_vat_actual_percent = ($value / 100) * $po_detail->total_unit_price_in_thb;
                    $po_detail->save();

                    $po_group = PoGroup::where('id', $request->po_group_id)->first();

                    $total_vat_actual_price = 0;
                    $total_vat_actual_percent = 0;
                    $po_group_details = $po_group->po_group_product_details;

                    $all_ids = PurchaseOrder::where('po_group_id', $po_detail->po_group_id)->where('supplier_id', $po_detail->supplier_id)->pluck('id');

                    $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $po_detail->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->get();
                    if ($all_record->count() > 1) {
                        foreach ($all_record as $record) {
                            $record->pod_vat_actual = $value;
                            $record->pod_vat_actual_total_price_in_thb = ($value / 100) * $record->total_unit_price_in_thb;
                            $record->save();
                        }
                    }

                    foreach ($po_group_details as $po_group_detail) {
                        $total_vat_actual_price += ($po_group_detail->pogpd_vat_actual_percent);
                        $total_vat_actual_percent += ($po_group_detail->pogpd_vat_actual);


                        if ($total_vat_actual_price == 0) {
                            foreach ($po_group_details as $po_group_detail) {
                                $count = PoGroupProductDetail::where('status', 1)->where('po_group_id', $po_group_detail->po_group_id)->count();
                                $vat_tax = (1 / $count) * $po_group_detail->total_unit_price_in_thb;
                                $total_vat_actual_price += $vat_tax;
                            }
                        }
                    }

                    $po_group->po_group_vat_actual = $total_vat_actual_price;
                    $po_group->po_group_vat_actual_percent = $total_vat_actual_percent;
                    $po_group->save();

                    /*Update vat on a product level*/
                    DB::commit();
                    return response()->json(['import_tax' => true, 'po_group' => $po_group]);
                } elseif ($key == 'custom_invoice_number') {
                    $po_group_custom_invoice = PoGroup::find($request->pod_id);

                    if ($po_group_custom_invoice !== null) {
                        $po_group_custom_invoice->$key = $value;
                        $po_group_custom_invoice->save();
                        DB::commit();
                        return response()->json(['custom_invoice_number' => true]);
                    }
                } elseif ($key == 'supplier_invoice_number') {
                    $po_group_custom_invoice = PoGroup::find($request->pod_id);
                    if ($po_group_custom_invoice !== null) {
                        $po_group_custom_invoice->$key = $value;
                        $po_group_custom_invoice->save();
                        DB::commit();
                        return response()->json(['custom_invoice_number' => true]);
                    }
                } elseif ($key == 'custom_line_number') {
                    $po_detail->$key = $value;
                } elseif ($key == 'total_extra_tax') {
                    if ($po_detail->po_group->is_review == 1) {
                        if ($po_detail) {
                            $p_g_pd = $po_detail;
                            $supplier_product       = SupplierProducts::where('supplier_id', $p_g_pd->supplier_id)->where('product_id', $p_g_pd->product_id)->first();
                            $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1;
                            $buying_price_in_thb    = $p_g_pd->unit_price_in_thb;

                            $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                            $supplier_product->freight             = $p_g_pd->freight;
                            $supplier_product->landing             = $p_g_pd->landing;
                            $supplier_product->extra_cost          = $p_g_pd->total_extra_cost / $p_g_pd->quantity_inv;
                            $supplier_product->extra_tax           = $value / $p_g_pd->quantity_inv;
                            $supplier_product->import_tax_actual   = $p_g_pd->actual_tax_percent;
                            $supplier_product->gross_weight        = $p_g_pd->total_gross_weight / $p_g_pd->quantity_inv;
                            $supplier_product->save();

                            $product = Product::find($p_g_pd->product_id);
                            // this is the price of after conversion for THB

                            $importTax              = $supplier_product->import_tax_actual;
                            $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                            $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);
                            $product->total_buy_unit_cost_price = $total_buying_price;
                            //this is supplier buying unit cost price
                            $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;
                            //this is selling price
                            $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                            // creating a history on a product detail page which shipment updated the product COGS
                            $product_history              = new ProductHistory;
                            $product_history->user_id     = Auth::user()->id;
                            $product_history->product_id  = $product->id;
                            $product_history->group_id  = @$p_g_pd->po_group->id;
                            $product_history->column_name = 'COGS Updated through closed shipment ' . @$p_g_pd->po_group->ref_id . ' by editing total extra tax';
                            $product_history->old_value   = @$product->selling_price;
                            $product_history->new_value   = @$total_selling_price;
                            $product_history->save();

                            $product->selling_price           = $total_selling_price;
                            $product->supplier_id             = $supplier_product->supplier_id;
                            $product->last_price_updated_date = Carbon::now();
                            $product->last_date_import        = Carbon::now();
                            $product->save();

                            $p_g_pd->product_cost = $total_selling_price;
                            $p_g_pd->save();

                            $stock_out = StockManagementOut::where('po_group_id', $p_g_pd->po_group_id)->where('product_id', $p_g_pd->product_id)->where('warehouse_id', $p_g_pd->to_warehouse_id)->update(['cost' => $p_g_pd->product_cost, 'cost_date' => Carbon::now()]);

                            $purchase_orders = PurchaseOrder::whereIn('id', PoGroupDetail::where('po_group_id', $po_detail->po_group->id)->pluck('purchase_order_id'))->get();
                            foreach ($purchase_orders as $PO) {
                                $purchase_order_details = PurchaseOrderDetail::where('po_id', $PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity', '!=', 0)->get();
                                foreach ($purchase_order_details as $p_o_d) {
                                    $product_id = $p_o_d->product_id;
                                    if ($p_o_d->order_product_id != null) {
                                        $product                           = Product::find($p_o_d->product_id);
                                        $p_o_d->order_product->actual_cost = $product != null ? $product->selling_price : 0;
                                        $p_o_d->order_product->save();
                                    }
                                }
                            }
                        }


                        $po_detail->$key = $value;
                        $po_detail->unit_extra_tax = ($value / $po_detail->quantity_inv);
                    } else {
                        $po_detail->$key = $value;
                        $po_detail->unit_extra_tax = ($value / $po_detail->quantity_inv);
                    }
                } elseif ($key == 'unit_extra_tax') {
                    if ($po_detail->po_group->is_review == 1) {
                        if ($po_detail) {
                            $p_g_pd = $po_detail;
                            $supplier_product       = SupplierProducts::where('supplier_id', $p_g_pd->supplier_id)->where('product_id', $p_g_pd->product_id)->first();
                            $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1;
                            $buying_price_in_thb    = $p_g_pd->unit_price_in_thb;

                            $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                            $supplier_product->freight             = $p_g_pd->freight;
                            $supplier_product->landing             = $p_g_pd->landing;
                            $supplier_product->extra_cost          = $p_g_pd->total_extra_cost / $p_g_pd->quantity_inv;
                            $supplier_product->extra_tax           = $value;
                            $supplier_product->import_tax_actual   = $p_g_pd->actual_tax_percent;
                            $supplier_product->gross_weight        = $p_g_pd->total_gross_weight / $p_g_pd->quantity_inv;
                            $supplier_product->save();

                            $product = Product::find($p_g_pd->product_id);
                            // this is the price of after conversion for THB

                            $importTax              = $supplier_product->import_tax_actual;
                            $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                            $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);
                            $product->total_buy_unit_cost_price = $total_buying_price;
                            //this is supplier buying unit cost price
                            $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;
                            //this is selling price
                            $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                            // creating a history on a product detail page which shipment updated the product COGS
                            $product_history              = new ProductHistory;
                            $product_history->user_id     = Auth::user()->id;
                            $product_history->product_id  = $product->id;
                            $product_history->group_id  = @$p_g_pd->po_group->id;
                            $product_history->column_name = 'COGS Updated through closed shipment ' . @$p_g_pd->po_group->ref_id . ' by editing total extra tax';
                            $product_history->old_value   = @$product->selling_price;
                            $product_history->new_value   = @$total_selling_price;
                            $product_history->save();

                            $product->selling_price           = $total_selling_price;
                            $product->supplier_id             = $supplier_product->supplier_id;
                            $product->last_price_updated_date = Carbon::now();
                            $product->last_date_import        = Carbon::now();
                            $product->save();

                            $p_g_pd->product_cost = $total_selling_price;
                            $p_g_pd->save();

                            $stock_out = StockManagementOut::where('po_group_id', $p_g_pd->po_group_id)->where('product_id', $p_g_pd->product_id)->where('warehouse_id', $p_g_pd->to_warehouse_id)->update(['cost' => $p_g_pd->product_cost, 'cost_date' => Carbon::now()]);

                            $purchase_orders = PurchaseOrder::whereIn('id', PoGroupDetail::where('po_group_id', $po_detail->po_group->id)->pluck('purchase_order_id'))->get();
                            foreach ($purchase_orders as $PO) {
                                $purchase_order_details = PurchaseOrderDetail::where('po_id', $PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity', '!=', 0)->get();
                                foreach ($purchase_order_details as $p_o_d) {
                                    $product_id = $p_o_d->product_id;
                                    if ($p_o_d->order_product_id != null) {
                                        $product                           = Product::find($p_o_d->product_id);
                                        $p_o_d->order_product->actual_cost = $product != null ? $product->selling_price : 0;
                                        $p_o_d->order_product->save();
                                    }
                                }
                            }
                        }


                        $po_detail->$key = $value;
                        $po_detail->total_extra_tax = ($value * $po_detail->quantity_inv);
                    } else {
                        $po_detail->$key = $value;
                        $po_detail->total_extra_tax = ($value * $po_detail->quantity_inv);
                    }
                } elseif ($key == 'total_extra_cost') {
                    if ($po_detail->po_group->is_review == 1) {
                        if ($po_detail) {
                            $p_g_pd = $po_detail;
                            $supplier_product       = SupplierProducts::where('supplier_id', $p_g_pd->supplier_id)->where('product_id', $p_g_pd->product_id)->first();
                            $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1;
                            $buying_price_in_thb    = $p_g_pd->unit_price_in_thb;

                            $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                            $supplier_product->freight             = $p_g_pd->freight;
                            $supplier_product->landing             = $p_g_pd->landing;
                            $supplier_product->extra_cost          = $value / $p_g_pd->quantity_inv;
                            $supplier_product->extra_tax           = $p_g_pd->total_extra_tax / $p_g_pd->quantity_inv;
                            $supplier_product->import_tax_actual   = $p_g_pd->actual_tax_percent;
                            $supplier_product->gross_weight        = $p_g_pd->total_gross_weight / $p_g_pd->quantity_inv;
                            $supplier_product->save();

                            $product = Product::find($p_g_pd->product_id);
                            // this is the price of after conversion for THB

                            $importTax              = $supplier_product->import_tax_actual;
                            $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                            $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);
                            $product->total_buy_unit_cost_price = $total_buying_price;
                            //this is supplier buying unit cost price
                            $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;
                            //this is selling price
                            $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                            // creating a history on a product detail page which shipment updated the product COGS
                            $product_history              = new ProductHistory;
                            $product_history->user_id     = Auth::user()->id;
                            $product_history->product_id  = $product->id;
                            $product_history->group_id    = @$p_g_pd->po_group->id;
                            $product_history->column_name = 'COGS Updated through closed shipment ' . @$p_g_pd->po_group->ref_id . ' by editing total extra cost';
                            $product_history->old_value   = @$product->selling_price;
                            $product_history->new_value   = @$total_selling_price;
                            $product_history->save();

                            $product->selling_price           = $total_selling_price;
                            $product->supplier_id             = $supplier_product->supplier_id;
                            $product->last_price_updated_date = Carbon::now();
                            $product->last_date_import        = Carbon::now();
                            $product->save();

                            $p_g_pd->product_cost = $total_selling_price;
                            $p_g_pd->save();

                            $stock_out = StockManagementOut::where('po_group_id', $p_g_pd->po_group_id)->where('product_id', $p_g_pd->product_id)->where('warehouse_id', $p_g_pd->to_warehouse_id)->update(['cost' => $p_g_pd->product_cost, 'cost_date' => Carbon::now()]);

                            $purchase_orders = PurchaseOrder::whereIn('id', PoGroupDetail::where('po_group_id', $po_detail->po_group->id)->pluck('purchase_order_id'))->get();
                            foreach ($purchase_orders as $PO) {
                                $purchase_order_details = PurchaseOrderDetail::where('po_id', $PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity', '!=', 0)->get();
                                foreach ($purchase_order_details as $p_o_d) {
                                    $product_id = $p_o_d->product_id;
                                    if ($p_o_d->order_product_id != null) {
                                        $product                = Product::find($p_o_d->product_id);
                                        $p_o_d->order_product->actual_cost = $product != null ? $product->selling_price : 0;
                                        $p_o_d->order_product->save();
                                    }
                                }
                            }
                        }
                        $po_detail->$key = $value;
                        $po_detail->unit_extra_cost = ($value / $po_detail->quantity_inv);
                    } else {
                        $po_detail->$key = $value;
                        $po_detail->unit_extra_cost = ($value / $po_detail->quantity_inv);
                    }
                } elseif ($key == 'unit_extra_cost') {
                    if ($po_detail->po_group->is_review == 1) {
                        if ($po_detail) {
                            $p_g_pd = $po_detail;
                            $supplier_product       = SupplierProducts::where('supplier_id', $p_g_pd->supplier_id)->where('product_id', $p_g_pd->product_id)->first();
                            $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1;
                            $buying_price_in_thb    = $p_g_pd->unit_price_in_thb;

                            $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                            $supplier_product->freight             = $p_g_pd->freight;
                            $supplier_product->landing             = $p_g_pd->landing;
                            $supplier_product->extra_cost          = $value;
                            $supplier_product->extra_tax           = $p_g_pd->total_extra_tax / $p_g_pd->quantity_inv;
                            $supplier_product->import_tax_actual   = $p_g_pd->actual_tax_percent;
                            $supplier_product->gross_weight        = $p_g_pd->total_gross_weight / $p_g_pd->quantity_inv;
                            $supplier_product->save();

                            $product = Product::find($p_g_pd->product_id);
                            // this is the price of after conversion for THB

                            $importTax              = $supplier_product->import_tax_actual;
                            $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                            $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);
                            $product->total_buy_unit_cost_price = $total_buying_price;
                            //this is supplier buying unit cost price
                            $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;
                            //this is selling price
                            $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                            // creating a history on a product detail page which shipment updated the product COGS
                            $product_history              = new ProductHistory;
                            $product_history->user_id     = Auth::user()->id;
                            $product_history->product_id  = $product->id;
                            $product_history->group_id  = @$p_g_pd->po_group->id;
                            $product_history->column_name = 'COGS Updated through closed shipment ' . @$p_g_pd->po_group->ref_id . ' by editing total extra cost';
                            $product_history->old_value   = @$product->selling_price;
                            $product_history->new_value   = @$total_selling_price;
                            $product_history->save();

                            $product->selling_price           = $total_selling_price;
                            $product->supplier_id             = $supplier_product->supplier_id;
                            $product->last_price_updated_date = Carbon::now();
                            $product->last_date_import        = Carbon::now();
                            $product->save();

                            $p_g_pd->product_cost = $total_selling_price;
                            $p_g_pd->save();

                            $stock_out = StockManagementOut::where('po_group_id', $p_g_pd->po_group_id)->where('product_id', $p_g_pd->product_id)->where('warehouse_id', $p_g_pd->to_warehouse_id)->update(['cost' => $p_g_pd->product_cost, 'cost_date' => Carbon::now()]);

                            $purchase_orders = PurchaseOrder::whereIn('id', PoGroupDetail::where('po_group_id', $po_detail->po_group->id)->pluck('purchase_order_id'))->get();
                            foreach ($purchase_orders as $PO) {
                                $purchase_order_details = PurchaseOrderDetail::where('po_id', $PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity', '!=', 0)->get();
                                foreach ($purchase_order_details as $p_o_d) {
                                    $product_id = $p_o_d->product_id;
                                    if ($p_o_d->order_product_id != null) {
                                        $product                = Product::find($p_o_d->product_id);
                                        $p_o_d->order_product->actual_cost = $product != null ? $product->selling_price : 0;
                                        $p_o_d->order_product->save();
                                    }
                                }
                            }
                        }
                        $po_detail->$key = $value;
                        $po_detail->total_extra_cost = ($value * $po_detail->quantity_inv);
                    } else {
                        $po_detail->$key = $value;
                        $po_detail->total_extra_cost = ($value * $po_detail->quantity_inv);
                    }
                } elseif ($key == 'currency_conversion_rate') {
                    $po_ids = $po_detail->po_group->purchase_orders()->where('supplier_id', $po_detail->supplier_id)->pluck('id')->toArray();


                    if (!empty($po_ids)) {
                        $all_record = PurchaseOrderDetail::whereIn('po_id', $po_ids)->where('product_id', $po_detail->product_id)->pluck('po_id')->toArray();

                        $all_pos = PurchaseOrder::whereIn('id', $all_record)->get();

                        //To update currency conversion rate through job

                        $statusCheck = ExportStatus::where('type', 'update_old_record')->where('user_id', 1)->first();
                        $data = $request->all();
                        if ($statusCheck == null) {
                            $new = new ExportStatus();
                            $new->type = 'update_old_record';
                            $new->user_id = 1;
                            $new->status = 1;
                            if ($new->save()) {
                                UpdateOldRecord::dispatch($data, 1, 1, $all_pos, $po_detail->product_id, $value);
                                DB::commit();
                                return response()->json(['status' => 1]);
                            }
                        } else if ($statusCheck->status == 0 || $statusCheck->status == 2) {

                            ExportStatus::where('type', 'update_old_record')->where('user_id', 1)->update(['status' => 1, 'exception' => null]);

                            UpdateOldRecord::dispatch($data, 1, 1, $all_pos, $po_detail->product_id, $value);
                            DB::commit();
                            return response()->json(['status' => 1]);
                        } else if ($statusCheck->status == 1) {
                            UpdateOldRecord::dispatch($data, 1, 1, $all_pos, $po_detail->product_id, $value);
                            DB::commit();
                            return response()->json(['status' => 1]);
                        } else {
                            DB::commit();
                            return response()->json(['msg' => 'Export already being prepared', 'status' => 2]);
                        }


                    }
                } elseif ($key == 'sup_ref_no') {
                    $getSupplierProduct = SupplierProducts::where('product_id', $po_detail->product_id)->where('supplier_id', $po_detail->supplier_id)->first();
                    if ($getSupplierProduct) {
                        $getOldValue = $getSupplierProduct->product_supplier_reference_no;

                        $getSupplierProduct->product_supplier_reference_no = $value;
                        $getSupplierProduct->save();

                        $product_history              = new ProductHistory;
                        $product_history->user_id     = Auth::user()->id;
                        $product_history->product_id  = $po_detail->product_id;
                        $product_history->column_name = 'Supplier Ref No. Updated through Shipment ' . @$po_detail->po_group->ref_id . ' ';
                        $product_history->old_value   = @$getOldValue;
                        $product_history->new_value   = @$value;
                        $product_history->save();
                    }
                } else {
                    $po_detail->$key = $value;
                }
            }
            $po_detail->save();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }
    }

    public function savePoGroupData(Request $request)
    {
        DB::beginTransaction();
        try {
            $is_group_update = false;
            $po_group = PoGroup::find($request->gId);

            $po_id_to_update_group = $po_group->po_group_detail[0]->purchase_order_id;
            foreach ($request->except('gId', 'old_value') as $key => $value) {
                $po_group->$key = $value;
                $po_group->save();

                if ($key != '') {

                    $PoGroupProduct_history = new PoGroupProductHistory;
                    $PoGroupProduct_history->user_id = Auth::user()->id;
                    $PoGroupProduct_history->order_product_id = '';
                    $PoGroupProduct_history->old_value = $request->old_value;
                    $PoGroupProduct_history->column_name = $key;
                    $PoGroupProduct_history->po_group_id = $request->gId;
                    $PoGroupProduct_history->new_value = $value;
                    $PoGroupProduct_history->save();
                }
            }

            $po_group_details = PoGroupProductDetail::where('status', 1)->where('po_group_id', $request->gId)->where('quantity_inv', '!=', 0)->get();

            $final_book_percent = 0;
            $final_vat_actual_percent = 0;
            foreach ($po_group_details as $value) {
                if ($value->import_tax_book != null && $value->import_tax_book != 0) {
                    $check_dis = $value->discount;
                    $discount_val = 0;
                    if ($check_dis != null) {
                        $discount_val = $value->unit_price_in_thb * ($value->discount / 100);
                    }

                    $final_book_percent = $final_book_percent + round((($value->import_tax_book / 100) * round(($value->unit_price_in_thb - $discount_val), 2)) * $value->quantity_inv, 2);
                }

                if ($value->pogpd_vat_actual != null && $value->pogpd_vat_actual != 0) {
                    $final_vat_actual_percent = $final_vat_actual_percent + (($value->pogpd_vat_actual / 100) * $value->total_unit_price_in_thb);
                }
            }

            foreach ($po_group_details as $group_detail) {
                if ($key == 'freight') {
                    $item_gross_weight     = $group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_freight         = $po_group->freight;
                    $total_quantity        = $group_detail->quantity_inv;
                    $freight               = (($total_freight * ($item_gross_weight / $total_gross_weight)) / $total_quantity);
                    $group_detail->freight = $freight;
                } else if ($key == 'landing') {
                    $item_gross_weight     = $group_detail->total_gross_weight;
                    $total_gross_weight    = $po_group->po_group_total_gross_weight;
                    $total_quantity        = $group_detail->quantity_inv;
                    $total_landing         = $po_group->landing;
                    $landing               = (($total_landing * ($item_gross_weight / $total_gross_weight)) / $total_quantity);
                    $group_detail->landing = $landing;
                } else if ($key == 'tax') {
                    $group_tax = $po_group->tax;
                    $check_dis = $group_detail->discount;
                    $discount_val = 0;
                    if ($check_dis != null) {
                        $discount_val = $group_detail->unit_price_in_thb * ($group_detail->discount / 100);
                    }
                    // $find_item_tax_value = $group_detail->import_tax_book/100 * $group_detail->total_unit_price_in_thb;
                    $find_item_tax_value = round(round(($group_detail->import_tax_book / 100) * ($group_detail->unit_price_in_thb - $discount_val), 2) * $group_detail->quantity_inv, 2);
                    if ($final_book_percent != 0 && $group_tax != 0) {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;

                        $cost = $find_item_tax * $group_tax;
                        if ($group_tax != 0) {
                            $group_detail->weighted_percent =  number_format(($find_item_tax) * 100, 8, '.', '');
                        } else {
                            $group_detail->weighted_percent = 0;
                        }
                        $group_detail->save();

                        $weighted_percent = round(($group_detail->weighted_percent / 100) * $group_tax, 2);

                        if ($group_detail->quantity_inv != 0) {
                            // $group_detail->actual_tax_price =  number_format(round($find_item_tax*$group_tax,2) / $group_detail->quantity_inv,2,'.','');
                            $group_detail->actual_tax_price =  round($weighted_percent / $group_detail->quantity_inv, 2);
                        } else {
                            $group_detail->actual_tax_price =  0;
                        }
                        $group_detail->save();

                        if ($group_detail->unit_price_in_thb != 0) {
                            $group_detail->actual_tax_percent = number_format(($group_detail->actual_tax_price / $group_detail->unit_price_in_thb) * 100, 2, '.', '');
                        } else {
                            $group_detail->actual_tax_percent = 0;
                        }
                        $group_detail->save();
                    } else if ($group_tax != 0) {
                        $all_pgpd = PoGroupProductDetail::where('status', 1)->where('po_group_id', $po_group->id)->count();
                        $tax = $group_detail->po_group->tax;
                        $weighted = 1 / $all_pgpd;
                        // $group_detail->actual_tax_price = number_format(($weighted*$tax),2,'.','');
                        // $actual_tax_val = number_format(($weighted*$tax),2,'.','');
                        if ($group_detail->quantity_inv != 0 && $group_detail->quantity_inv !== 0) {
                            $group_detail->actual_tax_price = number_format(($weighted * $tax) / $group_detail->quantity_inv, 2, '.', '');
                        } else {
                            $group_detail->actual_tax_price = number_format(($weighted * $tax), 2, '.', '');
                        }
                        $group_detail->save();

                        if ($group_detail->unit_price_in_thb != 0) {
                            $group_detail->actual_tax_percent = ($group_detail->actual_tax_price / $group_detail->unit_price_in_thb) * 100;
                        }
                        $group_detail->weighted_percent =  number_format(($weighted) * 100, 8, '.', '');
                        $group_detail->save();
                    }
                } else if ($key == 'vat_actual_tax') {
                    $vat_actual_tax = $po_group->vat_actual_tax;

                    $find_item_tax_value = $group_detail->pogpd_vat_actual / 100 * $group_detail->total_unit_price_in_thb;
                    if ($final_vat_actual_percent != 0 && $vat_actual_tax != 0) {
                        $find_item_tax = $find_item_tax_value / $final_vat_actual_percent;

                        $cost = $find_item_tax * $vat_actual_tax;
                        if ($vat_actual_tax != 0) {
                            $group_detail->vat_weighted_percent =  number_format(($cost / $vat_actual_tax) * 100, 4, '.', '');
                        } else {
                            $group_detail->vat_weighted_percent = 0;
                        }
                        $group_detail->save();

                        $vat_weighted_percent = ($group_detail->vat_weighted_percent / 100) * $vat_actual_tax;

                        if ($group_detail->quantity_inv != 0) {
                            $group_detail->pogpd_vat_actual_price =  number_format(round($find_item_tax * $vat_actual_tax, 2) / $group_detail->quantity_inv, 2, '.', '');
                        } else {
                            $group_detail->pogpd_vat_actual_price =  0;
                        }
                        $group_detail->save();

                        if ($group_detail->unit_price_in_thb != 0) {
                            $group_detail->pogpd_vat_actual_percent_val = number_format(($group_detail->pogpd_vat_actual_price / $group_detail->unit_price_in_thb) * 100, 2, '.', '');
                        } else {
                            $group_detail->pogpd_vat_actual_percent_val = 0;
                        }
                        $group_detail->save();

                        /*here we will update the PO's and group value regarding new vat*/

                        $group_id = $request->gId;
                        $pod = PurchaseOrderDetail::select('po_id')->where('product_id', $group_detail->product_id)->whereHas('PurchaseOrder', function ($q) use ($group_id, $group_detail) {
                            $q->where('po_group_id', $group_id);
                            $q->where('supplier_id', $group_detail->supplier_id);
                        })->get();

                        if ($pod->count() > 0) {
                            $is_group_update = true;
                            foreach ($pod as $po) {
                                $purchase_order = $po->PurchaseOrder;
                                $po_id = $purchase_order->id;

                                /*now we are calling a PO detail function to update the PO values*/
                                $objectCreated     = new PurchaseOrderDetail;
                                $updatePoVatValues = $objectCreated->updatePurchaseOrderVatFromGroup($po_id, $group_detail->pogpd_vat_actual_percent_val, $group_id);
                            }
                        }

                        /*here we will update the PO's and group value regarding new vat*/
                    } else if ($vat_actual_tax != 0) {
                        $all_pgpd = PoGroupProductDetail::where('status', 1)->where('po_group_id', $po_group->id)->count();

                        $tax = $group_detail->po_group->vat_actual_tax;
                        $weighted = 1 / $all_pgpd;
                        if ($group_detail->quantity_inv != 0 && $group_detail->quantity_inv !== 0) {
                            $group_detail->pogpd_vat_actual_price = number_format(($weighted * $tax) / $group_detail->quantity_inv, 2, '.', '');
                        } else {
                            $group_detail->pogpd_vat_actual_price = number_format(($weighted * $tax), 2, '.', '');
                        }
                        $group_detail->save();

                        if ($group_detail->unit_price_in_thb != 0) {
                            $group_detail->pogpd_vat_actual_percent_val = ($group_detail->pogpd_vat_actual_price / $group_detail->unit_price_in_thb) * 100;
                        }
                        $group_detail->vat_weighted_percent =  number_format(($weighted) * 100, 4, '.', '');
                        $group_detail->save();

                        /*here we will update the PO's and group value regarding new vat*/

                        $group_id = $request->gId;
                        $pod = PurchaseOrderDetail::select('po_id')->where('product_id', $group_detail->product_id)->whereHas('PurchaseOrder', function ($q) use ($group_id, $group_detail) {
                            $q->where('po_group_id', $group_id);
                            $q->where('supplier_id', $group_detail->supplier_id);
                        })->get();

                        if ($pod->count() > 0) {
                            $is_group_update = true;
                            foreach ($pod as $po) {
                                $purchase_order = $po->PurchaseOrder;
                                $po_id = $purchase_order->id;

                                /*now we are calling a PO detail function to update the PO values*/
                                $objectCreated     = new PurchaseOrderDetail;
                                $updatePoVatValues = $objectCreated->updatePurchaseOrderVatFromGroup($po_id, $group_detail->pogpd_vat_actual_percent_val, $group_id);
                            }
                        }

                        /*here we will update the PO's and group value regarding new vat*/
                    }
                }
                $group_detail->save();
            }

            /*if this variable is true then we will update the Group at once*/
            if ($is_group_update == true) {
                app('App\Http\Controllers\Purchasing\PurchaseOrderController')->updateGroupViaPo($po_id_to_update_group);
            }
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['success' => false]);
        }
    }

    public function confirmPoGroup(Request $request)
    {
        DB::beginTransaction();
        try {
            $confirm_from_draft = QuotationConfig::where('section', 'warehouse_management_page')->first();
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
            $po_group = PoGroup::find($request->id);

            //to find to warehouse is bonded or not
            if ($po_group->warehouse_id != null) {
                $check_to_bond = $po_group->ToWarehouse->is_bonded;
            } else {
                $check_to_bond = 0;
            }

            if ($has_warehouse_account == 1 && $po_group->from_warehouse_id == null) {
                $done = app('App\Http\Controllers\Warehouse\PoGroupsController')->confirmPoGroupDetail($request);
                $enc = json_decode($done->getContent());
                $warehouse_receiving = $enc->success;
            }

            $po_group_product_details = PoGroupProductDetail::where('status', 1)->where('po_group_id', $request->id)->where('quantity_inv', '!=', 0)->get();
            foreach ($po_group_product_details as $p_g_pd) {
                if ($p_g_pd->supplier_id != null) {
                    $supplier_product                    = SupplierProducts::where('supplier_id', $p_g_pd->supplier_id)->where('product_id', $p_g_pd->product_id)->first();
                } else {
                    $check_product = Product::find($p_g_pd->product_id);
                    if ($check_product) {
                        $supplier_product = SupplierProducts::where('supplier_id', $check_product->supplier_id)->where('product_id', $check_product->id)->first();
                    }
                }
                $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1;
                $buying_price_in_thb    = $p_g_pd->unit_price_in_thb;


                $product = Product::find($p_g_pd->product_id);
                $column = 'Purchasing Price (EUR) Before Discount';

                $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->buying_price_before_discount, $p_g_pd->unit_price);
                $supplier_product->buying_price_before_discount = $p_g_pd->unit_price;

                $column = 'Discount';
                $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->buying_price_before_discount, $p_g_pd->unit_price);
                $supplier_product->discount = $p_g_pd->discount;

                $column = 'Currency Conversion Rate';
                $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->currency_conversion_rate, 1 / $p_g_pd->currency_conversion_rate);
                $supplier_product->currency_conversion_rate = 1 / $p_g_pd->currency_conversion_rate;


                // $supplier_product->buying_price_in_thb = $buying_price_in_thb;

                // $buying_unit_price_in_thb = ($p_g_pd->unit_price - ($p_g_pd->unit_price * $p_g_pd->discount/100)) * (1/$p_g_pd->currency_conversion_rate);
                $buying_unit_price_in_thb    = $p_g_pd->unit_price_in_thb;
                $column = 'Buying Price in THB';
                $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->buying_price_in_thb, $buying_unit_price_in_thb);
                $supplier_product->buying_price_in_thb = $buying_unit_price_in_thb;

                if ($p_g_pd->supplier_id != null) {
                    $column = 'Freight';
                    $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->freight, $p_g_pd->freight);
                    $supplier_product->freight         = $p_g_pd->freight;

                    $column = 'Landing';
                    $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->landing, $p_g_pd->landing);
                    $supplier_product->landing         = $p_g_pd->landing;
                }

                $column = 'Extra Cost';
                $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->extra_cost, $p_g_pd->unit_extra_cost);
                $supplier_product->extra_cost          = $p_g_pd->unit_extra_cost;

                $column = 'Extra Tax';
                $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->extra_tax, $p_g_pd->unit_extra_tax);
                $supplier_product->extra_tax           = $p_g_pd->unit_extra_tax;

                $column = 'Import Tax';
                $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->unit_import_tax, $p_g_pd->actual_tax_price);
                $supplier_product->unit_import_tax     = $p_g_pd->actual_tax_price;

                $column = 'Import Tax Actual (%)';
                $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->vat_actual, $p_g_pd->pogpd_vat_actual_percent_val);
                $supplier_product->vat_actual          = $p_g_pd->pogpd_vat_actual_percent_val;
                if ($check_to_bond == 0) {
                    $column = 'Import Tax Actual';
                    $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->import_tax_actual, $p_g_pd->actual_tax_percent);
                    $supplier_product->import_tax_actual   = $p_g_pd->actual_tax_percent;
                }

                $column = 'Gross Weight';
                $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$supplier_product->gross_weight, $p_g_pd->total_gross_weight / $p_g_pd->quantity_inv);
                $supplier_product->gross_weight        = $p_g_pd->total_gross_weight / $p_g_pd->quantity_inv;
                $supplier_product->save();


                // this is the price of after conversion for THB
                $importTax              = $supplier_product->import_tax_actual;
                $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);

                $product->total_buy_unit_cost_price = $total_buying_price;
                $product->t_b_u_c_p_of_supplier     = $total_buying_price * $supplier_conv_rate_thb;
                $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                // creating a history on a product detail page which shipment updated the product COGS
                $column = 'COGS Updated through ' . @$po_group->ref_id . ' For ' . @$supplier_product->supplier->reference_name;
                $p_g_pd->saveProductHistory($product->id, @$po_group->id, $column, @$product->selling_price, @$total_selling_price);

                $product->selling_price           = $total_selling_price;
                $product->supplier_id             = $supplier_product->supplier_id;
                $product->last_price_updated_date = Carbon::now();
                $product->last_date_import        = Carbon::now();
                $product->save();

                $p_g_pd->product_cost = $total_selling_price;
                $p_g_pd->is_review = 1;
                $p_g_pd->save();

                $po__ids = $po_group->po_group_detail != null ? $po_group->po_group_detail()->pluck('purchase_order_id')->toArray() : [];
                $po_detail_products = PurchaseOrderDetail::where('product_id', $product->id)->whereNotNull('order_product_id')->whereIn('po_id', $po__ids)->get();
                if ($po_detail_products->count() > 0) {
                    foreach ($po_detail_products as $pod) {
                        if ($pod->order_product) {
                            $pod->order_product->actual_cost = $product->selling_price;
                            $pod->order_product->save();
                        }
                    }
                }

                $stock_out = StockManagementOut::where('po_group_id', $p_g_pd->po_group_id)->where('product_id', $p_g_pd->product_id)->where('warehouse_id', $p_g_pd->to_warehouse_id)->update(['cost' => $p_g_pd->product_cost, 'cost_date' => Carbon::now()]);
                //to update the cost for the orders out against this stock
                $stock_out = StockManagementOut::where('po_group_id', $p_g_pd->po_group_id)->where('product_id', $p_g_pd->product_id)->where('warehouse_id', $p_g_pd->to_warehouse_id)->get();
                $update_already_out_orders = (new StockOutHistory)->updateCostForOrders($stock_out);
            }

            $purchase_orders = PurchaseOrder::whereIn('id', PoGroupDetail::where('po_group_id', $request->id)->pluck('purchase_order_id'))->get();
            foreach ($purchase_orders as $PO) {
                $purchase_order_details = PurchaseOrderDetail::where('po_id', $PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity', '!=', 0)->get();
                foreach ($purchase_order_details as $p_o_d) {
                    $product_id = $p_o_d->product_id;
                    if ($p_o_d->order_product_id != null) {
                        $product                = Product::find($p_o_d->product_id);
                        $p_o_d->order_product->actual_cost = $product != null ? $product->selling_price : 0;
                        $p_o_d->order_product->save();
                    }
                }
            }
            $po_group->is_review = 1;
            $po_group->save();
            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    public function completedReceivingQueueDetail($id)
    {
        $po_group = PoGroup::find($id);
        $product_receiving_history = ProductReceivingHistory::with('get_user')->where('updated_by', auth()->user()->id)->where('po_group_id', $id)->get();
        $status_history = PoGroupStatusHistory::with('get_user')->where('po_group_id', $id)->get();
        $exportLog = ProductReceivingExportLog::where('group_id', $id)->first();
        $last_downloaded = null;
        if ($exportLog != null) {
            $last_downloaded = $exportLog->last_downloaded;
        }
        if ($po_group->from_warehouse_id != null) {
            $check_bond = $po_group->FromWarehouse->is_bonded;
        } else {
            $check_bond = 0;
        }
        // $table_hide_columns = TableHideColumn::select('hide_columns')->where('type','importing_closed_product_receiving')->where('user_id',Auth::user()->id)->first();
        $table_hide_columns = TableHideColumn::select('hide_columns')->where('type', 'importing_open_product_receiving')->where('user_id', Auth::user()->id)->first();
        $display_prods = ColumnDisplayPreference::where('type', 'importing_open_product_receiving')->where('user_id', Auth::user()->id)->first();
        // $display_prods = ColumnDisplayPreference::where('type', 'importing_closed_product_receiving')->where('user_id', Auth::user()->id)->first();
        #to find the po in the group
        $pos = PoGroupDetail::where('po_group_id', $po_group->id)->pluck('purchase_order_id')->toArray();
        $pos_supplier_invoice_no = PurchaseOrder::select('id', 'invoice_number')->whereNotNull('invoice_number')->whereIn('id', $pos)->get();
        $allow_custom_invoice_number = '';
        $show_custom_line_number = '';
        $show_supplier_invoice_number = '';
        $globalAccessConfig4 = QuotationConfig::where('section', 'groups_management_page')->first();
        if ($globalAccessConfig4) {
            $globalaccessForGroups = unserialize($globalAccessConfig4->print_prefrences);
            foreach ($globalaccessForGroups as $val) {
                if ($val['slug'] === "show_custom_invoice_number") {
                    $allow_custom_invoice_number = $val['status'];
                }
                if ($val['slug'] === "show_custom_line_number") {
                    $show_custom_line_number = $val['status'];
                }
                if ($val['slug'] === "supplier_invoice_number") {
                    $show_supplier_invoice_number = $val['status'];
                }
            }
        }

        return $this->render('importing.po-groups.complete-products-receiving', compact('po_group', 'id', 'product_receiving_history', 'status_history', 'last_downloaded', 'pos_supplier_invoice_no', 'table_hide_columns', 'display_prods', 'check_bond', 'allow_custom_invoice_number', 'show_custom_line_number', 'show_supplier_invoice_number'));
    }

    public function getCompletedPoGPD($id)
    {
        $all_record = PoGroupProductDetail::where('status', 1)->where('po_group_id', $id);
        $all_record = $all_record->with('product', 'po_group', 'get_supplier', 'product.units', 'product.sellingUnits');
        $all_pgpd = PoGroupProductDetail::where('status', 1)->where('po_group_id', $id)->count();
        $latest = PoGroup::where('is_review', 1)->orderBy('id', 'DESC')->first();

        $final_book_percent = 0;
        $final_vat_actual_percent = 0;
        foreach ($all_record->get() as $value) {
            if ($value->import_tax_book != null && $value->import_tax_book != 0) {
                $final_book_percent = $final_book_percent + (($value->import_tax_book / 100) * $value->total_unit_price_in_thb);
            }

            if ($value->pogpd_vat_actual != null && $value->pogpd_vat_actual != 0) {
                $final_vat_actual_percent = $final_vat_actual_percent + (($value->pogpd_vat_actual / 100) * $value->total_unit_price_in_thb);
            }
        }

        $not_visible_arr = [];
        $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', 'importing_closed_product_receiving')->where('user_id', Auth::user()->id)->first();
        if ($not_visible_columns != null) {
            $not_visible_arr = explode(',', $not_visible_columns->hide_columns);
        }
        // dd($not_visible_arr);
        $dt = Datatables::of($all_record);

        $dt->addColumn('occurrence', function ($item) {
            return $item->occurrence;
        });

        if (!in_array('40', $not_visible_arr)) {
            $dt->addColumn('product_cost', function ($item) {
                return $item->product_cost != null ? number_format($item->product_cost, 2, '.', ',') : 0;
            });
        } else {
            $dt->addColumn('product_cost', function ($item) {
                return '--';
            });
        }

        if (!in_array('1', $not_visible_arr)) {
            $dt->addColumn('po_number', function ($item) {
                $occurrence = $item->occurrence;
                if ($occurrence == 1) {
                    $id = $item->po_group_id;
                    $pod = PurchaseOrderDetail::select('po_id')->where('product_id', $item->product_id)->whereHas('PurchaseOrder', function ($q) use ($id) {
                        $q->where('po_group_id', $id);
                    })->get();
                    $po = $pod[0]->PurchaseOrder;
                    if ($po->ref_id !== null) {
                        $html_string = '<a target="_blank" href="' . route('get-purchase-order-detail', ['id' => $po->id]) . '" title="View Detail"><b>' . $po->ref_id . '<b</a>';
                        return $html_string;
                    } else {
                        return "--";
                    }
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('po_number', function ($item) {
                return '--';
            });
        }

        if (!in_array('10', $not_visible_arr)) {
            $dt->addColumn('customer', function ($item) {
                $occurrence = $item->occurrence;
                if ($occurrence == 1) {
                    $id = $item->po_group_id;
                    $pod = PurchaseOrderDetail::select('po_id', 'order_id')->where('product_id', $item->product_id)->whereHas('PurchaseOrder', function ($q) use ($id) {
                        $q->where('po_group_id', $id);
                    })->get();
                    $order = Order::find($pod[0]->order_id);
                    if ($order !== null) {

                        $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . $order->customer_id) . '"><b>' . $order->customer->reference_name . '</b></a>';
                        return $html_string . "<span style='visibility:hidden;'>abcabcabcabaaaaaa</span>";
                    } else {
                        return "N.A <span style='visibility:hidden;'>abcabcabcabaaaaaa</span>";
                    }
                } else {
                    return "-- <span style='visibility:hidden;'>abcabcabcabaaaaaa</span>";
                }
            });
        } else {
            $dt->addColumn('customer', function ($item) {
                return '--';
            });
        }

        if (!in_array('2', $not_visible_arr)) {
            $dt->addColumn('order_warehouse', function ($item) {
                $occurrence = $item->occurrence;
                if ($occurrence == 1) {
                    $id = $item->po_group_id;
                    $pod = PurchaseOrderDetail::select('po_id', 'order_id')->where('product_id', $item->product_id)->whereHas('PurchaseOrder', function ($q) use ($id) {
                        $q->where('po_group_id', $id);
                    })->get();
                    $order = Order::find($pod[0]->order_id);
                    return $order !== null ? $order->user->get_warehouse->warehouse_title : "N.A";
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('order_warehouse', function ($item) {
                return '--';
            });
        }

        if (!in_array('3', $not_visible_arr)) {
            $dt->addColumn('order_no', function ($item) {
                $occurrence = $item->occurrence;
                if ($occurrence == 1) {
                    $id = $item->po_group_id;
                    $pod = PurchaseOrderDetail::select('po_id', 'order_id')->where('product_id', $item->product_id)->whereHas('PurchaseOrder', function ($q) use ($id) {
                        $q->where('po_group_id', $id);
                    })->get();
                    $order = Order::find($pod[0]->order_id);
                    if ($order !== null) {
                        $ret = $order->get_order_number_and_link($order);
                        $ref_no = $ret[0];
                        $link = $ret[1];

                        $html_string = '<a target="_blank" href="' . route($link, ['id' => $order->id]) . '" title="View Detail"><b>' . $ref_no . '</b></a>';
                        return $html_string;
                    } else {
                        return "N.A";
                    }
                } else {
                    return '--';
                }
            });
        } else {
            $dt->addColumn('order_no', function ($item) {
                return '--';
            });
        }

        if (!in_array('5', $not_visible_arr)) {
            $dt->addColumn('supplier', function ($item) {
                if ($item->supplier_id !== NULL) {
                    return  $html_string = '<a target="_blank" href="' . url('get-supplier-detail/' . $item->supplier_id) . '"  ><b>' . $item->get_supplier->reference_name . '</b></a>';
                } else {
                    $sup_name = Warehouse::where('id', $item->from_warehouse_id)->first();
                    return  $html_string = $sup_name->warehouse_title;
                }
            });
        } else {
            $dt->addColumn('supplier', function ($item) {
                return '--';
            });
        }
        $dt->filterColumn('supplier', function ($query, $keyword) {
            $query = $query->whereIn('supplier_id', Supplier::select('id')->where('reference_name', 'LIKE', "%$keyword%")->pluck('id'));
        }, true);

        if (!in_array('4', $not_visible_arr)) {
            $dt->addColumn('reference_number', function ($item) {
                if ($item->supplier_id !== NULL) {
                    $sup_name = SupplierProducts::where('supplier_id', $item->supplier_id)->where('product_id', $item->product_id)->first();
                    return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no : "--";
                } else {
                    return "N.A";
                }
            });
        } else {
            $dt->addColumn('reference_number', function ($item) {
                return '--';
            });
        }
        $dt->filterColumn('reference_number', function ($query, $keyword) {
            $query = $query->whereIn('supplier_id', SupplierProducts::select('supplier_id')->where('product_supplier_reference_no', 'LIKE', "%$keyword%")->pluck('supplier_id'))->whereIn('product_id', SupplierProducts::select('product_id')->where('product_supplier_reference_no', 'LIKE', "%$keyword%")->pluck('product_id'));
        }, true);
        if (!in_array('6', $not_visible_arr)) {
            $dt->addColumn('prod_reference_number', function ($item) {
                return  $html_string = '<a target="_blank" href="' . url('get-product-detail/' . $item->product->id) . '"><b>' . $item->product->refrence_code . '</b></a>';
            });
        } else {
            $dt->addColumn('prod_reference_number', function ($item) {
                return '--';
            });
        }
        $dt->filterColumn('prod_reference_number', function ($query, $keyword) {
            $query = $query->whereIn('product_id', Product::select('id')->where('refrence_code', 'LIKE', "%$keyword%")->pluck('id'));
        }, true);

        if (!in_array('7', $not_visible_arr)) {
            $dt->addColumn('brand', function ($item) {
                return $item->product->brand != null ? $item->product->brand : '--';
            });
        } else {
            $dt->addColumn('brand', function ($item) {
                return '--';
            });
        }

        if (!in_array('8', $not_visible_arr)) {
            $dt->addColumn('desc', function ($item) {
                return $item->product->short_desc != null ? $item->product->short_desc : '';
            });
        } else {
            $dt->addColumn('desc', function ($item) {
                return '--';
            });
        }

        $dt->filterColumn('desc', function ($query, $keyword) {
            $query = $query->whereIn('product_id', Product::select('id')->where('short_desc', 'LIKE', "%$keyword%")->pluck('id'));
        }, true);

        if (!in_array('9', $not_visible_arr)) {
            $dt->addColumn('type', function ($item) {
                return $item->product->productType != null ? $item->product->productType->title : '--';
            });
        } else {
            $dt->addColumn('type', function ($item) {
                return '--';
            });
        }

        if (!in_array('21', $not_visible_arr)) {
            $dt->addColumn('buying_price', function ($item) {
                $all_items_buying_price = 0;
                $occurrence = 0;
                $final_unit_price = 0;
                $all_pos = $item->po_group->purchase_orders()->where('supplier_id', $item->supplier_id)->get();
                foreach ($all_pos as $po) {

                    $same_items = $po->PurchaseOrderDetail()->where('product_id', $item->product_id)->get();
                    $all_items_buying_price += $same_items !== null ? $same_items->sum('pod_unit_price') : 0;

                    $occurrence += $same_items->count();
                }
                if ($occurrence != 0) {
                    $final_unit_price = $all_items_buying_price / $occurrence;
                } else {
                    $occurrence = 0;
                }
                // dd($final_unit_price);
                $currency_code = @$item->get_supplier->getCurrency->currency_code;
                return $final_unit_price != null ? number_format($final_unit_price, 2, '.', ',') . ' ' . $currency_code : '';
            });
        } else {
            $dt->addColumn('buying_price', function ($item) {
                return '--';
            });
        }

        if (!in_array('22', $not_visible_arr)) {
            $dt->addColumn('discount', function ($item) {
                $discount = $item->discount !== null ? $item->discount . ' %' : 0;
                return $discount;
            });
        } else {
            $dt->addColumn('discount', function ($item) {
                return '--';
            });
        }

        if (!in_array('23', $not_visible_arr)) {
            $dt->addColumn('t_buying_price_eur', function ($item) {
                $all_items_total_buying_price = 0;
                $occurrence = 0;
                $all_pos = $item->po_group->purchase_orders()->where('supplier_id', $item->supplier_id)->get();

                foreach ($all_pos as $po) {

                    $same_items = $po->PurchaseOrderDetail()->where('product_id', $item->product_id)->get();
                    $all_items_total_buying_price += $same_items !== null ? $same_items->sum('pod_total_unit_price') : 0;

                    $occurrence += $same_items->count();
                }
                if ($occurrence != 0) {
                    $final_unit_price = $all_items_total_buying_price;
                } else {
                    $occurrence = 0;
                }
                if ($item->supplier_id != null) {
                    $currency_code = $item->get_supplier->getCurrency->currency_code;
                } else {
                    $currency_code = $item->product->def_or_last_supplier->getCurrency->currency_code;
                }
                return $final_unit_price != null ? number_format($final_unit_price, 2, '.', ',') . ' ' . $currency_code : '';
            });
        } else {
            $dt->addColumn('t_buying_price_eur', function ($item) {
                return '--';
            });
        }

        if (!in_array('24', $not_visible_arr)) {
            $dt->addColumn('currency_conversion_rate', function ($item) {
                // $currency_conversion_rate = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0 ;
                // if($currency_conversion_rate != 0)
                // {
                //     return number_format(1/$currency_conversion_rate,2,'.',',');
                // }
                // else
                // {
                //     return $currency_conversion_rate;
                // }

                if ($item->occurrence > 1) {
                    $ccr = $item->po_group->purchase_orders()->where('supplier_id', $item->supplier_id)->pluck('id')->toArray();

                    $total_occr = $item->averageCurrency($ccr, $item->product_id, 'currency_conversion_rate');

                    $currency_conversion_rate = $total_occr / $item->occurrence;
                } else {
                    $currency_conversion_rate = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0;
                    if ($currency_conversion_rate != 0) {
                        $currency_conversion_rate = (1 / $currency_conversion_rate);
                    } else {
                        $currency_conversion_rate = $currency_conversion_rate;
                    }
                }

                $html_string = '<input type="number"  name="currency_conversion_rate" data-id="' . $item->id . '" data-fieldvalue="' . number_format($currency_conversion_rate, 2, '.', '') . '" class="fieldFocus" value="' . number_format($currency_conversion_rate, 2, '.', '') . '" style="width:100%">';
                return $html_string;
            });
        } else {
            $dt->addColumn('currency_conversion_rate', function ($item) {
                return '--';
            });
        }

        if (!in_array('25', $not_visible_arr)) {
            $dt->addColumn('buying_price_in_thb', function ($item) {
                return $item->unit_price_in_thb != null ? number_format($item->unit_price_in_thb, 3, '.', ',') : '';
            });
        } else {
            $dt->addColumn('buying_price_in_thb', function ($item) {
                return '--';
            });
        }

        if (!in_array('26', $not_visible_arr)) {
            $dt->addColumn('total_buying_price', function ($item) {
                if ($item->occurrence > 1) {
                    $ccr = $item->po_group->purchase_orders()->where('supplier_id', $item->supplier_id)->pluck('id')->toArray();

                    $total_occr = $item->averageCurrency($ccr, $item->product_id, 'total_buying_price_in_thb');
                    return number_format($total_occr, 3, '.', ',');
                } else {
                    return $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb, 2, '.', ',') : '';
                }
                return $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb, 3, '.', ',') : '';
            });
        } else {
            $dt->addColumn('total_buying_price', function ($item) {
                return '--';
            });
        }

        if (!in_array('28', $not_visible_arr)) {
            $dt->addColumn('import_tax_book', function ($item) {
                return $item->import_tax_book != null ? $item->import_tax_book . '%' : 0;
            });
        } else {
            $dt->addColumn('import_tax_book', function ($item) {
                return '--';
            });
        }
        if (!in_array('27', $not_visible_arr)) {
            $dt->addColumn('vat_act', function ($item) {
                return $item->pogpd_vat_actual != null ? $item->pogpd_vat_actual . '%' : 0;
            });
        } else {
            $dt->addColumn('vat_act', function ($item) {
                return '--';
            });
        }

        if (!in_array('31', $not_visible_arr)) {
            $dt->addColumn('vat_percent_tax', function ($item) use ($all_pgpd) {
                $po_group_vat_actual_percent = $item->po_group->po_group_vat_actual_percent;
                $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;

                $import_tax = $item->pogpd_vat_actual;
                $total_price = $item->total_unit_price_in_thb;
                $book_tax = (($import_tax / 100) * $total_price);

                $check_book_tax = (($po_group_vat_actual_percent * $total_buying_price_in_thb) / 100);

                if ($check_book_tax != 0) {
                    return number_format($book_tax, 2, '.', ',');
                } else {
                    $book_tax = (1 / $all_pgpd) * $item->total_unit_price_in_thb;
                    return number_format($book_tax, 2, '.', ',');
                }
            });
        } else {
            $dt->addColumn('vat_percent_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('32', $not_visible_arr)) {
            $dt->addColumn('vat_weighted_percent', function ($item) use ($all_pgpd, $final_vat_actual_percent) {
                if ($item->vat_weighted_percent == null) {
                    $group_tax = $item->po_group->vat_actual_tax;
                    $find_item_tax_value = $item->pogpd_vat_actual / 100 * $item->total_unit_price_in_thb;
                    if ($final_vat_actual_percent != 0 && $group_tax != 0) {
                        $find_item_tax = $find_item_tax_value / $final_vat_actual_percent;
                        $cost = $find_item_tax * $group_tax;
                        if ($group_tax != 0) {
                            return number_format(($cost / $group_tax) * 100, 4, '.', ',') . " %";
                        } else {
                            return "0" . " %";
                        }
                    } else {
                        $po_group_vat_actual = $item->po_group->po_group_vat_actual;
                        $po_group_vat_percent = $item->po_group->po_group_vat_actual_percent;
                        $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;

                        $vat_actual = $item->pogpd_vat_actual;
                        $total_price = $item->total_unit_price_in_thb;
                        $vat_tax = (($vat_actual / 100) * $total_price);


                        $check_book_tax = (($po_group_vat_percent * $total_buying_price_in_thb) / 100);


                        if ($check_book_tax != 0) {
                            $vat_tax = round($vat_tax, 2);
                        } else {
                            $vat_tax = (1 / $all_pgpd) * $item->total_unit_price_in_thb;
                            $vat_tax = round($vat_tax, 2);
                        }
                        if ($po_group_vat_actual != 0) {
                            $vat_weighted = (($vat_tax / $po_group_vat_actual) * 100);
                        } else {
                            $vat_weighted = 0;
                        }

                        return number_format($vat_weighted, 4, '.', ',') . '%';
                    }
                } else {
                    return number_format($item->vat_weighted_percent, 4, '.', ',') . '%';
                }
            });
        } else {
            $dt->addColumn('vat_weighted_percent', function ($item) {
                return '--';
            });
        }

        if (!in_array('33', $not_visible_arr)) {
            $dt->addColumn('vat_act_tax', function ($item) use ($all_pgpd, $final_vat_actual_percent) {
                if ($item->pogpd_vat_actual_price == NULL) {
                    $group_tax = $item->po_group->vat_actual_tax;
                    $find_item_tax_value = $item->pogpd_vat_actual / 100 * $item->total_unit_price_in_thb;
                    if ($final_vat_actual_percent != 0) {
                        $find_item_tax = $find_item_tax_value / $final_vat_actual_percent;
                        if ($item->quantity_inv == 0) {
                            return 0;
                        } else {
                            return number_format(round($find_item_tax * $group_tax, 2) / $item->quantity_inv, 2, '.', ',');
                        }
                    } else {
                        $po_group_vat_actual = $item->po_group->po_group_vat_actual;
                        $po_group_vat_actual_percent = $item->po_group->po_group_vat_actual_percent;
                        $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;

                        $pogpd_vat_actual = $item->pogpd_vat_actual;
                        $total_price = $item->total_unit_price_in_thb;
                        $vat_tax = (($pogpd_vat_actual / 100) * $total_price);


                        $check_book_tax = (($po_group_vat_actual_percent * $total_buying_price_in_thb) / 100);


                        if ($check_book_tax != 0) {
                            $vat_tax = round($vat_tax, 2);
                        } else {
                            $vat_tax = (1 / $all_pgpd) * $item->total_unit_price_in_thb;
                            $vat_tax = round($vat_tax, 2);
                        }
                        if ($po_group_vat_actual != 0) {
                            $weighted = ($vat_tax / $po_group_vat_actual);
                        } else {
                            $weighted = 0;
                        }
                        $vat_actual_tax = $item->po_group->vat_actual_tax;
                        return number_format(($weighted * $vat_actual_tax), 2, '.', ',');
                    }
                } else {
                    return number_format($item->pogpd_vat_actual_price, 2, '.', ',');
                }
            });
        } else {
            $dt->addColumn('vat_act_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('34', $not_visible_arr)) {
            $dt->addColumn('vat_act_tax_percent', function ($item) use ($final_vat_actual_percent) {
                if ($item->pogpd_vat_actual_percent_val == NULL) {
                    $group_tax = $item->po_group->vat_actual_tax;
                    $find_item_tax_value = $item->pogpd_vat_actual / 100 * $item->total_unit_price_in_thb;
                    if ($final_vat_actual_percent != 0) {
                        $find_item_tax = $find_item_tax_value / $final_vat_actual_percent;
                        if ($item->quantity_inv == 0) {
                            return 0;
                        } else {
                            $actual_tax_per_quantity = number_format($find_item_tax * $group_tax, 2, '.', '') / $item->quantity_inv;
                            if ($item->unit_price_in_thb != 0) {
                                return number_format(($actual_tax_per_quantity / $item->unit_price_in_thb) * 100, 2, '.', ',') . ' %';
                            } else {
                                return 0;
                            }
                        }
                    } else {
                        return 0;
                    }
                } else {
                    return number_format($item->pogpd_vat_actual_percent_val, 2, '.', ',') . '%';
                }
            });
        } else {
            $dt->addColumn('vat_act_tax_percent', function ($item) {
                return '--';
            });
        }

        if (!in_array('29', $not_visible_arr)) {
            $dt->addColumn('freight', function ($item) {
                $freight = $item->freight;
                return number_format($freight, 2, '.', ',');
            });
        } else {
            $dt->addColumn('freight', function ($item) {
                return '--';
            });
        }

        if (!in_array('30', $not_visible_arr)) {
            $dt->addColumn('landing', function ($item) {
                $landing = $item->landing;
                return number_format($landing, 2, '.', ',');
            });
        } else {
            $dt->addColumn('landing', function ($item) {
                return '--';
            });
        }

        if (!in_array('35', $not_visible_arr)) {
            $dt->addColumn('book_tax', function ($item) use ($all_pgpd, $final_book_percent) {
                if ($item->occurrence > 1) {
                    $all_ids = PurchaseOrder::where('po_group_id', $item->po_group_id)->where('supplier_id', $item->supplier_id)->pluck('id');
                    $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $item->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->sum('pod_import_tax_book_price');

                    return number_format($all_record, 2, '.', ',');
                }
                $po_group_import_tax_book = $item->po_group->total_import_tax_book_percent;
                $total_buying_price_in_thb = $item->po_group->total_buying_price_in_thb;

                $import_tax = $item->import_tax_book;
                $total_price = $item->total_unit_price_in_thb;
                $book_tax = (($import_tax / 100) * $total_price);

                $check_book_tax = (($po_group_import_tax_book * $total_buying_price_in_thb) / 100);

                if ($check_book_tax != 0) {
                    return number_format($book_tax, 2, '.', ',');
                } else {
                    $book_tax = (1 / $all_pgpd) * $item->total_unit_price_in_thb;
                    return number_format($book_tax, 2, '.', ',');
                }
            });
        } else {
            $dt->addColumn('book_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('36', $not_visible_arr)) {
            $dt->addColumn('weighted', function ($item) use ($all_pgpd, $final_book_percent) {
                if ($item->weighted_percent == null) {
                    $group_tax = $item->po_group->tax;
                    $find_item_tax_value = $item->import_tax_book / 100 * $item->total_unit_price_in_thb;
                    if ($final_book_percent != 0) {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;
                        $cost = $find_item_tax * $group_tax;

                        if ($group_tax != 0) {
                            return number_format(($cost / $group_tax) * 100, 4, '.', ',') . " %";
                        } else {
                            return "0" . " %";
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
                            $book_tax = (1 / $all_pgpd) * $item->total_unit_price_in_thb;
                            $book_tax = round($book_tax, 2);
                        }

                        if ($total_import_tax != 0) {
                            $weighted = (($book_tax / $total_import_tax) * 100);
                        } else {
                            $weighted = 0;
                        }

                        return number_format($weighted, 2, '.', ',') . '%';
                    }
                } else {
                    return number_format($item->weighted_percent, 4, '.', ',') . '%';
                }
            });
        } else {
            $dt->addColumn('weighted', function ($item) {
                return '--';
            });
        }

        if (!in_array('37', $not_visible_arr)) {
            $dt->addColumn('actual_tax', function ($item) use ($all_pgpd, $final_book_percent) {
                if ($item->actual_tax_price == NULL) {
                    $group_tax = $item->po_group->tax;
                    $find_item_tax_value = $item->import_tax_book / 100 * $item->total_unit_price_in_thb;
                    if ($final_book_percent != 0 && $group_tax != 0) {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;
                        if ($item->quantity_inv == 0) {
                            return 0;
                        } else {
                            return number_format(round($find_item_tax * $group_tax, 2) / $item->quantity_inv, 2, '.', ',');
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
                            $book_tax = (1 / $all_pgpd) * $item->total_unit_price_in_thb;
                            $book_tax = round($book_tax, 2);
                        }

                        if ($total_import_tax != 0) {
                            $weighted = (($book_tax / $total_import_tax));
                        } else {
                            $weighted = 0;
                        }

                        $tax = $item->po_group->tax;
                        return number_format(($weighted * $tax), 2, '.', ',');
                    }
                } else {
                    return number_format($item->actual_tax_price, 2, '.', ',');
                }
            });
        } else {
            $dt->addColumn('actual_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('38', $not_visible_arr)) {
            $dt->addColumn('actual_tax_percent', function ($item) use ($final_book_percent) {
                // $actual_tax_percent = $item->actual_tax_percent;
                // return number_format($actual_tax_percent,2,'.',',').'%';
                if ($item->actual_tax_percent == NULL) {
                    $group_tax = $item->po_group->tax;
                    $find_item_tax_value = $item->import_tax_book / 100 * $item->total_unit_price_in_thb;
                    if ($final_book_percent != 0) {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;
                        if ($item->quantity_inv == 0) {
                            return 0;
                        } else {
                            $actual_tax_per_quantity = number_format($find_item_tax * $group_tax, 2, '.', '') / $item->quantity_inv;

                            if ($item->unit_price_in_thb != 0) {
                                return number_format(($actual_tax_per_quantity / $item->unit_price_in_thb) * 100, 2, '.', ',') . ' %';
                            } else {
                                return "0" . ' %';
                            }
                        }
                    } else {
                        return 0;
                    }
                } else {
                    return number_format($item->actual_tax_percent, 2, '.', ',') . '%';
                }
            });
        } else {
            $dt->addColumn('actual_tax_percent', function ($item) {
                return '--';
            });
        }
        if (!in_array('11', $not_visible_arr)) {
            $dt->addColumn('kg', function ($item) {
                return $item->product->units != null ? $item->product->units->title : '';
            });
        } else {
            $dt->addColumn('kg', function ($item) {
                return '--';
            });
        }

        if (!in_array('12', $not_visible_arr)) {
            $dt->addColumn('qty_ordered', function ($item) {
                return round($item->quantity_ordered, 3);
            });
        } else {
            $dt->addColumn('qty_ordered', function ($item) {
                return '--';
            });
        }

        if (!in_array('13', $not_visible_arr)) {
            $dt->addColumn('qty', function ($item) {
                return round($item->quantity_inv, 3);
            });
        } else {
            $dt->addColumn('qty', function ($item) {
                return '--';
            });
        }

        if (!in_array('15', $not_visible_arr)) {
            $dt->addColumn('pod_unit_gross_weight', function ($item) {
                if ($item->unit_gross_weight == NULL) {
                    $total_gross_weight = $item->total_gross_weight != null ? $item->total_gross_weight : 0;
                    $qty_inv = $item->quantity_inv != null ? $item->quantity_inv : 0;

                    if ($qty_inv != 0) {
                        $u_g_weight = ($total_gross_weight / $qty_inv);
                    } else {
                        $u_g_weight = 0;
                    }

                    $html_string = '<input type="number"  name="unit_gross_weight" data-id="' . $item->id . '" data-fieldvalue="' . $u_g_weight . '" class="fieldFocus" value="' . number_format($u_g_weight, 3, '.', '') . '" style="width:100%">';
                    return $html_string;
                } else {
                    $unit_gross_weight = $item->unit_gross_weight != null ? $item->unit_gross_weight : 0;

                    $html_string = '<input type="number"  name="unit_gross_weight" data-id="' . $item->id . '" data-fieldvalue="' . $unit_gross_weight . '" class="fieldFocus" value="' . number_format($unit_gross_weight, 3, '.', '') . '" style="width:100%">';
                    return $html_string;
                }
            });
        } else {
            $dt->addColumn('pod_unit_gross_weight', function ($item) {
                return '--';
            });
        }

        if (!in_array('16', $not_visible_arr)) {
            $dt->addColumn('pod_total_gross_weight', function ($item) use ($latest) {
                if ($latest->id == $item->po_group_id) {
                    $total_gross_weight = $item->total_gross_weight != null ? $item->total_gross_weight : 0;

                    $html_string = '<input type="number"  name="total_gross_weight" data-id="' . $item->id . '" data-fieldvalue="' . $total_gross_weight . '" class="fieldFocus" value="' . number_format($total_gross_weight, 2, '.', '') . '" style="width:100%">';
                    return $html_string;
                } else {
                    $total_gross_weight = $item->total_gross_weight != null ? number_format($item->total_gross_weight, 2, '.', ',') : 0;
                    return $total_gross_weight;
                }
            });
        } else {
            $dt->addColumn('pod_total_gross_weight', function ($item) {
                return '--';
            });
        }

        if (!in_array('17', $not_visible_arr)) {
            $dt->addColumn('pod_unit_extra_cost', function ($item) {
                $unit_extra_cost = $item->unit_extra_cost != null ? $item->unit_extra_cost : 0;
                $occurrence = $item->occurrence;
                if ($occurrence > 1) {
                    $html_string = number_format($unit_extra_cost, 2, '.', ',');
                } else {
                    $html_string = '<input type="number" name="unit_extra_cost" data-id="' . $item->id . '" data-fieldvalue="' . $unit_extra_cost . '" class="fieldFocus" value="' . number_format($unit_extra_cost, 2, '.', '') . '" style="width:100%">';
                }
                return $html_string;
            });
        } else {
            $dt->addColumn('pod_unit_extra_cost', function ($item) {
                return '--';
            });
        }

        if (!in_array('18', $not_visible_arr)) {
            $dt->addColumn('total_extra_costt', function ($item) {
                $total_extra_cost = $item->total_extra_cost != null ? number_format($item->total_extra_cost, 2, '.', '') : 0;
                $occurrence = $item->occurrence;
                if ($occurrence > 1) {
                    $html_string = number_format($total_extra_cost, 2, '.', ',');
                } else {
                    $html_string = '<input type="number"  name="total_extra_cost" data-id="' . $item->id . '" data-fieldvalue="' . $total_extra_cost . '" class="fieldFocus" value="' . number_format($total_extra_cost, 2, '.', '') . '" style="width:100%">';
                }
                return $html_string;
                // return $total_extra_cost;
            });
        } else {
            $dt->addColumn('total_extra_costt', function ($item) {
                return '--';
            });
        }

        if (!in_array('19', $not_visible_arr)) {
            $dt->addColumn('pod_unit_extra_tax', function ($item) {
                $unit_extra_tax = $item->unit_extra_tax != null ? $item->unit_extra_tax : 0;
                $occurrence = $item->occurrence;
                if ($occurrence > 1) {
                    $html_string = number_format($unit_extra_tax, 2, '.', ',');
                } else {
                    $html_string = '<input type="number" name="unit_extra_tax" data-id="' . $item->id . '" data-fieldvalue="' . $unit_extra_tax . '" class="fieldFocus" value="' . number_format($unit_extra_tax, 2, '.', '') . '" style="width:100%">';
                }
                return $html_string;
            });
        } else {
            $dt->addColumn('pod_unit_extra_tax', function ($item) {
                return '--';
            });
        }

        if (!in_array('20', $not_visible_arr)) {
            $dt->addColumn('total_extra_taxx', function ($item) {
                $total_extra_tax = $item->total_extra_tax != null ? number_format($item->total_extra_tax, 2, '.', '') : 0;
                $occurrence = $item->occurrence;
                if ($occurrence > 1) {
                    $html_string = number_format($total_extra_tax, 2, '.', ',');
                } else {
                    $html_string = '<input type="number"  name="total_extra_tax" data-id="' . $item->id . '" data-fieldvalue="' . $total_extra_tax . '" class="fieldFocus" value="' . number_format($total_extra_tax, 2, '.', '') . '" style="width:100%">';
                }
                return $html_string;
                // return $total_extra_tax;
            });
        } else {
            $dt->addColumn('total_extra_taxx', function ($item) {
                return '--';
            });
        }

        if (!in_array('39', $not_visible_arr)) {
            $dt->addColumn('custom_line_number', function ($item) {
                $html_string = '<input type="text"  name="custom_line_number" data-id="' . $item->id . '" data-fieldvalue="' . @$item->custom_line_number . '" class="fieldFocus" value="' . @$item->custom_line_number . '" style="width:100%">';
                return $html_string;
            });
        } else {
            $dt->addColumn('custom_line_number', function ($item) {
                return '--';
            });
        }
        if (!in_array('14', $not_visible_arr)) {
            $dt->addColumn('product_notes', function ($item) {
                return  $html_string = $item->product->product_notes != NULL ? $item->product->product_notes : "N.A";
            });
        } else {
            $dt->addColumn('product_notes', function ($item) {
                return '--';
            });
        }
        $dt->rawColumns(['po_number', 'order_no', 'supplier', 'reference_number', 'desc', 'currency_conversion_rate', 'kg', 'qty', 'pod_total_gross_weight', 'total_extra_costt', 'prod_reference_number', 'customer', 'custom_line_number', 'total_extra_taxx', 'product_notes', 'pod_unit_extra_tax', 'pod_unit_extra_cost', 'pod_unit_gross_weight']);
        return $dt->make(true);
    }

    public function getPoGroupEveryProductDetails(Request $request)
    {
        // dd($request->all());
        $all_ids = PurchaseOrder::where('po_group_id', $request->group_id)->where('supplier_id', $request->supplier_id)->pluck('id');
        $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $request->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer');
        $po_group_product_detail = PoGroupProductDetail::where('status', 1)->where('po_group_id', $request->group_id)->where('supplier_id', $request->supplier_id)->where('product_id', $request->product_id)->first();

        $all_pgpd = PoGroupProductDetail::where('status', 1)->where('po_group_id', $request->group_id)->count();

        $final_book_percent = 0;

        return Datatables::of($all_record)

            ->addColumn('po_no', function ($item) {
                if ($item->PurchaseOrder->ref_id !== null) {
                    $html_string = '<a target="_blank" href="' . route('get-purchase-order-detail', ['id' => $item->PurchaseOrder->id]) . '" title="View Detail"><b>' . $item->PurchaseOrder->ref_id . '</b></a>';
                    return $html_string;
                } else {
                    return "--";
                }
            })

            ->addColumn('customer', function ($item) {
                $order = $item->getOrder;
                // dd($order);
                if ($order !== null) {

                    $html_string = '<a target="_blank" href="' . url('sales/get-customer-detail/' . $order->customer_id) . '"><b>' . $order->customer->reference_name . '</b></a>';
                    return $html_string;
                } else {
                    return "N.A";
                }
            })

            ->addColumn('order_warehouse', function ($item) {
                return $item->getOrder !== null ? $item->getOrder->user->get_warehouse->warehouse_title : "--";
            })

            ->addColumn('order_no', function ($item) {
                $order = Order::find(@$item->order_id);
                if ($order !== null) {
                    $ret = $order->get_order_number_and_link($order);
                    $ref_no = $ret[0];
                    $link = $ret[1];

                    $html_string = '<a target="_blank" href="' . route($link, ['id' => $order->id]) . '" title="View Detail"><b>' . $ref_no . '</b></a>';
                    return $html_string;
                } else {
                    return "N.A";
                }
            })

            ->addColumn('supplier_ref_name', function ($item) {
                if ($item->PurchaseOrder->supplier_id !== NULL) {
                    $sup_name = Supplier::select('id', 'reference_name')->where('id', $item->PurchaseOrder->supplier_id)->first();
                    return  $html_string = '<a target="_blank" href="' . url('get-supplier-detail/' . $sup_name->id) . '"><b>' . $sup_name->reference_name . '</b></a>';
                } else {
                    $sup_name = Warehouse::where('id', $item->PurchaseOrder->from_warehouse_id)->first();
                    return  $html_string = $sup_name->warehouse_title;
                }
            })
            ->addColumn('supplier_description', function ($item) {
                if ($item->PurchaseOrder->supplier_id !== NULL) {
                    $supplier_product = $item->product->supplier_products->where('supplier_id',$item->supplier_id)->first();
                    if ($supplier_product) {
                        $supplier_description = $supplier_product->supplier_description;
                        return $supplier_description != null ? $supplier_description : '--';
                    }
                    return '--';
                }
            })

            ->addColumn('supplier_ref_no', function ($item) {
                if ($item->PurchaseOrder->supplier_id !== NULL) {
                    $sup_name = SupplierProducts::select('product_supplier_reference_no')->where('supplier_id', $item->PurchaseOrder->supplier_id)->where('product_id', $item->product_id)->first();
                    return $sup_name->product_supplier_reference_no != null ? $sup_name->product_supplier_reference_no : "--";
                } else {
                    return "N.A";
                }
            })

            ->addColumn('product_ref_no', function ($item) {
                return  $html_string = '<a target="_blank" href="' . url('get-product-detail/' . $item->product->id) . '"><b>' . $item->product->refrence_code . '</b></a>';
            })

            ->addColumn('brand', function ($item) {
                return $item->product->brand != null ? $item->product->brand : '--';
            })

            ->addColumn('short_desc', function ($item) {
                return $item->product->short_desc != null ? $item->product->short_desc : '';
            })

            ->addColumn('product_notes', function ($item) {
                return $item->product->product_notes != null ? $item->product->product_notes : 'N.A';
            })

            ->addColumn('type', function ($item) {
                return $item->product->productType != null ? $item->product->productType->title : '';
            })

            ->addColumn('buying_unit', function ($item) {
                return $item->product->units->title != null ? $item->product->units->title : '';
            })

            ->addColumn('quantity_ordered', function ($item) {
                // if($item->order_product_id != null)
                // {
                //     $sup_name = OrderProduct::select('quantity')->where('id',$item->order_product_id)->first();
                //     return $sup_name->quantity;
                // }
                // else
                // {
                //     return '--';
                // }

                $supplier_packaging = $item->supplier_packaging !== null ? $item->supplier_packaging : 'N.A';
                $decimals = $item->product != null ? ($item->product->units != null ? $item->product->units->decimal_places : 0) : 0;
                if ($item->product_id != null) {
                    if ($item->PurchaseOrder->status == 12) {
                        $html_string = '
                    <span class="m-l-15 inputDoubleClickQuantity desired_qty desired_qty_span_' . $item->id . ' mr-2" data-id id="desired_qty"  data-fieldvalue="' . number_format(@$item->desired_qty, $decimals, '.', ',') . '">';
                        $html_string .= $item->desired_qty !== null ? number_format(@$item->desired_qty, $decimals, '.', ',') : "--";
                        $html_string .= '</span>';
                        $html_string .= '<input type="number" style="width:100%;" name="desired_qty" class="unitfieldFocus d-none form-control input-height desired_qty_field_' . $item->id . '" min="0" value="' . number_format(@$item->desired_qty, $decimals, '.', ',') . '">';
                        $html_string .= $supplier_packaging;
                        return $html_string;
                    } else {
                        return $item->desired_qty !== null ? number_format(@$item->desired_qty, $decimals, '.', ',') . '<span class="ml-2">' . $supplier_packaging . '</span>' : "--" . '<span class="ml-2">' . $supplier_packaging . '</span>';
                    }
                } else {
                    return "N.A";
                }
            })

            ->addColumn('original_qty', function ($item) {
                if ($item->order_product_id != null) {
                    $selling_unit = ($item->order_product_id != null ? $item->order_product->product->sellingUnits->title : "N.A");
                    $html_string = '<span class="m-l-15 customer_qty">';
                    $html_string .= ($item->order_product_id != null ? ($item->order_product->quantity != null ? $item->order_product->quantity : "--") . ' ' . $selling_unit : "--");
                    $html_string .= '</span>';
                    return $html_string;
                } else {
                    return "Stock";
                }
            })
            ->addColumn('quantity_inv', function ($item) {
                return $item->quantity;
            })

            ->addColumn('pod_unit_gross_weight', function ($item) {
                return $item->pod_gross_weight != null ? number_format($item->pod_gross_weight, 3, '.', ',') : '';
            })

            ->addColumn('pod_total_gross_weight', function ($item) {
                return $item->pod_total_gross_weight != null ? number_format($item->pod_total_gross_weight, 3, '.', ',') : '';
            })

            ->addColumn('unit_extra_cost', function ($item) use ($po_group_product_detail) {
                $unit_extra_cost = $item->unit_extra_cost != null ? $item->unit_extra_cost : 0;
                $html_string = '<input type="number" name="unit_extra_cost" data-id="' . $item->id . '" id="pod_unit_extra_cost_' . $item->id . '" data-pod="yes" data-pogid="' . $po_group_product_detail->id . '" data-fieldvalue="' . $unit_extra_cost . '" class="fieldFocusDetail" value="' . number_format($unit_extra_cost, 2, '.', '') . '" style="width:100%">';
                return $html_string;
            })
            ->addColumn('total_extra_cost', function ($item) use ($po_group_product_detail) {
                $total_extra_cost = $item->total_extra_cost != null ? $item->total_extra_cost : 0;
                $html_string = '<input type="number" name="total_extra_cost" data-id="' . $item->id . '" id="pod_total_extra_cost_' . $item->id . '" data-pod="yes" data-pogid="' . $po_group_product_detail->id . '" data-fieldvalue="' . $total_extra_cost . '" class="fieldFocusDetail" value="' . number_format($total_extra_cost, 2, '.', '') . '" style="width:100%">';
                return  $html_string;
            })
            ->addColumn('unit_extra_tax', function ($item) use ($po_group_product_detail) {
                $unit_extra_tax = $item->unit_extra_tax != null ? $item->unit_extra_tax : 0;
                $html_string = '<input type="number" name="unit_extra_tax" data-id="' . $item->id . '" id="pod_unit_extra_tax_' . $item->id . '" data-pod="yes" data-pogid="' . $po_group_product_detail->id . '" data-fieldvalue="' . $unit_extra_tax . '" class="fieldFocusDetail" value="' . number_format($unit_extra_tax, 2, '.', '') . '" style="width:100%">';
                return  $html_string;
            })
            ->addColumn('total_extra_tax', function ($item) use ($po_group_product_detail) {
                $total_extra_tax = $item->total_extra_tax != null ? $item->total_extra_tax : 0;
                $html_string = '<input type="number" name="total_extra_tax" data-id="' . $item->id . '" id="pod_total_extra_tax_' . $item->id . '" data-pod="yes" data-pogid="' . $po_group_product_detail->id . '" data-fieldvalue="' . $total_extra_tax . '" class="fieldFocusDetail" value="' . number_format($total_extra_tax, 2, '.', '') . '" style="width:100%">';
                return  $html_string;
            })

            ->addColumn('buying_price', function ($item) {
                return $item->pod_unit_price != null ? number_format($item->pod_unit_price_with_vat, 3, '.', ',') : '';
            })
            ->addColumn('buying_price_wo_vat', function ($item) use ($po_group_product_detail) {
                // dd($po_group_product_detail->po_group);
                $ref_id = $po_group_product_detail->po_group->ref_id;
                    $old_value = $po_group_product_detail->po_group->po_group_history($ref_id, 'Unit Price', $item->pod_unit_price)->first();
                    // dd($old_value);
                    if($old_value) {
                        if($item->pod_unit_price != null) {
                            return '<span style="color:red"> '.number_format($item->pod_unit_price, 3, '.', ',') .'</span>'  . ' / ' . $old_value['old_value'];
                        } else {
                            return '';
                        }
                    } else {
                        return $item->pod_unit_price != null ? number_format($item->pod_unit_price, 3, '.', ',') : '';
                    }

            })

            ->addColumn('discount', function ($item) {
                return $item->discount != null ? $item->discount . ' %' : '';
            })

            ->addColumn('total_buying_price_o', function ($item) {
                return $item->pod_total_unit_price != null ? number_format($item->pod_total_unit_price_with_vat, 3, '.', ',') : '';
            })
            ->addColumn('total_buying_price_wo_vat', function ($item) {
                return $item->pod_total_unit_price != null ? number_format($item->pod_total_unit_price, 3, '.', ',') : '';
            })

            ->addColumn('currency_conversion_rate', function ($item) {
                $currency_conversion_rate = $item->currency_conversion_rate != null ? $item->currency_conversion_rate : 0;
                if ($currency_conversion_rate != 0) {
                    return number_format(1 / $currency_conversion_rate, 2, '.', ',');
                } else {
                    return $currency_conversion_rate;
                }
            })

            ->addColumn('unit_price_in_thb', function ($item) {
                return $item->unit_price_with_vat_in_thb != null ? number_format($item->unit_price_with_vat_in_thb, 3, '.', ',') : '';
            })

            ->addColumn('buying_price_in_thb_wo_vat', function ($item) {
                return $item->unit_price_in_thb != null ? number_format($item->unit_price_in_thb, 3, '.', ',') : '';
            })



            ->addColumn('total_buying_price', function ($item) {
                return $item->total_unit_price_with_vat_in_thb != null ? number_format($item->total_unit_price_with_vat_in_thb, 3, '.', ',') : '';
            })
            ->addColumn('total_buying_price_in_thb_wo_vat', function ($item) {
                return $item->total_unit_price_in_thb != null ? number_format($item->total_unit_price_in_thb, 3, '.', ',') : '';
            })

            ->addColumn('import_tax_book', function ($item) {
                $html_string =  number_format($item->pod_import_tax_book, 2, '.', '') . '%';
                return $html_string;
            })

            ->addColumn('vat_act', function ($item) {
                $html_string =  number_format($item->pod_vat_actual, 2, '.', '') . '%';
                return $html_string;
            })

            ->addColumn('vat_percent_tax', function ($item) {
                return number_format($item->pod_vat_actual_total_price_in_thb, 2, '.', ',');
            })

            ->addColumn('vat_weighted_percent', function ($item) {
                return  "--";
            })

            ->addColumn('vat_act_tax', function ($item) {
                $html_string =  number_format($item->pod_vat_actual_price_in_thb, 2, '.', '');
                return $html_string;
            })
            ->addColumn('vat_act_tax_percent', function ($item) {
                $html_string =  number_format($item->pod_vat_actual, 2, '.', '') . '%';
                return $html_string;
            })

            ->addColumn('freight', function ($item) use ($po_group_product_detail) {
                return number_format($po_group_product_detail->freight, 2, '.', ',');
            })

            ->addColumn('total_freight', function ($item) use ($po_group_product_detail) {
                return number_format($po_group_product_detail->freight * $item->quantity, 2, '.', ',');
            })

            ->addColumn('landing', function ($item) use ($po_group_product_detail) {
                return number_format($po_group_product_detail->landing, 2, '.', ',');
            })

            ->addColumn('total_landing', function ($item) use ($po_group_product_detail) {
                return number_format($po_group_product_detail->landing * $item->quantity, 2, '.', ',');
            })

            ->addColumn('book_tax', function ($item) {
                return number_format($item->pod_import_tax_book_price, 2, '.', ',');
            })

            ->addColumn('weighted', function ($item) use ($all_pgpd, $final_book_percent, $po_group_product_detail) {
                if ($po_group_product_detail->weighted_percent == null) {
                    // $group_tax = $po_group_product_detail->po_group->tax;
                    // $find_item_tax_value = $po_group_product_detail->import_tax_book/100 * $po_group_product_detail->total_unit_price_in_thb;
                    // if($final_book_percent != 0 && $group_tax != 0)
                    // {
                    //     $find_item_tax = $find_item_tax_value / $final_book_percent;
                    //     $cost = $find_item_tax * $group_tax;
                    //     if($group_tax != 0)
                    //     {
                    //         return number_format(($cost/$group_tax)*100,4,'.',',')." %";
                    //     }
                    //     else
                    //     {
                    //         return "0"." %";
                    //     }
                    // }
                    // else
                    // {
                    //     $total_import_tax = $po_group_product_detail->po_group->po_group_import_tax_book;
                    //     $po_group_import_tax_book = $po_group_product_detail->po_group->total_import_tax_book_percent;
                    //     $total_buying_price_in_thb = $po_group_product_detail->po_group->total_buying_price_in_thb;

                    //     $import_tax = $po_group_product_detail->import_tax_book;
                    //     $total_price = $po_group_product_detail->total_unit_price_in_thb;
                    //     $book_tax = (($import_tax/100)*$total_price);


                    //     $check_book_tax = (($po_group_import_tax_book*$total_buying_price_in_thb)/100);


                    //     if($check_book_tax != 0)
                    //     {
                    //         $book_tax = round($book_tax,2);
                    //     }
                    //     else
                    //     {
                    //         $book_tax = (1/$all_pgpd)* $po_group_product_detail->total_unit_price_in_thb;
                    //         $book_tax = round($book_tax,2);
                    //     }
                    //     if($total_import_tax != 0)
                    //     {
                    //         $weighted = (($book_tax/$total_import_tax)*100);
                    //     }
                    //     else
                    //     {
                    //         $weighted = 0;
                    //     }

                    //     return number_format($weighted,4,'.',',').'%';
                    // }

                    return '--';
                } else {
                    return number_format($po_group_product_detail->weighted_percent, 4, '.', ',') . '%';
                }
            })

            ->addColumn('actual_tax', function ($item) use ($all_pgpd, $final_book_percent, $po_group_product_detail) {
                if ($po_group_product_detail->actual_tax_price == NULL) {
                    $group_tax = $po_group_product_detail->po_group->tax;
                    $find_item_tax_value = $po_group_product_detail->import_tax_book / 100 * $po_group_product_detail->total_unit_price_in_thb;
                    if ($final_book_percent != 0) {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;
                        if ($po_group_product_detail->quantity_inv == 0) {
                            return 0;
                        } else {
                            return number_format(round($find_item_tax * $group_tax, 2) / $po_group_product_detail->quantity_inv, 2, '.', ',');
                        }
                    } else {
                        $total_import_tax = $po_group_product_detail->po_group->po_group_import_tax_book;
                        $po_group_import_tax_book = $po_group_product_detail->po_group->total_import_tax_book_percent;
                        $total_buying_price_in_thb = $po_group_product_detail->po_group->total_buying_price_in_thb;

                        $import_tax = $po_group_product_detail->import_tax_book;
                        $total_price = $po_group_product_detail->total_unit_price_in_thb;
                        $book_tax = (($import_tax / 100) * $total_price);


                        $check_book_tax = (($po_group_import_tax_book * $total_buying_price_in_thb) / 100);


                        if ($check_book_tax != 0) {
                            $book_tax = round($book_tax, 2);
                        } else {
                            $book_tax = (1 / $all_pgpd) * $po_group_product_detail->total_unit_price_in_thb;
                            $book_tax = round($book_tax, 2);
                        }
                        if ($total_import_tax != 0) {
                            $weighted = ($book_tax / $total_import_tax);
                        } else {
                            $weighted = 0;
                        }
                        $tax = $po_group_product_detail->po_group->tax;
                        return number_format(($weighted * $tax), 2, '.', ',');
                    }
                } else {
                    return number_format($po_group_product_detail->actual_tax_price, 2, '.', ',');
                }
            })
            ->addColumn('actual_tax_percent', function ($item) use ($final_book_percent, $po_group_product_detail) {
                if ($po_group_product_detail->actual_tax_percent == NULL) {
                    $group_tax = $po_group_product_detail->po_group->tax;
                    $find_item_tax_value = $po_group_product_detail->import_tax_book / 100 * $po_group_product_detail->total_unit_price_in_thb;
                    if ($final_book_percent != 0) {
                        $find_item_tax = $find_item_tax_value / $final_book_percent;
                        if ($po_group_product_detail->quantity_inv == 0) {
                            return 0;
                        } else {
                            $actual_tax_per_quantity = number_format($find_item_tax * $group_tax, 2, '.', '') / $po_group_product_detail->quantity_inv;
                            if ($po_group_product_detail->unit_price_in_thb != 0) {
                                return number_format(($actual_tax_per_quantity / $po_group_product_detail->unit_price_in_thb) * 100, 2, '.', ',') . ' %';
                            } else {
                                return 0;
                            }
                        }
                    } else {
                        return 0;
                    }
                } else {
                    return number_format($po_group_product_detail->actual_tax_percent, 2, '.', ',') . '%';
                }
            })

            ->addColumn('empty_col', function ($item) {
                return '--';
            })
            ->addColumn('total_vat_act_tax', function ($item) {
                $html_string =  number_format($item->pod_vat_actual_total_price_in_thb, 2, '.', '');
                return $html_string;
            })
            ->addColumn('total_actual_tax', function ($item) {
                return '--';
            })
            ->addColumn('product_cost', function ($item) use ($po_group_product_detail) {
                return $po_group_product_detail->product_cost != null ? number_format($po_group_product_detail->product_cost, 2, '.', ',') : 0;
            })
            ->addColumn('total_product_cost', function ($item) use ($po_group_product_detail) {
                return $po_group_product_detail->product_cost != null ? number_format($po_group_product_detail->product_cost * $item->quantity, 2, '.', ',') : 0;
            })


            ->rawColumns(['po_no', 'order_no', 'supplier_ref_name', 'product_ref_no', 'total_buying_price_o', 'customer', 'discount', 'empty_col', 'pod_unit_gross_weight', 'product_cost', 'vat_act_tax_percent', 'vat_act_tax', 'vat_percent_tax', 'vat_act', 'vat_weighted_percent', 'total_extra_tax', 'unit_extra_tax', 'total_extra_cost', 'unit_extra_cost', 'total_freight', 'total_landing', 'original_qty', 'quantity_ordered', 'buying_price_wo_vat', 'total_product_cost'])
            ->make(true);
    }

    public function uploadBulkUploadInGroupDetail(Request $request)
    {
        // dd($request->all());
        $import = new BulkProductImportInGroupDetail($request->group_id, Auth::user()->id);
        Excel::import($import, $request->file('product_excel'));
        ImportFileHistory::insertRecordIntoDb(Auth::user()->id, 'Group Detail Page ', $request->file('product_excel'));

        if ($import) {
            $PoGroupProduct_history = new PoGroupProductHistory;
            $PoGroupProduct_history->user_id = Auth::user()->id;
            $PoGroupProduct_history->order_product_id = '';
            $PoGroupProduct_history->old_value = '';
            $PoGroupProduct_history->column_name = 'Bulk Uploaded';
            $PoGroupProduct_history->po_group_id = $request->group_id;
            $PoGroupProduct_history->new_value = '';
            $PoGroupProduct_history->save();
        }
    }

    public function exportImportingProductReceivingRecordImport(Request $request)
    {
        // dd($request->group_id);
        $status = ExportStatus::where('type', 'products_receiving_importings_bulk_job')->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->type = 'products_receiving_importings_bulk_job';
            $new->user_id = Auth::user()->id;
            $new->status = 1;
            $new->save();
            // ProductsReceivingImportJob::dispatch($request['group_id'],Auth::user()->id, $request->file('product_excel'));
            return response()->json(['status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['status' => 2, 'recursive' => false]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'products_receiving_importings_bulk_job')->update(['status' => 1, 'user_id' => Auth::user()->id, 'exception' => null]);
            // ProductsReceivingImportJob::dispatch($request['group_id'],Auth::user()->id, $request->file('product_excel'));
            return response()->json(['msg' => "File is getting ready!", 'status' => 1, 'recursive' => true]);
        }
    }
    public function getPoGroupProductHistory(Request $request)
    {
        // dd($request->order_id);


        $query = PoGroupProductHistory::with('user', 'product_info', 'Po_Group')->where('po_group_id', $request->order_id)->orderBy('id', 'DESC');
        // dd($query);
        // dd($query->order_product_id);
        return Datatables::of($query)
            ->addColumn('user_name', function ($item) {
                return @$item->user_id != null ? $item->user->name : '--';
            })

            ->addColumn('item', function ($item) {

                return @$item->order_product_id != null ? @$item->product_info->refrence_code : '--';
            })

            ->addColumn('order_no', function ($item) {
                $ref_no = $item->ref_id != null ? ' (Ref#' . $item->ref_id . ')' : ' ';
                return @$item->po_group_id != null ? $item->Po_Group->ref_id . $ref_no : '--';
            })

            ->addColumn('column_name', function ($item) {
                return @$item->column_name != null ? $item->column_name : '--';
            })

            ->addColumn('old_value', function ($item) {
                return @$item->old_value != null ? $item->old_value : '--';
            })

            ->addColumn('new_value', function ($item) {
                if (@$item->column_name == 'selling_unit') {
                    return @$item->new_value != null ? @$item->units->title : '--';
                } else if (@$item->column_name == 'Supply From') {
                    return @$item->new_value != null ? @$item->from_warehouse->warehouse_title : '--';
                } else {

                    return @$item->new_value != null ? $item->new_value : '--';
                }
            })
            ->addColumn('created_at', function ($item) {
                return @$item->created_at != null ? $item->created_at->format('d/m/Y H:i:s') : '--';
            })
            // ->setRowId(function ($item) {
            //   return $item->id;
            // })

            ->rawColumns(['user_name', 'item', 'column_name', 'old_value', 'new_value', 'created_at', 'order_no'])
            ->make(true);
    }

    public function editPoGroupProductDetailsEach(Request $request)
    {
        // dd($request->all());
        $po_detail = PoGroupProductDetail::where('status', 1)->where('id', $request->pogid)->first();
        $pod_detail = PurchaseOrderDetail::where('id', $request->pod_id)->first();

        foreach ($request->except('pod_id', 'pogid', 'old_value') as $key => $value) {
            $PoGroupProduct_history = new PoGroupProductHistory;
            $PoGroupProduct_history->user_id = Auth::user()->id;
            $PoGroupProduct_history->ref_id = $request->pod_id;
            $PoGroupProduct_history->order_product_id = $po_detail->product_id;
            $PoGroupProduct_history->old_value = $request->old_value;
            $PoGroupProduct_history->column_name = $key;
            $PoGroupProduct_history->po_group_id = $po_detail->po_group_id;
            $PoGroupProduct_history->new_value = $value;
            $PoGroupProduct_history->save();

            if ($value == '') {
                // $supp_detail->$key = null;
            } elseif ($key == 'total_extra_tax') {
                //to find unit extra tax from total extra tax
                if ($pod_detail->quantity == 0) {
                    $u_e_t = 0;
                } else {
                    $u_e_t = ($value / $pod_detail->quantity);
                }

                //to update unit extra tax column in po group product detail
                // $po_detail->unit_extra_tax -= $pod_detail->unit_extra_tax;
                // $po_detail->unit_extra_tax += $u_e_t;

                $pod_detail->$key = $value;
                $pod_detail->unit_extra_tax = $u_e_t;
                $pod_detail->save();

                $all_ids = PurchaseOrder::where('po_group_id', $po_detail->po_group_id)->where('supplier_id', $po_detail->supplier_id)->pluck('id');
                $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $po_detail->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->get();

                if ($all_record->count() > 0) {
                    //to update total extra tax column in po group product detail
                    $po_detail->total_extra_tax = $all_record->sum('total_extra_tax');
                    //to update unit extra tax column in po group product detail
                    $po_detail->unit_extra_tax = $all_record->sum('unit_extra_tax') / $all_record->count();
                    $po_detail->save();
                }

                if ($po_detail->po_group->is_review == 1) {
                    if ($po_detail) {
                        //to find exact value of the unit extra cost
                        $all_ids = PurchaseOrder::where('po_group_id', $po_detail->po_group_id)->where('supplier_id', $po_detail->supplier_id)->pluck('id');
                        $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $po_detail->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->get();

                        if ($all_record->count() > 0) {
                            //to update total extra tax column in po group product detail
                            $po_detail->total_extra_cost = $all_record->sum('total_extra_cost') / $all_record->count();
                            //to update unit extra tax column in po group product detail
                            $po_detail->unit_extra_cost = $all_record->sum('unit_extra_cost') / $all_record->count();
                            $po_detail->save();
                        }
                        $p_g_pd = $po_detail;
                        $supplier_product       = SupplierProducts::where('supplier_id', $p_g_pd->supplier_id)->where('product_id', $p_g_pd->product_id)->first();
                        $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1;
                        $buying_price_in_thb    = $p_g_pd->unit_price_in_thb;

                        $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                        $supplier_product->freight             = $p_g_pd->freight;
                        $supplier_product->landing             = $p_g_pd->landing;
                        $supplier_product->extra_cost          = $po_detail->unit_extra_cost;
                        $supplier_product->extra_tax           = $po_detail->unit_extra_tax;
                        $supplier_product->import_tax_actual   = $p_g_pd->actual_tax_percent;
                        $supplier_product->gross_weight        = $p_g_pd->total_gross_weight / $p_g_pd->quantity_inv;
                        $supplier_product->save();

                        $product = Product::find($p_g_pd->product_id);
                        // this is the price of after conversion for THB

                        $importTax              = $supplier_product->import_tax_actual;
                        $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                        $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);
                        $product->total_buy_unit_cost_price = $total_buying_price;
                        //this is supplier buying unit cost price
                        $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;
                        //this is selling price
                        $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                        // creating a history on a product detail page which shipment updated the product COGS
                        $product_history              = new ProductHistory;
                        $product_history->user_id     = Auth::user()->id;
                        $product_history->product_id  = $product->id;
                        $product_history->group_id    = @$p_g_pd->po_group->id;
                        $product_history->column_name = 'COGS Updated through closed shipment ' . @$p_g_pd->po_group->ref_id . ' by editing total extra tax';
                        $product_history->old_value   = @$product->selling_price;
                        $product_history->new_value   = @$total_selling_price;
                        $product_history->save();

                        $product->selling_price           = $total_selling_price;
                        $product->supplier_id             = $supplier_product->supplier_id;
                        $product->last_price_updated_date = Carbon::now();
                        $product->last_date_import        = Carbon::now();
                        $product->save();

                        $p_g_pd->product_cost = $total_selling_price;
                        $p_g_pd->save();

                        $stock_out = StockManagementOut::where('po_group_id', $p_g_pd->po_group_id)->where('product_id', $p_g_pd->product_id)->where('warehouse_id', $p_g_pd->to_warehouse_id)->update(['cost' => $p_g_pd->product_cost, 'cost_date' => Carbon::now()]);

                        $purchase_orders = PurchaseOrder::whereIn('id', PoGroupDetail::where('po_group_id', $po_detail->po_group->id)->pluck('purchase_order_id'))->get();
                        foreach ($purchase_orders as $PO) {
                            $purchase_order_details = PurchaseOrderDetail::where('po_id', $PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity', '!=', 0)->get();
                            foreach ($purchase_order_details as $p_o_d) {
                                $product_id = $p_o_d->product_id;
                                if ($p_o_d->order_product_id != null) {
                                    $product                           = Product::find($p_o_d->product_id);
                                    $p_o_d->order_product->actual_cost = $product != null ? $product->selling_price : 0;
                                    $p_o_d->order_product->save();
                                }
                            }
                        }
                    }
                }
            } elseif ($key == 'unit_extra_tax') {
                // $po_detail->$key = $value;
                // $po_detail->total_extra_tax = ($value * $po_detail->quantity_inv);

                //to find total extra tax unit total extra tax
                if ($pod_detail->quantity == 0) {
                    $t_e_t = 0;
                } else {
                    $t_e_t = ($value * $pod_detail->quantity);
                }

                //to update total extra tax column in po group product detail
                // $po_detail->total_extra_tax -= $pod_detail->total_extra_tax;
                // $po_detail->total_extra_tax += $t_e_t;

                //to update unit extra tax column in po group product detail
                // $po_detail->unit_extra_tax -= $pod_detail->unit_extra_tax;
                // $po_detail->unit_extra_tax += $value;

                $pod_detail->$key = $value;
                $pod_detail->total_extra_tax = $t_e_t;
                $pod_detail->save();

                $all_ids = PurchaseOrder::where('po_group_id', $po_detail->po_group_id)->where('supplier_id', $po_detail->supplier_id)->pluck('id');
                $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $po_detail->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->get();

                if ($all_record->count() > 0) {
                    //to update total extra tax column in po group product detail
                    $po_detail->total_extra_tax = $all_record->sum('total_extra_tax');
                    $po_detail->total_extra_cost = $all_record->sum('total_extra_cost');

                    //to update unit extra tax column in po group product detail
                    $po_detail->unit_extra_tax = $all_record->sum('unit_extra_tax') / $all_record->count();
                    $po_detail->unit_extra_cost = $all_record->sum('unit_extra_cost') / $all_record->count();

                    $po_detail->save();
                }

                if ($po_detail->po_group->is_review == 1) {
                    if ($po_detail) {
                        $p_g_pd = $po_detail;
                        $supplier_product       = SupplierProducts::where('supplier_id', $p_g_pd->supplier_id)->where('product_id', $p_g_pd->product_id)->first();
                        $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1;
                        $buying_price_in_thb    = $p_g_pd->unit_price_in_thb;

                        $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                        $supplier_product->freight             = $p_g_pd->freight;
                        $supplier_product->landing             = $p_g_pd->landing;
                        $supplier_product->extra_cost          = $p_g_pd->unit_extra_cost;
                        $supplier_product->extra_tax           = $p_g_pd->unit_extra_tax;
                        $supplier_product->import_tax_actual   = $p_g_pd->actual_tax_percent;
                        $supplier_product->gross_weight        = $p_g_pd->total_gross_weight / $p_g_pd->quantity_inv;
                        $supplier_product->save();

                        $product = Product::find($p_g_pd->product_id);
                        // this is the price of after conversion for THB

                        $importTax              = $supplier_product->import_tax_actual;
                        $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                        $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);
                        $product->total_buy_unit_cost_price = $total_buying_price;
                        //this is supplier buying unit cost price
                        $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;
                        //this is selling price
                        $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                        // creating a history on a product detail page which shipment updated the product COGS
                        $product_history              = new ProductHistory;
                        $product_history->user_id     = Auth::user()->id;
                        $product_history->product_id  = $product->id;
                        $product_history->group_id    = @$p_g_pd->po_group->id;
                        $product_history->column_name = 'COGS Updated through closed shipment ' . @$p_g_pd->po_group->ref_id . ' by editing total extra tax';
                        $product_history->old_value   = @$product->selling_price;
                        $product_history->new_value   = @$total_selling_price;
                        $product_history->save();

                        $product->selling_price           = $total_selling_price;
                        $product->supplier_id             = $supplier_product->supplier_id;
                        $product->last_price_updated_date = Carbon::now();
                        $product->last_date_import        = Carbon::now();
                        $product->save();

                        $p_g_pd->product_cost = $total_selling_price;
                        $p_g_pd->save();

                        $stock_out = StockManagementOut::where('po_group_id', $p_g_pd->po_group_id)->where('product_id', $p_g_pd->product_id)->where('warehouse_id', $p_g_pd->to_warehouse_id)->update(['cost' => $p_g_pd->product_cost, 'cost_date' => Carbon::now()]);

                        $purchase_orders = PurchaseOrder::whereIn('id', PoGroupDetail::where('po_group_id', $po_detail->po_group->id)->pluck('purchase_order_id'))->get();
                        foreach ($purchase_orders as $PO) {
                            $purchase_order_details = PurchaseOrderDetail::where('po_id', $PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity', '!=', 0)->get();
                            foreach ($purchase_order_details as $p_o_d) {
                                $product_id = $p_o_d->product_id;
                                if ($p_o_d->order_product_id != null) {
                                    $product                           = Product::find($p_o_d->product_id);
                                    $p_o_d->order_product->actual_cost = $product != null ? $product->selling_price : 0;
                                    $p_o_d->order_product->save();
                                }
                            }
                        }
                    }
                }
            } elseif ($key == 'total_extra_cost') {

                // $po_detail->$key = $value;
                // $po_detail->unit_extra_cost = ($value / $po_detail->quantity_inv);

                //to find unit extra cost from total extra cost
                if ($pod_detail->quantity == 0) {
                    $u_e_c = 0;
                } else {
                    $u_e_c = ($value / $pod_detail->quantity);
                }

                //to update total extra cost column in po group product detail
                // $po_detail->total_extra_cost -= $pod_detail->total_extra_cost;
                // $po_detail->total_extra_cost += $value;

                //to update unit extra cost column in po group product detail
                // $po_detail->unit_extra_cost -= $pod_detail->unit_extra_cost;
                // $po_detail->unit_extra_cost += $u_e_c;

                $pod_detail->$key = $value;
                $pod_detail->unit_extra_cost = $u_e_c;
                $pod_detail->save();

                $all_ids = PurchaseOrder::where('po_group_id', $po_detail->po_group_id)->where('supplier_id', $po_detail->supplier_id)->pluck('id');
                $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $po_detail->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->get();

                if ($all_record->count() > 0) {
                    //to update total extra tax column in po group product detail
                    $po_detail->total_extra_cost = $all_record->sum('total_extra_cost');
                    $po_detail->total_extra_tax = $all_record->sum('total_extra_tax');

                    //to update unit extra tax column in po group product detail
                    $po_detail->unit_extra_cost = $all_record->sum('unit_extra_cost') / $all_record->count();
                    $po_detail->unit_extra_tax = $all_record->sum('unit_extra_tax') / $all_record->count();

                    $po_detail->save();
                }

                if ($po_detail->po_group->is_review == 1) {
                    if ($po_detail) {
                        $p_g_pd = $po_detail;
                        $supplier_product       = SupplierProducts::where('supplier_id', $p_g_pd->supplier_id)->where('product_id', $p_g_pd->product_id)->first();
                        $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1;
                        $buying_price_in_thb    = $p_g_pd->unit_price_in_thb;

                        $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                        $supplier_product->freight             = $p_g_pd->freight;
                        $supplier_product->landing             = $p_g_pd->landing;
                        $supplier_product->extra_cost          = $p_g_pd->unit_extra_cost;
                        $supplier_product->extra_tax           = $p_g_pd->unit_extra_tax;
                        $supplier_product->import_tax_actual   = $p_g_pd->actual_tax_percent;
                        $supplier_product->gross_weight        = $p_g_pd->total_gross_weight / $p_g_pd->quantity_inv;
                        $supplier_product->save();

                        $product = Product::find($p_g_pd->product_id);
                        // this is the price of after conversion for THB

                        $importTax              = $supplier_product->import_tax_actual;
                        $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                        $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);
                        $product->total_buy_unit_cost_price = $total_buying_price;
                        //this is supplier buying unit cost price
                        $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;
                        //this is selling price
                        $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                        // creating a history on a product detail page which shipment updated the product COGS
                        $product_history              = new ProductHistory;
                        $product_history->user_id     = Auth::user()->id;
                        $product_history->product_id  = $product->id;
                        $product_history->group_id    = @$p_g_pd->po_group->id;
                        $product_history->column_name = 'COGS Updated through closed shipment ' . @$p_g_pd->po_group->ref_id . ' by editing total extra cost';
                        $product_history->old_value   = @$product->selling_price;
                        $product_history->new_value   = @$total_selling_price;
                        $product_history->save();

                        $product->selling_price           = $total_selling_price;
                        $product->supplier_id             = $supplier_product->supplier_id;
                        $product->last_price_updated_date = Carbon::now();
                        $product->last_date_import        = Carbon::now();
                        $product->save();

                        $p_g_pd->product_cost = $total_selling_price;
                        $p_g_pd->save();

                        $stock_out = StockManagementOut::where('po_group_id', $p_g_pd->po_group_id)->where('product_id', $p_g_pd->product_id)->where('warehouse_id', $p_g_pd->to_warehouse_id)->update(['cost' => $p_g_pd->product_cost, 'cost_date' => Carbon::now()]);

                        $purchase_orders = PurchaseOrder::whereIn('id', PoGroupDetail::where('po_group_id', $po_detail->po_group->id)->pluck('purchase_order_id'))->get();
                        foreach ($purchase_orders as $PO) {
                            $purchase_order_details = PurchaseOrderDetail::where('po_id', $PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity', '!=', 0)->get();
                            foreach ($purchase_order_details as $p_o_d) {
                                $product_id = $p_o_d->product_id;
                                if ($p_o_d->order_product_id != null) {
                                    $product                = Product::find($p_o_d->product_id);
                                    $p_o_d->order_product->actual_cost = $product != null ? $product->selling_price : 0;
                                    $p_o_d->order_product->save();
                                }
                            }
                        }
                    }
                    // $po_detail->$key = $value;
                    // $po_detail->unit_extra_cost = ($value / $po_detail->quantity_inv);
                }
            } elseif ($key == 'unit_extra_cost') {
                // $po_detail->$key = $value;
                // $po_detail->total_extra_cost = ($value * $po_detail->quantity_inv);

                //to find total extra cost from unit extra cost
                if ($pod_detail->quantity == 0) {
                    $t_e_c = 0;
                } else {
                    $t_e_c = ($value * $pod_detail->quantity);
                }

                //to update total extra cost column in po group product detail
                // $po_detail->total_extra_cost -= $pod_detail->total_extra_cost;
                // $po_detail->total_extra_cost += $t_e_c;

                //to update unit extra cost column in po group product detail
                // $po_detail->unit_extra_cost -= $pod_detail->unit_extra_cost;
                // $po_detail->unit_extra_cost += $value;

                $pod_detail->$key = $value;
                $pod_detail->total_extra_cost = $t_e_c;
                $pod_detail->save();

                $all_ids = PurchaseOrder::where('po_group_id', $po_detail->po_group_id)->where('supplier_id', $po_detail->supplier_id)->pluck('id');
                $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $po_detail->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->get();

                if ($all_record->count() > 0) {
                    //to update total extra tax column in po group product detail
                    $po_detail->total_extra_cost = $all_record->sum('total_extra_cost');
                    $po_detail->total_extra_tax = $all_record->sum('total_extra_tax');

                    //to update unit extra tax column in po group product detail
                    $po_detail->unit_extra_cost = $all_record->sum('unit_extra_cost') / $all_record->count();
                    $po_detail->unit_extra_tax = $all_record->sum('unit_extra_tax') / $all_record->count();

                    $po_detail->save();
                }

                if ($po_detail->po_group->is_review == 1) {
                    if ($po_detail) {
                        $p_g_pd = $po_detail;
                        $supplier_product       = SupplierProducts::where('supplier_id', $p_g_pd->supplier_id)->where('product_id', $p_g_pd->product_id)->first();
                        $supplier_conv_rate_thb = @$p_g_pd->currency_conversion_rate != 0 ? $p_g_pd->currency_conversion_rate : 1;
                        $buying_price_in_thb    = $p_g_pd->unit_price_in_thb;

                        $supplier_product->buying_price_in_thb = $buying_price_in_thb;
                        $supplier_product->freight             = $p_g_pd->freight;
                        $supplier_product->landing             = $p_g_pd->landing;
                        $supplier_product->extra_cost          = $p_g_pd->unit_extra_cost;
                        $supplier_product->extra_tax           = $p_g_pd->unit_extra_tax;
                        $supplier_product->import_tax_actual   = $p_g_pd->actual_tax_percent;
                        $supplier_product->gross_weight        = $p_g_pd->total_gross_weight / $p_g_pd->quantity_inv;
                        $supplier_product->save();

                        $product = Product::find($p_g_pd->product_id);
                        // this is the price of after conversion for THB

                        $importTax              = $supplier_product->import_tax_actual;
                        $total_buying_price     = (($importTax / 100) * $buying_price_in_thb) + $buying_price_in_thb;

                        $total_buying_price     = ($supplier_product->freight) + ($supplier_product->landing) + ($supplier_product->extra_cost) + ($supplier_product->extra_tax) + ($total_buying_price);
                        $product->total_buy_unit_cost_price = $total_buying_price;
                        //this is supplier buying unit cost price
                        $product->t_b_u_c_p_of_supplier = $total_buying_price * $supplier_conv_rate_thb;
                        //this is selling price
                        $total_selling_price = $product->total_buy_unit_cost_price * $product->unit_conversion_rate;

                        // creating a history on a product detail page which shipment updated the product COGS
                        $product_history              = new ProductHistory;
                        $product_history->user_id     = Auth::user()->id;
                        $product_history->product_id  = $product->id;
                        $product_history->group_id    = @$p_g_pd->po_group->id;
                        $product_history->column_name = 'COGS Updated through closed shipment ' . @$p_g_pd->po_group->ref_id . ' by editing total extra cost';
                        $product_history->old_value   = @$product->selling_price;
                        $product_history->new_value   = @$total_selling_price;
                        $product_history->save();

                        $product->selling_price           = $total_selling_price;
                        $product->supplier_id             = $supplier_product->supplier_id;
                        $product->last_price_updated_date = Carbon::now();
                        $product->last_date_import        = Carbon::now();
                        $product->save();

                        $p_g_pd->product_cost = $total_selling_price;
                        $p_g_pd->save();

                        $stock_out = StockManagementOut::where('po_group_id', $p_g_pd->po_group_id)->where('product_id', $p_g_pd->product_id)->where('warehouse_id', $p_g_pd->to_warehouse_id)->update(['cost' => $p_g_pd->product_cost, 'cost_date' => Carbon::now()]);

                        $purchase_orders = PurchaseOrder::whereIn('id', PoGroupDetail::where('po_group_id', $po_detail->po_group->id)->pluck('purchase_order_id'))->get();
                        foreach ($purchase_orders as $PO) {
                            $purchase_order_details = PurchaseOrderDetail::where('po_id', $PO->id)->whereNotNull('purchase_order_details.product_id')->where('quantity', '!=', 0)->get();
                            foreach ($purchase_order_details as $p_o_d) {
                                $product_id = $p_o_d->product_id;
                                if ($p_o_d->order_product_id != null) {
                                    $product                = Product::find($p_o_d->product_id);
                                    $p_o_d->order_product->actual_cost = $product != null ? $product->selling_price : 0;
                                    $p_o_d->order_product->save();
                                }
                            }
                        }
                    }
                    // $po_detail->$key = $value;
                    // $po_detail->unit_extra_cost = ($value / $po_detail->quantity_inv);
                }
            } else {
                $po_detail->$key = $value;
            }
        }
        $pod_detail->save();
        $po_detail->save();

        return response()->json(['success' => true, 'pod' => $pod_detail, 'pogpd' => $po_detail]);
    }

    public function clearGroupValues(Request $request)
    {
        $group = PoGroup::find($request->id);
        $title = $request->title;
        if ($group) {
            if ($group->is_review == 0) {
                $po_group_product_details = PoGroupProductDetail::where('status', 1)->where('po_group_id', $request->id)->get();
                if ($request->action == 'Clear Values') {

                    if ($title == 'extra_cost') {
                        foreach ($po_group_product_details as $detail) {
                            if ($detail->unit_extra_cost != null) {
                                if ($detail) {
                                    $PoGroupProduct_history = new PoGroupProductHistory;
                                    $PoGroupProduct_history->user_id = Auth::user()->id;
                                    $PoGroupProduct_history->ref_id = null;
                                    $PoGroupProduct_history->order_product_id = $detail->product_id;
                                    $PoGroupProduct_history->old_value = $detail->unit_extra_cost;
                                    $PoGroupProduct_history->column_name = 'Extra Cost';
                                    $PoGroupProduct_history->po_group_id = $request->id;
                                    $PoGroupProduct_history->new_value = 'cleared';
                                    $PoGroupProduct_history->save();
                                }
                                if ($detail->occurrence > 1) {
                                    $detail->unit_extra_cost = null;
                                    $detail->total_extra_cost = null;
                                    $detail->save();

                                    $all_ids = PurchaseOrder::where('po_group_id', $detail->po_group_id)->where('supplier_id', $detail->supplier_id)->pluck('id');
                                    $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $detail->product_id)->get();

                                    if ($all_record->count() > 0) {
                                        foreach ($all_record as $record) {
                                            $record->unit_extra_cost = null;
                                            $record->total_extra_cost = null;
                                            $record->save();
                                        }
                                    }
                                } else {
                                    $detail->unit_extra_cost = null;
                                    $detail->total_extra_cost = null;
                                    $detail->save();
                                }
                            }
                        }
                    }
                    if ($title == 'extra_tax') {
                        foreach ($po_group_product_details as $detail) {
                            if ($detail->unit_extra_tax != null) {
                                if ($detail) {
                                    $PoGroupProduct_history = new PoGroupProductHistory;
                                    $PoGroupProduct_history->user_id = Auth::user()->id;
                                    $PoGroupProduct_history->ref_id = null;
                                    $PoGroupProduct_history->order_product_id = $detail->product_id;
                                    $PoGroupProduct_history->old_value = $detail->unit_extra_tax;
                                    $PoGroupProduct_history->column_name = 'Extra Tax';
                                    $PoGroupProduct_history->po_group_id = $request->id;
                                    $PoGroupProduct_history->new_value = 'cleared';
                                    $PoGroupProduct_history->save();
                                }
                                if ($detail->occurrence > 1) {
                                    $detail->unit_extra_tax = null;
                                    $detail->total_extra_tax = null;
                                    $detail->save();

                                    $all_ids = PurchaseOrder::where('po_group_id', $detail->po_group_id)->where('supplier_id', $detail->supplier_id)->pluck('id');
                                    $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $detail->product_id)->get();

                                    if ($all_record->count() > 0) {
                                        foreach ($all_record as $record) {
                                            $record->unit_extra_tax = null;
                                            $record->total_extra_tax = null;
                                            $record->save();
                                        }
                                    }
                                } else {
                                    $detail->unit_extra_tax = null;
                                    $detail->total_extra_tax = null;
                                    $detail->save();
                                }
                            }
                        }
                    }
                    if ($title == 'book_vat') {
                        foreach ($po_group_product_details as $detail) {
                            if ($detail->pogpd_vat_actual != null) {
                                if ($detail) {
                                    $PoGroupProduct_history = new PoGroupProductHistory;
                                    $PoGroupProduct_history->user_id = Auth::user()->id;
                                    $PoGroupProduct_history->ref_id = null;
                                    $PoGroupProduct_history->order_product_id = $detail->product_id;
                                    $PoGroupProduct_history->old_value = $detail->pogpd_vat_actual;
                                    $PoGroupProduct_history->column_name = 'Book Vat %';
                                    $PoGroupProduct_history->po_group_id = $request->id;
                                    $PoGroupProduct_history->new_value = 'cleared';
                                    $PoGroupProduct_history->save();
                                }

                                $detail->pogpd_vat_actual = 0;
                                $detail->pogpd_vat_actual_percent = null;
                                $detail->save();
                                $all_ids = PurchaseOrder::where('po_group_id', $detail->po_group_id)->where('supplier_id', $detail->supplier_id)->pluck('id');
                                $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $detail->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->get();
                                // dd($all_record->count());
                                foreach ($all_record as $record) {
                                    $record->pod_vat_actual = 0;
                                    $record->pod_vat_actual_total_price_in_thb = 0;
                                    $record->save();
                                }
                            }
                        }
                        $group->po_group_vat_actual = 0;
                        $group->po_group_vat_actual_percent = null;
                        $group->save();
                    }
                    return response()->json(['success' => true, 'msg' => 'Values cleared successfully !!!']);
                } else {
                    if ($title == 'extra_cost') {
                        foreach ($po_group_product_details as $detail) {
                            $PoGroupProduct_history = new PoGroupProductHistory;
                            $PoGroupProduct_history->user_id = Auth::user()->id;
                            $PoGroupProduct_history->ref_id = null;
                            $PoGroupProduct_history->order_product_id = $detail->product_id;
                            $PoGroupProduct_history->old_value = $detail->unit_extra_cost;
                            $PoGroupProduct_history->column_name = 'Extra Cost';
                            $PoGroupProduct_history->po_group_id = $request->id;
                            $PoGroupProduct_history->new_value = 'undo';
                            $PoGroupProduct_history->save();


                            $extra_cost = $detail->product->supplier_products;
                            $extra_cost = $extra_cost->where('supplier_id', $detail->supplier_id)->first()->extra_cost;
                            $total_extra_cost = $extra_cost * $detail->quantity_inv;

                            if ($detail->occurrence > 1) {
                                $unit_extra_cost_average = 0;
                                $total_extra_cost_average = 0;

                                $all_ids = PurchaseOrder::where('po_group_id', $detail->po_group_id)->where('supplier_id', $detail->supplier_id)->pluck('id');
                                $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $detail->product_id)->get();

                                if ($all_record->count() > 0) {
                                    foreach ($all_record as $record) {
                                        $record->unit_extra_cost = $extra_cost;
                                        $record->total_extra_cost = $extra_cost * $record->quantity;;
                                        $record->save();
                                        $unit_extra_cost_average += $extra_cost;
                                        $total_extra_cost_average += $extra_cost * $record->quantity;
                                    }
                                }
                                $detail->unit_extra_cost = $unit_extra_cost_average / $detail->occurrence;
                                $detail->total_extra_cost = $total_extra_cost_average;
                                $detail->save();
                            } else {
                                $detail->unit_extra_cost = $extra_cost;
                                $detail->total_extra_cost = $total_extra_cost;
                                $detail->save();
                            }
                        }
                    }
                    if ($title == 'extra_tax') {
                        foreach ($po_group_product_details as $detail) {
                            $PoGroupProduct_history = new PoGroupProductHistory;
                            $PoGroupProduct_history->user_id = Auth::user()->id;
                            $PoGroupProduct_history->ref_id = null;
                            $PoGroupProduct_history->order_product_id = $detail->product_id;
                            $PoGroupProduct_history->old_value = $detail->unit_extra_tax;
                            $PoGroupProduct_history->column_name = 'Extra Tax';
                            $PoGroupProduct_history->po_group_id = $request->id;
                            $PoGroupProduct_history->new_value = 'undo';
                            $PoGroupProduct_history->save();


                            $extra_tax = $detail->product->supplier_products;
                            $extra_tax = $extra_tax->where('supplier_id', $detail->supplier_id)->first()->extra_tax;
                            $total_extra_tax = $extra_tax * $detail->quantity_inv;

                            if ($detail->occurrence > 1) {
                                $unit_extra_tax_average = 0;
                                $total_extra_tax_average = 0;

                                $all_ids = PurchaseOrder::where('po_group_id', $detail->po_group_id)->where('supplier_id', $detail->supplier_id)->pluck('id');
                                $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $detail->product_id)->get();

                                if ($all_record->count() > 0) {
                                    foreach ($all_record as $record) {
                                        $record->unit_extra_tax = $extra_tax;
                                        $record->total_extra_tax = $extra_tax * $record->quantity;
                                        $record->save();
                                        $unit_extra_tax_average += $extra_tax;
                                        $total_extra_tax_average += $extra_tax * $record->quantity;
                                    }
                                }
                                $detail->unit_extra_tax = $unit_extra_tax_average / $detail->occurrence;
                                $detail->total_extra_tax = $total_extra_tax_average;
                                $detail->save();
                            } else {
                                $detail->unit_extra_tax = $extra_tax;
                                $detail->total_extra_tax = $total_extra_tax;
                                $detail->save();
                            }
                        }
                    }
                    if ($title == 'book_vat') {
                        foreach ($po_group_product_details as $detail) {
                            $PoGroupProduct_history = new PoGroupProductHistory;
                            $PoGroupProduct_history->user_id = Auth::user()->id;
                            $PoGroupProduct_history->ref_id = null;
                            $PoGroupProduct_history->order_product_id = $detail->product_id;
                            $PoGroupProduct_history->old_value = $detail->pogpd_vat_actual;
                            $PoGroupProduct_history->column_name = 'Book Vat %';
                            $PoGroupProduct_history->po_group_id = $request->id;
                            $PoGroupProduct_history->new_value = 'undo';
                            $PoGroupProduct_history->save();

                            $book_vat = $detail->product->vat;
                            $total_book_vat = ($book_vat / 100) * $detail->total_unit_price_in_thb;
                            $detail->pogpd_vat_actual = $book_vat;
                            $detail->pogpd_vat_actual_percent = $total_book_vat;
                            $detail->save();
                            $all_ids = PurchaseOrder::where('po_group_id', $detail->po_group_id)->where('supplier_id', $detail->supplier_id)->pluck('id');
                            $all_record = PurchaseOrderDetail::whereIn('po_id', $all_ids)->where('product_id', $detail->product_id)->with('product', 'PurchaseOrder', 'getOrder', 'product.units', 'getOrder.user', 'getOrder.customer')->get();
                            foreach ($all_record as $record) {
                                $record->pod_vat_actual = $book_vat;
                                $record->pod_vat_actual_total_price_in_thb = ($book_vat / 100) * $record->total_unit_price_in_thb;
                                $record->save();
                            }
                        }

                        $po_group = PoGroup::where('id', $request->id)->first();

                        $total_vat_actual_price = 0;
                        $total_vat_actual_percent = 0;
                        $po_group_details = $po_group->po_group_product_details;

                        foreach ($po_group_details as $po_group_detail) {
                            $total_vat_actual_price += ($po_group_detail->pogpd_vat_actual_percent);
                            $total_vat_actual_percent += ($po_group_detail->pogpd_vat_actual);
                        }

                        if ($total_vat_actual_price == 0) {
                            foreach ($po_group_details as $po_group_detail) {
                                $count = PoGroupProductDetail::where('status', 1)->where('po_group_id', $po_group_detail->po_group_id)->count();
                                $vat_tax = (1 / $count) * $po_group_detail->total_unit_price_in_thb;
                                $total_vat_actual_price += $vat_tax;
                            }
                        }

                        $po_group->po_group_vat_actual = $total_vat_actual_price;
                        $po_group->po_group_vat_actual_percent = $total_vat_actual_percent;
                        $po_group->save();
                    }
                    return response()->json(['success' => true, 'msg' => 'Undo data successfully !!!']);
                }
            } else {
                return response()->json(['success' => false, 'msg' => 'Group is closed cannot update values !!!']);
            }
        } else {
            return response()->json(['success' => false, 'msg' => 'Group no longer exists !!!']);
        }
    }

    public function receivingQueueDetailImport($id){
        $group = PoGroup::find($id);
        return $this->render('importing.po-groups.products-receiving-import-verification', compact('id','group'));
    }
    public function receivingQueueImportDetail(Request $request){
        // dd($request->all());
        $import_records = ProductReceivingImportTemp::where('user_id',auth()->user()->id)->where('group_id',$request->id)->with('po_group:id,ref_id','purchase_order:id,ref_id');

        $dt = Datatables::of($import_records);
        $add_columns = ['pf', 'ref_id', 'po', 'unit_price', 'discount', 'qty_inv', 'gross_weight', 'total_gross_weight', 'extra_cost', 'total_extra_cost', 'extra_tax', 'total_extra_tax', 'currency_conversion_rate','unit_price_old', 'discount_old', 'qty_inv_old', 'gross_weight_old', 'total_gross_weight_old', 'extra_cost_old', 'total_extra_cost_old', 'extra_tax_old', 'total_extra_tax_old', 'currency_conversion_rate_old'];

        foreach ($add_columns as $column) {
            $dt->addColumn($column, function ($item) use ($column) {
                switch ($column) {
                    case 'pf':
                        return $item->prod_ref_no;
                        break;
                    case 'ref_id':
                        return @$item->po_group->ref_id ?? '--';
                        break;
                    case 'po':
                        return @$item->purchase_order->ref_id ?? '--';
                        break;
                    case 'unit_price':
                        $html = $item->purchasing_price_euro_updated == 0 ? $item->purchasing_price_euro : ($item->purchasing_price_euro != null ? '<span style="background-color: #ebd283;display:block">'.$item->purchasing_price_euro.'</span>' : '');
                        return $html;
                        break;
                    case 'discount':
                        $html = $item->discount_updated == 0 ? $item->discount : ($item->discount != null ? '<span style="background-color: #ebd283; display:block">'.$item->discount.'</span>' : '');
                        return $html;
                        break;
                    case 'qty_inv':
                        $html = $item->qty_inv_updated == 0 ? $item->qty_inv : ($item->qty_inv != null ? '<span style="background-color: #ebd283; display:block">'.$item->qty_inv.'</span>' : '');
                        return $html;
                        break;
                    case 'gross_weight':
                        $html = $item->gross_weight_updated == 0 ? $item->gross_weight : ($item->gross_weight != null ? '<span style="background-color: #ebd283; display:block">'.$item->gross_weight.'</span>' : '');
                        return $html;
                        break;
                    case 'total_gross_weight':
                        $html = $item->total_gross_weight_updated == 0 ? $item->total_gross_weight : ($item->total_gross_weight != null ? '<span style="background-color: #ebd283; display:block">'.$item->total_gross_weight.'</span>' : '');
                        return $html;
                        break;
                    case 'extra_cost':
                        $html = $item->extra_cost_updated == 0 ? $item->extra_cost : ($item->extra_cost != null ? '<span style="background-color: #ebd283; display:block">'.$item->extra_cost.'</span>' : '');
                        return $html;
                        break;
                    case 'total_extra_cost':
                        $html = $item->total_extra_cost_updated == 0 ? $item->total_extra_cost : ($item->total_extra_cost != null ? '<span style="background-color: #ebd283; display:block">'.$item->total_extra_cost.'</span>' : '');
                        return $html;
                        break;
                    case 'extra_tax':
                        $html = $item->extra_tax_updated == 0 ? $item->extra_tax : ($item->extra_tax != null ? '<span style="background-color: #ebd283; display:block">'.$item->extra_tax.'</span>' : '');
                        return $html;
                        break;
                    case 'total_extra_tax':
                        $html = $item->total_extra_tax_updated == 0 ? $item->total_extra_tax : ($item->total_extra_tax != null ? '<span style="background-color: #ebd283; display:block">'.$item->total_extra_tax.'</span>' : '');
                        return $html;
                        break;
                    case 'currency_conversion_rate':
                        $html = $item->currency_conversion_rate_updated == 0 ? $item->currency_conversion_rate : ($item->currency_conversion_rate != null ? '<span style="background-color: #ebd283; display:block">'.$item->currency_conversion_rate.'</span>' : '');
                        return $html;
                        break;
                    //old record
                        case 'unit_price_old':
                        $html = $item->purchasing_price_euro_old;
                        return $html;
                        break;
                    case 'discount_old':
                        $html = $item->discount_old;
                        return $html;
                        break;
                    case 'qty_inv_old':
                        $html = $item->qty_inv_old;
                        return $html;
                        break;
                    case 'gross_weight_old':
                        $html = $item->gross_weight_old;
                        return $html;
                        break;
                    case 'total_gross_weight_old':
                        $html = $item->total_gross_weight_old;
                        return $html;
                        break;
                    case 'extra_cost_old':
                        $html = $item->extra_cost_old;
                        return $html;
                        break;
                    case 'total_extra_cost_old':
                        $html = $item->total_extra_cost_old;
                        return $html;
                        break;
                    case 'extra_tax_old':
                        $html = $item->extra_tax_old;
                        return $html;
                        break;
                    case 'total_extra_tax_old':
                        $html = $item->total_extra_tax_oldd;
                        return $html;
                        break;
                    case 'currency_conversion_rate_old':
                        $html = $item->currency_conversion_rate_old != 0 ? number_format(1 / $item->currency_conversion_rate_old,2) : '';
                        return $html;
                        break;

                    default:
                        return $column.' not found !!!';
                        break;
                }
            });
        }

        $dt->setRowClass(function ($item) {
            return $item->row_updated == 1 ? 'yellowRow' : '';
        });

        $dt->rawColumns(['pf', 'ref_id', 'po', 'unit_price', 'discount', 'qty_inv', 'gross_weight', 'total_gross_weight', 'extra_cost', 'total_extra_cost', 'extra_tax', 'total_extra_tax', 'currency_conversion_rate','unit_price_old', 'discount_old', 'qty_inv_old', 'gross_weight_old', 'total_gross_weight_old', 'extra_cost_old', 'total_extra_cost_old', 'extra_tax_old', 'total_extra_tax_old', 'currency_conversion_rate_old']);

        return $dt->make(true);
    }
    public function receivingQueueImportDetailConfirm(Request $request){
        // dd($request->all());
        $query = ProductReceivingImportTemp::where('group_id',$request->id)->where('user_id',auth()->user()->id)->get();
        $status = ExportStatus::where('type', 'products_receiving_bulk_preview_confirm')->where('user_id',auth()->user()->id)->first();
        if ($status == null) {
            $new = new ExportStatus();
            $new->type = 'products_receiving_bulk_preview_confirm';
            $new->user_id = Auth::user()->id;
            $new->status = 1;
            $new->save();
            ConfirmGroupImportData::dispatch($query, auth()->user()->id);
            return response()->json(['status' => 1, 'recursive' => true]);
        } elseif ($status->status == 1) {
            return response()->json(['status' => 2, 'recursive' => false]);
        } elseif ($status->status == 0 || $status->status == 2) {
            ExportStatus::where('type', 'products_receiving_bulk_preview_confirm')->where('user_id',auth()->user()->id)->update(['status' => 1, 'exception' => null]);
            ConfirmGroupImportData::dispatch($query, auth()->user()->id);
            return response()->json(['msg' => "File is getting ready!", 'status' => 1, 'recursive' => true]);
        }
    }
    public function receivingQueueImportDetailConfirmRecursive(){
        $status = ExportStatus::where('type', 'products_receiving_bulk_preview_confirm')->where('user_id',auth()->user()->id)->first();
        return response()->json(['msg' => "File Created!", 'status' => $status->status, 'exception' => $status->exception]);
    }
}
