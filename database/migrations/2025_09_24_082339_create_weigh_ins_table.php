<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('weigh_ins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('weight_kg', 5, 1);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['client_id','date']); // één per dag
        });
    }
    public function down(): void {
        Schema::dropIfExists('weigh_ins');
    }
};
