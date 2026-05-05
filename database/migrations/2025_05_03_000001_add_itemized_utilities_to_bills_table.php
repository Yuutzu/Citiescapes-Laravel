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
            $table->decimal('extras', 10, 2)->default(0)->after('wifi');
            $table->text('extras_note')->nullable()->after('extras');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn(['electricity', 'water', 'wifi', 'extras', 'extras_note']);
        });
    }
};
