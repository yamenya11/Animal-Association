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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
           $table->foreignId('user_id')
            ->constrained()
            ->onDelete('cascade');

        $table->foreignId('post_id')
            ->constrained()
            ->onDelete('cascade');

        $table->foreignId('parent_id') // هذا الحقل يجعل التعليق ردًا
            ->nullable()
            ->constrained('comments')
            ->onDelete('cascade');

        $table->text('content');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
