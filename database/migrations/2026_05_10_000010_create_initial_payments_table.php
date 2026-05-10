<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SS3 — Initial Payment Recording (move-in fees)
        // Amounts are pulled from the linked SS4 contract and stored read-only.
        // GM only inputs date received and payment method.
        Schema::create('initial_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->decimal('deposit_amount', 10, 2);
            $table->decimal('first_month_rent', 10, 2);
            $table->decimal('room_key_fee', 10, 2);
            $table->decimal('total_collected', 10, 2);
            $table->date('date_received');
            $table->enum('payment_method', ['cash', 'bank_transfer', 'e_wallet']);
            $table->string('reference_number')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique('contract_id');
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('initial_payments');
    }
};
