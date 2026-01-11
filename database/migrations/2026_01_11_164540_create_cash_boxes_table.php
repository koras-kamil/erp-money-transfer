<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cash_boxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');             // Nav
            $table->string('type')->nullable(); // Jor
            
            // Relationships
            $table->foreignId('currency_id')->constrained('currency_configs');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('user_id')->constrained('users');

            $table->decimal('balance', 20, 2)->default(0); 
            $table->text('description')->nullable();       
            $table->date('date_opened')->nullable();       
            
            $table->boolean('is_active')->default(true);   
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_boxes');
    }
};