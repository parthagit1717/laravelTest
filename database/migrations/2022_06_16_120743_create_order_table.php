<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->nullable();
            $table->string('SupplierOrderNumber');
            $table->string('OrderDate')->nullable();
            $table->string('order_type')->nullable();
            $table->integer('service_config_id')->nullable();
            $table->string('CustomerID')->nullable();
            $table->string('CurrentlyTrading')->nullable();
            $table->string('TrainingIndicator')->nullable();
            $table->string('SupplierFulfilmentRoute')->nullable();
            $table->string('OrderingStorenumber')->nullable();
            $table->string('OrderCurrency')->nullable();
            $table->decimal('OrderTotal',15, 2)->nullable();
            $table->decimal('ordertax',15, 2)->nullable();
            $table->string('CustomerOrderNumber')->nullable();
            $table->string('Barcode')->nullable();
            $table->string('ExpectedDeliveryDate')->nullable();
            $table->string('DoNotDeliverBeforeDate')->nullable();
            $table->string('OdexSenderID')->nullable();
            $table->string('OdexReceiverID')->nullable();
            $table->string('OdexMessageType')->nullable();
            $table->string('exportStatus')->default(0)->comment('1 = exported');
            $table->string('delivery_method')->nullable();
            $table->string('order_type_ekm')->nullable();
            $table->string('order_status')->nullable();
            $table->text('order_status_note')->nullable();
            $table->integer('display_status')->default(1);
            $table->string('stock_status')->default(0)->comment('1=back_in_stock, 2=unsellable, 3=damaged, 4=refund, 5=exchanged');
            $table->enum('dropbox_flag', ['0', '1'])->default('0');
            $table->enum('is_marked', ['0', '1'])->default('0');
            $table->text('mark_note')->nullable();
            $table->integer('onepatch_customer_id')->nullable();
            $table->enum('is_printed', ['0', '1'])->default('0')->comment('1 = printed');
            $table->integer('location_id')->default(0);
            $table->integer('created_by')->default(0);
            $table->decimal('cash_amount',15, 2)->nullable()->comment('if order type cash then how much amount customer gives and then return the change');
            $table->decimal('card_amount',15, 2)->nullable();
            $table->decimal('card_left_amount',15, 2)->nullable();
            $table->decimal('cash_left_amount',15, 2)->nullable();
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
        Schema::dropIfExists('order');
    }
}
