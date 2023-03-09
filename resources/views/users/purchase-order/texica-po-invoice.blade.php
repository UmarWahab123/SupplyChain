
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8"/>
  <title>PURCHASE ORDER</title>
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">
  <!-- Bootstrap CSS -->
 
  <style type="text/css">
    @page {
      size: auto;   /* auto is the initial value */
      margin: 5mm;
    }
    table { font-size: 10px; }
    table tr td 
    {
      vertical-align: top; padding-bottom: 3px;
    }
    table 
    {
      border-collapse: collapse;
      font-size:12px;
      border-spacing: 0px;}
      .invoicetable tr td, .invoicetable tr th {
      border: 1px solid #ddd;
      padding: 4px 7px;
    }
    .main-table > tbody > tr > td  { padding-right: 10px; padding-left: 10px;  }

    .invoicetable tr.inv-total-tr td {
        border: none;
        padding: 10px 2px 5px;
    }
    .inv-total-td span {
        font-weight: bold;
        border-bottom: 2px solid grey;
        display: inline-block;
        padding: 0px 5px 2px;
    }
    body {
      font-family: 'BoonRegular';
      font-weight: normal;
      font-style: normal;font-size: 14px;
      line-height: 1;
    }
  </style>

  @php
    use App\Models\Common\SupplierProducts;
    use App\Models\Common\Order\OrderProductNote;
  @endphp
  </head>

  <body>
    <table class="main-table" style="max-width: 970px;width: 100%;margin-left: auto;margin-right: auto;margin: 0px auto;">
      <tbody>
        <tr>
          <td width="100%">
            <table class="" style="width: 100%; border: none;">
              <tbody>
                @if(isset($pf))
                
                <tr style="border: none;">
                  <td colspan="2" width="50%">
                    <img src="{{public_path('uploads/logo/'.@$pf->logo)}}" width="180" style="margin-bottom: 20px;">
                  </td>
                  <td style="width: 50%;">
                    <h5 style="text-transform: uppercase; font-size: 18px; margin: 0px 0px 6px;text-align: right;"><strong>
                      Purchase Order
                    </strong></h5>
                    <div class="mt-5" style="text-align: right;">
                      <h5 style="text-transform: uppercase; font-size: 18px; margin: 0px 0px 6px;text-align: right;"><strong>{{$pf->company_name}}</strong></h5>

                      <p style="margin: 0px 0px 6px;">{{ $pf->billing_address}},<br> {{$pf->getcountry->name.', '.$pf->getstate->name.', '.$pf->billing_zip }}</p>
                      <p style="margin: 0px 0px 6px;">Phone: {{ $pf->billing_phone }} Fax: {{ $pf->billing_fax }}</p>
                    </div>
                  </td>
                </tr>
                
                @else
                
                <tr style="border: none;">
                  <td colspan="2" width="50%">
                    <img src="{{public_path('uploads/logo/'.@$purchaseOrder->createdBy->getCompany->logo)}}" width="180" style="margin-bottom: 20px;">
                  </td>
                  <td style="width: 50%;">
                    <h5 style="text-transform: uppercase; font-size: 18px; margin: 0px 0px 6px;text-align: right;"><strong>
                      Purchase Order
                    </strong></h5>
                  <div class="mt-5" style="text-align: right;">
                    <h5 style="text-transform: uppercase; font-size: 18px; margin: 0px 0px 6px;text-align: right;"><strong>
                      {{$purchaseOrder->createdBy->getCompany->company_name}}
                    </strong></h5>

                    <p style="margin: 0px 0px 6px;">
                      {{ $purchaseOrder->createdBy->getCompany->billing_address}},<br> {{$purchaseOrder->createdBy->getCompany->getcountry->name.', '.$purchaseOrder->createdBy->getCompany->getstate->name.', '.$purchaseOrder->createdBy->getCompany->billing_zip }}
                    </p>
                    <p style="margin: 0px 0px 6px;">
                      Phone: {{ $purchaseOrder->createdBy->getCompany->billing_phone }} Fax: {{ $purchaseOrder->createdBy->getCompany->billing_fax }}
                    </p>
                  </div>
                  </td>
                </tr>

                @endif
                <tr>
                  <td colspan="2">
                  </td>
                </tr>
                 <tr width="100%">
                  <table class="" style="width: 100%;border: none;">
                    <tr>
                      <td width="60%">
                      <div style="padding:3px 3px 3px 25px;background-color: grey;">
                      <table class="table" style="width: 100%;background-color: white;height: 105px;border: none;">
                        <tbody>
                          <tr>
                            @if($purchaseOrder->supplier_id !== null)
                            <td width="20%" style="padding-top: 8px; padding-left: 5px;"><strong>Supplier </strong></td>
                            @else
                            <td width="20%" style="padding-top: 8px; padding-left: 5px;"><strong>Warehouse </strong></td>
                            @endif
                            <td width="80%" style="" valign="center;">
                            @if($purchaseOrder->supplier_id !== null)
                              <p style="margin: 0px 0px 2px;">
                                {{ $purchaseOrder->PoSupplier->company }}
                              </p>
                             @if($purchaseOrder->PoSupplier->address_line_1 !== null)
                              <p>
                                {{ $purchaseOrder->PoSupplier->address_line_1.' '.$purchaseOrder->PoSupplier->address_line_2 }}, @endif  @if($purchaseOrder->PoSupplier->country !== null) {{ $purchaseOrder->PoSupplier->getcountry->name }}, @endif @if($purchaseOrder->PoSupplier->state !== null) {{ $purchaseOrder->PoSupplier->getstate->name }}, @endif @if($purchaseOrder->PoSupplier->city !== null) {{ $purchaseOrder->PoSupplier->city }}, @endif @if($purchaseOrder->PoSupplier->postalcode !== null) {{ $purchaseOrder->PoSupplier->postalcode }}
                              </p>
                              @endif
                            @endif

                            @if($purchaseOrder->from_warehouse_id !== null)
                            <p style="margin: 0px 0px 2px;">
                              {{ $purchaseOrder->PoWarehouse->warehouse_title }}
                            </p>
                            <p>
                              @if($purchaseOrder->PoWarehouse->getCompany !== null) {{ $purchaseOrder->PoWarehouse->getCompany->billing_address }}, @endif  
                              @if($purchaseOrder->PoWarehouse->getCompany->getcountry !== null) {{ $purchaseOrder->PoWarehouse->getCompany->getcountry->name }}, @endif 
                              @if($purchaseOrder->PoWarehouse->getCompany->getstate !== null) {{ $purchaseOrder->PoWarehouse->getCompany->getstate->name }}, @endif 
                              @if($purchaseOrder->PoWarehouse->getCompany->city !== null) {{ $purchaseOrder->PoWarehouse->getCompany->city }}, @endif @if($purchaseOrder->PoWarehouse->getCompany->postalcode !== null) {{ $purchaseOrder->PoWarehouse->getCompany->postalcode }} @endif
                            </p>
                            @endif
                            </td>
                          </tr>
                          <tr>
                            <td width="20%" style="padding-top: 8px; padding-left: 5px;"><strong>Ship To </strong></td>
                            <td width="80%" style="" valign="center;">
                              <p style="margin: 0px 0px 2px;">
                              {{ $purchaseOrder->ToWarehouse->warehouse_title }}
                              </p>
                              <p>
                                @if($purchaseOrder->ToWarehouse->getCompany !== null) {{ $purchaseOrder->ToWarehouse->getCompany->billing_address }}, @endif  
                                @if($purchaseOrder->ToWarehouse->getCompany->getcountry !== null) {{ $purchaseOrder->ToWarehouse->getCompany->getcountry->name }}, @endif 
                                @if($purchaseOrder->ToWarehouse->getCompany->getstate !== null) {{ $purchaseOrder->ToWarehouse->getCompany->getstate->name }}, @endif 
                                @if($purchaseOrder->ToWarehouse->getCompany->city !== null) {{ $purchaseOrder->ToWarehouse->getCompany->city }}, @endif @if($purchaseOrder->ToWarehouse->getCompany->postalcode !== null) {{ $purchaseOrder->ToWarehouse->getCompany->postalcode }} @endif
                              </p>
                            </td>
                          </tr>
                          </tbody>
                        </table>
                       </div>
                      </td>
                      <td width="40%;">
                      <div style="padding:3px 3px 3px 25px;background-color: grey;">
                      <table class="table" style="width: 100%;background-color: white;height: 105px;">
                      <tbody>
                        <tr style="border-bottom: none !important;">
                          <td width="60%" style="padding-top: 8px; padding-left: 5px;vertical-align: center;"><strong>PO No. </strong><br><strong>
                            Document No. 
                          </strong></td>
                          <td width="40%;" style="vertical-align: center;">
                            <p>{{ @$purchaseOrder->ref_id}}</p>
                          </td>
                        </tr>
                       
                        <tr>
                          <td width="40%" style="padding-top: 8px; padding-left: 5px;vertical-align: center;"> <strong>Date: </strong></td>
                          <td width="60%" style="vertical-align: center;"><p>{{$createDate}}</p></td>
                        </tr>

                      </tbody>
                      </table>
                      </div>
                   </td>
                   </tr>
                 </table>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>

    <div style="padding: 0 10px;">
      <table class="table invoicetable" style="width: 100%; height: auto;">
        <thead align="center" style="background-color: grey;">
          <tr>
            <th>Item #</th> 
            <th>Sup Reference #</th>
            <th>Description</th>
            <!-- <th>Customer</th> -->
            <th>Order QTY</th>
            <!-- <th>Pkg QTY Inv (est.)</th> -->
            
            <th @if(@$price_checked == 1) colspan="1" @endif>Note</th>
            @if(@$price_checked == 1)
            <th>Billing QTY</th>
            <th>Price</th>
           
            <th>Amount</th>
            @endif
          </tr>
        </thead>

          <?php
            $i = 0;
          ?>
        <tbody>
        @php 
          $subtotal=null;
        @endphp
          @foreach($getPoDetail as $result)
          <?php $result_pro_id = $result[0]->product_id;

          ?>
          <tr>
            <td align="left">{{ $result[0]->product_id != null ? @$result[0]->product->refrence_code : "--" }}</td>
            <td align="left">
              @php
              if($result[0]->PurchaseOrder->supplier_id != null)
              {
                $gettingProdSuppData = SupplierProducts::where('product_id',$result[0]->product_id)->where('supplier_id',$result[0]->PurchaseOrder->PoSupplier->id)->first();
              }
              else
              {
                $gettingProdSuppData = SupplierProducts::where('product_id',$result[0]->product_id)->where('supplier_id',@$result[0]->product->supplier_id)->first();
              }
                
              if($result[0]->product_id != null)
              {
                $ref_no1 = $gettingProdSuppData->product_supplier_reference_no !== null ? $gettingProdSuppData->product_supplier_reference_no : "--";
              }
              else
              {
                $ref_no1 = "--";
              }
              @endphp
                {{ $ref_no1 }}
            </td>
            @php
            $customer = @$result[0]->customer_id != null ? @$result[0]->customer->reference_name : "Stock";
            
            if($result[0]->PurchaseOrder->supplier_id != null)
            {
               $supplier_id = $result[0]->PurchaseOrder->supplier_id;

                $getDescription = SupplierProducts::where('product_id',$result[0]->product_id)->where('supplier_id',$supplier_id)->first();

                $description =  $getDescription->supplier_description != null ? $getDescription->supplier_description : ($result[0]->product->short_desc != null ? $result[0]->product->short_desc : "--") ;
            }
            else
            {
                $description =  $result[0]->product->short_desc != null ? $result[0]->product->short_desc : "--" ;
            }
            @endphp
            <td align="left" width="30%">{{ $result[0]->product->brand != null ? $result[0]->product->brand : "" }} - {{ $description }} - {{ $result[0]->product->productType != null ? $result[0]->product->productType->title : "" }}</td>
            {{-- <td align="left">{{ $customer }}</td> --}}
            @php
              if($result[0]->supplier_packaging != null)
              {
                if($result[0]->supplier_packaging == "Bottle")
                {
                  $unit = "Bt.";
                }
                else
                {
                  $unit = $result[0]->supplier_packaging != null ? @$result[0]->supplier_packaging : "--";
                }
              }
              else
              {
                $unit = "--";
              }
            @endphp

            <td align="left">{{ @$result->sum('desired_qty') != 0 ? number_format($result->sum('desired_qty'), 0, '', '') : "-"}} {{$unit}}</td>
            {{--<td align="left">{{ @$result->sum('billed_unit_per_package') != 0 ? @$result->sum('billed_unit_per_package') : "-"  }}</td>--}}
            
            {{-- <td align="left">{{ $result[0]->product_id != null ? @$result[0]->product->units->title : "--" }}</td> --}}
            <td  align="left" @if(@$price_checked == 1) colspan="1" @endif>
         
         @foreach($getPoDetailForNote as $detail)
              @if ($detail->product_id === $result_pro_id)
                @php
                  if($detail->order_product_id != NULL)
                  {
                    $op_notes = OrderProductNote::where('order_product_id', $detail->order_product_id)->get();
                  }
                  else
                  {
                    $op_notes = OrderProductNote::where('pod_id', $detail->id)->get();
                  }
                @endphp
                  @if($op_notes->count() > 0)
                      @foreach($op_notes as $note)
                        {{$note->note.', '}}
                      @endforeach
                  @else
                  {{''}}
                  @endif
              @endif
          @endforeach
         </td>
            @if(@$price_checked == 1)

            @php
              if($result[0]->product_id != null)
              {
                if($result[0]->product->units->title == "Bottle")
                {
                  $unit = "Bt.";
                }
                else
                {
                  $unit = $result[0]->product_id != null ? @$result[0]->product->units->title : "--";
                }
              }
              else
              {
                $unit = "--";
              }
            @endphp

            <td align="left">{{ @$result->sum('quantity') !== null ? number_format($result->sum('quantity'), 0, '', '') : "-" }} {{$unit}}</td>

            <td align="right">
              @php
                $unit_price = number_format($result[0]->pod_unit_price, 2, '.', ',');
              @endphp
              @if($unit_price !== null)
                {{ $unit_price }}
              @endif
            </td>
           
            <td align="right">
              @php
                $amount = $result[0]->pod_unit_price * $result[0]->quantity;
                $amount = $amount - ($amount * (@$result[0]->discount / 100));
                $amount = number_format($amount, 2, '.', ',');
              @endphp
              @if($amount !== null)
                {{ $amount }}
              @endif
            </td>
           
            @endif
            </tr>

          @endforeach


          @if($poNote)
          <tr>
            <td @if(@$price_checked == 1) colspan="8" @else colspan="5" @endif>
              <p><strong>Notes:</strong>
              {{$poNote->note}}</p><p>
            </td>
          </tr>
          @endif
        </tbody>
      </table>
     @if($price_checked)
     <p style="float:right; margin: 5"><strong>Subtotal:</strong> {{number_format($purchaseOrder->total, 2, '.', ',')}} <p>
     @endif
     <p style="float:right; margin: 5" class="d-none"><strong>Total Qty:</strong> {{number_format($purchaseOrder->total_quantity, 0, '.', ',')}} Bt. <p>
    </div>
  </body>
</html>

