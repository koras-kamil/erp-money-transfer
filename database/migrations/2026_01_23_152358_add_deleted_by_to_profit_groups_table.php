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
        if (!Schema::hasColumn('profit_groups', 'deleted_by')) {
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        }
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profit_groups', function (Blueprint $table) {
            //
        });
    }
};
