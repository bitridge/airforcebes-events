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
        Schema::table('registrations', function (Blueprint $table) {
            // Personal Information
            $table->string('first_name')->nullable()->after('notes');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('email')->nullable()->after('last_name');
            $table->string('phone')->nullable()->after('email');
            $table->string('organization_name')->nullable()->after('phone');
            $table->string('title')->nullable()->after('organization_name');
            
            // Registration Type
            $table->enum('type', ['registration', 'checkin'])->default('registration')->after('title');
            $table->enum('checkin_type', ['in_person', 'virtual', 'hybrid'])->nullable()->after('type');
            
            // Business Information
            $table->text('naics_codes')->nullable()->after('checkin_type');
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
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name', 
                'email',
                'phone',
                'organization_name',
                'title',
                'type',
                'checkin_type',
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
