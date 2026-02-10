<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('accounts', function (Blueprint $table) {
        // Stores IDs like [1, 2] for USD, IQD
        $table->json('supported_currency_ids')->nullable()->after('currency_id'); 
    });
}

public function down()
{
    Schema::table('accounts', function (Blueprint $table) {
        $table->dropColumn('supported_currency_ids');
    });
}
};
