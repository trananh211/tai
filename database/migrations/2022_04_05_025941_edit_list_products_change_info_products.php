<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditListProductsChangeInfoProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('list_products', function (Blueprint $table) {
            $table->smallInteger('t_status')->default(0)->after('store_product_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('list_products', function (Blueprint $table) {
            $table->dropColumn('t_status');
        });
    }
}
