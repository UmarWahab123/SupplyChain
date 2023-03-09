<?php

namespace App\Imports;

use Auth;
use App\Models\Common\State;
use App\Models\Common\Country;
use App\Models\Common\Currency;
use App\Models\Common\PaymentTerm;
use App\Models\Common\TempSupplier;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SupplierBulkImport implements ToCollection, WithStartRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function collection(Collection $rows)
    {
        $country_id      = null;
        $state_id        = null;
        $currency_id     = null;
        $payment_term_id = null;
        $status = 1;

        if($rows[0][0] == 'Supplier Bulk Import') {
        if ($rows->count() > 1) {

            $row1 = $rows->toArray();
            $remove = array_shift($row1);
            foreach ($row1 as $row) {
                if ($row[1] == null) { //ok
                    $status = 0;
                }

                if ($row[2] == null) { //ok
                    $status = 0;
                }

                if ($row[5] == null) { //ok
                    $status = 0;
                }

                if ($row[7] != null) {
                    $country_id = Country::where('name', $row[7])->pluck('id')->first();
                    if ($country_id == null) {
                        $country_id = $row[7];
                        $status = 0 ;
                    }
                }

                if ($row[8] != null) {
                    $state_id = State::where('name', $row[8])->pluck('id')->first();
                    if ($state_id == null) {
                        $state_id = $row[8];
                    }
                }

                if ($row[9] != null) { //ok
                    $city_id = $row[9];
                }

                if ($row[10] == null) { //ok
                    $status = 0;
                }

                if ($row[11] != null) {
                    $currency_id = Currency::where('currency_name', $row[11])->pluck('id')->first();
                    if ($currency_id == null) {
                        $currency_id = $row[11];
                        $status = 0;
                    }
                }

                if ($row[12] != null) {
                    $payment_term_id = PaymentTerm::where('title', $row[12])->pluck('id')->first();
                    if ($payment_term_id == null) {
                        $payment_term_id = $row[12];
                    }
                }

            TempSupplier::create([
              'reference_name'    => $row[1],
              'company'           => $row[2],
              'first_name'           => '',
              'last_name'           => '',
              'email'             => $row[3],
              'phone'             => $row[4],
              'address_line_1'    => $row[5],
              'tax_id'            => $row[6],
              'country'           => $country_id,
              'state'             => $state_id,
              'city'              => $city_id,
              'postalcode'        => $row[10],
              'currency_id'       => $currency_id,
              'credit_term'       => $payment_term_id,
              'status'            => $status,
              'c_name'            => $row[13],
              'c_sur_name'        => $row[14],
              'c_email'           => $row[15],
              'c_telehone_number' => $row[16],
              'c_position'        => $row[17],
            ]);

          }

          return redirect()->route('bulk-upload-suppliers-form')->with('successmsg','Suppliers Created Successfully');


        }
        else{
          return redirect()->route('bulk-upload-suppliers-form')->with('successmsg','File is Empty Please Upload Valid File!');
        }

    } else {
        return redirect()->route('bulk-upload-suppliers-form')->with('successmsg','Invalid file');
    }
    }

    public function startRow():int
    {
      return 1;
    }
}
