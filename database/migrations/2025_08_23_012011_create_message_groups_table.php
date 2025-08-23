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
        Schema::create('message_groups', function (Blueprint $table) {
            $table->id();
                $table->text('body');
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
        $table->enum('type', ['text', 'image', 'file'])->default('text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_groups');
    }
};
