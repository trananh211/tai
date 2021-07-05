<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateListProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('list_products', function (Blueprint $table) {
            $table->bigIncrements('id');
	        $table->integer('web_scrap_id')->nullable(false);
            $table->string('product_name','255');
            $table->integer('store_tag_id')->nullable(true);
            $table->smallInteger('status')->default(0);
            $table->text('product_link')->nullable(false);
            $table->text('img')->nullable(true);
            $table->integer('count');
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
        Schema::dropIfExists('list_products');
    }
}
