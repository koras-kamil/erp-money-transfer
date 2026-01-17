<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('type_spendings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code'); // Auto-generated
            $table->string('accountant_code')->nullable();
            
            // Relationships
            $table->foreignId('group_id')->nullable()->constrained('group_spendings')->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            
            $table->text('note')->nullable();
            $table->softDeletes(); // Trash support
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('type_spendings');
    }
};