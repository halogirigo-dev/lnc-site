<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('mark_sent')
                ->label('Mark Sent')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->visible(fn () => $this->record->status === 'draft')
                ->action(function () {
                    $this->record->markSent();
                    Notification::make()->title('Invoice marked as Sent')->success()->send();
                    $this->refreshRecord();
                }),

            Actions\Action::make('mark_accepted')
                ->label('Mark Accepted')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => in_array($this->record->status, ['sent', 'viewed']))
                ->action(function () {
                    $this->record->markAccepted();
                    Notification::make()->title('Invoice marked as Accepted')->success()->send();
                    $this->refreshRecord();
                }),

            Actions\Action::make('cancel_invoice')
                ->label('Cancel Invoice')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => !in_array($this->record->status, ['accepted', 'cancelled']))
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);
                    Notification::make()->title('Invoice cancelled')->warning()->send();
                    $this->refreshRecord();
                }),
        ];
    }
}
