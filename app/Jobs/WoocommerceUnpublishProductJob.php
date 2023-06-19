<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\ExportStatus;
use App\User;
use Exception;
use App\FailedJobException;
use App\Models\Common\ShareProduct;
use App\ProductHistory;
use App\Models\Common\Deployment;
use App\Helpers\GuzzuleRequestHelper;

class WoocommerceUnpublishProductJob implements ShouldQueue
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
    public function __construct($productIds,$user_id)
    {
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
        $type = 'unpublish_wocommerce_products';
        $deployment = Deployment::where('type','woocommerce')->first();
        $status = ExportStatus::where('user_id',$user_id)->where('type',$type)->first();
        $status->status = 0;
        $status->save();
        $productIds = explode(',',$productIds);
        $token = config('app.external_token');
        $body = ['ids' => $productIds];
        $url = @$deployment->url.'/wp-json/supplychain-woocommerce/v1/delete-products/';
        $method = "Post";
        
        try{
            $response = GuzzuleRequestHelper::guzzuleRequest($token, $url, $method, $body, $with_header = false);
        }
        catch(RequestException $e){
            $statusCode = $e->getResponse()->getStatusCode();
            $errorMessage = $e->getMessage();
            throw new Exception("Request failed with status code: $statusCode. Error message: $errorMessage");
        }
        if($response['success'] == true){
            foreach($productIds as $productId){
                $checkAlreadyUnsharedProduct = ShareProduct::where('product_id', $productId)->where('store_type','woocommerce')->first();
                if($checkAlreadyUnsharedProduct){
                    $checkAlreadyUnsharedProduct->delete();
                }
                $product_history = new ProductHistory;
                $product_history->user_id     = $user_id;
                $product_history->product_id  = $productId;
                $product_history->column_name = 'product disabled by ' . $user_id;
                $product_history->old_value   = '---';
                $product_history->new_value   = "disabled";
                $product_history->save();
               
            }
        }
    }
}
