<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewTableStoreCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name',255);
            $table->string('slug',255);
            $table->smallInteger('type_platform')->default(0);
            $table->integer('store_category_id');
            $table->integer('store_info_id');
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
        Schema::dropIfExists('store_categories');
    }
}
