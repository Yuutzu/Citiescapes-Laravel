<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $t) {
            // Tracks how much of the security deposit has been applied toward
            // unpaid bills (arrears). Default 0 = deposit fully refundable on
            // good-standing move-out. When a GM applies deposit to a bill in
            // eviction, this amount accumulates and prevents double-application.
            $t->decimal('deposit_applied_amount', 10, 2)->default(0)->after('deposit');
            $t->timestamp('deposit_applied_at')->nullable()->after('deposit_applied_amount');
        });
    }

    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $t) {
            $t->dropColumn(['deposit_applied_amount', 'deposit_applied_at']);
        });
    }
};
