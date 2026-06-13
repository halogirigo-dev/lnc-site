<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('booking_status_logs', function (Blueprint $table) {
            // FK to the authenticated user who made the change
            $table->foreignId('changed_by_id')
                ->nullable()
                ->after('changed_by')
                ->constrained('users')
                ->nullOnDelete();

            // IP address of the request that triggered the change
            $table->string('ip_address', 45)->nullable()->after('changed_by_id');
        });
    }

    public function down(): void {
        Schema::table('booking_status_logs', function (Blueprint $table) {
            $table->dropForeign(['changed_by_id']);
            $table->dropColumn(['changed_by_id', 'ip_address']);
        });
    }
};
