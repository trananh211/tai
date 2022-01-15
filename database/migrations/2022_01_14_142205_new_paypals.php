<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NewPaypals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paypals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('api_email',100)->unique();
            $table->string('api_pass',100);
            $table->string('api_merchant_id',100);
            $table->string('api_signature',255);
            $table->string('api_client_id',255);
            $table->string('api_secret',255);
            $table->integer('store_info_id')->default(0);
            $table->smallInteger('status')->nullable(false)->default(0);
            $table->float('profit_limit')->nullable(false)->default(0);
            $table->float('profit_value')->nullable(false)->default(0);
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
        Schema::dropIfExists('paypals');
    }
}
