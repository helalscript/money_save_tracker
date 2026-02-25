<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add total_spend_allowed column to goals table for easier savings calculation.
 * total_spend_allowed = monthly_income - target_amount (total money available for spending).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->decimal('total_spend_allowed', 15, 2)->nullable()->after('target_amount');
        });

        // Populate total_spend_allowed for existing goals: monthly_income - target_amount.
        \DB::statement('UPDATE goals SET total_spend_allowed = GREATEST(0, monthly_income - target_amount) WHERE total_spend_allowed IS NULL');
    }

    public function down(): void
    {
        Schema::table('goals', function (Blueprint $table) {
            $table->dropColumn('total_spend_allowed');
        });
    }
};
