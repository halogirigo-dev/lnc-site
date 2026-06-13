<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $counts = Invoice::query()
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return [
            null => Tab::make('All')
                ->badge(array_sum($counts) ?: null),

            'draft' => Tab::make('Draft')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'draft'))
                ->badge($counts['draft'] ?? null)
                ->badgeColor('gray'),

            'sent' => Tab::make('Sent')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'sent'))
                ->badge($counts['sent'] ?? null)
                ->badgeColor('info'),

            'viewed' => Tab::make('Viewed')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'viewed'))
                ->badge($counts['viewed'] ?? null)
                ->badgeColor('warning'),

            'accepted' => Tab::make('Accepted')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'accepted'))
                ->badge($counts['accepted'] ?? null)
                ->badgeColor('success'),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'cancelled'))
                ->badge($counts['cancelled'] ?? null)
                ->badgeColor('danger'),
        ];
    }
}
