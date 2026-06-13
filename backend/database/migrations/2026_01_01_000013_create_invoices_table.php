<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('booking_ref', 30);
            $table->string('invoice_number', 50)->unique();

            // type: proposal | quote | deposit_invoice | final_receipt
            $table->string('type', 30)->default('proposal');
            // status: draft | sent | viewed | accepted | cancelled
            $table->string('status', 30)->default('draft');

            $table->bigInteger('total_amount')->default(0);
            $table->bigInteger('deposit_amount')->default(0);
            $table->bigInteger('balance_amount')->default(0);
            $table->integer('deposit_pct')->default(30);

            $table->date('issued_at')->nullable();
            $table->date('valid_until')->nullable();
            $table->date('due_deposit_at')->nullable();
            $table->date('due_balance_at')->nullable();

            $table->timestamp('sent_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('accepted_at')->nullable();

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index('booking_ref');
            $table->index('status');
            $table->foreign('booking_ref')->references('ref')->on('bookings')->cascadeOnDelete();
        });
    }

    public function down(): void { Schema::dropIfExists('invoices'); }
};
