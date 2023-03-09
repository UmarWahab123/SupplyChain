@extends('importing.layouts.layout')
@section('title','Recieving Queue')


@section('content')

{{-- Content Start from here --}}

<div class="right-content pt-0 headings-color">

    <div class="row">
    <div class="col-lg-10">
  <h4>Recived Queue</h4>
</div>
 <div class="col-lg-2">
   <div class="form-group">
                <select class="form-control">
                    <option>Received</option>
                    <option>1</option>
                    <option>2</option>
                    <option>3</option>
                </select>
                </div>
</div>
</div>




<div class="row mb-3">
    <div class="col-lg-12">
<div class="bg-white table-responsive">
  <table id="example" class="table headings-color" style="width:100%">
        <thead class="sales-coordinator-thead">
            <tr>
                <th class="first-coloum"></th>
                <th>Group#</th>
                <th>PO#</th>
                <th>Vendor</th>
                <th>Vendor Ref#</th>
                <th>Issue Date </th>
                <th> PO Total</th>
                <th>Target Received Date</th>
                <th>Action</th>
                                                                 
            </tr>
        </thead>
        <tbody class="dot-dash">
            <tr>
                <td></td>
                <td>#1</td>
                <td>1,2</td>
                <td>Gleichner</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
               <td class="fontbold icon-size" ><i class="fa fa-eye"></i></td>
            </tr>
            <tr>
                <td></td>
               <td>#1</td>
                <td>1,2</td>
                <td>Gleichner</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
               <td class="fontbold icon-size" ><i class="fa fa-eye"></i></td>
            </tr>
           <tr>
            <td></td>
              <td>#1</td>
                <td>1,2</td>
                <td>Gleichner</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
               <td class="fontbold icon-size" ><i class="fa fa-eye"></i></td>
            </tr>
          <tr>
               <td></td>
                <td>#1</td>
                <td>1,2</td>
                <td>Gleichner</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
               <td class="fontbold icon-size" ><i class="fa fa-eye"></i></td>
            </tr>
            <tr>
              <td></td>
             <td>#1</td>
                <td>1,2</td>
                <td>Gleichner</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
               <td class="fontbold icon-size" ><i class="fa fa-eye"></i></td>
            </tr>
           <tr>
            <td></td>
               <td>#1</td>
                <td>1,2</td>
                <td>Gleichner</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
               <td class="fontbold icon-size" ><i class="fa fa-eye"></i></td>
            </tr>
           <tr>
            <td></td>
               <td>#1</td>
                <td>1,2</td>
                <td>Gleichner</td>
                <td>12345</td>
                <td>14/09/2019</td>
                <td>$ 12,235</td>
                <td>14/09/2019</td>
               <td class="fontbold icon-size" ><i class="fa fa-eye"></i></td>
            </tr>
           
        </tbody>
    </table>
</div>


</div>
</div>



</div>

@endsection


@section('javascript')
<script type="text/javascript">
 
</script>
@stop

