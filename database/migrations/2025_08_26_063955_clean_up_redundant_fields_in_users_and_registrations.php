<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added missing import for DB facade

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update existing records to populate first_name and last_name from name
        // and organization_name from organization if they're empty
        DB::statement("
            UPDATE users 
            SET first_name = SUBSTRING_INDEX(name, ' ', 1),
                last_name = TRIM(SUBSTRING(name, LOCATE(' ', name) + 1))
            WHERE (first_name IS NULL OR first_name = '') 
            AND (last_name IS NULL OR last_name = '')
            AND name IS NOT NULL 
            AND name != ''
        ");

        DB::statement("
            UPDATE users 
            SET organization_name = organization
            WHERE (organization_name IS NULL OR organization_name = '')
            AND organization IS NOT NULL 
            AND organization != ''
        ");

        // Now remove redundant fields from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['name', 'organization']);
        });

        // Remove redundant fields from registrations table since they're already in users
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name', 
                'email',
                'phone',
                'organization_name',
                'title',
                'naics_codes',
                'industry_connections',
                'core_specialty_area',
                'contract_vehicles',
                'meeting_preference',
                'small_business_forum',
                'small_business_matchmaker'
            ]);
        });

        // Keep only the registration-specific fields
        // type, checkin_type, notes remain
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the name field to users
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('organization')->nullable()->after('phone');
        });

        // Re-add the fields to registrations
        Schema::table('registrations', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('notes');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('email')->nullable()->after('last_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('organization_name')->nullable()->after('phone');
            $table->string('title')->nullable()->after('organization_name');
            $table->text('naics_codes')->nullable()->after('title');
            $table->text('industry_connections')->nullable()->after('naics_codes');
            $table->text('core_specialty_area')->nullable()->after('industry_connections');
            $table->text('contract_vehicles')->nullable()->after('core_specialty_area');
            $table->enum('meeting_preference', ['in_person', 'virtual', 'hybrid', 'no_preference'])->default('no_preference')->after('contract_vehicles');
            $table->boolean('small_business_forum')->default(false)->after('meeting_preference');
            $table->boolean('small_business_matchmaker')->default(false)->after('small_business_forum');
        });
    }
};
