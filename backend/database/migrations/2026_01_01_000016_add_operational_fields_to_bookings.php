<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('bookings', function (Blueprint $table) {
            // Group demographics
            $table->string('group_type', 30)->nullable()->after('guests');
            $table->string('trip_purpose', 50)->nullable()->after('group_type');

            // Specific accommodation property
            $table->string('accommodation_name', 200)->nullable()->after('accommodation');

            // Pickup / drop-off
            $table->text('pickup_location')->nullable()->after('accommodation_name');

            // Flight coordination
            $table->string('arrival_flight', 30)->nullable()->after('departure_date');
            $table->string('arrival_time', 20)->nullable()->after('arrival_flight');
            $table->string('departure_flight', 30)->nullable()->after('arrival_time');
            $table->string('departure_time', 20)->nullable()->after('departure_flight');

            // Separated requirements (safety — removed from catch-all 'special')
            $table->text('dietary_requirements')->nullable()->after('special');
            $table->text('transport_requirements')->nullable()->after('dietary_requirements');

            // Emergency contact — required for trekking packages
            $table->string('emergency_contact_name', 200)->nullable();
            $table->string('emergency_contact_phone', 50)->nullable();

            // Index group_type for filtering
            $table->index('group_type');
        });
    }

    public function down(): void {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['group_type']);
            $table->dropColumn([
                'group_type', 'trip_purpose',
                'accommodation_name', 'pickup_location',
                'arrival_flight', 'arrival_time',
                'departure_flight', 'departure_time',
                'dietary_requirements', 'transport_requirements',
                'emergency_contact_name', 'emergency_contact_phone',
            ]);
        });
    }
};
