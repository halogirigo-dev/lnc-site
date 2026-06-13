<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('booking_field_logs', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref', 30);
            $table->string('field_name', 100);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->string('changed_by', 200)->default('system');
            $table->foreignId('changed_by_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            // Append-only — no updated_at needed
            $table->timestamp('created_at')->useCurrent();

            $table->index('booking_ref');
            $table->index('field_name');
            $table->index('created_at');

            $table->foreign('booking_ref')
                ->references('ref')
                ->on('bookings')
                ->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('booking_field_logs');
    }
};
