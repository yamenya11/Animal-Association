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
        Schema::create('animals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('purpose', ['adoption', 'temporary_care'])->default('adoption'); // الغرض الأساسي
            $table->boolean('available_for_care')->default(false)->comment('يتم تحديثه تلقائياً عند تغيير purpose');            $table->string('type'); // مثل: قط، كلب، عصفور
            $table->date('birth_date')->nullable(); // بالعمر (أشهر أو سنوات)
            $table->text('health_info')->nullable(); // وصف للحالة أو الشخصية
            $table->string('image')->nullable(); // صورة
             $table->string('describtion')->nullable(); // صورة
            $table->boolean('is_adopted')->default(false); // تم التبني أم لا
            $table->timestamps();
         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animals');
    }
};
