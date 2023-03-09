<?php

namespace App\Imports;

use Auth;
use App\ProductHistory;
use App\Models\Common\Product;
use App\Jobs\UserBulkImportJob;
use App\Models\Common\Supplier;
use Illuminate\Support\Collection;
use App\Models\Common\SupplierProducts;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Jobs\SupplierBulkPricesImportJob;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

// ToCollection ,WithStartRow, WithHeadingRow
class UserBulkImport implements  ToCollection,WithHeadingRow
{

    public function __construct($user_id)
    {
        $this->user_id = $user_id;
    }
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $user_id  = $this->user_id;
        $result = UserBulkImportJob::dispatch($rows,$user_id);
    }
}