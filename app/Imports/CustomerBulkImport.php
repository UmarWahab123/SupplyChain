<?php

namespace App\Imports;
use Auth;
use App\Models\Common\State;
use App\Models\Common\Country;
use App\Models\Common\Currency;
use App\Models\Common\PaymentTerm;
use App\Models\Common\TempCustomers;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use App\Models\Sales\Customer;
use App\Models\Common\CustomerCategory;
use App\Models\Common\PaymentType;
use App\Models\Common\Order\CustomerBillingDetail;
use App\User;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CustomerBulkImport implements WithMultipleSheets
{
    /**
    * @param Collection $collection
    */

    public function sheets(): array
    {
        return [
            0 => new CustomerFirstSheetImport(),
        ];
    }
}
