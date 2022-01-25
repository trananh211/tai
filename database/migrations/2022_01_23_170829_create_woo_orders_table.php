<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateWooOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woo_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('store_info_id');
            $table->integer('order_id');
            $table->integer('product_id');
            $table->smallInteger('paypal_id');
            $table->smallInteger('status')->default(0);
            $table->string('transaction_id',255);
            $table->string('order_status',100);
            $table->smallInteger('quantity');
            $table->string('payment_method',100);
            $table->text('customer_note');
            $table->float('price');
            $table->float('shipping_cost');
            $table->string('number',100);
            $table->text('product_name');
            $table->string('email',255);
            $table->string('last_name',100);
            $table->string('first_name',100);
            $table->string('fullname',255);
            $table->text('address');
            $table->string('city',255);
            $table->string('postcode',100);
            $table->string('country',50);
            $table->string('state',100);
            $table->string('phone',100);
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
        Schema::dropIfExists('woo_orders');
    }
}
