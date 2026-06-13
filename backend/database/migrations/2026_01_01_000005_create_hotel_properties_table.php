<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('hotel_properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type', 100)->nullable();
            $table->string('room_type')->nullable();
            $table->text('features')->nullable();
            $table->string('price_low', 50)->nullable();
            $table->string('price_high', 50)->nullable();
            $table->string('breakfast', 100)->nullable();
            $table->string('rating', 100)->nullable();
            $table->text('review_text')->nullable();
            $table->string('contact', 100)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('hotel_properties'); }
};
