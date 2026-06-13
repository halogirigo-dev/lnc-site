<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique()->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('age_range', 20)->nullable();
            $table->string('source', 100)->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestamp('last_booking_at')->nullable();
            $table->timestamps();
            $table->index('email');
            $table->index('created_at');
        });
    }

    public function down(): void { Schema::dropIfExists('customers'); }
};
