<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_code', 20)->unique();
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->string('duration', 100)->nullable();
            $table->string('category', 50)->nullable();
            $table->string('image_path', 500)->nullable();
            $table->bigInteger('price_per_pax')->default(0);
            $table->string('price_label', 50)->nullable();
            $table->integer('min_pax')->default(1);
            $table->jsonb('includes')->default('[]');
            $table->jsonb('excludes')->default('[]');
            $table->jsonb('itinerary')->default('[]');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_long_stay')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('tour_packages'); }
};
