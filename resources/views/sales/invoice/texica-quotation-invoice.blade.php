<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Quotation</title>
    <meta charset="utf-8"/>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/thsarabun-new" type="text/css"/>
    <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/dzhitl" type="text/css"/>
    <link rel="stylesheet" media="screen" href="https://fontlibrary.org/face/boon-v1-1" type="text/css"/> -->
    <link href="{{asset('public/site/assets/backend/css/font.css')}}" rel="stylesheet" type="text/css">
  </head>

  <style type="text/css">
    body {
      font-family: 'TH Sarabun New';
      font-weight: bold;
      /*font-style: normal;*/
      font-size: 17px;
    }
    .header-table tr td{/*line-height: 1.4em;*/ vertical-align: top; margin:0; padding: 0}

    .pagenum:before {
        content: counter(page);
    }
  </style>
  
  <?php
    use Carbon\Carbon;
    use App\PrintHistory;
    use App\Models\Common\Order\Order;
  ?>
  @foreach($orders_array as $id)
  <?php
    //To take history
      $history = PrintHistory::saveHistory($id, $print_type, $page_type);
      $data = Order::getDataForQuoTexica($id,$default_sort);
      $order = $data['order'];
      $order_products = $data['order_products'];
      $company_info = $data['company_info'];
      $address = $data['address'];
      $customerAddress = $data['customerAddress'];
      $inv_note = $data['inv_note'];
      $warehouse_note = $data['warehouse_note'];
      $do_pages_count = $data['do_pages_count'];

      $per_page_id1 = 0; 
      $indexes = 1;

      $totalQty = 0; 
  ?>
  @for($z = 1 ; $z <= $do_pages_count ; $z++)
  <body style="margin: 0; line-height: 1;">
    <div class="container" style="width: 100%;">
      <table class="header-table" style=" vertical-align: top; width: 100%;" border="0">
        <thead>
          <tr>
            <td style="width: 55%;">
              <table class="header-table" border="0">
                <tr style="font-size: 18px;">
                  <td style="line-height: 1; width: 20%;"><strong>Quotation #<br>ใบเสนอราคา</strong></td>
                  <td style="line-height: 1; vertical-align: top;">
                    <strong>
                    @if(@$order->status_prefix !== null)
                      {{@$order->status_prefix.'-'.$order->ref_prefix.$order->ref_id}}
                    @else
                      {{@$order->user->get_warehouse->order_short_code}}{{@$order->customer->CustomerCategory->short_code}}{{$order->ref_id }}
                    @endif
                    </strong>
                  </td>
                </tr>

                <tr>
                  <td style="vertical-align: top; line-height: .7; padding: 5px 0;"><strong>Customer <br> Name:</strong></td>
                  <td style="line-height: .7; vertical-align: top; padding: 5px 0;">
                    {{@$order->customer->reference_name !== null ? @$order->customer->reference_name : '--'}}<br>
                    {{@$order->customer->company}}
                  </td>
                </tr>

                <tr>
                  <td style="vertical-align: top; line-height: .7; padding: 5px 0;"><strong>Customer <br> Adress:</strong></td>
                  <td style="line-height: 1; vertical-align: top;"> 
                      {{@$order->customer_billing_address->billing_address}}, {{@$order->customer_billing_address->billing_city}}, {{@$order->customer->language == 'en' ? @$order->customer_billing_address->getstate->name : (@$order->customer_billing_address->getstate->thai_name != null ? @$order->customer_billing_address->getstate->thai_name : @$order->customer_billing_address->getstate->name)}}, {{@$order->customer->language == 'en' ? (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name :'') : (@$customerAddress->getcountry->thai_name !== null ? (@$customerAddress->getcountry->thai_name !== 'ประเทศไทย' ? @$customerAddress->getcountry->thai_name : '') : (@$customerAddress->getcountry->name !== 'Thailand' ? @$customerAddress->getcountry->name : ''))}}, {{@$order->customer_billing_address->billing_zip}}
                  </td>
                </tr>

                <tr>
                  <td style="width: 5%;"><strong>Tax ID:</strong></td>
                  <td style="">{{@$order->customer_billing_address->tax_id != null ? @$order->customer_billing_address->tax_id : "N.A"}}</td>
                </tr>
              </table>
            </td>
            <td style=" width: 45%;">
              <table class="header-table" border="0">
                <tr>
                  <td style="text-align: center;padding: 3px;">
                    <img src="{{public_path('uploads/logo/'.@$company_info->logo)}}" height="50" style="margin-bottom: 0;">
                  </td>
                </tr>
                <tr> 
                  <td style="padding: 5px 0; text-align: center;line-height: 14px;">
                    <h3 style="font-weight: bold; font-size: 27px; padding: 0; margin:0; text-align: center;">
                      {{@auth::user()->getCompany->company_name}}
                    </h3>
                  </td>
                </tr> 
                <tr>
                  <td style="padding: 5px 0; line-height: 13px; text-align: center; font-size: 14px;">46/8 ซอยแยกสันติสุข แขวงพระโขนง เขตคลองเตย กรุงเทพมหานคร 10110<br>46/8 Soi Yeak Santisuk, Phrakhanong, Klongtoey, Bangkok 10110 THAILAND<br>Tel. (+66) 02 713 6034-35  Fax. (+66) 02 713 6036<br>เลขประจำตัวผู้เสียภาษี / TAX NO : 0105549032227</td>
                </tr>     
              </table>
            </td>
          </tr>
        </thead>
      </table>

      <table class="prod-table" cellpadding="0" cellspacing="0" style="margin-top: 15px; margin-left: -15px; width: 100%;"  border="1">
        <tbody>
          <tr>
            <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">Picture</strong></td>
            <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">{{$global_terminologies['subcategory']}}</strong></td>
            <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">{{$global_terminologies['brand']}}</strong></td>
            <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">{{$global_terminologies['product_description']}}</strong></td>
            <!-- <td style="padding: 4px; text-align: center; font-weight: bold;"><strong style="font-weight: bold;">Category</strong></td> -->
            <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">{{$global_terminologies['type']}}</strong></td>
            <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">{{$global_terminologies['note_two']}} <br> (L)</strong></td>
            <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">% Vol</strong></td>
            @if(@$with_vat == null)
              <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">Unit <br> Price</strong></td>
            @else
              <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">Unit <br> Price<br>(Inc VAT)</strong></td>
            @endif
             <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">Qty</strong></td>
            @if($show_discount == 1)
              <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">Disc.</strong></td>
              <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">Unit Price<br>(With Discount)</strong></td>
            @endif
            <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">Total</strong></td>
            <td style="padding: 4px; text-align: center; font-weight: bold; line-height: .6;"><strong style="font-weight: bold;">Remarks</strong></td>
          </tr>

        @if(@$order->count() > 0)
        @php
          $product_count1 = 0;
          $totalQty = 0; 
        @endphp

        @foreach($order->order_products as $result)
        @if($result->id > $per_page_id1)
        @if($result->is_retail == 'qty' && $result->quantity != 0 || $result->is_retail == 'pieces' && $result->number_of_pieces != 0)

        @php 
          $product_count1++; 
          if($product_count1 > 4)
          {
            break;
          }
        @endphp

          <tr>
            <td style="padding: 1px; text-align: center; line-height: .6; width: 94px;">
              @if(file_exists( public_path() . '/uploads/products/product_'.@$result->product->prouctImages[0]->product_id.'/'.@$result->product->prouctImages[0]->image))
                <img src="{{public_path('uploads/products/product_'.@$result->product->prouctImages[0]->product_id.'/'.@$result->product->prouctImages[0]->image)}}" alt="img" style="width: 100%; ">
              @else
                <img src="{{public_path('uploads/Product-Image-Coming-Soon.png')}}" alt="image" style="width: 100%; ">
              @endif
            </td>
            <td style="padding: 4px; text-align: center; line-height: .6;">
              @if($result->is_billed == "Product")
                {{@$result->product->productCategory->title}} / {{@$result->product->productSubCategory->title}}
              @else
                {{"N.A"}}
              @endif
            </td>
            <td style="padding: 4px; text-align: center; line-height: .6;">{{$result->brand != NULL ? $result->brand : "--"}}</td>
            @if($show_discount == 1)
              @php $dynamic = "300px"; @endphp
            @else
              @php $dynamic = "300px"; @endphp
            @endif
            <td style="padding-left: 4px; line-height: .6;" width="{{$dynamic}}">{{$result->short_desc != NULL ? $result->short_desc : "--"}}</td>
            <!-- <td style="padding: 4px; text-align: center">SPK</td>  -->
            <td style=" padding: 4px;text-align: center; line-height: .6;">{{$result->type_id != NULL ? $result->productType->title : "--"}}</td>
            @if($result->is_billed == "Product")
              <td style=" padding: 4px;text-align: center; line-height: .6;">{{$result->product->product_notes != null ? $result->product->product_notes : "--"}}</td>
            @else
              <td style=" padding: 4px;text-align: center; line-height: .6;">N.A</td>
            @endif
            @if($result->is_billed == "Product")
              <td style="padding: 4px;text-align: center; line-height: .6;">{{$result->product->product_temprature_c != null ? $result->product->product_temprature_c." %" : "--"}}</td>
            @else
              <td style="padding: 4px;text-align: center; line-height: .6;">--</td>
            @endif
            <td style="padding: 4px;text-align: center; line-height: .6;">
              @if(@$with_vat == null)
                {{number_format(@$result->unit_price, 2, '.', ',')}}
              @else
                @php
                  $unit_price = $result->unit_price;
                  $vat = $result->vat;
                  $vat_amount = @$unit_price * ( @$vat / 100 );
                  $unit_price_with_vat = number_format(floor((@$unit_price+@$vat_amount)*100)/100,2,'.',',');
                  $total = @$result->total_price;
                  $vat_amount_w_v = @$total * ( @$vat / 100 );
                  $vat_amount_with_vat = number_format(floor((@$total+@$vat_amount_w_v)*100)/100,2,'.',',');
                @endphp
                {{@$unit_price_with_vat}}
              @endif
            </td>
            <td style="padding: 4px; text-align: center; line-height: .6;">{{$result->quantity != null ? $result->quantity : "--"}}</td>
            @if($show_discount == 1)
              <td style="padding: 4px; text-align: center; line-height: .6;">
                {{@$result->discount != null ? @$result->discount.' %': '--'}}
              </td>
              <td style="padding: 4px;text-align: center; line-height: .6;">
                {{$result->unit_price_with_discount != null ? number_format($result->unit_price_with_discount, 2, '.', ','): "--" }}
              </td>
            @endif
            <td style="padding: 4px;text-align: center; line-height: .6;">
              @if(@$with_vat == null)
                {{number_format(floor(@$result->total_price*100)/100,2,'.',',')}}
              @else
                {{@$vat_amount_with_vat}}
              @endif
            </td>
            <td style=" padding: 4px;text-align: center; line-height: .6;">
              @if($result->get_order_product_notes->count() > 0)
                @foreach(@$result->get_order_product_notes as $note)
                {{@$note->note.','}}
                @endforeach
              @else
                {{''}}
              @endif
            </td>
          </tr>
        
        @php
          @$per_page_id1 = $result->id;
          $totalQty += $result->quantity;
        @endphp

        @endif
        @endif
        @endforeach
        @endif
        </tbody>

        @if($z == $do_pages_count)
        <tfoot>
          <tr>
            <td colspan="8" style="font-weight: bold; line-height: .6;">&nbsp;Total</td>
            <td style="padding: 4px; text-align: center; line-height: .6;">{{$totalQty}}</td>
            @if($show_discount == 1)
            <td></td>
            <td></td>
            @endif
            <td colspan="2" style="padding: 4px; text-align: center; line-height: .6;">
              @if(@$with_vat == null)
                {{number_format(floor($order->order_products->sum('total_price')*100)/100,2,'.',',')}}
              @else
                {{number_format(floor($order->order_products->sum('total_price_with_vat')*100)/100,2,'.',',')}}
              @endif
            </td>
          </tr>
        </tfoot>
        @endif
      </table>
      
      @if($z == $do_pages_count)
      <table class="footer-table" style="width: 100%; margin-top: 10px;" border="0" cellspacing="0" cellpadding="0">
        <tbody>
          <tr>
            <td colspan="2">
              <table cellpadding="0" cellspacing="0" border="0">
                <tr><td style="line-height: .7;"><strong style="font-weight: bold;">Remark :</strong> Prices are not included of VAT 7%</td></tr>
              </table>
            </td>
          </tr>
          <tr>
            <td style="width: 20%; text-align: left;font-weight: bold; line-height: .7;">To The attention:</td>
            <td style="width:20%; text-align: left; line-height: .7;">{{@$order->customer->reference_name !== null ? @$order->customer->reference_name : '--'}}</td>
            @if($order->customer->email != null)
            <td colspan="10" style=" line-height: .7;">Email / {{@$order->customer->email}}</td>
            @endif
          </tr>
          <tr>
            <td style="width: 20%; text-align: left;font-weight: bold; line-height: .7;">From:</td>
            <td style="width:20%; text-align: left; line-height: .7;">Sales Person</td>
            <td colspan="10" style=" line-height: .7;">{{@$order->user->name}} / {{@$order->user->phone_number !== null ? @$order->user->phone_number : '--'}}</td>
          </tr>
           <tr>
            <td style="width: 20%; text-align: left; line-height: .7;"></td>
            <td style="width:20%; text-align: left; line-height: .7;">Sales Person E-mail</td>
            <td colspan="10" style="line-height: .7;">{{@$order->user->email !== null ? @$order->user->email : '--'}}</td>
          </tr>
          <tr>
            <td style="width: 20%; text-align: left; line-height: .7;"></td>
            <td style="width:20%; text-align: left; line-height: .7;">Customer Service</td>
            <td colspan="10" style="line-height: .7">Customer service here </td>
          </tr>
          <tr>
            <td style="width: 20%; text-align: left; line-height: .7;"></td>
            <td style="width:20%; text-align: left; line-height: .7;">Office Contact Number:</td>
            <td colspan="10" style="line-height: .7;">Contact no. here</td>
          </tr>
        </tbody>
      </table>
      @endif

      <footer style="position: absolute; bottom: 0; float: right;">
        <span class="pagenum">
          
        </span>
      </footer>
    </div>
  </body>
  @endfor
  @endforeach
</html>