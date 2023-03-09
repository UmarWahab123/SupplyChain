@extends('sales.layouts.layout')

@section('title','Supplier Detail | Supply Chain')

@section('content')
@php
use App\Models\Common\ProductCategory;
use App\Models\Common\SupplierCategory;
@endphp
<style type="text/css">
.invalid-feedback {
     font-size: 100%; 
}
.disabled:disabled{
  opacity:0.5;
  cursor: not-allowed; 
}
p
{
  font-size: small;
  font-style: italic;
  color: red;
}

</style>

{{-- Content Start from here --}}
<!-- New Design Starts Here -->
<!-- Right Content Start Here -->
<div class="right-content pt-0 pl-0 pr-0">

<div class="row mb-3">

<div class="col-lg-12 headings-color mb-2">
<div class="row">
  <div class="col-lg-1">
  @if(@$supplier->logo != Null && file_exists( public_path() . '/uploads/sales/customer/logos/' . @$supplier->logo))
  <div class="logo-container">
  <img src="{{asset('public/uploads/sales/customer/logos/'.$supplier->logo)}}" style="border-radius:50%;height:68px;" alt="logo" class="img-fluid lg-logo">
  <div class="overlay">
  </div>
</div>
  @else
  <div class="logo-container">
  <img src="{{asset('public/uploads/sales/customer/logos/profileImg.png')}}" alt="Avatar" class="image">
  <div class="overlay">

  </div>
</div>
  @endif
  </div>
  <div class="col-lg-2 p-0">
  <h5 class="fontbold headings-color mb-0 mt-4">Supplier Detail Page</h5>
  </div>
  <div class="col-lg-2">
<a href="{{  url('sales/list-supplier') }}" class="btn pull-right mt-4">Back</a>
    
  </div>
</div>

