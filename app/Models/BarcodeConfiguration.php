<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarcodeConfiguration extends Model
{
    protected $table = 'barcode_configurations';
    protected $primaryKey = 'id';
    protected $fillable = ['barcode_columns','height','width'];
}
