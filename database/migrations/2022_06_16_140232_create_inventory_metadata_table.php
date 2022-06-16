<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryMetadataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_metadata', function (Blueprint $table) {
            $table->id();
            $table->integer('account_id')->nullable();
            $table->integer('inventory_id')->nullable();
            $table->string('sku')->nullable();
            $table->integer('config_id')->nullable();
            $table->text('extrainfo')->nullable();
            $table->string('meta_key');
            $table->longText('meta_value')->nullable();
            $table->integer('service_flag')->default(1);
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
        Schema::dropIfExists('inventory_metadata');
    }
}
