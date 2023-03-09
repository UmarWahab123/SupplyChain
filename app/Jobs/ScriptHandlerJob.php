<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ScriptHandlerJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $data;
    public $timeout = 0;
    public $tries = 2;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $data = $this->data;
        foreach ($data as $or) {
            foreach ($or->order_products as $op) {
                $op->total_price_with_vat = round($op->total_price + $op->vat_amount_total,4);
                $op->save();
            }
            $or->ref_prefix = 1;
            $or->total_amount = round($or->order_products->sum('total_price_with_vat'),2);
            $or->save();
        }
        return true;
    }
}
