<?php

namespace App\Http\Controllers\Purchasing;

use App\General;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Common\Company;
use App\Models\Common\Configuration;
use App\Models\Common\PaymentTerm;
use App\Models\Common\PoGroupDetail;
use App\Models\Common\PurchaseOrders\PurchaseOrder;
use App\Models\Common\PurchaseOrders\PurchaseOrderDetail;
use App\Models\Common\State;
use App\Models\Common\Status;
use App\Models\Common\Supplier;
use App\Models\Common\TableHideColumn;
use App\Models\Common\Unit;
use App\Models\Common\Warehouse;
use App\Notification;
use App\QuotationConfig;
use App\Variable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

class SupplierDebitNoteController extends Controller
{
    protected $targetShipDateConfig;
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $this->user = Auth::user();

            return $next($request);
        });
        $dummy_data = null;
        if ($this->user && Schema::has('notifications')) {
            $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        }
        $general = new General();
        $targetShipDate = $general->getTargetShipDateConfig();
        $this->targetShipDateConfig = $targetShipDate;

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
        View::share(['global_terminologies' => $global_terminologies, 'sys_name' => $sys_name, 'sys_logos' => $sys_logos, 'sys_color' => $sys_color, 'sys_border_color' => $sys_border_color, 'btn_hover_border' => $btn_hover_border, 'current_version' => $current_version, 'dummy_data' => $dummy_data]);
    }
    public function index()
    {
        $status     = Status::where('id', 28)->first();

        $company_prefix  = @Auth::user()->getCompany->prefix;
        $counter_formula = $status->counter_formula;
        $counter_formula = explode('-', $counter_formula);
        $counter_length  = strlen($counter_formula[1]) != null ? strlen($counter_formula[1]) : 4;
        $date = Carbon::now();
        $date = $date->format($counter_formula[0]); //we expect the inner varaible to be ym so it will produce 2005 for date 2020/05/anyday
        // $date = 2005;
        $status_prefix    = $status->prefix . $company_prefix;

        $c_p_ref = PurchaseOrder::where('primary_status', 28)->orderby('id', 'DESC')->first();
        $str = @$c_p_ref->ref_id;
        $onlyIncrementGet = substr($str, 4);
        if ($str == NULL) {
            $onlyIncrementGet = 0;
        }
        $system_gen_no = str_pad(@$onlyIncrementGet + 1, $counter_length, 0, STR_PAD_LEFT);
        $system_gen_no = $date . $system_gen_no;
        // dd($system_gen_no);
        $credit_note = PurchaseOrder::create(['created_by' => Auth::user()->id,'status' => 29, 'ref_id' => $system_gen_no]);
        $po = PurchaseOrder::find($credit_note->id);
        $po->primary_status = 28;
        $po->save();
        return redirect()->route("get-supplier-debit-note-detail", $po->id);
    }

    public function getSupplierNoteDetail($id)
    {
        $paymentTerms           = PaymentTerm::all();
        $getPurchaseOrder       = PurchaseOrder::with('p_o_group.po_group', 'p_o_statuses', 'PoSupplier', 'ToWarehouse', 'pOpaymentTerm', 'createdBy', 'po_notes', 'po_documents')->find($id);
        $warehouses             = Warehouse::where('status', 1)->get();
        $company_info           = Company::with('getcountry', 'getstate')->where('id', $getPurchaseOrder->createdBy->company_id)->first();
        $getPoNote              = $getPurchaseOrder->po_notes->first();
        $checkPoDocs            = $getPurchaseOrder->po_documents->count();

        $quotation_config   = QuotationConfig::where('section', 'purchase_order')->first();
        $hidden_by_default  = '';
        $hidden_columns     = null;
        $columns_prefrences = null;
        $hidden_columns_by_admin = [];
        $user_plus_admin_hidden_columns = [];
        if ($quotation_config == null) {
            $hidden_by_default = '';
        } else {
            $dislay_prefrences = $quotation_config->display_prefrences;
            $hide_columns = $quotation_config->show_columns;
            if ($quotation_config->show_columns != null) {
                $hidden_columns = json_decode($hide_columns);
                $hidden_columns = implode(",", $hidden_columns);
                $hidden_columns_by_admin = explode(",", $hidden_columns);
            }
            $columns_prefrences = json_decode($quotation_config->display_prefrences);
            $columns_prefrences = implode(",", $columns_prefrences);

            $user_hidden_columns = [];
            $not_visible_columns = TableHideColumn::select('hide_columns')->where('type', 'po_detail')->where('user_id', Auth::user()->id)->first();
            if ($not_visible_columns != null) {
                $user_hidden_columns = $not_visible_columns->hide_columns;
            } else {
                $user_hidden_columns = "";
            }
            $user_plus_admin_hidden_columns = $user_hidden_columns . ',' . $hidden_columns;
        }

        $supplier_currency_logo = @$getPurchaseOrder->PoSupplier->getCurrency->currency_symbol;

        if ($getPurchaseOrder->status == 14) {
            $check_group = $getPurchaseOrder->po_group->id;
            $all_group = PoGroupDetail::where('po_group_id', $check_group)->get();
            if ($all_group->count() > 1) {
                $pos_count = 2;
            } else if ($all_group->count() == 1) {
                $pos_count = 1;
            } else {
                $pos_count = 0;
            }
        } else {
            $pos_count = 0;
        }

        $total_system_units = Unit::whereNotNull('id')->count();
        $itemsCount = PurchaseOrderDetail::where('is_billed', 'Product')->where('po_id', $id)->sum('quantity');

        $dummy_data = null;
        $dummy_data = Notification::where('notifiable_id', Auth::user()->id)->orderby('created_at', 'desc')->get();
        $globalAccessConfig3 = QuotationConfig::where('section', 'target_ship_date')->first();
        if ($globalAccessConfig3 != null) {
            $targetShipDate = unserialize($globalAccessConfig3->print_prefrences);
        } else {
            $targetShipDate = null;
        }
        $suppliers = Supplier::all();
        // dd($getPurchaseOrder);
        return view('accounting.notes.supplier-debit-note-detail', compact('getPurchaseOrderDetail', 'table_hide_columns', 'display_purchase_list', 'id', 'getPurchaseOrder', 'checkPoDocs', 'getPoNote', 'po_setting', 'company_info', 'warehouses', 'supplier_currency_logo', 'paymentTerms', 'columns_prefrences', 'hidden_columns', 'pos_count', 'hidden_columns_by_admin', 'user_plus_admin_hidden_columns', 'dummy_data', 'targetShipDate', 'suppliers'));
    }

    public function addSupplierToCreditNote(Request $request)
    {
        $po = PurchaseOrder::find($request->po_id)->update(['supplier_id' => $request->supplier_id]);
        return response()->json(['success' => true]);
    }
}
