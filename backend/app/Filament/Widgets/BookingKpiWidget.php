<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class BookingKpiWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $avgGroupSize = Booking::whereNotNull('guests')
            ->where('guests', '>', 0)
            ->avg('guests');

        $avgGuests = $avgGroupSize ? round($avgGroupSize, 1) : 0;

        $topPackage = Booking::select('package_title', DB::raw('COUNT(*) as total'))
            ->whereNotNull('package_title')
            ->where('package_title', '!=', '')
            ->whereNotIn('status', [Booking::STATUS_CANCELLED])
            ->groupBy('package_title')
            ->orderByDesc('total')
            ->first();

        $unassigned = Booking::whereIn('status', [
            Booking::STATUS_CONFIRMED,
        ])->whereNull('assigned_guide_id')->count();

        $noEmergencyContact = Booking::whereIn('status', [
            Booking::STATUS_CONFIRMED,
        ])->where(function ($q) {
            $q->whereNull('emergency_contact_name')
              ->orWhereNull('emergency_contact_phone');
        })->count();

        return [
            Stat::make('Avg Group Size', $avgGuests . ' pax')
                ->description('Across all non-cancelled bookings')
                ->color('info'),

            Stat::make('Top Package', $topPackage?->package_title ?? '—')
                ->description($topPackage ? $topPackage->total . ' bookings' : 'No data yet')
                ->color('primary'),

            Stat::make('Confirmed — No Guide', $unassigned)
                ->description('Confirmed bookings without a guide')
                ->color($unassigned > 0 ? 'danger' : 'success'),

            Stat::make('Missing Emergency Contact', $noEmergencyContact)
                ->description('Confirmed bookings — ops risk')
                ->color($noEmergencyContact > 0 ? 'warning' : 'success'),
        ];
    }
}
