<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->nullable();
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('barcode_type')->nullable()->comment('barcode type eg.EAN,UPC Etc');
            $table->string('asin')->nullable();
            $table->string('name')->nullable();
            $table->string('product_name_description')->nullable();
            $table->integer('type')->default(1)->comment('0=>simple 1=> child 2=>variable 3=> Bundle');
            $table->integer('parent_id')->default(0);
            $table->longText('short_description')->nullable();
            $table->longText('long_description')->nullable();
            $table->integer('quantity')->nullable();
            $table->integer('stock_status')->default(1)->comment('0=>outofstock 1 =>instock');
            $table->string('currency')->comment('default=>GBP');
            $table->decimal('buy_price',15, 2)->nullable();
            $table->decimal('rrp',15, 2)->nullable();
            $table->decimal('price',15, 2)->default(0.00);
            $table->decimal('sale_price',15, 2)->nullable();
            $table->decimal('vat',15, 2)->nullable();
            $table->string('brand')->nullable();
            $table->text('location')->nullable();
            $table->text('supplier')->nullable();
            $table->integer('supplier_id')->nullable();
            $table->integer('status')->nullable()->comment('0=>preimport 1=>import 2=>import with images 3=> discontinue products');
            $table->integer('category_id')->nullable();
            $table->integer('purchase_order_id')->nullable();
            $table->integer('suggested_purchase_order_below_stock')->nullable();
            $table->string('supplier_sku')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventory');
    }
}
