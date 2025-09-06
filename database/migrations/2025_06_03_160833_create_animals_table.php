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
            $table->enum('purpose', ['adoption', 'temporary_care'])->default('adoption'); 
            $table->boolean('available_for_care')->default(false)->comment('يتم تحديثه تلقائياً عند تغيير purpose');  
            $table->string('type'); 
            $table->date('birth_date')->nullable();
            $table->text('health_info')->nullable(); 
            $table->string('image')->nullable();
             $table->string('describtion')->nullable();
            $table->boolean('is_adopted')->default(false); 
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
