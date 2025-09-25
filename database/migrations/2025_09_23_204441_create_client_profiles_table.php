<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('client_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('birthdate')->nullable();
            $table->string('address')->nullable();
            $table->enum('gender', ['m','f','x'])->nullable();
            $table->integer('height_cm')->nullable();
            $table->float('weight_kg')->nullable();
            $table->json('injuries')->nullable();
            $table->json('goals')->nullable();
            $table->unsignedSmallInteger('period_weeks')->nullable(); // 12 | 24
            $table->json('frequency')->nullable(); // sessions/week, duur, multiple/day
            $table->text('background')->nullable();
            $table->text('facilities')->nullable();
            $table->text('materials')->nullable();
            $table->text('work_hours')->nullable();
            $table->json('heartrate')->nullable();
            $table->json('test_12min')->nullable();
            $table->json('test_5k')->nullable();
            $table->string('coach_preference')->nullable(); // Eline | Nicky | Roy
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_profiles');
    }
};
