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
        Schema::create('identify_value_question_answer', function (Blueprint $table) {
            $table->foreignId('identify_value_id')->constrained();
            $table->foreignId('question_id')->constrained();
            $table->foreignId('answer_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('identify_value_question_answer');
    }
};
