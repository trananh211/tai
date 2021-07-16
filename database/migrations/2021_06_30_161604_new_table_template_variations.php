<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewTableTemplateVariations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_variations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('store_variation_id');
            $table->integer('template_id');
            $table->integer('store_template_id');
            $table->integer('store_info_id');
            $table->text('variation_source');
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
        Schema::dropIfExists('template_variations');
    }
}
