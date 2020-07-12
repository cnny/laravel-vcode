<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVcodeTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vcodes', function (Blueprint $table) {

            $table->increments('id');
            $table->string('channel', 20);
            $table->string('scene', 100);
            $table->string('target', 100);
            $table->string('vcode', 20);
            $table->timestamp('sent_at', 0)->nullable();
            $table->timestamp('expried_at', 0)->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamps();

            $table->index(['channel', 'scene', 'target']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vcodes');
    }
}
