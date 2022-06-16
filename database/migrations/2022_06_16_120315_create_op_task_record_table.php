<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpTaskRecordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('op_task_record', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('account_id');
            $table->integer('priority')->default(2)->comment('1=Low|2=Default|3=High');
            $table->string('name');
            $table->text('data')->nullable();
            $table->integer('isstarted')->default(0);
            $table->integer('isfinished')->default(0);
            $table->integer('iscancelled')->default(0);
            $table->timestamp('created_date')->useCurrent();
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
        Schema::dropIfExists('op_task_record');
    }
}
