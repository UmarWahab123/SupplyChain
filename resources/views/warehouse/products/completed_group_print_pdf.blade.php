  <!DOCTYPE html>
  <html>
  <head>
    <meta charset="utf-8"/>
    <title>PO Group</title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/boon-v1-1" type="text/css"/>
    <style type="text/css">
      *{
        margin: 0;
        padding: 0;
      }
    	  table { font-size: 10px;
  border-collapse: collapse;
  text-align: center;
         }
         td, th {
  border: 1px solid #dddddd;

}


.verticalTableHeader {
    text-align:center;
    white-space:nowrap;
    transform-origin:50% 50%;
    -webkit-transform: rotate(270deg);
    -moz-transform: rotate(270deg);
    -ms-transform: rotate(270deg);
    -o-transform: rotate(270deg);
    transform: rotate(270deg);

}
.verticalTableHeader:before {
    content:'';
    padding-top:110%;/* takes width as reference, + 10% for faking some extra padding */
    display:inline-block;
    vertical-align:middle;
}

    table tr td
    {
      vertical-align: top; padding-bottom: 3px;
    }

      .rotate_text
      {
         -moz-transform:rotate(-90deg);
         -moz-transform-origin: top left;
         -webkit-transform: rotate(-90deg);
         -webkit-transform-origin: top left;
         -o-transform: rotate(-90deg);
         -o-transform-origin:  top left;
          position:relative;
         top:10px;
         left: 10px;
      }
      .rotated_cell
      {
         height:50px;
         width: 5px;
         vertical-align:bottom;
      }

    .inv-total-td span {
        font-weight: bold;
        border-bottom: 2px solid #000;
        display: inline-block;
        padding: 0px 5px 2px;
    }
     .before {
        page-break-before: always;
      }
      .before > td{
        height: 80px;
        border: 1px solid white;
      }

  body
  {
    font-family: BoonRegular;
    font-weight: normal;
    font-style: normal;
  }
.boon
{
  font-family: BoonRegular;
  font-weight: normal;
  font-style: normal;
  font-size: 14px;
}

    </style>
    @php
    use Carbon\Carbon;
    use App\Models\Common\Product;
    use App\Models\Common\ProductType;
    use App\Models\Common\SupplierProducts;
    use App\Models\Common\Supplier;
    use App\Models\Common\Courier;
  @endphp

  </head>

  <body style="font-family: sans-serif;width: 100%;margin: 20px 15px;">
    <table class="table" style="width: 100%;border: 0px;border: none;text-align: left;">
    <tbody>
    <tr>
    <td style="border: 0" width="5%">
      <img src="{{public_path('uploads/logo/logo1_1587959552.JPG')}}" width="150" style="margin-bottom: 0px;" height="80">
    </td>
    <td colspan="1" style="border: 0px;text-align: left;" width="95%">

    <h5 style="text-transform: uppercase; font-size: 18px; margin: 0px 0px 6px;"><strong>Group No {{@$group_detail[0]->po_group->ref_id}}<br>Product Receiving Records <span class="boon">(บันทึกผลการตรวจรับสินค้า)</span></strong></h5>
    @if(@$configuration->server != 'lucilla')
      <h5 style="position: absolute;right: 0;top: 0px;font-size: 16px">FO-PF-WH-003</h5>
    @endif
    </td>
</tr>
    <tr>
    <td style="width: 50%;border: none;">
    <p style="margin: 0px 0px 6px;"> <span class="boon">ประจำวันที่:</span> {{Carbon::parse(@$group_detail[0]->created_at)->format('d/m/Y')}}</p>
    <p style="margin: 0px 0px 6px;">AWB or B/L:{{@$group_detail[0]->po_group->bill_of_landing_or_airway_bill}}</p>
    <p style="margin: 0px 0px 6px;">
      @php
        $courier = Courier::find(@$group_detail[0]->po_group->courier);
      @endphp
      courier: {{@$courier->title}}
    </p>
    </td>
