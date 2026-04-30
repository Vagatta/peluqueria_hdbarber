<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('stripe_payment_intent_id')->nullable()->unique();
            $table->string('stripe_checkout_session_id')->nullable()->index();
            $table->unsignedInteger('amount_cents');
            $table->string('currency', 3)->default('eur');
            $table->enum('status', ['pending', 'processing', 'succeeded', 'failed', 'refunded', 'cancelled'])->default('pending');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
