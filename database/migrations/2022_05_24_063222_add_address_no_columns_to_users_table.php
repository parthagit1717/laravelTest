<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddressNoColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
           if (!Schema::hasColumn('users', 'country')){
             $table->string('country')->nullable()->after('name');
           };
        });

        Schema::table('users', function (Blueprint $table) {
           if (!Schema::hasColumn('users', 'state')){
            $table->string('state')->nullable()->after('country');
           };
        });

        Schema::table('users', function (Blueprint $table) {
           if (!Schema::hasColumn('users', 'city')){
            $table->string('city')->nullable()->after('state');
           };
        });

        Schema::table('users', function (Blueprint $table) {
           if (!Schema::hasColumn('users', 'zipcode')){
            $table->string('zipcode')->nullable()->after('city');
           };
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['country','state','city','zipcode']);
        });
    }
}
