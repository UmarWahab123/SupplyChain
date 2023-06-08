<?php
namespace App\Exports;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Carbon\Carbon;

class MarginReportBySpoilageExport implements  FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $query;

    public function __construct($query,$request)
	{
		$this->query = $query;
        $this->request = $request;
	}


    public function query()
    {
        return $this->query;
    }

    public function map($item): array
    {
        $data_array = [];
        // Modify the logic here to match your column names and data retrieval
        $data_array[] = @$item->reference_code != null ? @$item->reference_code : '--';
        $data_array[] = @$item->default_supplier != null ? @$item->default_supplier : '--';
        $data_array[] = @$item->customer != null ? @$item->customer : '--';
        $data_array[] = @$item->quantity != null ? abs(@$item->quantity) : '--';
        $data_array[] = @$item->unit_cogs != null ? @$item->unit_cogs : '--';
        $data_array[] = @$item->cogs_total != null ? abs(@$item->cogs_total) : '--';

        return $data_array;
    }

    public function headings(): array
    {
        // Define the column headings for the Excel file
        return [
            'Refrence Code',
            'Default Supplier',
            'Customer',
            'Quantity',
            'Unit Cogs',
            'Cogs Total',
        ];
    }
}
