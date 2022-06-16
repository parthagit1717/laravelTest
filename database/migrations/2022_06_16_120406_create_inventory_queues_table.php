<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryQueuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('task_id');
            $table->unsignedBigInteger('config_id');
            $table->longText('extra_details')->nullable();
            $table->integer('priority')->default(2)->comment('1=Low|2=Default|3=High');
            $table->integer('offset')->default(0)->comment('offset');
            $table->integer('limit')->default(0)->comment('limit');
            $table->longText('api_response')->nullable();
            $table->integer('api_status')->default(0)->comment('0=Error|1=Success');
            $table->longText('type')->nullable()->comment('servivename_import,servivename_upload');
            $table->integer('status')->default(0)->comment('0=>pending|1=>started|2=>finished|3=>cancelled');
            $table->integer('display_status')->default(0)->comment('only for show in the upload process. 0=> unread|1=> read');
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
        Schema::dropIfExists('inventory_queues');
    }
}
