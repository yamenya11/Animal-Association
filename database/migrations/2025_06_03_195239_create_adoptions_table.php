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
        Schema::create('adoptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');  // المستخدم الطالب للتبني
            $table->foreignId('animal_id')->constrained('animals')->onDelete('cascade'); // الحيوان     
            $table->foreignId('type_id')->constrained('animals')->onDelete('cascade'); // مثل: قط، كلب، عصفور
            $table->string('breed')->nullable();
            $table->string('address')->default('not set');
            $table->date('birth_date')->nullable(); // بالعمر (أشهر أو سنوات)
            $table->string('phone')->nullable()->unique(); 
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // حالة الطلب
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adoptions');
    }
};
