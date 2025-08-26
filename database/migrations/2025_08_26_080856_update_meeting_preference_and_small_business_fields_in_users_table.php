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
        Schema::table('users', function (Blueprint $table) {
            // Change meeting_preference from enum to text
            $table->text('meeting_preference')->nullable()->change();
            
            // Change small_business fields from boolean to string
            $table->string('small_business_forum')->nullable()->change();
            $table->string('small_business_matchmaker')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Revert meeting_preference back to enum
            $table->enum('meeting_preference', ['in_person', 'virtual', 'hybrid', 'no_preference'])->default('no_preference')->change();
            
            // Revert small_business fields back to boolean
            $table->boolean('small_business_forum')->default(false)->change();
            $table->boolean('small_business_matchmaker')->default(false)->change();
        });
    }
};
