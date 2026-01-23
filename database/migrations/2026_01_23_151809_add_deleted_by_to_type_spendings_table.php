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
    // Check your actual table name in the database (likely 'type_spendings' or 'spending_types')
    Schema::table('type_spendings', function (Blueprint $table) {
        if (!Schema::hasColumn('type_spendings', 'deleted_by')) {
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('type_spendings', function (Blueprint $table) {
            //
        });
    }
};
