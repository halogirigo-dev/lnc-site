<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListBookings extends ListRecords
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('New Booking'),
        ];
    }

    public function getTabs(): array
    {
        $counts = Booking::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $total = array_sum($counts);

        return [
            null => Tab::make('All')
                ->badge($total ?: null),

            'new' => Tab::make('New')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Booking::STATUS_NEW))
                ->badge($counts[Booking::STATUS_NEW] ?? null)
                ->badgeColor('warning'),

            'contacted' => Tab::make('Contacted')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Booking::STATUS_CONTACTED))
                ->badge($counts[Booking::STATUS_CONTACTED] ?? null)
                ->badgeColor('info'),

            'quoted' => Tab::make('Quoted')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Booking::STATUS_QUOTED))
                ->badge($counts[Booking::STATUS_QUOTED] ?? null)
                ->badgeColor('primary'),

            'confirmed' => Tab::make('Confirmed')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Booking::STATUS_CONFIRMED))
                ->badge($counts[Booking::STATUS_CONFIRMED] ?? null)
                ->badgeColor('success'),

            'completed' => Tab::make('Completed')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Booking::STATUS_COMPLETED))
                ->badge($counts[Booking::STATUS_COMPLETED] ?? null)
                ->badgeColor('gray'),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', Booking::STATUS_CANCELLED))
                ->badge($counts[Booking::STATUS_CANCELLED] ?? null)
                ->badgeColor('danger'),
        ];
    }
}
