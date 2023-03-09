<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
              <tr>
                @if(!in_array('2',$not_visible_arr))<th><b>Customer #</b></th>@endif
                @if(!in_array('3',$not_visible_arr))<th><b>Reference Name</b></th>@endif
                @if(!in_array('4',$not_visible_arr))<th><b>{{$global_terminologies['company_name']}}</b></th>@endif
                @if(!in_array('5',$not_visible_arr))<th><b>Email</b></th>@endif
                @if(!in_array('6',$not_visible_arr))<th><b>Primary Sale Person</b></th>@endif
                @if(!in_array('7',$not_visible_arr))<th><b>Secondary Sale Person</b></th>@endif
                @if(!in_array('8',$not_visible_arr))<th><b>District</b></th>@endif
                @if(!in_array('9',$not_visible_arr))<th><b>City</b></th>@endif
                @if(!in_array('10',$not_visible_arr))<th><b>Classification</b></th>@endif
                @if(!in_array('11',$not_visible_arr))<th><b>Payment Terms</b></th>@endif
                @if(!in_array('12',$not_visible_arr))<th><b>Customer Since</b></th>@endif
                @if(!in_array('13',$not_visible_arr))<th><b>Draft Orders</b></th>@endif
                @if(!in_array('14',$not_visible_arr))<th><b>Total Orders</b></th>@endif
                @if(!in_array('15',$not_visible_arr))<th><b>Last Order Date</b></th>@endif
                @if(!in_array('17',$not_visible_arr))<th><b>Status</b></th>@endif
              </tr>
            </thead>
            <tbody>
                @foreach($query as $customer)
                <tr>
                  @if(!in_array('2',$not_visible_arr))<td>{{@$customer->reference_number}}</td>@endif
                  @if(!in_array('3',$not_visible_arr))<td>{{@$customer->reference_name}}</td>@endif
                  @if(!in_array('4',$not_visible_arr))<td>{{@$customer->company}}</td>@endif
                  @if(!in_array('5',$not_visible_arr))<td>{{@$customer->email}}</td>@endif
                  @if(!in_array('6',$not_visible_arr))<td>{{@$customer->primary_sale_person->name}}</td>@endif
                  @if(!in_array('7',$not_visible_arr))<td>
                      @if($customer->CustomerSecondaryUser)
                    @for($i=0;$i<count($customer->CustomerSecondaryUser);$i++)
                         {{$customer->CustomerSecondaryUser[$i]->secondarySalesPersons->name}}
                        @if(($i)==(count($customer->CustomerSecondaryUser)-1))
                        @else
                            {{","}}
                        @endif
                    @endfor
                    @endif
                </td>@endif
                  @if($customer->getbilling->count() >=1)
                    @if(!in_array('8',$not_visible_arr))<td>{{@$customer->getbilling[0]->billing_city }}</td>@endif
                    @if(!in_array('9',$not_visible_arr))<td>{{@$customer->getbilling[0]->getstate->name }}</td>@endif
                  @endif
                  @if(!in_array('10',$not_visible_arr))<td>{{@$customer->CustomerCategory->title}}</td>@endif
                  @if(!in_array('11',$not_visible_arr))<td>{{@$customer->getpayment_term->title}}</td>@endif
                  @if(!in_array('12',$not_visible_arr))<td>{{$customer->created_at !== null ? Carbon\Carbon::parse(@$customer->created_at)->format('d/m/Y') : 'N.A'}}</td>@endif
                  @if(!in_array('13',$not_visible_arr))<td>{{@$customer->get_total_draft_orders($customer->id)}}</td>@endif
                  @php
                      $total = $customer->customer_orders()->whereIn('primary_status',[2,3])->sum('total_amount');
                      $all_val = number_format($total,2,'.','');
                  @endphp
                  @if(!in_array('14',$not_visible_arr))<td>{{@$all_val}}</td>@endif
                  @php
                    $orders = $customer->customer_orders()->whereIn('primary_status',[2,3])->orderBy('id','desc')->first();
                  @endphp
                  @if(!in_array('15',$not_visible_arr))<td>{{$orders !== null ? Carbon\Carbon::parse(@$orders->created_at)->format('d/m/Y') : 'N.A'}}</td>@endif
                  @php
                      $status = null;
                      if($customer->status == 1)
                      {
                        $status = "Completed";
                      }
                      elseif ($customer->status == 2)
                      {
                        $status = "Suspend";
                      }
                      else {
                        $status = "Incomplete";
                      }
                  @endphp
                  @if(!in_array('17',$not_visible_arr))<td>{{@$status}}</td>@endif




                  {{-- @if(!in_array('2',$not_visible_arr))
                    <td>
                      @foreach($customer->customer_payment_types as $key => $cpt)

                        @if( $customer->customer_payment_types->count() != $key + 1 )
                            {{@$cpt->get_payment_type->title}},
                         @else
                            {{@$cpt->get_payment_type->title}}
                        @endif

                      @endforeach
                    </td>
                  @endif


                      @if($customer->getbilling->count() >=1)
                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->getbilling[0]->title }}</td>@endif
                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->getbilling[0]->billing_phone }}</td>@endif
                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->getbilling[0]->cell_number }}</td>@endif
                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->getbilling[0]->billing_address }}</td>@endif
                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->getbilling[0]->tax_id }}</td>@endif
                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->getbilling[0]->billing_email }}</td>@endif
                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->getbilling[0]->billing_fax }}</td>@endif
                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->getbilling[0]->getstate->name }}</td>@endif
                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->getbilling[0]->billing_city }}</td>@endif
                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->getbilling[0]->billing_zip }}</td>@endif
                      @endif

                      @if($customer->customer_contacts->count() >=1)

                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->customer_contacts[0]->name}}</td>@endif

                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->customer_contacts[0]->sur_name}}</td>@endif

                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->customer_contacts[0]->email}}</td>@endif

                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->customer_contacts[0]->telehone_number}}</td>@endif

                      @if(!in_array('2',$not_visible_arr))<td>{{@$customer->customer_contacts[0]->postion}}</td>@endif

                      @endif --}}



                </tr>
                    <!-- @if($customer->getbilling->count() > $customer->customer_contacts->count())
                      @php
                        $loop_count = $customer->getbilling->count();
                      @endphp
                    @else
                      @php
                        $loop_count = $customer->customer_contacts->count();
                      @endphp
                    @endif

                    @for ($j = 1 ; $j < $loop_count ; $j++)
                    <tr>
                      <td>{{@$customer->reference_number}}</td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>
                      <td></td>

                      @if($j < $customer->getbilling->count())

                        <td>{{@$customer->getbilling[$j]->title }}</td>

                        <td>{{@$customer->getbilling[$j]->billing_phone }}</td>

                        <td>{{@$customer->getbilling[$j]->cell_number }}</td>

                        <td>{{@$customer->getbilling[$j]->billing_address }}</td>

                        <td>{{@$customer->getbilling[$j]->tax_id }}</td>

                        <td>{{@$customer->getbilling[$j]->billing_email }}</td>

                        <td>{{@$customer->getbilling[$j]->billing_fax }}</td>

                        <td>{{@$customer->getbilling[$j]->getstate->name }}</td>

                        <td>{{@$customer->getbilling[$j]->billing_city }}</td>

                        <td>{{@$customer->getbilling[$j]->billing_zip }}</td>
                      @else
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
                      @endif

                      @if($j < $customer->customer_contacts->count())

                      <td>{{@$customer->customer_contacts[$j]->name }}</td>

                        <td>{{@$customer->customer_contacts[$j]->sur_name }}</td>

                        <td>{{@$customer->customer_contacts[$j]->email }}</td>

                        <td>{{@$customer->customer_contacts[$j]->telehone_number }}</td>

                        <td>{{@$customer->customer_contacts[$j]->postion }}</td>

                      @else
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                      @endif

                    </tr>
                    @endfor -->
                @endforeach

            </tbody>

    </table>

    </body>
</html>
