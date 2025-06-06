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
        Schema::create('sleep_diaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapy_id')->constrained();
            $table->string('title');
            $table->integer('week');
            $table->integer('day');
            $table->date('date');
            $table->string('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sleep_diaries');
    }
};
