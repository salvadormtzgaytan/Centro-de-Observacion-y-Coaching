<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderToChannelsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->integer('order')
                ->default(0)
                ->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('channels', function (Blueprint $table) {
            $table->dropColumn('order');
        });
    }
}