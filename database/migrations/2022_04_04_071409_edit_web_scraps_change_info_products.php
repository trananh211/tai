<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditWebScrapsChangeInfoProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('web_scraps', function (Blueprint $table) {
            $table->string('product_name_change',255)->nullable('true')->after('tag_position');
            $table->string('product_name_exclude',255)->nullable('true')->after('product_name_change');
            $table->smallInteger('t_status')->default(0)->after('product_name_exclude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('web_scraps', function (Blueprint $table) {
            $table->dropColumn('product_name_change');
            $table->dropColumn('product_name_exclude');
            $table->dropColumn('t_status');
        });
    }
}
