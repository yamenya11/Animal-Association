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
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
              $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
        $table->timestamp('joined_at')->useCurrent();
        $table->enum('role', ['admin', 'member'])->default('member');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
