<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SS6 — OTP records for first-time activation
        Schema::create('otp_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('code'); // hashed
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'used']);
        });

        // Notifications log (shared by SS2, SS4)
        Schema::create('notifications_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // 30_day_warning, 7_day_warning, grace_reminder, overdue, etc.
            $table->string('source'); // SS2, SS3, SS4
            $table->text('message');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
            $table->index(['user_id', 'is_read']);
        });

        // SS6 — Audit log (immutable)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_type')->nullable(); // tenant, gm, system
            $table->string('action'); // login, logout, failed_login, lockout, password_change, etc.
            $table->string('subsystem')->nullable(); // SS1-SS6
            $table->text('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['user_id', 'action']);
            $table->index('created_at');
        });

        // SS5 — Archive database
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_record_id');
            $table->string('record_type'); // room, tenant_account, payment, contract
            $table->string('source_subsystem'); // SS1, SS2, SS3, SS4
            $table->text('archive_reason')->nullable();
            $table->json('data'); // full snapshot of the archived record
            $table->string('scan_file_path')->nullable(); // for contract scans
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('restored')->default(false);
            $table->timestamp('restored_at')->nullable();
            $table->timestamps();

            $table->index(['record_type', 'source_subsystem']);
        });

        // SS6 — System settings (configurable by GM)
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
        Schema::dropIfExists('archives');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('notifications_log');
        Schema::dropIfExists('otp_records');
    }
};
