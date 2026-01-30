<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. FIX: Remove the old relationship from 'accounts' FIRST
        Schema::table('accounts', function (Blueprint $table) {
            // Check if column exists to avoid errors on re-run
            if (Schema::hasColumn('accounts', 'zone_id')) {
                $table->dropForeign(['zone_id']); // Drops the constraint
                $table->dropColumn('zone_id');    // Drops the column
            }
        });

        // 2. Now it is safe to drop 'zones'
        Schema::dropIfExists('zones');

        // 3. Create Cities Table
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('city_name');
            // Assuming you have branch_id on users/accounts, usually linked via Auth
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade'); 
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 4. Create Neighborhoods Table
        Schema::create('neighborhoods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable();
            $table->string('neighborhood_name');
            $table->foreignId('city_id')->constrained('cities')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 5. Add NEW relationships to 'accounts'
        Schema::table('accounts', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('set null');
            $table->foreignId('neighborhood_id')->nullable()->constrained('neighborhoods')->onDelete('set null');
        });
    }

    public function down()
    {
        // Reverse everything
        Schema::table('accounts', function (Blueprint $table) {
            $table->dropForeign(['city_id']);
            $table->dropColumn('city_id');
            $table->dropForeign(['neighborhood_id']);
            $table->dropColumn('neighborhood_id');
            $table->foreignId('zone_id')->nullable()->constrained('zones'); // Re-add zone_id
        });

        Schema::dropIfExists('neighborhoods');
        Schema::dropIfExists('cities');
        
        // Re-create zones table (simplified for rollback)
        Schema::create('zones', function (Blueprint $table) {
            $table->id();
            $table->string('city');
            $table->string('neighborhood')->nullable();
            $table->timestamps();
        });
    }
};