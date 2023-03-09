<?php

namespace App\Services;

use Barryvdh\DomPDF\PDF;
use Milon\Barcode\DNS1D;
use Illuminate\Http\Request;
use App\Models\BarcodeConfiguration;

class BarcodeService
{
    public function saveBarcodeConfiguration($request)
    {
        try
        {
            if($request->height_width=='custom_size')
            {
                $height_width_new = $request->height_width_input;
            }
            else
            {
                $height_width_new = $request->height_width;
            }
            $height = explode('x',$height_width_new);
            $h = $height[1];
            $width = explode('x',$height_width_new);
            $w = $width[0];
            $data = BarcodeConfiguration::first();
            if($data == null)
            $data = new BarcodeConfiguration();
            $data->barcode_columns = serialize($request->barcode_columns);
            $data->width = $w;
            $data->height = $h;
            $data->save();
            return true;
        }
        catch (\Exception $ex)
        {
            throw $ex;
        }
    }
}
