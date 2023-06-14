<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\ExportStatus;
use Illuminate\Support\Str;
use App\FailedJobException;
use App\User;
use Exception;
use App\Models\Common\ShareProduct;
use App\Models\Common\Deployment;
use App\Helpers\GuzzuleRequestHelper;

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
        $user_id = $this->user_id;
        $productIds = $this->productIds;
        $type = 'wocommerce_products';
        $status = ExportStatus::where('user_id',$user_id)->where('type',$type)->first();
        $status->status = 0;
        $status->save();
        $productIds = explode(',', $productIds);
        $deployment = Deployment::where("type","woocommerce")->first();
        $warehose_id = @$deployment->warehouse_id;
        $token = config('app.external_token');
        $body = ['ids' => $productIds];
        $url = @$deployment->url.'/wp-json/supplychain-woocommerce/v1/update-products-from-marketplace/';
        $method = "Post";
        try {
        $response =GuzzuleRequestHelper::guzzuleRequest($token, $url, $method, $body, $with_header = false);
        } catch (RequestException $e) {
            $statusCode = $e->getResponse()->getStatusCode();
            $errorMessage = $e->getMessage();
            throw new Exception("Request failed with status code: $statusCode. Error message: $errorMessage");
        }
        dd($response['success']);
        if($response['success'] == true){
            foreach ($productIds as $productId) {
            $checkAlreadyShareProduct = ShareProduct::where('product_id', $productId)->first();
                if (!$checkAlreadyShareProduct) {
                $shareProduct = new ShareProduct();
                $shareProduct->product_id = $productId;
                $shareProduct->user_id = $user_id;
                $shareProduct->store_type = "woocommerce";
                $shareProduct->save();
            }
                
                $product_history = new ProductHistory;
                $product_history->user_id     = $user_id;
                $product_history->product_id  = $productId;
                $product_history->column_name = 'product enabled by ' . $user_id;
                $product_history->old_value   = '---';
                $product_history->new_value   = "enabled";
                $product_history->save();
            } 
        }
        
    }
}
