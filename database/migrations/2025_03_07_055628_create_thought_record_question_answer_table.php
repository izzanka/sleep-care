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
        Schema::create('thought_record_question_answer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thought_record_id')->constrained();
            $table->foreignId('question_id')->constrained();
            $table->foreignId('answer_id')->constrained();
            $table->boolean('is_read')->nullable();
            $table->string('comment')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thought_record_question_answer');
    }
};
