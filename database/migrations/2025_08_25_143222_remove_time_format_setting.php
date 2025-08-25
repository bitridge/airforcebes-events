<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // Added missing import

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Remove the time format setting if it exists
        DB::table('settings')->where('key', 'app.time_format')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add the time format setting (though this shouldn't be needed)
        DB::table('settings')->insert([
            'key' => 'app.time_format',
            'value' => 'H:i', // Default to 24-hour format
            'type' => 'select',
            'group' => 'general',
            'label' => 'Time Format',
            'description' => 'Default time format for the application',
            'options' => json_encode([
                'H:i' => '09:30 (24-hour)',
                'g:i A' => '9:30 AM (12-hour)',
            ]),
            'is_required' => false,
            'default_value' => 'H:i',
            'sort_order' => 7,
            'is_public' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
