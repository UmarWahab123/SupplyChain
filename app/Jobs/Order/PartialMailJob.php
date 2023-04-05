<?php

namespace App\Jobs\Order;

use App\Mail\PartialMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class PartialMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $order = null;
    protected $tries = 1;
    public function __construct($order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $order = $this->order;
        $data = array(
                  'from' => config('app.mail_username'),
                  'to' => config('app.partial_email'),
                );
        Mail::to($data['to'])->send(new PartialMail($order->id, $data['from']));
    }
}
