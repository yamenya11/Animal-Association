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
           $table->string('name');
            $table->string('type'); // مثل: قط، كلب، عصفور
            $table->integer('age')->nullable(); // بالعمر (أشهر أو سنوات)
            $table->text('health_info')->nullable(); // وصف للحالة أو الشخصية
            $table->string('image')->nullable(); // صورة
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
