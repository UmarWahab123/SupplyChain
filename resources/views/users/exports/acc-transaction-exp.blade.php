@php
use Carbon\Carbon;

@endphp
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <table>
        <thead>
            <tr>
                <th><b>Payment Reference</b></th>
                <th><b>Received Date</b></th>
                <th><b>Invoice # </b></th>
                <th><b>Reference Name</b></th>
                <th><b>{{$global_terminologies['company_name']}}</b></th>
                <th><b>Delivery Date</b></th>
                <th><b>Invoice Total</b></th>
                <th><b>Total Paid Vat</b></th>
                <th><b>Total Paid Non Vat</b></th>
                <th><b>Total Paid</b></th>
                <th><b>{{$global_terminologies['difference']}}</b></th>
                <th><b>Payment method</b></th>
                <th><b>Sales Person</b></th>
                <th><b>Remarks</b></th>

            </tr>
        </thead>
            <tbody>
                @foreach($query as $item)
                <tr>
                  <td>{{$item->payment_reference}}</td>
                  <td>{{$item->received_date}}</td>
                  <td>{{$item->invoice_number}}</td>
                  <td>{{$item->reference_name}}</td>
                  <td>{{$item->billing_name}}</td>
                  <td>{{$item->delivery_date}}</td>
                  <td>{{$item->invoice_total}}</td>
                  <td>{{$item->total_paid_vat}}</td>
                  <td>{{$item->total_paid_non_vat}}</td>
                  <td>{{$item->total_paid}}</td>
                  <td>{{$item->difference}}</td>
                  <td>{{$item->payment_method}}</td>
                  <td>{{$item->sale_person}}</td>
                  <td>{{$item->remarks}}</td>
                </tr>

                @endforeach

            </tbody>

    </table>

    </body>
</html>
