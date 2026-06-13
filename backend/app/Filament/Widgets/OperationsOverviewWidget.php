<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $arrivingToday    = Booking::arrivingToday()->count();
        $arrivingTomorrow = Booking::arrivingTomorrow()->count();
        $activeTours      = Booking::activeTours()->count();

        $newUncontacted = Booking::where('status', Booking::STATUS_NEW)->count();
        $pendingQuote   = Booking::where('status', Booking::STATUS_CONTACTED)->count();
        $awaitingConfirm = Booking::where('status', Booking::STATUS_QUOTED)->count();

        return [
            Stat::make("Today's Arrivals", $arrivingToday)
                ->description('Guests arriving today')
                ->color($arrivingToday > 0 ? 'success' : 'gray')
                ->icon('heroicon-o-arrow-down-on-square'),

            Stat::make("Tomorrow's Arrivals", $arrivingTomorrow)
                ->description('Arriving tomorrow')
                ->color($arrivingTomorrow > 0 ? 'info' : 'gray')
                ->icon('heroicon-o-calendar-days'),

            Stat::make('Active Tours', $activeTours)
                ->description('Currently on tour')
                ->color($activeTours > 0 ? 'success' : 'gray')
                ->icon('heroicon-o-map'),

            Stat::make('New — Uncontacted', $newUncontacted)
                ->description('Needs first contact')
                ->color($newUncontacted > 0 ? 'danger' : 'gray')
                ->icon('heroicon-o-envelope'),

            Stat::make('Pending Quote', $pendingQuote)
                ->description('Contacted, no quote sent')
                ->color($pendingQuote > 0 ? 'warning' : 'gray')
                ->icon('heroicon-o-document-text'),

            Stat::make('Awaiting Confirmation', $awaitingConfirm)
                ->description('Quote sent — pending deposit')
                ->color($awaitingConfirm > 0 ? 'primary' : 'gray')
                ->icon('heroicon-o-clock'),
        ];
    }
}