</tr>
    </tbody>
    </table>

    <table class="main-table" style="max-width: 970px;width: 100%;margin-left: auto;margin-right: auto;margin: 0px auto;page-break-inside: all;">
  <thead>
  <col>
  <colgroup span="2"></colgroup>
  <colgroup span="2"></colgroup>
  <tr>
    <th rowspan="1" colspan="5" style="text-align: left;padding: 12px 2px;">{{Carbon::parse(@$group_detail[0]->datee)->format('M-d-Y')}}</th>
    <th rowspan="1" colspan="2" style="text-align: center;" valign="top">truck 1:</th>
    <th rowspan="1" colspan="2" style="text-align: center;" valign="top">temp:</th>
    <th rowspan="1" colspan="2" style="text-align: center;" valign="top">truck 2:</th>
    <th rowspan="1" colspan="2" style="text-align: center;" valign="top">temp:</th>
    <th rowspan="1" colspan="2" style="text-align: center;" valign="top">truck 3:</th>
    <th rowspan="1" colspan="2" style="text-align: center;" valign="top">temp:</th>
    <th rowspan="1" colspan="2" style="text-align: center;" valign="top">truck 4:</th>
    <th rowspan="1" colspan="2" style="text-align: center;" valign="top">temp:</th>
  </tr>
  <tr>
    <th rowspan="2" style="text-align: center;">Supplier</th>
    <th rowspan="2" style="text-align: center;" colspan="4">Description</th>
    <th colspan="4" scope="colgroup" style="text-align: center;">@if(!array_key_exists('qty', $global_terminologies)) QTY @else {{$global_terminologies['qty']}} @endif of Goods</th>
    <th colspan="4" scope="colgroup" style="text-align: center;">Goods Condition</th>
    <th colspan="4" scope="colgroup" style="text-align: center;">Goods Type</th>
    <th rowspan="2" style="text-align: center;">@if(!array_key_exists('temprature_c',$global_terminologies)) Temprature<br> C @else {{$global_terminologies['temprature_c']}}  @endif</th>
    <th rowspan="2" style="text-align: center;"><span class="boon" style="line-height: 0.8;">ลงชื่อผู้<br>ตรวจรับ<br></span>Checker</th>
    <th colspan="2" scope="colgroup" style="text-align: center;">Problems and solutions (if found)</th>
    <th rowspan="2" style="text-align: center;" width="70px"><span class="boon" style="line-height: 0.8;">ลงชื่อผู้อนุมัติ<br>ให้ดำเนิน<br>การแก้ไข<br></span>Authorised<br>Changes</th>
  </tr>
  <tr>
    <th scope="col">Unit</th>
    <th scope="col">Qty</th>
    <th scope="col" class="">Qty<br>Rcvd</th>
    <th scope="col">Diff</th>
     <th scope="col" class="rotated_cell"><div class="rotate_text" style="top: 33px;left: -9px;"><span class="boon">ปกติ</span>Normal</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text" style="top: 30px;left: -4px;"><span class="boon" style="font-size: 12px">พบปัญหา</span><br>Problem</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Passed</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Failed</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Frozen</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Chilled</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Dried</div></th>
     <th scope="col" class="rotated_cell"><div class="rotate_text">Fresh</div></th>
        <th scope="col" style="width: 70pt"><span class="boon" style="line-height: 0.8;">ปัญหาที่พบ<br></span>Problem <br>Found</th>
    <th scope="col" style="width: 70pt"><span class="boon" style="line-height: 0.8">การแก้ไขที่ไ<br>ด้ดำเนินการ<br></span>Solution</th>
  </tr>
