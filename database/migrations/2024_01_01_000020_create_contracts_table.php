<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SS4 — Contract Management System
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->decimal('base_rent_rate', 10, 2);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->decimal('room_key_fee', 10, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['draft', 'active', 'expired', 'terminated'])->default('draft');
            $table->decimal('penalty_rate', 10, 2)->default(100); // daily penalty PHP
            $table->unsignedTinyInteger('penalty_grace_days')->default(3);
            $table->text('house_rules')->nullable();
            $table->text('penalty_schedule')->nullable();
            $table->string('scan_file_path')->nullable();
            $table->string('scan_file_name')->nullable();
            $table->timestamp('step1_acknowledged_at')->nullable(); // read & understood
            $table->timestamp('step2_acknowledged_at')->nullable(); // penalty accepted
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('terminated_at')->nullable();
            $table->text('termination_reason')->nullable();
            $table->boolean('warning_30_sent')->default(false);
            $table->boolean('warning_7_sent')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'end_date']);
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
