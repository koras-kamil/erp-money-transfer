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
        $table->renameColumn('group_id', 'group_spending_id');
    });
}

public function down()
{
    Schema::table('type_spendings', function (Blueprint $table) {
        $table->renameColumn('group_spending_id', 'group_id');
    });
}
};
