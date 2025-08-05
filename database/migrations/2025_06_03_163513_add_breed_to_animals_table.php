<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    
    public function up(): void
    {
        Schema::table('animals', function (Blueprint $table) {
             $table->string('breed')->nullable()->after('type');
        });
    }

   
    public function down(): void
    {
        Schema::table('animals', function (Blueprint $table) {
             $table->dropColumn('breed');
        });
    }
};
