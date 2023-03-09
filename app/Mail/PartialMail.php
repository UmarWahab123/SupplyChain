<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Models\Common\Order\Order;
use App\Models\Common\Order\OrderProduct;
use Illuminate\Support\Carbon;

class PartialMail extends Mailable
{
    use Queueable, SerializesModels;
    public $order_id = null;
    public $fr_email = null;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($order_id,$fr_email)
    {
        $this->order_id = $order_id;
        $this->fr_email = $fr_email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $order_id = $this->order_id;
        $fr_email = $this->fr_email;
        $getOrder = Order::with('order_products')->find($order_id);
        $ref_no   = @$getOrder->status_prefix.'-'.@$getOrder->ref_prefix.@$getOrder->ref_id;
        $subject  = "Order ".$ref_no;
        $getNow   = Carbon::now();
        $createBy = $getOrder->user_created->name;
        
        $getOrderProducts = OrderProduct::with('product')->where('order_id',$order_id)->where('is_billed','Product')->get();

        return $this->from($fr_email, $fr_email)
                ->subject($subject)
                ->markdown('emails.partial_email')
                ->with([        
                   'order_id' => $order_id,
                   'getOrder' => $getOrder,
                   'getNow'   => $getNow,
                   'createBy' => $createBy,
                   'getOrderProducts' => $getOrderProducts,
                ]);
        // return $this->subject($subject)->view('emails.partial_email',compact('order_id','getOrder'));
    }
}
