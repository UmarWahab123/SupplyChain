<?php

namespace App\Jobs\Order;

use App\Models\Common\StockManagementOut;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ConfirmPickInstructionStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $stock = null;
    protected $order_product = null;
    protected $quantity = null;
    protected $warehouse_id = null;
    protected $user_id = null;
    protected $stock_out = null;
    public function __construct($stock, $order_product, $quantity, $warehouse_id, $user_id, $stock_out)
    {
        $this->stock = $stock;
        $this->order_product = $order_product;
        $this->quantity = $quantity;
        $this->warehouse_id = $warehouse_id;
        $this->user_id = $user_id;
        $this->stock_out = $stock_out;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $stock = $this->stock;
        $order_product = $this->order_product;
        $quantity = $this->quantity;
        $warehouse_id = $this->warehouse_id;
        $user_id = $this->user_id;
        $stock_out = $this->stock_out;

        $stock_out = StockManagementOut::addManualAdjustment($stock, $order_product, $quantity, $warehouse_id, null, true, $user_id);

        $find_stock_from_which_order_deducted = StockManagementOut::findStockFromWhicOrderIsDeducted($quantity, $stock, $stock_out, $order_product);
    }
}
