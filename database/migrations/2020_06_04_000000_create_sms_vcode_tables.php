<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsVcodeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_vcodes', function (Blueprint $table) {

            $table->increments('id');
            $table->string('mobile', 20);
            $table->string('vcode', 20);
            $table->string('content', 200);
            $table->string('third_user_id', 191);
            $table->timestamps();

            $table->unique(['platform', 'user_id', 'third_user_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_users_third_pf_bind');
    }
}
