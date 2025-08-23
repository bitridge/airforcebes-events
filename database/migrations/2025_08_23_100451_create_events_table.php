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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('slug')->unique();
            $table->date('start_date');
            $table->date('end_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('venue');
            $table->integer('max_capacity')->nullable();
            $table->datetime('registration_deadline')->nullable();
            $table->enum('status', ['draft', 'published', 'completed', 'cancelled'])->default('draft');
            $table->string('featured_image')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            // Add indexes for performance
            $table->index('slug');
            $table->index('start_date');
            $table->index('status');
            $table->index('created_by');
            $table->index(['status', 'start_date']); // Composite index for event listings
            
            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
