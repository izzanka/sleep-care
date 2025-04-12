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
        Schema::create('therapy_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('therapy_id')->constrained();
            $table->string('title');
            $table->text('description');
            $table->string('note')->nullable();
            $table->string('link')->nullable();
            $table->boolean('is_completed')->default(false);
            $table->date('date');
            $table->time('time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('therapy_schedules');
    }
};
