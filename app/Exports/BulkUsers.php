<?php

namespace App\Exports;

use App\Models\Common\Product;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\Common\CustomerCategory;
use App\QuotationConfig;
use Illuminate\Foundation\Auth\User;

// FromView, ShouldAutoSize, WithEvents
class BulkUsers implements FromView,ShouldAutoSize
{
     public function view(): View
     {
        return view('users.exports.bulkUsers');
     }
}