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
        Schema::create('custom_registration_fields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('label');
            $table->string('field_name')->unique();
            $table->enum('field_type', ['text', 'textarea', 'select', 'checkbox', 'radio', 'date', 'number', 'email', 'phone', 'url']);
            $table->text('options')->nullable(); // JSON for select, radio, checkbox options
            $table->text('validation_rules')->nullable(); // JSON for validation rules
            $table->boolean('is_required')->default(false);
            $table->text('help_text')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->index(['event_id', 'is_active']);
            $table->index(['event_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_registration_fields');
    }
};
