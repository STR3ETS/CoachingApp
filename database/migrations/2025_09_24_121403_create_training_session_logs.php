<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('training_session_logs', function (Blueprint $t) {
            $t->id();
            $t->foreignId('client_id')->constrained()->cascadeOnDelete();
            $t->foreignId('plan_id')->constrained('training_plans')->cascadeOnDelete();
            $t->unsignedInteger('week_number');       // 1,2,3,...
            $t->unsignedTinyInteger('session_index'); // 0..n binnen die week
            $t->string('session_day')->nullable();    // bijv. "Maandag" (optioneel)
            $t->timestamp('completed_at')->nullable();

            // Feedbackvelden
            $t->text('went_well')->nullable();
            $t->text('went_poorly')->nullable();
            $t->unsignedTinyInteger('rpe')->nullable();           // 1..10, optioneel
            $t->unsignedSmallInteger('duration_minutes')->nullable(); // optioneel
            $t->text('notes')->nullable();

            $t->timestamps();

            $t->unique(['client_id','plan_id','week_number','session_index'], 'uniq_session_log');
        });
    }
    public function down(): void { Schema::dropIfExists('training_session_logs'); }
};
