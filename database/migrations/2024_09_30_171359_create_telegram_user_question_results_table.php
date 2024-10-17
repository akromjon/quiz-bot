<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('telegram_user_question_results', function (Blueprint $table) {
            $table->id();
            $table->string('type', 50)->default('topical');
            $table->unsignedBigInteger('telegram_user_id');
            $table->unsignedBigInteger('page_number');
            $table->unsignedBigInteger('question_id')->nullable();
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('telegram_user_question_results');
    }
};
