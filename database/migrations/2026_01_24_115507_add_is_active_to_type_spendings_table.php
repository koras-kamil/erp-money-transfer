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
    Schema::table('type_spendings', function (Blueprint $table) {
        // Adding the column as a boolean, defaulting to true
        $table->boolean('is_active')->default(true)->after('note');
    });
}

public function down()
{
    Schema::table('type_spendings', function (Blueprint $table) {
        $table->dropColumn('is_active');
    });
}
};
