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
            if (!Schema::hasColumn('registrations', 'custom_field_values')) {
                $table->json('custom_field_values')->nullable()->after('notes');
            }
            
            if (!Schema::hasColumn('registrations', 'paid_amount')) {
                $table->decimal('paid_amount', 8, 2)->nullable()->after('custom_field_values');
            }
            
            if (!Schema::hasColumn('registrations', 'payment_status')) {
                $table->enum('payment_status', ['pending', 'paid', 'refunded', 'cancelled'])->default('pending')->after('paid_amount');
            }
            
            if (!Schema::hasColumn('registrations', 'payment_date')) {
                $table->timestamp('payment_date')->nullable()->after('payment_status');
            }
            
            if (!Schema::hasColumn('registrations', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('payment_date');
            }
            
            if (!Schema::hasColumn('registrations', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_method');
            }
        });
        
        // Add indexes if they don't exist
        Schema::table('registrations', function (Blueprint $table) {
            if (!Schema::hasIndex('registrations', 'registrations_payment_status_created_at_index')) {
                $table->index(['payment_status', 'created_at'], 'registrations_payment_status_created_at_index');
            }
            
            if (!Schema::hasIndex('registrations', 'registrations_event_id_payment_status_index')) {
                $table->index(['event_id', 'payment_status'], 'registrations_event_id_payment_status_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropIndex(['payment_status', 'created_at']);
            $table->dropIndex(['event_id', 'payment_status']);
            
            $table->dropColumn([
                'custom_field_values', 'paid_amount', 'payment_status',
                'payment_date', 'payment_method', 'transaction_id'
            ]);
        });
    }
};
