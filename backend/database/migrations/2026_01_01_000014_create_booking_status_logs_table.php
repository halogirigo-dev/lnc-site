<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('booking_status_logs', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref', 30);
            $table->string('from_status', 50)->nullable();
            $table->string('to_status', 50);
            $table->string('changed_by', 200)->default('system');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('booking_ref');
            $table->index('created_at');
            $table->foreign('booking_ref')->references('ref')->on('bookings')->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('booking_status_logs'); }
};
