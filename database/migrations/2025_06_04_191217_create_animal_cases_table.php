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
            $table->string('name_animal');
           $table->string('case_type');
           $table->text('description')->nullable();
            $table->string('image')->nullable();
    //$table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('request_type', ['regular', 'immediate'])->default('regular');
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
