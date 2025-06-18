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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
        
        // المستخدم الذي طلب الموعد
        $table->foreignId('user_id')->constrained()->onDelete('cascade');

        // الموظف المسؤول عن الموعد (اختياري بالبداية)
        $table->foreignId('employee_id')->nullable()->constrained('users')->onDelete('set null');

        // الحالة الحيوانية
        $table->foreignId('animal_case_id')->constrained('animal_cases')->onDelete('cascade');

        // موعد الحجز
        $table->dateTime('scheduled_at');

        // حالة الموعد
        $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
