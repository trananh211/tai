<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',255)->nullable(false);
            $table->string('product_code',20)->nullable(true);
            $table->smallInteger('type_platform')->default(1);
            $table->integer('store_id')->default(0);
            $table->float('origin_price')->default(0);
            $table->float('sale_price')->default(0);
            $table->text('path')->nullable(false);
            $table->smallInteger('status')->default(0);
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
        Schema::dropIfExists('templates');
    }
}
