<?php

namespace App\Exports;

use App\Models\Common\PoGroupProductDetail;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class purchasingReportExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithEvents
{
    protected $query = null;
    protected $global_terminologies = null;
    protected $product_detail_section = null;

     /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($query, $global_terminologies, $product_detail_section)
    {
        $this->query = $query;
        $this->global_terminologies = $global_terminologies;
        $this->product_detail_section = $product_detail_section;
    }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function map($item) : array
    {
        $po_group_product_detail = PoGroupProductDetail::where('status', 1)->where('po_group_id', @$item->PurchaseOrder->po_group_id)->where('supplier_id', @$item->PurchaseOrder->supplier_id)->where('product_id', $item->product_id)->first();
        $product_detail_section = $this->product_detail_section;
        if($item->PurchaseOrder->confirm_date !== null)
        {
            $confirm_date = Carbon::parse($item->PurchaseOrder->confirm_date)->format('d/m/Y');
        }
        else
        {
            $confirm_date = 'N.A';
        }
        $supplier = $item->PurchaseOrder->PoSupplier->reference_name;
        if($item->po_id !== null)
        {
            $po_no = $item->PurchaseOrder->ref_id;
        }
        else
        {
            $po_no = 'N.A';
        }
        if($item->product_id != null)
        {
            $pf_no = $item->product->refrence_code;
            $desc = $item->product->short_desc;
            $product_type = @$item->product->productType != null ? @$item->product->productType->title : 'N.A';
            $product_type_2 = @$item->product->productType2 != null ? @$item->product->productType2->title : 'N.A';
            $product_type_3 = @$item->product->productType3 != null ? @$item->product->productType3->title : 'N.A';
            $billing_unit = $item->product->units->title;
            $unit_mes_code = $item->product->sellingUnits->title;
            if($po_group_product_detail){
                    $fright = round(@$po_group_product_detail->freight,4) ?? 0;
            }
            else if($item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->freight !== null)
            {
                $fright = number_format((float) $item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->freight, 3, '.', ',');
            }
            else
            {
                $fright = '--';
            }
            if($po_group_product_detail){
                    $landing = round(@$po_group_product_detail->landing,4) ?? 0;
            }
            else if($item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->landing !== null)
            {
                $landing = number_format((float) $item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->landing, 3, '.', ',');
            }
            else
            {
                $landing = '--';
            }
            if($po_group_product_detail){
                    $tax_actual = round(@$po_group_product_detail->actual_tax_percent,4) ?? 0;
            }
            else if($item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->import_tax_actual !== null)
            {
                $tax_actual = number_format((float) $item->product->supplier_products->where('supplier_id',$item->product->supplier_id)->first()->import_tax_actual, 3, '.', ',');
            }
            else
            {
                $tax_actual = '--';
            }
            if($item->product->total_buy_unit_cost_price !== null)
            {
                $cost_price = number_format((float) $item->product->total_buy_unit_cost_price, 3, '.', '');
            }
            else
            {
                $cost_price = '--';
            }
        }
        else
        {
            $pf_no = 'N.A';
            $desc = 'N.A';
            $billing_unit = 'N.A';
            $unit_mes_code = 'N.A';
            $fright = 'N.A';
            $landing = 'N.A';
            $tax_actual = 'N.A';
            $cost_price = 'N.A';
        }

        if($item->quantity !== null)
        {
            $sum_qty = $item->quantity;
        }
        else
        {
            $sum_qty = 'N.A';
        }

        if($item->pod_unit_price !== null)
        {
            $cost_unit = number_format($item->pod_unit_price,3,'.','');
        }
        else
        {
            $cost_unit = 'N.A';
        }
        if($item->pod_total_unit_price !== null)
        {
            $pod_total_unit = number_format($item->pod_total_unit_price,3,'.','');
        }
        else
        {
            $pod_total_unit = 'N.A';
        }
        $cost_unit_thb = $item->unit_price_in_thb + ($item->pod_freight + $item->pod_landing + $item->pod_total_extra_cost);
        $minimum_stock = $item->product->min_stock != null ? $item->product->min_stock :'--';
        $sum_cost_amt = ($item->unit_price_in_thb * $item->quantity);
        $cst_unt_thb = number_format($cost_unit_thb,3,'.','');
        $sm_cst_amt = number_format($sum_cost_amt,3,'.','');
        if($item->product->vat !== null)
        {
            $vat = $item->product->vat.' %';
        }
        else
        {
            $vat = '0 %';
        }

        $supplier_invoice = $item->PurchaseOrder->invoice_number !== null ? $item->PurchaseOrder->invoice_number : "--";

        $supplier_invoice_date = $item->PurchaseOrder->invoice_date !== null ? Carbon::parse($item->PurchaseOrder->invoice_date)->format('d/m/Y')  : "--";

        $vat_amount_euro = $item->pod_vat_actual_price !== null ? number_format($item->pod_vat_actual_price,3,'.',',') : "--";

        $vat_amount_thb = $item->pod_vat_actual_price_in_thb !== null ? number_format($item->pod_vat_actual_price_in_thb,3,'.',',') : "--";

        $unit_price_before_vat_euro = $item->pod_unit_price !== null ? number_format($item->pod_unit_price,3,'.',',') : "--";

        $unit_price_before_vat_thb = $item->unit_price_in_thb !== null ? number_format($item->unit_price_in_thb,3,'.',',') : "--";

        $unit_price_after_vat_euro = $item->pod_unit_price_with_vat !== null ? number_format($item->pod_unit_price_with_vat,3,'.',',') : "--";

        $unit_price_after_vat_thb = $item->unit_price_with_vat_in_thb !== null ? number_format($item->unit_price_with_vat_in_thb,3,'.',',') : "--";

        $discount_percent = $item->discount !== null ? number_format($item->discount,3,'.',',') : "--";

        $sub_total_euro = $item->pod_total_unit_price !== null ? number_format($item->pod_total_unit_price,3,'.',',') : "--";

        $sub_total_thb = $item->total_unit_price_in_thb !== null ? number_format($item->total_unit_price_in_thb,3,'.',',') : "--";

        $total_amount_sfter_vat_euro = $item->pod_total_unit_price_with_vat !== null ? number_format($item->pod_total_unit_price_with_vat,3,'.',',') : "--";

        $total_amount_sfter_vat_thb = $item->total_unit_price_with_vat_in_thb !== null ? number_format($item->total_unit_price_with_vat_in_thb,3,'.',',') : "--";

        $conversion_rate = $item->product->unit_conversion_rate != null ? number_format($item->product->unit_conversion_rate,3,'.',',') : "--";

        $u_c_r = $item->product->unit_conversion_rate == 0 ? 1 : $item->product->unit_conversion_rate;
        $qty_into_stock = number_format($item->quantity/$u_c_r,3,'.',',');

        $data_array = [];
        array_push($data_array,$confirm_date);
        array_push($data_array,$supplier);
        array_push($data_array,@$item->PurchaseOrder->PoSupplier->getcountry->name);
        array_push($data_array,$po_no);

        // Sup-1124
        array_push($data_array,$supplier_invoice);
        array_push($data_array,$supplier_invoice_date);
        // End

        array_push($data_array,$pf_no);
        array_push($data_array,$desc);
        array_push($data_array,@$item->product->productCategory->title);
        array_push($data_array,@$item->product->weight);
        array_push($data_array,$product_type);
        if (in_array('product_type_2', $product_detail_section))
        {
            array_push($data_array,$product_type_2);
        }
        if (in_array('product_type_3', $product_detail_section))
        {
            array_push($data_array,$product_type_3);
        }
        array_push($data_array,$billing_unit);
        array_push($data_array,$unit_mes_code);
        array_push($data_array,$minimum_stock);
        array_push($data_array,$sum_qty);

        // Sup-1124
        array_push($data_array,$conversion_rate);
        array_push($data_array,$qty_into_stock);
        // End

        array_push($data_array,$fright);
        array_push($data_array,$landing);
        array_push($data_array,$tax_actual);
        array_push($data_array,$cost_price);
        array_push($data_array,$cost_unit);
        array_push($data_array,$pod_total_unit);
        array_push($data_array,$cst_unt_thb);
        array_push($data_array,$sm_cst_amt);
        array_push($data_array,$vat);

        // Sup-1124
        array_push($data_array,$vat_amount_euro);
        array_push($data_array,$vat_amount_thb);
        array_push($data_array,$unit_price_before_vat_euro);
        array_push($data_array,$unit_price_before_vat_thb);
        array_push($data_array,$unit_price_after_vat_euro);
        array_push($data_array,$unit_price_after_vat_thb);
        array_push($data_array,$discount_percent);
        array_push($data_array,$sub_total_euro);
        array_push($data_array,$sub_total_thb);
        array_push($data_array,$total_amount_sfter_vat_euro);
        array_push($data_array,$total_amount_sfter_vat_thb);
        // END
        // return [
        //     $confirm_date,
        //     $supplier,
        //     $po_no,
        //     $pf_no,
        //     $desc,
        //     $product_type,
        //     $product_type_2,
        //     $product_type_3,
        //     $billing_unit,
        //     $unit_mes_code,
        //     $sum_qty,
        //     $fright,
        //     $landing,
        //     $tax_actual,
        //     $cost_price,
        //     $cost_unit,
        //     $pod_total_unit,
        //     $cst_unt_thb,
        //     $sm_cst_amt,
        //     $vat
        // ];

        return $data_array;

    }

    public function headings(): array
    {
        $global_terminologies = $this->global_terminologies;
        if(!array_key_exists('product_type_2', $global_terminologies))
        {
            $type2 = 'Type 2';
        }
        else
        {
            $type2 = $global_terminologies['product_type_2'];
        }

        if(!array_key_exists('product_type_3', $global_terminologies))
        {
            $type3 = 'Type 3';
        }
        else
        {
            $type3 = $global_terminologies['product_type_3'];
        }

        $product_detail_section = $this->product_detail_section;
        $data_array = [];
        array_push($data_array, 'Confirm Date');
        array_push($data_array,'Supplier');
        array_push($data_array,'Country');
        array_push($data_array,'PO#');
        // Sup-1124
        array_push($data_array,'Supplier invoice#');
        array_push($data_array,'Supplier invoice Date');
        // END
        array_push($data_array,$global_terminologies['our_reference_number']);
        array_push($data_array,$global_terminologies['product_description']);
        array_push($data_array,'Category');
        array_push($data_array,$global_terminologies['avg_units_for-sales']);
        array_push($data_array,$global_terminologies['type']);
        if (in_array('product_type_2', $product_detail_section))
        {
            array_push($data_array,$type2);
        }
        if (in_array('product_type_3', $product_detail_section))
        {
            array_push($data_array,$type3);
        }
        array_push($data_array,'Billing Unit');
        array_push($data_array,$global_terminologies['selling_unit']);
        array_push($data_array,$global_terminologies['minimum_stock']);
        array_push($data_array,'Sum of ' . $global_terminologies['qty']);
        // Sup-1124
        array_push($data_array,'Conversion Rate');
        array_push($data_array,'QTY Into Stock');
        // End

        array_push($data_array,$global_terminologies['freight_per_billed_unit']);
        array_push($data_array,$global_terminologies['landing_per_billed_unit']);
        array_push($data_array,$global_terminologies['import_tax_actual']);
        array_push($data_array,$global_terminologies['cost_price']);
        array_push($data_array,$global_terminologies['product_cost']);
        array_push($data_array,$global_terminologies['sum_pro_cost']);
        array_push($data_array,$global_terminologies['cost_unit_thb']);
        array_push($data_array,$global_terminologies['sum_cost_amnt']);
        array_push($data_array,'Vat');

        // Sup-1124
        array_push($data_array,'VAT Amount (EUR)');
        array_push($data_array,'VAT Amount (THB)');
        array_push($data_array,'Unit Price Before VAT (EUR)');
        array_push($data_array,'Unit Price Before VAT (THB)');
        array_push($data_array,'Unit Price After VAT (EUR)');
        array_push($data_array,'Unit Price After VAT (THB)');
        array_push($data_array,'Discount%');
        array_push($data_array,'Sub Total (EUR)');
        array_push($data_array,'Sub Total (THB)');
        array_push($data_array,'Total Amount After VAT (EUR)');
        array_push($data_array,'Total Amount After VAT (THB)');
        // END

        // return [
        //     'Confirm Date',
        //     'Supplier',
        //     'PO#',
        //     $global_terminologies['our_reference_number'],
        //     $global_terminologies['product_description'],
        //     $global_terminologies['type'],
        //     $type2,
        //     $type3,
        //     'Billing Unit',
        //     $global_terminologies['selling_unit'],
        //     'Sum of ' . $global_terminologies['qty'],
        //     $global_terminologies['freight_per_billed_unit'],
        //     $global_terminologies['landing_per_billed_unit'],
        //     $global_terminologies['import_tax_actual'],
        //     $global_terminologies['cost_price'],
        //     $global_terminologies['product_cost'],
        //     $global_terminologies['sum_pro_cost'],
        //     $global_terminologies['cost_unit_thb'],
        //     $global_terminologies['sum_cost_amnt'],
        //     'Vat'
        // ];
        return $data_array;
    }

    // public function view(): View
    // {
    //     $query = $this->query;
    //     $global_terminologies = $this->global_terminologies;
    //     return view('users.exports.purchasing-report-exp', ['query' => $query, 'global_terminologies' => $global_terminologies]);
    // }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getStyle('A1:AJ1')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

            },
        ];
    }
}
