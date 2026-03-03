<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cash_boxes', function (Blueprint $table) {
            // Make the old columns nullable since we don't need them anymore
            $table->unsignedBigInteger('currency_id')->nullable()->change();
            $table->decimal('balance', 20, 2)->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('cash_boxes', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable(false)->change();
            $table->decimal('balance', 20, 2)->nullable(false)->change();
        });
    }
};