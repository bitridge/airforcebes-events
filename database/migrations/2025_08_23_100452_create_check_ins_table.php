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
        Schema::create('check_ins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('registration_id')->unique(); // One check-in per registration
            $table->datetime('checked_in_at');
            $table->unsignedBigInteger('checked_in_by')->nullable(); // Admin who checked them in (for manual check-ins)
            $table->enum('check_in_method', ['qr', 'manual', 'id'])->default('qr');
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('registration_id');
            $table->index('checked_in_at');
            $table->index('checked_in_by');
            $table->index('check_in_method');
            
            // Foreign key constraints
            $table->foreign('registration_id')->references('id')->on('registrations')->onDelete('cascade');
            $table->foreign('checked_in_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_ins');
    }
};
