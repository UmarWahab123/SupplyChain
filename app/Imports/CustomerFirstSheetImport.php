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
use Maatwebsite\Excel\Concerns\WithValidation;

class CustomerFirstSheetImport implements ToCollection, WithStartRow
{

    /**
     * @param Collection $collection
     */

    public function collection(Collection $rows)
    {
        TempCustomers::truncate();
        $status = 1;
        // dd($rows);

        if ($rows[0][0] == 'Customer Bulk Import') {
            if ($rows->count() > 1) {

                $row1 = $rows->toArray();
                $remove = array_shift($row1);

                foreach ($row1 as $row) {

                    // if (trim($row[0]) != null) {
                    //     $reference_number = Customer::where('reference_number', $row[1])->first();
                    // }

                    if (trim($row[6]) != null) {
                        $customer_category = CustomerCategory::where('is_deleted', 0)->where('title', $row[6])->first();
                        if ($customer_category == null) {
                            $status = 0;
                        }
                    }

                    if (trim($row[3]) != null) {
                        $user = User::where('name', $row[3])->first();
                        if ($user == null) {
                            $status = 0;
                        }
                    }

                    if (trim($row[8]) != null) {
                        $payment_methods = explode(',', preg_replace('/\s+/', '', $row[8]));
                        foreach ($payment_methods as $payment_meth) {
                            $payment_type = PaymentType::where('title', $payment_meth)->first();
                            // if($payment_type == null)
                            // {
                            //   $status = 0 ;
                            // }
                        }
                    }

                    // if($row[7] != null)
                    // {
                    //     $address_reference_name = CustomerBillingDetail::where('title',$row[7])->first();

                    //     if($address_reference_name == null)
                    //     {
                    //       $status = 0 ;
                    //     }
                    // }

                    if (trim($row[7]) != null) {

                        $credit_term = PaymentTerm::where('title', $row[7])->first();
                        // if($credit_term == null)
                        // {
                        //   $status = 0 ;
                        // }
                    }
                    if (trim($row[3]) != null && trim($row[6]) != null) {
                        TempCustomers::create([
                            'reference_number'    => $row[1],
                            'reference_name'    => $row[2],
                            'sales_person'    => $row[3],
                            'secondary_sale'    => $row[4],
                            'company_name'    => $row[5],
                            'classification'    => $row[6],
                            'credit_term'    => $row[7],
                            'payment_method'    => $row[8],
                            'address_reference_name'    => $row[9],
                            'phone_no'    => $row[10],
                            'cell_no'    => $row[11],
                            'address'    => $row[12],
                            'tax_id'    => $row[13],
                            'email'    => $row[14],
                            'fax'    => $row[15],
                            'state'    => $row[16],
                            'city'    => $row[17],
                            'zip'    => $row[18],
                            'contact_name'    => $row[19],
                            'contact_sur_name'    => @$row[20],
                            'contact_email'    => @$row[21],
                            'contact_tel'    => @$row[22],
                            'contact_position'    => @$row[23],
                            'status'    => $status,

                        ]);
                    }
                }
            } else {
                return redirect()->route('bulk-upload-customer-form')->with('successmsg', 'File is Empty Please Upload Valid File!');
            }
        } else {
            return redirect()->route('bulk-upload-customer-form')->with('successmsg', 'Invalid File !');
        }
    }

    public function startRow(): int
    {
        return 1;
    }
}
