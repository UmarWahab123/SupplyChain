<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Auth;
use App\Variable;
class PrintHistory extends Model
{
    
    public function user(){
    	return $this->belongsTo('App\User', 'user_id', 'id');
    }

    // public function order(){
    // 	return $this->belongsTo('App\Models\Common\Order\Order', 'order_id', 'id');
    // }

    public static function saveHistory($id, $print_type,$page_type)
    {
      $print_history             = new PrintHistory;
      $print_history->order_id    = $id;
      $print_history->user_id    = Auth::user()->id;
      $print_history->print_type = 'proforma';
      $print_history->page_type = $page_type;
      $print_history->save();

      return true;
    }

    public static function getTerminology($slug)
    {
      $vairables=Variable::select('slug','standard_name','terminology')->whereIn('slug',$slug)->get();
      $global_terminologies=[];
      foreach($vairables as $variable)
      {
          if($variable->terminology != null)
          {
              $global_terminologies[$variable->slug]=$variable->terminology;
          }
          else
          {
              $global_terminologies[$variable->slug]=$variable->standard_name;
          }
      }

      return $global_terminologies;
    }
}
