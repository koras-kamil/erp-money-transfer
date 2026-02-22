<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // This safely removes the strict check so you can save Profit and Spending IDs
            $table->dropForeign('transactions_category_id_foreign');
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('category_id')->references('id')->on('transaction_categories');
        });
    }
};