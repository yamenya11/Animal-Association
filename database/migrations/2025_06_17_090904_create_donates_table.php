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
                Schema::create('donates', function (Blueprint $table) {
                $table->id();
                $table->string('full_name');
                $table->string('number');
                $table->string('email')->nullable();
                $table->string('donation_type', 50); // تحويل enum إلى string
                $table->decimal('amount', 15, 2)->nullable(); 
                 $table->string('ammountinkello')->nullable(); 
                $table->text('notes')->nullable();
                $table->boolean('is_approved')->default(false);                     
               $table->timestamps();
                });
            }

        /**
         * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donates');
    }
};
