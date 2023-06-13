<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\ExportStatus;
use App\FailedJobException;
use App\User;
use Exception;

class WoocommerceProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $productIds;
    protected $user_id;
    
    protected $tries = 2;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($productIds, $user_id){
    $this->productIds = $productIds;
    $this->user_id = $user_id;
        
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $productIds = $this->productIds;
        $user_id = $this->user_id;

        $type = 'wocommerce_products';
        $status = ExportStatus::where('user_id',$user_id)->where('type',$type)->first();
        $status->status = 0;
        $status->save();
    }
}
