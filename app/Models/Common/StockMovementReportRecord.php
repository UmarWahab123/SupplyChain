<?php

namespace App\Models\Common;

use Illuminate\Database\Eloquent\Model;

class StockMovementReportRecord extends Model
{
	protected $table = 'stock_movement_report_records';
    protected $fillable = ['reference_code','short_desc','brand','selling_unit','start_count','stock_in','stock_out','stock_balance','min_stock','type','type_2'];
}
