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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_case_id')->constrained('animal_cases')->onDelete('cascade');
            $table->string('animal_name')->nullable();
             $table->integer('animal_age');
            $table->string('animal_weight');
            $table->string('image')->nullable();
            $table->enum('status', ['Pending', 'Completed', 'Canceled'])->default('pending');
            $table->string('temperature')->nullable();
            $table->string('pluse')->nullable();
            $table->string('respiration')->nullable();
            $table->string('general_condition')->nullable();
            $table->string('midical_separated')->nullable();
            
           $table->string('note');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
