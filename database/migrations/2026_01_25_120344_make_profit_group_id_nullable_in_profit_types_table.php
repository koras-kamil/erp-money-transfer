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
    Schema::table('profit_types', function (Blueprint $table) {
        // This allows the column to be empty (null)
        $table->foreignId('profit_group_id')->nullable()->change();
    });
}

public function down()
{
    Schema::table('profit_types', function (Blueprint $table) {
        // Revert back if needed
        $table->foreignId('profit_group_id')->nullable(false)->change();
    });
}
};
