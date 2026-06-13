<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\BookingStatusLog;
use App\Models\Customer;
use Filament\Resources\Pages\CreateRecord;

class CreateBooking extends CreateRecord
{
    protected static string $resource = BookingResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['ref'])) {
            $data['ref'] = Booking::generateRef();
        }
        $data['status'] = $data['status'] ?? Booking::STATUS_NEW;

        if (!empty($data['email'])) {
            $customer = Customer::upsertFromBooking($data);
            $data['customer_id'] = $customer->id;
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        BookingStatusLog::record(
            $this->record->ref,
            null,
            $this->record->status,
            auth()->user()?->email ?? 'admin',
            'Booking created manually from admin panel.'
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
