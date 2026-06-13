<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref', 20);
            $table->string('payment_type', 20);
            $table->bigInteger('amount')->default(0);
            $table->string('midtrans_order_id', 100)->nullable();
            $table->string('midtrans_transaction_id', 100)->nullable();
            $table->string('midtrans_status', 50)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->text('snap_token')->nullable();
            $table->jsonb('raw_notification')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->unique(['booking_ref', 'payment_type']);
            $table->index('booking_ref');
            $table->index('midtrans_order_id');
            $table->foreign('booking_ref')->references('ref')->on('bookings')->cascadeOnDelete();
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
