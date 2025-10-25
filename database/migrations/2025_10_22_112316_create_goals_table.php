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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->decimal('fixed_expense', 15, 2)->nullable();
            $table->decimal('alredy_saved', 15, 2)->nullable(); // Note: keeping original typo as per schema
            $table->decimal('debt_blance', 15, 2)->nullable(); // Note: keeping original typo as per schema
            $table->enum('goal_type', ['save_money', 'paying_debt']);
            $table->decimal('target_amount', 15, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('daily_target', 15, 2)->nullable();
            $table->integer('current_streak')->default(0);
            $table->enum('deadline_indicator',['green', 'yellow', 'red'])->default('green');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('goals');
    }
};
