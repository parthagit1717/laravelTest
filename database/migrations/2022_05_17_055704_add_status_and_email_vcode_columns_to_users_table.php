<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAndEmailVcodeColumnsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
           if (!Schema::hasColumn('users', 'email_vcode')){
             $table->string('email_vcode')->nullable()->after('email');
           };
        });

        Schema::table('users', function (Blueprint $table) {
           if (!Schema::hasColumn('users', 'status')){
            $table->unsignedTinyInteger('status')->default(0)
            ->comment('0=Unverified,1=Active,2=Inactive')->after('name');
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
            $table->dropColumn(['email_vcode','status']);
        });
    }
}
