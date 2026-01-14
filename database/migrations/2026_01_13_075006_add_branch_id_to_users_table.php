<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        // We make it constrained so it links to the branches table
        // 'nullOnDelete' is safe so users aren't deleted if a branch is removed
        $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropForeign(['branch_id']);
        $table->dropColumn('branch_id');
    });
}
};
