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
        Schema::table('events', function (Blueprint $table) {
            // Add columns only if they don't exist
            if (!Schema::hasColumn('events', 'category_id')) {
                $table->unsignedBigInteger('category_id')->nullable()->after('description');
            }
            
            if (!Schema::hasColumn('events', 'tags')) {
                $table->json('tags')->nullable()->after('category_id');
            }
            
            if (!Schema::hasColumn('events', 'series_id')) {
                $table->unsignedBigInteger('series_id')->nullable()->after('tags');
            }
            
            if (!Schema::hasColumn('events', 'series_order')) {
                $table->integer('series_order')->nullable()->after('series_id');
            }
            
            if (!Schema::hasColumn('events', 'has_waitlist')) {
                $table->boolean('has_waitlist')->default(false)->after('max_capacity');
            }
            
            if (!Schema::hasColumn('events', 'waitlist_capacity')) {
                $table->integer('waitlist_capacity')->nullable()->after('has_waitlist');
            }
            
            if (!Schema::hasColumn('events', 'early_bird_enabled')) {
                $table->boolean('early_bird_enabled')->default(false)->after('waitlist_capacity');
            }
            
            if (!Schema::hasColumn('events', 'early_bird_deadline')) {
                $table->timestamp('early_bird_deadline')->nullable()->after('early_bird_enabled');
            }
            
            if (!Schema::hasColumn('events', 'early_bird_price')) {
                $table->decimal('early_bird_price', 8, 2)->nullable()->after('early_bird_deadline');
            }
            
            if (!Schema::hasColumn('events', 'regular_price')) {
                $table->decimal('regular_price', 8, 2)->nullable()->after('early_bird_price');
            }
            
            if (!Schema::hasColumn('events', 'requires_confirmation')) {
                $table->boolean('requires_confirmation')->default(false)->after('regular_price');
            }
            
            if (!Schema::hasColumn('events', 'confirmation_message')) {
                $table->text('confirmation_message')->nullable()->after('requires_confirmation');
            }
            
            if (!Schema::hasColumn('events', 'has_custom_fields')) {
                $table->boolean('has_custom_fields')->default(false)->after('confirmation_message');
            }
            
            if (!Schema::hasColumn('events', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('has_custom_fields');
            }
            
            if (!Schema::hasColumn('events', 'archived_at')) {
                $table->timestamp('archived_at')->nullable()->after('is_archived');
            }
            
            if (!Schema::hasColumn('events', 'archived_by')) {
                $table->unsignedBigInteger('archived_by')->nullable()->after('archived_at');
            }
        });
        
        // Add indexes only if they don't exist
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasIndex('events', 'events_category_id_status_index')) {
                $table->index(['category_id', 'status'], 'events_category_id_status_index');
            }
            
            if (!Schema::hasIndex('events', 'events_series_id_series_order_index')) {
                $table->index(['series_id', 'series_order'], 'events_series_id_series_order_index');
            }
            
            if (!Schema::hasIndex('events', 'events_is_archived_start_date_index')) {
                $table->index(['is_archived', 'start_date'], 'events_is_archived_start_date_index');
            }
        });
        
        // Add foreign key constraints only if they don't exist
        Schema::table('events', function (Blueprint $table) {
            if (!Schema::hasColumn('events', 'category_id_foreign')) {
                $table->foreign('category_id')->references('id')->on('event_categories')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('events', 'series_id_foreign')) {
                $table->foreign('series_id')->references('id')->on('event_series')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('events', 'archived_by_foreign')) {
                $table->foreign('archived_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['category_id']);
            $table->dropForeign(['series_id']);
            $table->dropForeign(['archived_by']);
            
            // Drop indexes
            $table->dropIndex('events_category_id_status_index');
            $table->dropIndex('events_series_id_series_order_index');
            $table->dropIndex('events_is_archived_start_date_index');
            
            // Drop columns
            $table->dropColumn([
                'category_id', 'tags', 'series_id', 'series_order',
                'has_waitlist', 'waitlist_capacity', 'early_bird_enabled',
                'early_bird_deadline', 'early_bird_price', 'regular_price',
                'requires_confirmation', 'confirmation_message', 'has_custom_fields',
                'is_archived', 'archived_at', 'archived_by'
            ]);
        });
    }
};
