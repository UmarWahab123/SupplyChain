<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarginReportRecords extends Model
{
    protected $fillable = ['product_id', 'vat_out', 'sales', 'percent_sales', 'vat_in', 'cogs', 'gp', 'percent_gp', 'margins'];
}
