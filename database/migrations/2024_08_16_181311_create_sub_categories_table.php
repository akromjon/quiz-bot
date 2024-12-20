<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(table: 'sub_categories', callback: function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('category_id');
            $table->string('excel_file_path')->nullable();
            $table->unsignedBigInteger('sheet_number')->default(0);
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};
