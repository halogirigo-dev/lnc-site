<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('bookings', function (Blueprint $table) {
            $table->date('arrival_date')->nullable()->after('dates');
            $table->date('departure_date')->nullable()->after('arrival_date');
            $table->index('arrival_date');
            $table->index('departure_date');
        });
    }

    public function down(): void {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['arrival_date']);
            $table->dropIndex(['departure_date']);
            $table->dropColumn(['arrival_date', 'departure_date']);
        });
    }
};
