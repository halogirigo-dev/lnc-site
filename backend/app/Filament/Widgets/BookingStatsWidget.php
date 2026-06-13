<?php
namespace App\Filament\Widgets;
use App\Models\Booking;
use App\Models\Payment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsWidget extends BaseWidget {
    protected function getStats(): array {
        $totalRevenue = Payment::where('midtrans_status', 'settlement')
            ->orWhere('midtrans_status', 'capture')
            ->sum('amount');
        return [
            Stat::make('Total Bookings', Booking::count())
                ->description('All time')->color('primary'),
            Stat::make('Pending Payment', Booking::where('status', 'pending_payment')->count())
                ->description('Awaiting deposit')->color('warning'),
            Stat::make('Confirmed', Booking::whereIn('status', ['deposit_paid','balance_paid','confirmed'])->count())
                ->description('Active bookings')->color('success'),
            Stat::make('Revenue Collected', 'Rp ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Via Midtrans')->color('success'),
        ];
    }
}
