<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // SS4 — default amenity items the tenant requested for the room.
        // Stored as JSON: [{"name": "Aircon", "fee": 500.00}, ...]
        // Flows into the SS3 initial-payment modal as a starting list.
        Schema::table('contracts', function (Blueprint $table) {
            $table->json('requested_amenities')->nullable()->after('room_key_fee');
        });

        // SS3 — frozen breakdown of amenity charges actually collected at move-in.
        // Same JSON shape as contracts.requested_amenities.
        Schema::table('initial_payments', function (Blueprint $table) {
            $table->json('amenities')->nullable()->after('room_key_fee');
            $table->decimal('amenities_total', 10, 2)->default(0)->after('amenities');
        });
    }

    public function down(): void
    {
        Schema::table('initial_payments', function (Blueprint $table) {
            $table->dropColumn(['amenities', 'amenities_total']);
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn('requested_amenities');
        });
    }
};
