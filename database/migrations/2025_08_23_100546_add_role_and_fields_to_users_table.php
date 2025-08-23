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
            $table->enum('role', ['admin', 'attendee'])->default('attendee')->after('email');
            $table->string('phone')->nullable()->after('role');
            $table->string('organization')->nullable()->after('phone');
            $table->boolean('is_active')->default(true)->after('organization');
            $table->unsignedBigInteger('created_by')->nullable()->after('is_active');
            
            // Add indexes
            $table->index('role');
            $table->index('is_active');
            $table->index('created_by');
            
            // Add foreign key constraint
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['created_by']);
            $table->dropColumn(['role', 'phone', 'organization', 'is_active', 'created_by']);
        });
    }
};
