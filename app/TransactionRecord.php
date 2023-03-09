<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionRecord extends Model
{
  protected $fillable=['order_id','payment_reference','received_date','invoice_number','reference_name','billing_name','delivery_date','invoice_total','total_paid_vat','total_paid_non_vat','total_paid','difference','payment_method','sale_person'];

 

}
