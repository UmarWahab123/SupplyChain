<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
            <tr>
              <th>Sales Person</th>
              <th>Order#</th>
              <th>Draft#</th>
              <th>Customer#</th>
              <th>@if(!array_key_exists('reference_name', $global_terminologies)) Reference Name  @else {{$global_terminologies['reference_name']}} @endif</th>
              <th>Date Purchase</th>
              <th>Order Total</th>
              <th>@if(!array_key_exists('target_ship_date', $global_terminologies)) Target Ship Date @else {{$global_terminologies['target_ship_date']}} @endif</th>
              <th>Memo</th>
              <th>Status</th>
            </tr>
        </thead>
            <tbody>
              @if($query->count() > 0)
                @foreach($query as $item)
                  <tr>
                    <td>{{$item->customer !== null ? @$item->customer->primary_sale_person->name : '--'}}</td>
                    <td>
                      <?php
                        if($item->in_status_prefix !== null)
                        {
                          $ref_no = $item->in_status_prefix.'-'.$item->in_ref_prefix.$item->in_ref_id;
                          ?>
                          {{$ref_no}}
                          <?php
                        }
                        else
                        { 
                          ?>
                          {{'--'}}
                          <?php
                        }
                      ?>
                    </td>
                    <td>
                      <?php
                        if($item->status_prefix !== null)
                        {
                          $ref_no = $item->status_prefix.'-'.$item->ref_prefix.$item->ref_id;
                          ?>
                          {{$ref_no}}
                          <?php
                        }
                        else
                        { 
                          ?>
                          {{'--'}}
                          <?php
                        }
                      ?>
                    </td>
                    <td>
                      <?php
                        if($role_id == 3){
                          ?>
                          {{$item->customer->reference_number}}
                          <?php
                        }else{
                          ?>
                          {{$item->customer->reference_number}}
                          <?php
                        }
                      ?>
                    </td>
                    <td>
                      <?php
                        if($item->customer_id != null){
                          if($role_id == 3){
                            if($item->customer['reference_name'] != null)
                            {
                              ?>
                            {{$item->customer['reference_name']}}
                            <?php
                            }
                            else
                            {
                            ?>
                            {{$item->customer['first_name'].' '.$item->customer['last_name']}}
                            <?php
                            }
                          }else{
                            if($item->customer['reference_name'] != null)
                            {
                              ?>
                              {{$item->customer['reference_name']}}
                              <?php
                            }
                            else
                            {
                              ?>
                            {{$item->customer['first_name'].' '.$item->customer['last_name']}}
                            <?php
                            }
                          }
                        }else{
                          ?>
                          {{'N.A'}}
                          <?php
                        }
                      ?>
                    </td>
                    <td>
                      <?php
                        $date = Illuminate\Support\Carbon::parse(@$item->updated_at)->format('d/m/Y');
                      ?>
                      {{$date}}
                    </td>
                    <td>
                      <?php
                        $total = number_format($item->total_amount,2,'.','');
                      ?>
                      {{$total}}
                    </td>
                    <td>
                      <?php
                        $terget_date = Illuminate\Support\Carbon::parse(@$item->target_ship_date)->format('d/m/Y');
                      ?>
                      {{$terget_date}}
                    </td>
                    <td>{{$item->memo != null ? @$item->memo : '--'}}</td>
                    <td>{{$item->statuses->title}}</td>
                  </tr>
                @endforeach
              @endif
            </tbody> 
    
    </table>

    </body>
</html>