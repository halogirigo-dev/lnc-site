<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('ref', 30)->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_guide_id')->nullable()->constrained('team_members')->nullOnDelete();

            // Lifecycle status: new → contacted → quoted → confirmed → cancelled → completed
            $table->string('status', 50)->default('new');

            // Package
            $table->string('package_id', 20)->nullable();
            $table->string('package_title')->nullable();
            $table->string('package_duration', 100)->nullable();
            $table->bigInteger('package_price_per_pax')->default(0);

            // Pricing (admin-adjustable for quote)
            $table->bigInteger('total_amount')->default(0);
            $table->bigInteger('deposit_amount')->default(0);
            $table->bigInteger('balance_amount')->default(0);

            // Journey details
            $table->integer('guests')->default(1);
            $table->string('dates')->nullable();
            $table->string('flexibility', 100)->nullable();
            $table->string('accommodation')->nullable();

            // Guest-submitted info (denormalized for speed; canonical copy is in customers)
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('age_range', 20)->nullable();
            $table->string('source', 100)->nullable();

            // Guest vision
            $table->text('message')->nullable();
            $table->text('special')->nullable();
            $table->string('budget', 100)->nullable();

            // Admin fields
            $table->text('admin_notes')->nullable();
            $table->text('cancellation_reason')->nullable();

            // Lifecycle timestamps
            $table->timestamp('contacted_at')->nullable();
            $table->timestamp('quoted_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('ref');
            $table->index('status');
            $table->index('email');
            $table->index('customer_id');
            $table->index('created_at');
        });
    }

    public function down(): void { Schema::dropIfExists('bookings'); }
};
