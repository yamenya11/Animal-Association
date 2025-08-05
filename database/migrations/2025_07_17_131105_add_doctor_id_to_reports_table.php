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
       Schema::table('reports', function (Blueprint $table) {
            if (!Schema::hasColumn('reports', 'doctor_id')) {
                $table->foreignId('doctor_id')
                      ->nullable()
                      ->constrained('users')
                      ->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
          $table->dropForeign(['doctor_id']);
            $table->dropColumn('doctor_id');
        });
    }
};
