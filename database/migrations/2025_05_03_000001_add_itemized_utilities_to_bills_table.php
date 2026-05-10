<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->decimal('electricity', 10, 2)->default(0)->after('utilities');
            $table->decimal('water', 10, 2)->default(0)->after('electricity');
            $table->decimal('wifi', 10, 2)->default(0)->after('water');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['electricity', 'water', 'wifi']);
        });
    }
};
