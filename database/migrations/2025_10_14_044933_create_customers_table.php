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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_detail_id')->constrained('store_details')->onDelete('cascade');
            $table->foreignId('time_package_id')->nullable()->constrained('time_packages')->onDelete('set null');
            $table->string('time_package_name')->nullable();
            $table->string('name');
            $table->string('rfid_sn_left');
            $table->string('rfid_sn_right');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->datetime('start_time');
            $table->datetime('end_time');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
