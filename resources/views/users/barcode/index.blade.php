<!DOCTYPE html>
<html lang="en">
<head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta http-equiv="X-UA-Compatible" content="ie=edge">
     <title>Barcode</title>
     <style>
          .brdr,tr,td{
               border: 1px solid black;
               border-collapse : collapse;
          }
          table{
               width:40%;
          }
     </style>

</head>
<body>

     @foreach ($get_prds as $item)
          <table class="brdr"> 
               <tbody>
                    @if(in_array('refrence_code',$serialize_columns))
                         <tr>
                              <td><b>PF</b></td>
                              <td>{{ $item->refrence_code ?? '' }}</td>
                         </tr>
                    @endif
                    @if(in_array('short_desc',$serialize_columns))
                         <tr>
                              <td><b>Short Desc</b></td>
                              <td>{{ $item->short_desc ?? '' }}</td>
                         </tr>
                    @endif
                    @if(in_array('hs_code',$serialize_columns))
                         <tr>
                              <td><b>HS Code</b></td>
                              <td>{{ $item->hs_code ?? '' }}</td>
                         </tr>
                    @endif
                    @if(in_array('product_notes',$serialize_columns))
                         <tr>
                              <td><b>Product Notes</b></td>
                              <td>{{ $item->product_notes ?? '' }}</td>
                         </tr>
                    @endif
                    @if(in_array('category_id',$serialize_columns))
                         <tr>
                              <td><b>Category</b></td>
                              <td>{{ $item->productCategory->title ?? '' }}</td>
                         </tr>
                    @endif
                    @if(in_array('selling_unit',$serialize_columns))  
                         <tr>
                              <td><b>Selling Unit</b></td>
                              <td>{{ $item->sellingUnits->title ?? '' }}</td>
                         </tr>
                    @endif
                    <tr>
                         <td colspan="2"><?php  echo '<img src="data:image/png;base64,' . DNS2D::getBarcodePNG($item->bar_code ? $item->bar_code : $item->refrence_code, 'PDF417',$width,$height) . '" alt="barcode"   />'; ?></td>
                    </tr>
               </tbody>
          </table>
          <br><br><br><br><br>
     @endforeach
          
     <div><?php   ?></div>

</body>
</html>