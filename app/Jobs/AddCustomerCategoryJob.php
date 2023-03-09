<?php

namespace App\Jobs;
use App\ExportStatus;
use App\FailedJobException;
use App\Models\Common\CustomerCategory;
use App\Models\Common\CustomerTypeCategoryMargin;
use App\Models\Common\CustomerTypeProductMargin;
use App\Models\Common\Product;
use App\Models\Common\ProductCategory;
use App\Models\Common\ProductFixedPrice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AddCustomerCategoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

   public $timeout = 12000;
   public $tries = 1;
   protected $user_id;
   protected $title;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($title,$user_id)
    {
        $this->title = $title;
        $this->user_id = $user_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      try
      {
        $user_id = $this->user_id;
        $title   = $this->title;

        $product_categories = ProductCategory::select('id')->where('parent_id','!=',0)->get();  // getting all product categories
        foreach ($product_categories as $category) 
        {
          $categoryMarginsNew = new CustomerTypeCategoryMargin;
          $categoryMarginsNew->category_id      = $category->id;
          $categoryMarginsNew->customer_type_id = $title;
          $categoryMarginsNew->default_margin   = "Percentage";
          $categoryMarginsNew->default_value    = 0;
          $categoryMarginsNew->save();
        }
          
        $products = Product::select('id')->get();
        foreach ($products as $product) 
        {
          $categoryMarginsNew = new CustomerTypeProductMargin;
          $categoryMarginsNew->product_id       = $product->id;
          $categoryMarginsNew->customer_type_id = $title;
          $categoryMarginsNew->default_margin   = "Percentage";
          $categoryMarginsNew->default_value    = 0;
          $categoryMarginsNew->save();
         
          $productFixedPrices = new ProductFixedPrice;
          $productFixedPrices->product_id       = $product->id;
          $productFixedPrices->customer_type_id = $title;
          $productFixedPrices->fixed_price      = 0;
          $productFixedPrices->expiration_date  = NULL;
          $productFixedPrices->save();
        }
        ExportStatus::where('type','add_customer_type')->update(['status' => 0]);
        // changing the status to 0 of added category
        $findCategory = CustomerCategory::find($title);
        $findCategory->is_deleted = 0;
        $findCategory->save();
      }
      
      catch(Exception $e) {
        $this->failed($e);
      }
      catch(MaxAttemptsExceededException $e) {
        $this->failed($e);
      }
    }

    public function failed($exception)
    {
      ExportStatus::where('type','add_customer_type')->where('user_id',$this->user_id)->update(['status'=>2,'exception'=>$exception->getMessage()]);
      $failedJobException=new FailedJobException();
      $failedJobException->type="Customer Category Type";
      $failedJobException->exception=$exception->getMessage();
      $failedJobException->save();
    }
}


