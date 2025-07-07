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
        Schema::create('temporary_care_requests', function (Blueprint $table) {
            $table->id();
           
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('animal_id')->constrained()->onDelete('cascade');
            $table->string('address');
             $table->string('type');
             $table->string('breed')->nullable();
            $table->string('duration');
            $table->string('custom_duration')->nullable();
            $table->text('health_info');
            $table->foreignId('vet_id')->nullable()->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_care_requests');
    }
};
