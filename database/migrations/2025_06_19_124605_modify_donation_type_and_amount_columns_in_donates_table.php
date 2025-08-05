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
        Schema::table('donates', function (Blueprint $table) {
            Schema::table('donates', function (Blueprint $table) {
              // تأكيد على الحجم
        });
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('donates', function (Blueprint $table) {
             Schema::table('donates', function (Blueprint $table) {
            
            // إرجاع النوع enum
        });
        });
    }
};
