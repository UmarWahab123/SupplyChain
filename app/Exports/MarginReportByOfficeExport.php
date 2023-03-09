<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
// use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Helpers\MarginReportHelper;

class MarginReportByOfficeExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
	protected $query = null;
    protected $not_visible_arr = null;
    protected $global_terminologies = null;
    protected $request = null;
	public function __construct($query, $not_visible_arr, $global_terminologies, $request)
	{
		$this->query = $query;
		$this->not_visible_arr = $not_visible_arr;
		$this->global_terminologies = $global_terminologies;
        $this->request = $request;
	}


    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function map($item) : array {
        $not_visible_arr = $this->not_visible_arr;
        $global_terminologies = $this->global_terminologies;
        $request = $this->request;
        $data_array = [];

        $query = $this->query;

        $query = (clone $query)->get();
        $total_items_sales = $query->sum('sales');
        $total_items_cogs  = $query->sum('products_total_cost');
        $total_items_gp    = $total_items_sales - $total_items_cogs;


        $sales = $item->sales;
        $cogs = $item->products_total_cost;
        $adjustment_out = 0;
        $total = $sales - $cogs;
        $formated = $total;
        if($total_items_gp !== 0)
        {
          $formated = number_format(($formated/$total_items_gp)*100,2,'.','');
        }
        $margin = 0;
        if($sales != 0)
        {
          $margin = ($sales - $cogs - abs($adjustment_out)) / $sales;
          // $total = $item->marg;
        }
        else
        {
          $margin = 0;
        }
        if($margin == 0)
        {
          $margin = "00";
        }
        else
        {
          $margin = number_format($margin * 100,2);
        }

        $vat_out = $item->vat_amount_total != null ? number_format($item->vat_amount_total,2,'.','') : '--';
        $percent_sales = ($total_items_sales != 0) ? number_format($sales / $total_items_sales * 100,2,'.','') : 0;
        if ($request['filter'] == 'office' || $request['filter'] == 'product_type_2' || $request['filter'] == 'product_type_3') {
            $vat_in = $item->import_vat_amount != null ? number_format($item->import_vat_amount,2,'.','') : '--';
        }
        else{
            $vat_in = $item->vat_in != null ? round($item->vat_in,2) : '--';
        }
        $percent_gp = number_format($sales - $cogs,2,'.','');

        if($request['filter'] == 'customer'){
            if(!in_array('0',$not_visible_arr)){
                array_push($data_array, $item->reference_number);
            }
            if(!in_array('1',$not_visible_arr)){
                array_push($data_array, $item->reference_name);
            }

            if(!in_array('2',$not_visible_arr)){
                array_push($data_array, $vat_out);
            }

            if(!in_array('3',$not_visible_arr)){
                array_push($data_array, $sales);
            }

            if(!in_array('4',$not_visible_arr)){
                array_push($data_array, $percent_sales . '%');
            }

            if(!in_array('5',$not_visible_arr)){
                array_push($data_array, $vat_in);
            }

            if(!in_array('6',$not_visible_arr)){
                array_push($data_array, number_format($cogs,2,'.',''));
            }

            if(!in_array('7',$not_visible_arr)){
                array_push($data_array, $percent_gp);
            }

            if(!in_array('8',$not_visible_arr)){
                array_push($data_array, $formated . '%');
            }

            if(!in_array('9',$not_visible_arr)){
                array_push($data_array, $margin . '%');
            }
        }
        else if($request['filter'] == 'product'){
            if(!in_array('0',$not_visible_arr)){
                array_push($data_array, MarginReportHelper::getFirstColumnData($item, $request['filter']));
            }
            if(!in_array('1',$not_visible_arr)){
                array_push($data_array, $item->short_desc);
            }

            if(!in_array('2',$not_visible_arr)){
                array_push($data_array, @$item->def_or_last_supplier->reference_name);
            }
            if(!in_array('3',$not_visible_arr)){
                array_push($data_array, $vat_out);
            }

            if(!in_array('4',$not_visible_arr)){
                array_push($data_array, $sales);
            }

            if(!in_array('5',$not_visible_arr)){
                array_push($data_array, $percent_sales . '%');
            }

            if(!in_array('6',$not_visible_arr)){
                array_push($data_array, $vat_in);
            }

            if(!in_array('7',$not_visible_arr)){
                $unit_cogs = $item->qty != null && $item->qty != 0 && $cogs != null ? number_format(($cogs / $item->qty),4,'.','') : '--';
                array_push($data_array, $unit_cogs);
            }

            if(!in_array('8',$not_visible_arr)){
                array_push($data_array, number_format($item->qty,2,'.',''));
            }

            if(!in_array('9',$not_visible_arr)){
                array_push($data_array, number_format($cogs,2,'.',''));
            }

            if(!in_array('10',$not_visible_arr)){
                array_push($data_array, MarginReportHelper::getGPData($request['filter'], $sales, $cogs, $adjustment_out));
            }

            if(!in_array('11',$not_visible_arr)){
                array_push($data_array, $formated . '%');
            }

            if(!in_array('12',$not_visible_arr)){
                array_push($data_array, $margin . '%');
            }
        }
        else{
            if(!in_array('0',$not_visible_arr)){
                array_push($data_array, MarginReportHelper::getFirstColumnData($item, $request['filter']));
            }
            if(!in_array('1',$not_visible_arr)){
                array_push($data_array, $vat_out);
            }

            if(!in_array('2',$not_visible_arr)){
                array_push($data_array, $sales);
            }

            if(!in_array('3',$not_visible_arr)){
                array_push($data_array, $percent_sales . '%');
            }

            if(!in_array('4',$not_visible_arr)){
                array_push($data_array, $vat_in);
            }

            if(!in_array('5',$not_visible_arr)){
                array_push($data_array, number_format($cogs,2,'.',''));
            }

            if(!in_array('6',$not_visible_arr)){
                array_push($data_array, MarginReportHelper::getGPData($request['filter'], $sales, $cogs, $adjustment_out));
            }

            if(!in_array('7',$not_visible_arr)){
                array_push($data_array, $formated . '%');
            }

            if(!in_array('8',$not_visible_arr)){
                array_push($data_array, $margin . '%');
            }
        }
        return $data_array;
    }

    public function headings(): array {
        $not_visible_arr = $this->not_visible_arr;
        $global_terminologies = $this->global_terminologies;
        $request = $this->request;

        $headding_array = [];

        if(!in_array('0',$not_visible_arr)){
            if($request['filter'] == 'product'){
                array_push($headding_array, 'PF#');
            }
            elseif($request['filter'] == 'sales'){
                array_push($headding_array, 'Sales Person');
            }
            elseif($request['filter'] == 'office'){
                array_push($headding_array, 'Office');
            }
            elseif($request['filter'] == 'product_category'){
                array_push($headding_array, 'Product Category');
            }
            elseif($request['filter'] == 'customer'){
                array_push($headding_array, 'Customer Ref #');
            }
            elseif($request['filter'] == 'customer_type'){
                array_push($headding_array, 'Customer Types');
            }
            elseif($request['filter'] == 'product_type'){
                array_push($headding_array, 'Product Type');
            }
            elseif($request['filter'] == 'product_type 2'){
                if(!array_key_exists('product_type_2', $global_terminologies)){
                    array_push($headding_array, 'Type 2');
                }
                else{
                    array_push($headding_array, $global_terminologies['product_type_2']);
                }
            }
            elseif($request['filter'] == 'product_type 3'){
                if(!array_key_exists('product_type_3', $global_terminologies)){
                    array_push($headding_array, 'Type 3');
                }
                else{
                    array_push($headding_array, $global_terminologies['product_type_3']);
                }
            }
            elseif($request['filter'] == 'supplier'){
                array_push($headding_array, 'Supplier');
            }
        }
        if($request['filter'] == 'customer'){
            if(!in_array('1',$not_visible_arr)){
                array_push($headding_array, 'Customers');
            }

            if(!in_array('2',$not_visible_arr)){
                array_push($headding_array, 'VAT Out');
            }

            if(!in_array('3',$not_visible_arr)){
                array_push($headding_array, 'Sales');
            }

            if(!in_array('4',$not_visible_arr)){
                array_push($headding_array, '% Sales');
            }

            if(!in_array('5',$not_visible_arr)){
                array_push($headding_array, 'VAT In');
            }

            if(!in_array('6',$not_visible_arr)){
                array_push($headding_array, $global_terminologies['net_price']);
            }

            if(!in_array('7',$not_visible_arr)){
                array_push($headding_array, 'GP');
            }

            if(!in_array('8',$not_visible_arr)){
                array_push($headding_array, '% GP');
            }

            if(!in_array('9',$not_visible_arr)){
                array_push($headding_array, 'Margin');
            }
        }
        else if($request['filter'] == 'product'){
            if(!in_array('1',$not_visible_arr)){
                array_push($headding_array, 'Description');
            }

            if(!in_array('2',$not_visible_arr)){
                array_push($headding_array, 'Default/Last Supplier');
            }
            if(!in_array('3',$not_visible_arr)){
                array_push($headding_array, 'VAT Out');
            }

            if(!in_array('4',$not_visible_arr)){
                array_push($headding_array, 'Sales');
            }

            if(!in_array('5',$not_visible_arr)){
                array_push($headding_array, '% Sales');
            }

            if(!in_array('6',$not_visible_arr)){
                array_push($headding_array, 'VAT In');
            }

            if(!in_array('7',$not_visible_arr)){
                array_push($headding_array, 'Unit ' . $global_terminologies['net_price']);
            }

            if(!in_array('8',$not_visible_arr)){
                array_push($headding_array, $global_terminologies['qty']);
            }

            if(!in_array('9',$not_visible_arr)){
                array_push($headding_array, $global_terminologies['net_price']);
            }

            if(!in_array('10',$not_visible_arr)){
                array_push($headding_array, 'GP');
            }

            if(!in_array('11',$not_visible_arr)){
                array_push($headding_array, '% GP');
            }

            if(!in_array('12',$not_visible_arr)){
                array_push($headding_array, 'Margin');
            }
        }
        else{
            if(!in_array('1',$not_visible_arr)){
                array_push($headding_array, 'VAT Out');
            }

            if(!in_array('2',$not_visible_arr)){
                array_push($headding_array, 'Sales');
            }

            if(!in_array('3',$not_visible_arr)){
                array_push($headding_array, '% Sales');
            }

            if(!in_array('4',$not_visible_arr)){
                array_push($headding_array, 'VAT In');
            }

            if(!in_array('5',$not_visible_arr)){
                array_push($headding_array, $global_terminologies['net_price']);
            }

            if(!in_array('6',$not_visible_arr)){
                array_push($headding_array, 'GP');
            }

            if(!in_array('7',$not_visible_arr)){
                array_push($headding_array, '% GP');
            }

            if(!in_array('8',$not_visible_arr)){
                array_push($headding_array, 'Margin');
            }
        }
        return $headding_array;
    }

    // public function view(): View
    // {
    //     $records = $this->records;
    //     $not_visible_arr = $this->not_visible_arr;
    //     $global_terminologies = $this->global_terminologies;
    //     $request['filter'] = $this->request['filter'];

    //     return view('users.exports.margin-report-by-office-export', ['records' => $records,'not_visible_arr' => $not_visible_arr ,'global_terminologies' => $global_terminologies, 'request['filter']' => $request['filter']]);
    // }


}
