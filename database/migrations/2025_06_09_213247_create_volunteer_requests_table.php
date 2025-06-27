<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   
    public function up(): void
    {
        Schema::create('volunteer_requests', function (Blueprint $table) {
            $table->id();
             $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('full_name'); // مسار ملف السيرة الذاتية
         $table->string('phone')->nullable();
        // نوع التطوع: 6 أقسام (يمكن تعديلها حسب الحاجة)
        $table->enum('volunteer_type', [
            'cleaning_shelters',    // البيئة
            'animal_care',   // رعاية الحيوانات
            'photography_and_documentation',     // التعليم
            'design_and_markiting',   // جمع التبرعات
            'social_midea_administrator',// الإدارة
            'school_awareness'          // أخرى
        ]);
        
        // وقت التوفر (يمكن استخدام حقل نصي أو حقل زمني)
        $table->string('availability')->nullable(); 
        
        $table->enum('status', ['pending','approved','rejected'])->default('pending'); // حالة الطلب
        
        $table->text('notes')->nullable(); // ملاحظات إضافية
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('volunteer_requests');
    }
};
