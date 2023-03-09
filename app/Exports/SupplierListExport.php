<?php

namespace App\Exports;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Helpers\MarginReportHelper;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;

class SupplierListExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $query = null;
    protected $global_terminologies = null;
    protected $request = null;
    protected $not_visible_arr = null;

	public function __construct($query, $global_terminologies, $request, $not_visible_arr)
	{
		$this->query = $query;
		$this->global_terminologies = $global_terminologies;
        $this->request = $request;
        $this->not_visible_arr = $not_visible_arr;
	}


    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function map($item) : array {

        $not_visible_arr = $this->not_visible_arr;
        $data_array = [];

        $last_order = $item->supplier_po->first();
        $last_order_date = @$last_order->confirm_date != null ? Carbon::parse(@$last_order->confirm_date)->format('d/m/Y') : '--';
        if ($item->status == 1){
            $status = 'Completed';
        }
        elseif ($item->status == 2){
            $status = 'Suspended';
        }
        else{
            $status = 'Incomplete';
        }

        if (!in_array(1, $not_visible_arr)) {
            $data = $item->reference_number != null ? $item->reference_number : '--';
            array_push($data_array, $data);
        }
        if (!in_array(2, $not_visible_arr)) {
            $data = $item->reference_name != null ? $item->reference_name : '--';
            array_push($data_array, $data);
        }
        if (!in_array(3, $not_visible_arr)) {
            $data = $item->company !== null ? $item->company : '--';
            array_push($data_array, $data);
        }
        if (!in_array(4, $not_visible_arr)) {
            $data = $item->country !== null && $item->getcountry !== null ? $item->getcountry->name : '--';
            array_push($data_array, $data);
        }
        if (!in_array(5, $not_visible_arr)) {
            $data = $item->address_line_1 !== null ? $item->address_line_1 . ' ' . $item->address_line_2 : '--';
            array_push($data_array, $data);
        }
        if (!in_array(6, $not_visible_arr)) {
            $data = $item->state !== null && $item->getstate !== null ? $item->getstate->name : '--';
            array_push($data_array, $data);
        }
        if (!in_array(7, $not_visible_arr)) {
            $data = $item->postalcode !== null ? $item->postalcode : '--';
            array_push($data_array, $data);
        }
        if (!in_array(8, $not_visible_arr)) {
            $data = $item->tax_id !== null ? $item->tax_id : '--';
            array_push($data_array, $data);
        }
        if (!in_array(9, $not_visible_arr)) {
            $data = $item->created_at !== null ? Carbon::parse($item->created_at)->format('d/m/Y') : '--';
            array_push($data_array, $data);
        }
        if (!in_array(10, $not_visible_arr)) {
            $data = $item->supplier_po->where('status', 12)->count();
            array_push($data_array, $data);
        }
        if (!in_array(11, $not_visible_arr)) {
            $data = $item->supplier_po->count();
            array_push($data_array, $data);
        }
        if (!in_array(12, $not_visible_arr)) {
            $data = $last_order_date;
            array_push($data_array, $data);
        }
        if (!in_array(13, $not_visible_arr)) {
            $data = $item->getnotes->count() > 0 ? $item->getnotes->first()->note_description : '--';
            array_push($data_array, $data);
        }
        if (!in_array(14, $not_visible_arr)) {
            $data = $status;
            array_push($data_array, $data);
        }
        return $data_array;
    }

    public function headings(): array {
        $global_terminologies = $this->global_terminologies;
        $not_visible_arr = $this->not_visible_arr;
        $headding_array = [];

        if (!in_array(1, $not_visible_arr)){
            array_push($headding_array, $global_terminologies['supplier']);
        }
        if (!in_array(2, $not_visible_arr)){
            array_push($headding_array, 'Reference Name');
        }
        if (!in_array(3, $not_visible_arr)){
            array_push($headding_array, $global_terminologies['company_name']);
        }
        if (!in_array(4, $not_visible_arr)){
            array_push($headding_array, 'Country');
        }
        if (!in_array(5, $not_visible_arr)){
            array_push($headding_array, 'Address');
        }
        if (!in_array(6, $not_visible_arr)){
            array_push($headding_array, 'District');
        }
        if (!in_array(7, $not_visible_arr)){
            array_push($headding_array, 'Zip Code');
        }
        if (!in_array(8, $not_visible_arr)){
            array_push($headding_array, 'Tax ID');
        }
        if (!in_array(9, $not_visible_arr)){
            array_push($headding_array, 'Supplier Since');
        }
        if (!in_array(10, $not_visible_arr)){
            array_push($headding_array, $global_terminologies['open_pos']);
        }
        if (!in_array(11, $not_visible_arr)){
            array_push($headding_array, $global_terminologies['total_pos']);
        }
        if (!in_array(12, $not_visible_arr)){
            array_push($headding_array, 'Last Order Date');
        }
        if (!in_array(13, $not_visible_arr)){
            array_push($headding_array, $global_terminologies['note_two']);
        }
        if (!in_array(14, $not_visible_arr)){
            array_push($headding_array, 'Status');
        }

        return $headding_array;
    }
}
