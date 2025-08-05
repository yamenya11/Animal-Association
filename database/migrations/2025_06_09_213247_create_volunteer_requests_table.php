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
        $table->string('full_name');
        $table->string('phone')->nullable();
        
        // تغيير enum إلى foreignId إذا أردت جدول منفصل للأقسام
        $table->foreignId('volunteer_type_id')->constrained('volunteer_types')->onDelete('cascade');
        
        $table->string('availability')->nullable();
        $table->enum('status', ['pending','approved','rejected'])->default('pending');
        $table->text('notes')->nullable();
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
