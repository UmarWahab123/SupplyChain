@if(@$config->server == 'lucilla')
<table class="custom_font" style="margin-top: 50px;">
   <tr>
  <td style="border: none !important;margin-top: 20px;" colspan="2" width="170pt">
  	<b>ผู้รับของ / Receiver</b>
  	<br><br>
  	<b>_______________</b>
  </td>
  <td style="border: none !important;" colspan="2" width="170pt">
  	<b>ผู้ส่งของ / Delivered By</b>
  	<br><br>
  	<b>__________________</b>
  </td>
  <td style="border: none !important;" colspan="2">
  	<b>ผู้มีอำนาจลงนาม / Authorized Signature</b>
  	<br><br>
  	<b>_________________________________</b>
  </td>

</tr>
</table>
@elseif(@$order->primary_status == 3)
<table class="custom_font" style="margin-top: 50px;">
   <tr>
  <td style="border: none !important;margin-top: 20px;" colspan="2" width="170pt"><b>ผู้รับของ / Receiver</b></td>
  <td style="border: none !important;" colspan="2" width="170pt"><b>ผู้ส่งของ / Delivered By</b></td>
  <td style="border: none !important;" colspan="2"><b>ผู้มีอำนาจลงนาม / Authorized Signature</b></td>

</tr>
</table>
@endif