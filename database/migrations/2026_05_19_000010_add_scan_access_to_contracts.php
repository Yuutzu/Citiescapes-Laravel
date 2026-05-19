<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $t) {
            // Tenant must request GM approval before they can view the scanned contract.
            // Status: null = no request; pending; approved; denied.
            $t->string('scan_view_status', 16)->nullable()->after('scan_file_name');
            $t->timestamp('scan_view_requested_at')->nullable()->after('scan_view_status');
            $t->timestamp('scan_view_decided_at')->nullable()->after('scan_view_requested_at');
            $t->foreignId('scan_view_decided_by')->nullable()->after('scan_view_decided_at')
                ->constrained('users')->nullOnDelete();
            $t->string('scan_view_decision_note', 500)->nullable()->after('scan_view_decided_by');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $t) {
            $t->dropForeign(['scan_view_decided_by']);
            $t->dropColumn([
                'scan_view_status',
                'scan_view_requested_at',
                'scan_view_decided_at',
                'scan_view_decided_by',
                'scan_view_decision_note',
            ]);
        });
    }
};
