<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewTableStoreInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->smallInteger('type')->default(0);
            $table->string('url', 255)->unique();
            $table->string('sku', 100)->unique();
            $table->string('email', 191);
            $table->string('password', 191);
            $table->text('consumer_key');
            $table->text('consumer_secret');
            $table->smallInteger('status')->default(0);
            $table->string('host',100);
            $table->string('port',100);
            $table->string('security',100);
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
        Schema::dropIfExists('store_infos');
    }
}
