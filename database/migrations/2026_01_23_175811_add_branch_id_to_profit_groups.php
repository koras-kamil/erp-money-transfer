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
        // Add branch_id after id, nullable (optional)
        if (!Schema::hasColumn('profit_groups', 'branch_id')) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
        }
    });
}

public function down()
{
    Schema::table('profit_groups', function (Blueprint $table) {
        $table->dropForeign(['branch_id']);
        $table->dropColumn('branch_id');
    });
}
};