</thead>
<tbody>
  @php $i = 0; $first = true; $j = 0; @endphp
    @foreach($group_detail as $item)
                <tr>
                  <td>
                  @php
                  if($item->supplier_id !== NULL)
                  {
                    $sup_name = $item->get_supplier->reference_name;
                  }
                  else
                  {
                    $sup_name = $item->get_warehouse->warehouse_title;
                  }
                  @endphp
                  {{$sup_name}}

                  </td>

                  <td style="width: 160pt;white-space: normal;" colspan="4">

                    <span >

                  @php
                  $product_ref = $item->product->short_desc;
                  @endphp
                  {{$product_ref}}

                    </span>
                  </td>
                  <td>
                    {{$item->product->units->title != null ? $item->product->units->title : ''}}
                  </td>
                  <td>{{round($item->quantity_inv,3)}}</td>
                  <td> {{@$item->quantity_received_1 + $item->quantity_received_2}} </td> <!-- Quantity Received -->
                  <td> {{number_format(($item->quantity_inv - (@$item->quantity_received_1 + $item->quantity_received_2)),2,'.',',')}} </td> <!-- Quantity Received -->
                  <td>
                  @if(@$item->good_condition == 'normal')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>
                  <td>
                   @if(@$item->good_condition == 'problem')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>
                  <td>
                     @if(@$item->result == 'pass')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>
                  <td>
                     @if(@$item->result == 'fail')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>0
                  <td>
                    @if(@$item->good_type == '2')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif</td>
                  <td>
                     @if(@$item->good_type == '1')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>
                  <td>
                     @if(@$item->good_type == '3')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>
                  <td>
                     @if(@$item->good_type == '4')
                    <i class="fa fa-check" style="margin-top: 10px;margin-left: 5px;"></i>
                  @endif
                  </td>
                  <td> {{ @$item->temperature_c != null ? @$item->temperature_c : '' }}</td>
                  <td> <span class="boon" style="line-height: 0.8">{{@$item->checker != null ? @$item->checker : ''}}</span> </td> <!-- checker -->
                  <td> <span class="boon" style="line-height: 0.8">{{@$item->problem_found != null ? @$item->problem_found : ''}}</span> </td> <!-- Problem Found -->
                  <td> <span class="boon" style="line-height: 0.8">{{@$item->solution != null ? @$item->solution : ''}}</span> </td> <!-- Solution -->
                  <td> <span class="boon" style="line-height: 0.8">{{@$item->authorized_changes != null ? @$item->authorized_changes : ''}}</span> </td> <!-- Authorized Changes -->
                </tr>

                @if($loop->iteration == 14)
                <tr>
                  <td>
                <div style="page-break-after: always;width: 100%"></div>
                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                </tr>
                @endif

                {{--  @if($item->occurrence > 1)
                  @php
                  $all_record = App\Models\Common\PurchaseOrders\PurchaseOrderDetail::with('PurchaseOrder.PoSupplier','PurchaseOrder.PoWarehouse')->where('product_id',$item->product_id)->whereHas('PurchaseOrder',function($q) use ($item){
                    $q->where('po_group_id',$item->po_group_id)->where('supplier_id',$item->supplier_id);
                  })->get();
                  @endphp
                  @foreach($all_record as $record)
                  <tr style="background:#B0E7DC;">
                      <td>

                        @php

                        if($record->PurchaseOrder->supplier_id !== NULL)
                        {
                          $sup_name = $record->PurchaseOrder->PoSupplier->reference_name;
                        }
                        else
                        {
                          $sup_name = $record->PurchaseOrder->PoWarehouse->warehouse_title;
                        }
                        @endphp
                        {{$sup_name}}

                      </td>

                      <td style="width: 160pt;white-space: normal;" colspan="4">

                        <span >
                      @php
                      $product_ref = $record->product->short_desc;
                      @endphp
                      {{$product_ref}}
                                          </span>
                      </td>
                      <td>  {{$record->product->units->title != null ? $record->product->units->title : ''}}                    </td>
                      <td>{{round($record->quantity,3)}}</td>
                      <td></td> <!-- Quantity Received -->
                      <td></td> <!-- Quantity Received -->
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td> <!-- checker -->
                      <td></td> <!-- Problem Found -->
                      <td></td> <!-- Solution -->
                      <td></td> <!-- Authorized Changes -->
                    </tr>
                  @endforeach
                  @endif --}}
                @endforeach
              </tbody>

</table>

</body>
</html>
