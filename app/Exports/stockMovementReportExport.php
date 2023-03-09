<?php

namespace App\Exports;

// use Illuminate\Contracts\View\View;
// use Maatwebsite\Excel\Concerns\FromCollection;
// use Maatwebsite\Excel\Concerns\FromView;
use App\Models\Common\StockManagementOut;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;

class stockMovementReportExport implements ShouldAutoSize, WithEvents, FromQuery, WithHeadings, WithMapping
{
	protected $query = null;
	protected $global_terminologies = null;
    protected $role_id = null;
    protected $column_visiblity = null;
    protected $from_date = null;
    protected $product_detail_section = null;
    protected $warehouse_id = null;

    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($query,$global_terminologies, $column_visiblity,$role_id,$from_date,$product_detail_section, $warehouse_id)
    {
        $this->query = $query;
        $this->global_terminologies = $global_terminologies;
        $this->role_id = $role_id;
        $this->column_visiblity = $column_visiblity;
        $this->from_date = $from_date;
        $this->product_detail_section = $product_detail_section;
    }

    // public function view(): View
    // {

    // 	$query = $this->query;
    // 	$global_terminologies = $this->global_terminologies;
    //     $role_id = $this->role_id;
    //     $column_visiblity = $this->column_visiblity;

    // 	// dd($query);
    //     return view('users.exports.stock-movement-report', ['query' => $query,'global_terminologies'=>$global_terminologies, 'column_visiblity' => $column_visiblity, 'role_id' => $role_id]);
    // }

    public function query()
    {
        $query = $this->query;
        return $query;
    }

    public function map($item) : array
    {
        $role_id = $this->role_id;
        $column_visiblity = $this->column_visiblity;
        $from_date = $this->from_date;
        $product_detail_section = $this->product_detail_section;
        $warehouse_id = $this->warehouse_id;
        $data_array = [];

        $stock_query = StockManagementOut::where('product_id', $item->id);
        if($warehouse_id != null && $warehouse_id != ''){
            $stock_query = $stock_query->where('warehouse_id', $warehouse_id);
        }
        $Start_count_out = (clone $stock_query)->where('created_at', '<', $from_date)->sum('quantity_out');

    
        $Start_count_in = (clone $stock_query)->where('created_at', '<', $from_date)->sum('quantity_in');

        if(!in_array('0', $column_visiblity))
        {
            $data = $item->refrence_code != null ? $item->refrence_code : 'N.A';
            array_push($data_array, $data);
        }

        if(!in_array('1', $column_visiblity))
        {
            $data = $item->short_desc !== null ? $item->short_desc : 'N.A';
            array_push($data_array, $data);
        }

        if(!in_array('2', $column_visiblity))
        {
            $data = $item->brand != null ? $item->brand : '--';
            array_push($data_array, $data);
        }

        if(!in_array('3', $column_visiblity))
        {
            $data = $item->productType != null ? $item->productType->title : '--';
            array_push($data_array, $data);
        }

        if (in_array('product_type_2', $product_detail_section))
        {
            if(!in_array('4', $column_visiblity))
            {
                $data = $item->productType2 != null ? $item->productType2->title : '--';
                array_push($data_array, $data);
            }
        }

        if (in_array('product_type_3', $product_detail_section))
        {
            if(!in_array('5', $column_visiblity))
            {
                $data = $item->productType3 != null ? $item->productType3->title : '--';
                array_push($data_array, $data);
            }
        }

        if(!in_array('6', $column_visiblity))
        {
            $data = round($item->min_stock,2);
            array_push($data_array, $data);
        }

        if(!in_array('7', $column_visiblity))
        {
            $data = $item->selling_unit != null ? $item->sellingUnits->title : '--';
            array_push($data_array, $data);
        }

        if(!in_array('8', $column_visiblity))
        {
            // $Start_count_out = $item->stock_out();
            //     if($warehouse_id != null && $warehouse_id != ''){
            //         $Start_count_out = $Start_count_out->where('warehouse_id', $warehouse_id);
            //     }
            //     $Start_count_out = $Start_count_out->whereDate('created_at', '<', $from_date)->sum('quantity_out');

            //     $Start_count_in = $item->stock_out();
            //     if($warehouse_id != null && $warehouse_id != ''){
            //         $Start_count_in = $Start_count_in->where('warehouse_id', $warehouse_id);
            //     }
            //     $Start_count_in = $Start_count_in->where('warehouse_id', $warehouse_id)->whereDate('created_at', '<', $from_date)->sum('quantity_in');
            // return 0;
            $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
            $data = round($Start_count_out+$Start_count_in,$decimal_places);
            array_push($data_array, $data);
        }

        if(!in_array('9', $column_visiblity))
        {
            $data = round($item->stock_out->where('po_group_id','!=',Null)->sum('quantity_in'),2);
            array_push($data_array, $data);
        }

        if(!in_array('10', $column_visiblity))
        {
            $data = $item->stock_out->where('title','!=',Null)->where('title','!=','TD')->where('quantity_out',NULL)->sum('quantity_in');
            $data = round($data,2);
            array_push($data_array, $data);
        }

        if(!in_array('11', $column_visiblity))
        {
            $data = $item->stock_out->where('title','!=',Null)->where('title','TD')->where('quantity_out',NULL)->sum('quantity_in');
            $data = round($data,2);
            array_push($data_array, $data);
        }

        if(!in_array('12', $column_visiblity))
        {
            $data = $item->stock_out->where('order_id','!=',Null)->where('quantity_out',NULL)->sum('quantity_in');
            $data = round($data,2);
            array_push($data_array, $data);
        }

        if(!in_array('13', $column_visiblity))
        {
            $INS = $item->stock_out->sum('quantity_in');
            $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
            $data = round($INS, $decimal_places);
            array_push($data_array, $data);
        }

        if(!in_array('14', $column_visiblity))
        {
            $data = $item->stock_out->where('order_id','!=',Null)->where('quantity_in',NULL)->sum('quantity_out');
            $data = round($data,2);
            array_push($data_array, $data);
        }

        if(!in_array('15', $column_visiblity))
        {
            $data = $item->stock_out->where('title','!=',Null)->where('title','!=','TD')->where('quantity_in',NULL)->sum('quantity_out');
            $data = round($data,2);
            array_push($data_array, $data);
        }

        if(!in_array('16', $column_visiblity))
        {
            $data = $item->stock_out->where('title','!=',Null)->where('title','TD')->where('quantity_in',NULL)->sum('quantity_out');
            $data = round($data,2);
            array_push($data_array, $data);
        }

        if(!in_array('17', $column_visiblity))
        {
            $OUTs = $item->stock_out->sum('quantity_out');
            $decimal_places = $item->sellingUnits != null ? $item->sellingUnits->decimal_places : 3;
            $data = round($OUTs,$decimal_places);
            array_push($data_array, $data);
        }

        if(!in_array('18', $column_visiblity))
        {
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
            $data = round($Start_count_out+$Start_count_in+$INS+$OUTs,$decimal_places);
            array_push($data_array, $data);
        }

        if($role_id == 1 || $role_id == 2 || $role_id == 7 || $role_id == 11 && !in_array('19', $column_visiblity))
        {
            $unit_conversion_rate = ($item->unit_conversion_rate != null) ? $item->unit_conversion_rate : 1;
            $cogs = $item->total_buy_unit_cost_price * $unit_conversion_rate;
            $data = round($cogs,3);
            array_push($data_array, $data);
        }
        return $data_array;
    }

