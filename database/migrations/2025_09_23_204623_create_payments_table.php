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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_session_id')->unique();
            $table->integer('amount'); // cents
            $table->string('currency', 3)->default('EUR');
            $table->enum('status', ['pending','paid','failed','refunded'])->default('pending');
            $table->unsignedSmallInteger('period_weeks'); // 12 | 24
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
