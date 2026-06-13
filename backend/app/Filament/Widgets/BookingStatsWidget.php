<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BookingStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $thisMonth  = now()->startOfMonth();
        $lastMonth  = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();

        $thisMonthCount = Booking::where('created_at', '>=', $thisMonth)->count();
        $lastMonthCount = Booking::whereBetween('created_at', [$lastMonth, $lastMonthEnd])->count();

        $pendingAction = Booking::whereIn('status', [
            Booking::STATUS_NEW,
            Booking::STATUS_CONTACTED,
            Booking::STATUS_QUOTED,
        ])->count();

        $confirmedActive = Booking::where('status', Booking::STATUS_CONFIRMED)->count();

        $totalCompleted = Booking::where('status', Booking::STATUS_COMPLETED)->count();
        $totalAll       = Booking::whereNotIn('status', [Booking::STATUS_CANCELLED])->count();
        $conversionRate = $totalAll > 0
            ? round(($totalCompleted / $totalAll) * 100)
            : 0;

        return [
            Stat::make('Bookings This Month', $thisMonthCount)
                ->description($lastMonthCount > 0
                    ? ($thisMonthCount >= $lastMonthCount ? '+' : '') . ($thisMonthCount - $lastMonthCount) . ' vs last month'
                    : 'No data last month')
                ->color($thisMonthCount >= $lastMonthCount ? 'success' : 'warning'),

            Stat::make('Pending Action', $pendingAction)
                ->description('New / Contacted / Quoted')
                ->color($pendingAction > 5 ? 'danger' : 'warning'),

            Stat::make('Confirmed Active', $confirmedActive)
                ->description('Bookings in confirmed state')
                ->color('success'),

            Stat::make('Completion Rate', $conversionRate . '%')
                ->description('Completed ÷ non-cancelled')
                ->color($conversionRate >= 70 ? 'success' : 'info'),
        ];
    }
}