    public function headings(): array
    {
        $global_terminologies = $this->global_terminologies;
        $role_id = $this->role_id;
        $column_visiblity = $this->column_visiblity;
        $product_detail_section = $this->product_detail_section;
        $heading_array = [];

        if(!in_array('0', $column_visiblity))
        {
            array_push($heading_array, 'PF#');
        }

        if(!in_array('1', $column_visiblity))
        {
            array_push($heading_array, 'Description');
        }

        if(!in_array('2', $column_visiblity))
        {
            array_push($heading_array, $global_terminologies['brand']);
        }

        if(!in_array('3', $column_visiblity))
        {
            array_push($heading_array, $global_terminologies['type']);
        }

        if (in_array('product_type_2', $product_detail_section))
        {
            if(!in_array('4', $column_visiblity))
            {
                if(!array_key_exists('product_type_2', $global_terminologies))
                {
                    array_push($heading_array, 'Type 2');
                }
                else
                {
                    array_push($heading_array, $global_terminologies['product_type_2']);
                }
            }
        }

        if (in_array('product_type_3', $product_detail_section))
        {
            if(!in_array('5', $column_visiblity))
            {
                if(!array_key_exists('product_type_3', $global_terminologies))
                {
                    array_push($heading_array, 'Type 3');
                }
                else
                {
                    array_push($heading_array, $global_terminologies['product_type_3']);
                }
            }
        }

        if(!in_array('6', $column_visiblity))
        {
            array_push($heading_array, 'Minimum Stock');
        }

        if(!in_array('7', $column_visiblity))
        {
            array_push($heading_array, 'Unit');
        }

        if(!in_array('8', $column_visiblity))
        {
            array_push($heading_array, 'Start Count');
        }

        if(!in_array('9', $column_visiblity))
        {
            array_push($heading_array, 'In(From Purchase)');
        }

        if(!in_array('10', $column_visiblity))
        {
            array_push($heading_array, 'In(Manual Adjustment)');
        }

        if(!in_array('11', $column_visiblity))
        {
            array_push($heading_array, 'In(Transfer Document)');
        }

        if(!in_array('12', $column_visiblity))
        {
            array_push($heading_array, 'In(Order Update)');
        }

        if(!in_array('13', $column_visiblity))
        {
            array_push($heading_array, 'IN(Total)');
        }

        if(!in_array('14', $column_visiblity))
        {
            array_push($heading_array, 'Out(Order)');
        }

        if(!in_array('15', $column_visiblity))
        {
            array_push($heading_array, 'Out(Manual Adjustment)');
        }

        if(!in_array('16', $column_visiblity))
        {
            array_push($heading_array, 'Out(Transfer Document)');
        }

        if(!in_array('17', $column_visiblity))
        {
            array_push($heading_array, 'OUT(Total)');
        }

        if(!in_array('18', $column_visiblity))
        {
            array_push($heading_array, 'Balance');
        }

        if($role_id == 1 || $role_id == 2 || $role_id == 7 || $role_id == 11 && !in_array('19', $column_visiblity))
        {
            array_push($heading_array, 'COGS');
        }
        return $heading_array;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // $event->sheet->getStyle('A1:V1')->applyFromArray([
                //     'font' => [
                //         'bold' => true
                //     ]
                // ]);

            },
        ];
    }
}