</div>
  <div class="col-lg-5 headings-color">
  <div class="row">
    <div class="col-lg-12">
    <div class="bg-white h1-00">
    <table  class="table-responsive table sales-customer-table dataTable const-font" style="width: 100%;">
      <tbody>
        
      <input type="hidden" name="supplier_id" id="supplier_id" value="{{$id}}">

      <tr>
        <td class="fontbold"><div style="width:250px">Reference # <b style="color: red;">*</b></div></td>
        <td> 
          <span class="m-l-15 " id="reference_number">
          {{(@$supplier->reference_number!=null)?@$supplier->reference_number:'N/A'}}
          </span>
        </td>
      </tr>

      <tr>
        <td class="fontbold">{{$global_terminologies['company_name']}} <b style="color: red;">*</b></td>
        <td class="text-nowrap">
          <span class="m-l-15 " id="company"  >
          {{(@$supplier->company!=null)?@$supplier->company:'N/A'}}
          </span>
        </td>
      </tr>

      <tr>
        <td class="fontbold">Primary Contact <b style="color: red;">*</b></td>
        <td>
          <span class="m-l-15 " id="phone">
          {{(@$supplier->phone!=null)?@$supplier->phone:'N/A'}}
          </span>        
        </td>
      </tr>

      <tr>
        <td class="fontbold">Addresses <b style="color: red;">*</b></td>
        <td class="text-nowrap">
          <span class="m-l-15 " id="address_line_1">
            {{$supplier->address_line_1}}
          </span>
          <br>
          @if($supplier->address_line_2 != NULL)
          <span class="m-l-15 " id="address_line_2">
          {{$supplier->address_line_2}}
          </span>     
          @endif
        </td>
      </tr>

      <tr>
        <td class="fontbold">Country</td>
        <td>
          <span class="m-l-15 " id="country">
            {{$supplier->country != null ? $supplier->getcountry->name : 'N.A'}}
          </span>
      </td>
      </tr>

      <tr>
        <td class="fontbold">@if(!array_key_exists('state', $global_terminologies)) State @else {{$global_terminologies['state']}} @endif</td>
        <td>
          <span class="m-l-15 " id="state">
            {{$supplier->state != null ? $supplier->getstate->name : 'N.A'}}
          </span>
        </td>
      </tr>

      <tr>
        <td class="fontbold">City</td>
        <td>
          <span class="m-l-15 " id="city">
            {{$supplier->city}}
          </span>
        </td>
      </tr>

      @if($supplier->secondary_phone != NULL)
      <tr>
        <td class="fontbold text-nowrap">Additional Contacts </td>
        <td>
          <span class="m-l-15 " id="additional-contact">
          {{$supplier->secondary_phone}}
          </span>
        </td>
      </tr>
      @endif

      <tr>
        <td class="fontbold">Payment Terms <b style="color: red;">*</b></td>
        <td>
          <span class="m-l-15 " id="credit_term">
          {{$supplier->credit_term != null ? @$supplier->getpayment_term->title : 'N.A'}}
          </span>
        </td>
      </tr>

      <tr>
        <td class="fontbold">Currency <b style="color: red;">*</b></td>
        <td>
          <span class="m-l-15 " id="currency_id">
          {{$supplier->currency_id != null ? @$supplier->getCurrency->currency_name : 'N.A'}}
          </span>
         
        </td>
      </tr>   

      <tr>
        <td class="fontbold">Tags</td>
          @php
          $multi_tags = null;
              if($supplier->main_tags != null)
              $multi_tags = explode(',', $supplier->main_tags);    
          @endphp
          <td width="100%">
            @if($multi_tags != null)
          @foreach($multi_tags as $tag)
          <span class="abc">{{@$tag}}</span>  
          @endforeach
          
          @else
          <span class="" style="color:red;">No tags found</span>
          
          @endif
          @php 
          $string='';
          @endphp
           @if($multi_tags != null)
         @foreach($multi_tags as $tag)
         @php $string .=  $tag.','; @endphp
          @endforeach 
          @else
          @php $string = ''; @endphp
          @endif 
        </td>
    </tr>

    <tr>
      <td class="fontbold">Categories  <b style="color: red;">*</b></td>
      <td>
        <span class="m-l-15" id="category_id">
        @if($SupplierCat_count > 0)
          
          @foreach($categories as $pCat)
            @php
            $getChild = SupplierCategory::with('supplierCategories')
            ->whereIn('category_id',$pCat->get_Child->pluck('id')->toArray())
            ->where('supplier_id',$id)->get();
            @endphp
            
            @if($getChild->count() > 0)
              <b>{{$pCat->title}}</b>
              <ul>
                  @foreach($getChild as $subCat)
                  <li>{{$subCat->supplierCategories->title}}</li>
                  @endforeach
              </ul>
            @endif
          @endforeach
        @else
          <p>No Categories Found</p>
          
        @endif
        </span>
      </td>
    </tr>
  </tbody>
  </table>
  </div>
  </div>
  

  <div class="col-lg-12 d-none">
  <h4 class="pb-2 mt-2">Vendor Score Card</h4>

  <div class="bg-white table-responsive">
    <table  class=" table  headings-color mb-0 const-font" style="width: 100%;">
      <thead class="sales-coordinator-thead">
        <tr>
          <th>Event Date</th>
          <th>Event Type </th>
          <th>Description</th>
          <th>Points</th> 
        </tr>
      </thead>
      <tbody class="sale-form-page">
        <tr>
          <td>23/09/2019</td>
          <td> 
            <div>
              <select>
                <option>xyz</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
              </select>
            </div>
          </td>
          <td>abc</td>
          <td>10</td>
        </tr>
        <tr>
          <td>23/09/2019</td>
          <td> 
            <div>
              <select>
                <option>xyz</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
              </select>
            </div>
          </td>
          <td>abc</td>
          <td>10</td>
        </tr>
        <tr>
          <td>23/09/2019</td>
          <td> 
            <div>
              <select>
                <option>xyz</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
              </select>
            </div>
          </td>
          <td>abc</td>
          <td>10</td>
        </tr>
        <tr>
          <td>23/09/2019</td>
          <td> 
            <div>
              <select>
                <option>xyz</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
              </select>
            </div>
          </td>
          <td>abc</td>
          <td>10</td>
        </tr>
        <tr>
          <td>23/09/2019</td>
          <td> 
            <div>
              <select>
                <option>xyz</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
              </select>
            </div>
          </td>
          <td>abc</td>
          <td>10</td>
        </tr>
        <tr>
          <td>23/09/2019</td>
          <td> 
            <div>
              <select>
                <option>xyz</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
              </select>
            </div>
          </td>
          <td>abc</td>
          <td>10</td>
        </tr>
        <tr>
          <td>23/09/2019</td>
          <td> 
            <div>
              <select>
                <option>xyz</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
              </select>
            </div>
          </td>
          <td>abc</td>
          <td>10</td>
        </tr>
        <tr>
          <td>23/09/2019</td>
          <td> 
            <div>
              <select>
                <option>xyz</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
              </select>
            </div>
          </td>
          <td>abc</td>
          <td>10</td>
        </tr>
        
        <tr>
          <td>    
            <a href="#" class="supplier-detail-plus">+</a>
          </td>
        </tr>
      
      </tbody>
    </table>

    
  </div>
  </div>

  </div>
  </div>
  <div class="col-lg-6 overflow-hidden table-responsive">
  <div class="bg-white h-80 p-2">
  <!-- <div class="tab-content table-responsive" id="myTabContent">
  <div class="tab-pane fade show active p-3" id="salesone" role="tabpanel" aria-labelledby="one-tab"> -->
  <table  class="table headings-color const-font" style="width:100%">
    <thead class="sales-coordinator-thead ">           
      <tr style="border-bottom: 2px dashed #eee;">
          <th>Sn # </th>
          <th>Month </th>
          <th>Paid POs</th>
          <th>Unpaid POs</th>
          <th>Due POs</th>                                
      </tr>
    </thead>
    <tbody class="dot-dash">
      <tr>
        <td>1</td>
        <td>January 2019 </td>
        <td>$200.00</td>
        <td>$200.00</td>
        <td>$200.00</td>
      </tr>
       <tr>
        <td>1</td>
        <td>January 2019 </td>
        <td>$200.00</td>
        <td>$200.00</td>
        <td>$200.00</td>
      </tr>
       <tr>
        <td>1</td>
        <td>January 2019 </td>
        <td>$200.00</td>
        <td>$200.00</td>
        <td>$200.00</td>
      </tr>
    </tbody>
  </table>


  </div>
  </div>
</div>
</div> 


<!--  Content End Here -->

@endsection

@section('javascript')
<script type="text/javascript">

</script>
@stop