<?php

// database/migrations/xxxx_xx_xx_create_profit_management_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Profit Groups Table (Parent)
        Schema::create('profit_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. Profit Types Table (Child)
        Schema::create('profit_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profit_group_id')->constrained('profit_groups')->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profit_types');
        Schema::dropIfExists('profit_groups');
    }
};