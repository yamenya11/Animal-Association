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
        
        $table->foreignId('user_id')->constrained()->onDelete('cascade'); // المستخدم الذي طلب الموعد

    $table->foreignId('employee_id')->nullable()->constrained('users')->onDelete('set null'); // الموظف المسؤول

    $table->foreignId('animal_case_id')->constrained('animal_cases')->onDelete('cascade'); // الحالة الحيوانية

      $table->date('scheduled_date');   // حقل التاريخ فقط
     $table->time('scheduled_time');  
    $table->text('description')->nullable(); // وصف الحالة من الطبيب
       $table->enum('status', ['scheduled', 'completed', 'canceled'])->default('scheduled');

    $table->boolean('is_immediate')->default(false); // لتحديد إذا كان موعد فوري أم لا
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
