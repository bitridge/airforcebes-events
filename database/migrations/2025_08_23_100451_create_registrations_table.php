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
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->unsignedBigInteger('user_id');
            $table->string('registration_code', 12)->unique();
            $table->text('qr_code_data')->nullable();
            $table->datetime('registration_date');
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('confirmed');
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('registration_code');
            $table->index(['event_id', 'user_id']);
            $table->index('event_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('registration_date');
            
            // Ensure unique registration per user per event
            $table->unique(['event_id', 'user_id']);
            
            // Foreign key constraints
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
