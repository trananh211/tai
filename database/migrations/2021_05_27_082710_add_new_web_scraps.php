<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewWebScraps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('web_scraps', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('template_id')->nullable(false);
            $table->smallInteger('type_platform')->default(0);
            $table->string('exclude_text',255)->nullable(true);
            $table->string('image_array',30)->nullable(true);
            $table->text('exclude_image')->nullable(true);
            $table->string('first_title')->nullable(true);
            $table->smallInteger('type_tag')->default(0);
            $table->string('tag_text',50)->nullable(true);
            $table->smallInteger('tag_position')->nullable(true)->default(0);
            $table->tinyInteger('status')->default(0);
            $table->text('url')->nullable(false);
            $table->text('catalog_source')->nullable(false);
            $table->text('product_source')->nullable(false);
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
        Schema::dropIfExists('web_scraps');
    }
}
