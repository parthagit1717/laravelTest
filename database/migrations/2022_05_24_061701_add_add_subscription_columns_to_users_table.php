<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAddSubscriptionColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
           if (!Schema::hasColumn('users', 'subs_id')){
             $table->unsignedTinyInteger('subs_id')->nullable()->after('status');
           };
        });

        Schema::table('users', function (Blueprint $table) {
           if (!Schema::hasColumn('users', 'subs_start')){
            $table->timestamp('subs_start')->nullable()->after('subs_id');
           };
        });

         Schema::table('users', function (Blueprint $table) {
           if (!Schema::hasColumn('users', 'subs_end')){
            $table->timestamp('subs_end')->nullable()->after('subs_start');
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
            $table->dropColumn(['subs_id','subs_start','subs_end']);
        });
    }
}
