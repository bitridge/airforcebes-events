<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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

        // Insert essential default settings
        $this->insertDefaultSettings();
    }

    /**
     * Insert default settings data
     */
    private function insertDefaultSettings(): void
    {
        $settings = [
            // General Settings
            [
                'key' => 'app.name',
                'value' => 'AirforceBES Events',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Application Name',
                'description' => 'The name of your application',
                'is_required' => true,
                'default_value' => 'AirforceBES Events',
                'sort_order' => 1,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'app.description',
                'value' => 'Professional event management system for AirforceBES',
                'type' => 'text',
                'group' => 'general',
                'label' => 'Application Description',
                'description' => 'Brief description of your application',
                'is_required' => false,
                'default_value' => 'Professional event management system for AirforceBES',
                'sort_order' => 2,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'app.timezone',
                'value' => 'UTC',
                'type' => 'select',
                'group' => 'general',
                'label' => 'Default Timezone',
                'description' => 'Default timezone for the application',
                'options' => json_encode([
                    'UTC' => 'UTC',
                    'America/New_York' => 'Eastern Time',
                    'America/Chicago' => 'Central Time',
                    'America/Denver' => 'Mountain Time',
                    'America/Los_Angeles' => 'Pacific Time',
                    'Europe/London' => 'London',
                    'Europe/Paris' => 'Paris',
                    'Asia/Tokyo' => 'Tokyo',
                    'Asia/Shanghai' => 'Shanghai',
                    'Australia/Sydney' => 'Sydney',
                ]),
                'is_required' => true,
                'default_value' => 'UTC',
                'sort_order' => 3,
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'app.date_format',
                'value' => 'M j, Y',
                'type' => 'select',
                'group' => 'general',
                'label' => 'Date Format',
                'description' => 'Default date format for the application',
                'options' => json_encode([
                    'M j, Y' => 'Jan 1, 2025',
                    'j M Y' => '1 Jan 2025',
                    'Y-m-d' => '2025-01-01',
                    'm/d/Y' => '01/01/2025',
                    'd/m/Y' => '01/01/2025',
                ]),
                'is_required' => true,
                'default_value' => 'M j, Y',
                'sort_order' => 4,
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'app.time_format',
                'value' => 'g:i A',
                'type' => 'select',
                'group' => 'general',
                'label' => 'Time Format',
                'description' => 'Default time format for the application',
                'options' => json_encode([
                    'g:i A' => '2:30 PM',
                    'H:i' => '14:30',
                    'g:i a' => '2:30 pm',
                    'H:i:s' => '14:30:00',
                ]),
                'is_required' => true,
                'default_value' => 'g:i A',
                'sort_order' => 5,
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Appearance Settings
            [
                'key' => 'appearance.primary_color',
                'value' => '#dc2626',
                'type' => 'color',
                'group' => 'appearance',
                'label' => 'Primary Color',
                'description' => 'Primary brand color for the application',
                'is_required' => false,
                'default_value' => '#dc2626',
                'sort_order' => 1,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'appearance.secondary_color',
                'value' => '#1e293b',
                'type' => 'color',
                'group' => 'appearance',
                'label' => 'Secondary Color',
                'description' => 'Secondary brand color for the application',
                'is_required' => false,
                'default_value' => '#1e293b',
                'sort_order' => 2,
                'is_public' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Email Template Settings
            [
                'key' => 'email_templates.registration_confirmation_subject',
                'value' => 'Event Registration Confirmation - {{event_title}}',
                'type' => 'text',
                'group' => 'email_templates',
                'label' => 'Registration Confirmation Subject',
                'description' => 'Subject line for registration confirmation emails',
                'is_required' => true,
                'default_value' => 'Event Registration Confirmation - {{event_title}}',
                'sort_order' => 1,
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'email_templates.registration_confirmation_body',
                'value' => "Dear {{attendee_name}},\n\nThank you for registering for {{event_title}}!\n\nEvent Details:\n- Date: {{event_date}}\n- Time: {{event_time}}\n- Venue: {{event_venue}}\n- Registration Code: {{registration_code}}\n\nPlease bring this registration code or the QR code to the event for check-in.\n\nIf you have any questions, please contact us at {{contact_email}}.\n\nBest regards,\n{{app_name}} Team",
                'type' => 'text',
                'group' => 'email_templates',
                'label' => 'Registration Confirmation Body',
                'description' => 'Body content for registration confirmation emails',
                'is_required' => true,
                'default_value' => "Dear {{attendee_name}},\n\nThank you for registering for {{event_title}}!\n\nEvent Details:\n- Date: {{event_date}}\n- Time: {{event_time}}\n- Venue: {{event_venue}}\n- Registration Code: {{registration_code}}\n\nPlease bring this registration code or the QR code to the event for check-in.\n\nIf you have any questions, please contact us at {{contact_email}}.\n\nBest regards,\n{{app_name}} Team",
                'sort_order' => 2,
                'is_public' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->insert($setting);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
