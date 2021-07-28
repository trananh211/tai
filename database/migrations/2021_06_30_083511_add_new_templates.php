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
            $table->text('product_name')->nullable(true);
            $table->integer('store_template_id')->default(0);
            $table->integer('store_category_id')->default(0);
            $table->smallInteger('type_platform')->default(1);
            $table->integer('store_info_id')->default(0);
            $table->float('origin_price')->default(0);
            $table->float('sale_price')->default(0);
            $table->smallInteger('status')->default(0);
            $table->smallInteger('t_status')->default(0);
            $table->string('product_name_change',255)->nullable(true);
            $table->string('product_name_exclude',255)->nullable(true);
            $table->text('woo_template_source')->nullable(true);
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
