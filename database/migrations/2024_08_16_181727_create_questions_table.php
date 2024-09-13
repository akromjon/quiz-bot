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

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('question');
            $table->unsignedBigInteger('number');
            $table->string('file')->nullable();
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('sub_category_id')->nullable();
            $table->boolean('is_free')->default(false);
            $table->foreign('sub_category_id')->references('id')->on('sub_categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
