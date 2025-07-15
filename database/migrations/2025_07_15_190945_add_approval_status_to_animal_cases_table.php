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
        Schema::table('animal_cases', function (Blueprint $table) {
           $table->enum('approval_status', ['pending', 'approved', 'rejected'])
              ->nullable() // لجعلها غير مطلوبة للطلبات الطارئة
              ->after('request_type');
                
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('animal_cases', function (Blueprint $table) {
      $table->dropColumn(['approval_status']);
        });
    }
};
