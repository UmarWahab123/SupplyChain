<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CreateOrReplaceProductRecordsViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'view:CreateOrReplaceProductRecordsView';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create or Replace SQL View.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        DB::statement("
        CREATE VIEW employees_records 
        AS
        SELECT
            products.id as product_id,
            products.refrence_code,
            products.short_desc,
            products.buying_unit,
            products.selling_unit,
            products.type_id,
            products.brand, 
            products.product_temprature_c,
            products.total_buy_unit_cost_price,
            products.weight,
            products.unit_conversion_rate,
            products.selling_price,
            products.vat,
            products.import_tax_book,
            products.hs_code,
            products.primary_category,
            product_categories.title as primary_category_title,
            products.category_id,
            product_categories.title as category_title,
            products.supplier_id,
            supplier_products.supplier_description,

        FROM
            products
            LEFT JOIN brands ON products.brand_id = brands.id
            LEFT JOIN product_categories ON products.parent_category = product_categories.id
            LEFT JOIN product_categories ON products.category_id = product_categories.id
            LEFT JOIN supplier_products ON products.id = supplier_products.product_id
    ");
    }
}
