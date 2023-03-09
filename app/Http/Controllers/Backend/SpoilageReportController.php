<?php

namespace App\Http\Controllers\Backend;

use App\General;
use App\Variable;
use App\Notification;
use Illuminate\Http\Request;
use App\Models\Common\Deployment;
use App\Http\Controllers\Controller;
use App\Models\Common\Configuration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Schema;
use Yajra\DataTables\Facades\DataTables;
use App\TransferDocumentReservedQuantity;
use App\Helpers\ProductConfigurationHelper;

class SpoilageReportController extends Controller
{
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
        // current controller constructor
        $general = new General();
        $targetShipDate = $general->getTargetShipDateConfig();
        $this->targetShipDateConfig = $targetShipDate;

        $extra_space_for_select2 = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';

        $deployment = Deployment::where('status', 1)->first();

        $product_detail_section = ProductConfigurationHelper::getProductConfigurations();
        // Sharing is caring
        View::share(['global_terminologies' => $global_terminologies, 'sys_name' => $sys_name, 'sys_logos' => $sys_logos, 'sys_color' => $sys_color, 'sys_border_color' => $sys_border_color, 'btn_hover_border' => $btn_hover_border, 'current_version' => $current_version, 'dummy_data' => $dummy_data, 'extra_space' => $extra_space_for_select2, 'server' => @$config->server, 'config' => $config, 'deployment' => $deployment, 'product_detail_section' => $product_detail_section]);
    }

    public function index()
    {
        return view('users.reports.spoilage_report');
    }

    public function getSpoilageReport(Request $request)
    {
        $query = TransferDocumentReservedQuantity::with('po', 'po_detail.product', 'stock_m_out.get_po_group', 'spoilage_table', 'inbound_pod.PurchaseOrder')->where('spoilage' , '>', 0);
        if ($request->from_date != null) {
            $date = str_replace("/", "-", $request->from_date);
            $date =  date('Y-m-d', strtotime($date));
            $query->where('created_at', '>=', $date. ' 00:00:00');
        }
        if ($request->to_date != null) {
            $date = str_replace("/", "-", $request->to_date);
            $date =  date('Y-m-d', strtotime($date));
            $query->where('created_at', '<=', $date . ' 23:59:59');
        }

        return DataTables::of($query)
        ->addColumn('occurence', function ($item){
            $html = '--';
            if ($item->po->status == '22') {
                $occurence = 'TD ' . $item->po->ref_id;
                $html = '<a target="_blank" href="' .route('get-purchase-order-detail', $item->po->id). '" class="font-weight-bold">'.$occurence.'</a>';
            }
            return $html;
        })
        ->addColumn('refrence_code', function ($item){
            $pf = $item->po_detail->product->refrence_code;
            $html = '<a target="_blank" href="' .route('get-product-detail', $item->po_detail->product_id) . '" class="font-weight-bold">'.$pf.'</a>';

            return $html;
        })
        ->addColumn('shippment_no', function ($item){
            $html = '--';
            $group_no = $item->stock_m_out != null && $item->stock_m_out->get_po_group != null ? $item->stock_m_out->get_po_group->ref_id : null;
            if ($group_no != null) {
                $html = '<a target="_blank" href="' .route('warehouse-completed-receiving-queue-detail', $item->stock_m_out->get_po_group->id). '" class="font-weight-bold">Shippment ('.$group_no.')</a>';
            }
            else{
                $po_no = $item->inbound_pod != null && $item->inbound_pod->PurchaseOrder != null? $item->inbound_pod->PurchaseOrder->ref_id : '--';
                $html = '<a target="_blank" href="' .route('get-purchase-order-detail', $item->inbound_pod->PurchaseOrder->id). '" class="font-weight-bold">PO ('.$po_no.')</a>';
            }
            return $html;
        })
        ->addColumn('quantity', function ($item){
            $spoilage = $item->spoilage != null ? $item->spoilage : '--';
            return $spoilage;
        })
        ->addColumn('spoilage_type', function ($item){
            $type = $item->spoilage_type != null ? $item->spoilage_table->title : '--';
            return $type;
        })
        ->rawColumns(['occurence', 'refrence_code', 'shippment_no'])
        ->make(true);
    }
}
