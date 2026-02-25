<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Safe, additive migration for new savings logic.
 * - Adds daily_spent_amount (primary input) and calculated_daily_save (system-calculated).
 * - Does NOT remove amount_spending or amount_saved (backward compatibility).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->decimal('daily_spent_amount', 15, 2)->nullable()->after('amount_saved');
            $table->decimal('calculated_daily_save', 15, 2)->nullable()->after('daily_spent_amount');
        });
    }

    public function down(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropColumn(['daily_spent_amount', 'calculated_daily_save']);
        });
    }
};
