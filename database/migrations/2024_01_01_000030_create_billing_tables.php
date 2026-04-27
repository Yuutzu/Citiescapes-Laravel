<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SS3 — Billing Management System
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->enum('type', ['initial', 'monthly'])->default('monthly');
            $table->string('billing_period')->nullable(); // "2024-03" format
            $table->decimal('base_rent', 10, 2)->default(0);
            $table->decimal('utilities', 10, 2)->default(0);
            $table->decimal('deposit_amount', 10, 2)->default(0); // for initial only
            $table->decimal('room_key_fee', 10, 2)->default(0);   // for initial only
            $table->decimal('penalty_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->date('due_date');
            $table->enum('status', ['unpaid', 'grace', 'overdue', 'delinquent', 'eviction', 'paid', 'archived'])
                  ->default('unpaid');
            $table->unsignedSmallInteger('days_overdue')->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['due_date', 'status']);
        });

        // Payment records
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('payment_method')->nullable(); // cash, gcash, bank_transfer etc.
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });

        // Penalty override/waive log
        Schema::create('penalty_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bill_id')->constrained('bills')->cascadeOnDelete();
            $table->foreignId('overridden_by')->constrained('users')->cascadeOnDelete();
            $table->decimal('original_penalty', 10, 2);
            $table->decimal('adjusted_penalty', 10, 2);
            $table->text('reason');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_overrides');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bills');
    }
};
