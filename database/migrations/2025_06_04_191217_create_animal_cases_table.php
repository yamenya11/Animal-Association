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
        Schema::create('animal_cases', function (Blueprint $table) {
            $table->id();
            $table->String('name_animal');
            $table->string('case_type'); // مثل: مرض، إصابة، متابعة
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // صورة توضيحية للحالة
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('animal_cases');
    }
};
