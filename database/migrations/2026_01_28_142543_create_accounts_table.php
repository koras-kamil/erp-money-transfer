<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // System generated code
            $table->string('manual_code')->nullable(); // User defined code
            $table->string('name');
            $table->string('secondary_name')->nullable();
            $table->string('profile_picture')->nullable();
            
            // Contact Info
            $table->string('mobile_number_1')->nullable();
            $table->string('mobile_number_2')->nullable();
            
            // Classification
            $table->enum('account_type', ['customer', 'vendor', 'buyer_and_seller', 'other'])->default('customer');
            
            // Financials
            $table->foreignId('currency_id')->constrained('currency_configs')->onDelete('cascade');
            $table->decimal('debt_limit', 15, 2)->default(0);
            $table->integer('debt_due_time')->default(0); // Days
            
            // Location
            $table->foreignId('zone_id')->nullable()->constrained('zones')->nullOnDelete();
            $table->string('location')->nullable(); // GPS Coordinates
            
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};