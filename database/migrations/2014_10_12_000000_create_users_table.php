<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('country')->nullable();
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('email_vcode')->nullable();
            $table->string('zipcode')->nullable();
            $table->string('image')->nullable();
            $table->unsignedTinyInteger('status')->default(0)
            ->comment('0=Unverified,1=Active,2=Inactive');
            $table->timestamp('email_verified_at')->nullable();
            $table->integer('user_type')->default(3)->comment('1=>SuperAdmin|2=>Admin|=>User');
            $table->string('password');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
