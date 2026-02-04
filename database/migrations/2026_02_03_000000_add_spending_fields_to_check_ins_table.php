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
        Schema::table('check_ins', function (Blueprint $table) {
            $table->string('spending_type')->nullable()->after('amount_spending');
            $table->string('spending_name')->nullable()->after('spending_type');
            $table->text('note')->nullable()->after('spending_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('check_ins', function (Blueprint $table) {
            $table->dropColumn(['spending_type', 'spending_name', 'note']);
        });
    }
};

