<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SS1 — Room Management System
        // Status is updated MANUALLY by the GM only (no automated changes).
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('room_number')->unique(); // e.g. "101", "201"
            $table->unsignedTinyInteger('floor_level'); // 1, 2, or 3
            $table->enum('room_type', ['compact', 'spacious'])->default('compact');
            $table->json('photos')->nullable(); // array of file paths
            $table->json('amenities')->nullable(); // ["air-conditioning", "wifi", ...]
            $table->decimal('rate', 10, 2)->default(0);
            $table->unsignedTinyInteger('max_occupants')->default(4);
            $table->enum('status', ['available', 'occupied', 'under_maintenance'])->default('available');
            $table->text('description')->nullable();
            $table->foreignId('current_tenant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_status_update')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'floor_level']);
        });

        // Inquiry log — public visitors can submit without an account
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('sender_name');
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->enum('preferred_room_type', ['compact', 'spacious', 'any'])->default('any');
            $table->text('message');
            $table->enum('status', ['pending', 'responded', 'closed'])->default('pending');
            $table->text('gm_notes')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('rooms');
    }
};
