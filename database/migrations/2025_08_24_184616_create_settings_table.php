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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->enum('type', ['text', 'email', 'password', 'boolean', 'select', 'json', 'integer', 'float', 'url', 'color', 'file']);
            $table->enum('group', ['general', 'smtp', 'notifications', 'appearance', 'system', 'email_templates', 'integrations', 'security']);
            $table->string('label');
            $table->text('description')->nullable();
            $table->json('options')->nullable(); // For select fields
            $table->boolean('is_encrypted')->default(false);
            $table->boolean('is_required')->default(false);
            $table->string('validation_rules')->nullable();
            $table->longText('default_value')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_public')->default(false); // Can be accessed without authentication
            $table->timestamps();
            
            // Indexes
            $table->index(['group', 'sort_order']);
            $table->index('key');
            $table->index('is_public');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
