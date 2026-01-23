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
    Schema::table('profit_groups', function (Blueprint $table) {
        // Add 'code' column after 'branch_id'
        if (!Schema::hasColumn('profit_groups', 'code')) {
            $table->string('code')->nullable()->after('branch_id');
        }
    });
}

public function down()
{
    Schema::table('profit_groups', function (Blueprint $table) {
        $table->dropColumn('code');
    });
}
};
