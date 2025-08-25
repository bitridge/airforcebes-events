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
            // Personal Information
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('organization_name')->nullable()->after('organization');
            $table->string('title')->nullable()->after('organization_name');
            
            // Business Information
            $table->text('naics_codes')->nullable()->after('title');
            $table->text('industry_connections')->nullable()->after('naics_codes');
            $table->text('core_specialty_area')->nullable()->after('industry_connections');
            $table->text('contract_vehicles')->nullable()->after('core_specialty_area');
            
            // Preferences
            $table->enum('meeting_preference', ['in_person', 'virtual', 'hybrid', 'no_preference'])->default('no_preference')->after('contract_vehicles');
            
            // Event Specific
            $table->boolean('small_business_forum')->default(false)->after('meeting_preference');
            $table->boolean('small_business_matchmaker')->default(false)->after('small_business_forum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
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
    }
};
