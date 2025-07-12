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
            Schema::create('courses', function (Blueprint $table) {
                $table->id();
            $table->string('name');
        $table->string('description')->nullable();
        $table->string('video')->nullable();
        $table->boolean('is_active')->default(true);
        $table->string('duration')->nullable();

        // التعديل هنا
        $table->unsignedBigInteger('category_id')->nullable();
        $table->foreign('category_id')
              ->references('id')
              ->on('catgory_courses')
              ->onDelete('set null');

        $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
            Schema::dropIfExists('courses');
        }
    };
