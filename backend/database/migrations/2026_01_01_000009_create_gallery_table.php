<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::create('gallery', function (Blueprint $table) {
            $table->id();
            $table->string('image_path', 500);
            $table->string('caption', 500)->nullable();
            $table->string('alt_text')->nullable();
            $table->string('category', 50)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('gallery'); }
};
