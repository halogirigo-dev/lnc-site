<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Invoice;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewBooking extends ViewRecord
{
    protected static string $resource = BookingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Contact (new → contacted)
            Actions\Action::make('contact')
                ->label('Mark Contacted')
                ->icon('heroicon-o-phone')
                ->color('info')
                ->visible(fn () => $this->record->status === Booking::STATUS_NEW)
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('e.g. Sent WhatsApp message and intro email')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->transitionTo(
                        Booking::STATUS_CONTACTED,
                        Auth::user()?->email ?? 'admin',
                        $data['notes'] ?? null
                    );
                    Notification::make()->title('Marked as Contacted')->success()->send();
                    $this->refreshRecord();
                }),

            // Send quote (contacted → quoted)
            Actions\Action::make('send_quote')
                ->label('Send Quote')
                ->icon('heroicon-o-document-text')
                ->color('primary')
                ->visible(fn () => $this->record->status === Booking::STATUS_CONTACTED)
                ->form([
                    Forms\Components\TextInput::make('total_amount')
                        ->label('Quoted Total (IDR)')
                        ->numeric()->prefix('Rp')
                        ->default(fn () => $this->record->total_amount ?: null)
                        ->required()
                        ->helperText('Deposit (30%) and balance (70%) will be auto-calculated.'),
                    Forms\Components\Textarea::make('notes')
                        ->label('Notes')
                        ->placeholder('e.g. Sent full proposal PDF via email')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    Invoice::createQuote($this->record, (int) $data['total_amount']);
                    $this->record->transitionTo(
                        Booking::STATUS_QUOTED,
                        Auth::user()?->email ?? 'admin',
                        $data['notes'] ?? null
                    );
                    Notification::make()->title('Quote created — status updated to Quoted')->success()->send();
                    $this->refreshRecord();
                }),

            // Re-quote (quoted → contacted, for renegotiation)
            Actions\Action::make('re_quote')
                ->label('Re-negotiate')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('warning')
                ->visible(fn () => $this->record->status === Booking::STATUS_QUOTED)
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label('Re-negotiation Notes')
                        ->placeholder('e.g. Guest requested itinerary change — reverting to contacted for revised quote')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->transitionTo(
                        Booking::STATUS_CONTACTED,
                        Auth::user()?->email ?? 'admin',
                        $data['notes']
                    );
                    Notification::make()->title('Reverted to Contacted — re-quote in progress')->warning()->send();
                    $this->refreshRecord();
                }),

            // Confirm booking (quoted → confirmed)
            Actions\Action::make('confirm_booking')
                ->label('Confirm Booking')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () => $this->record->status === Booking::STATUS_QUOTED)
                ->form([
                    Forms\Components\Select::make('assigned_guide_id')
                        ->label('Assign Guide (required)')
                        ->options(\App\Models\User::query()->orderBy('name')->pluck('name', 'id'))
                        ->default(fn () => $this->record->assigned_guide_id)
                        ->searchable()
                        ->required()
                        ->helperText('A guide must be assigned before a booking can be confirmed.'),
                    Forms\Components\Textarea::make('notes')
                        ->label('Confirmation Notes')
                        ->placeholder('e.g. Guest accepted quote via WhatsApp on 14 Jun 2026')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    if (!$this->record->assigned_guide_id || $this->record->assigned_guide_id !== (int) $data['assigned_guide_id']) {
                        $this->record->assigned_guide_id = (int) $data['assigned_guide_id'];
                        $this->record->saveQuietly();
                    }
                    $this->record->transitionTo(
                        Booking::STATUS_CONFIRMED,
                        Auth::user()?->email ?? 'admin',
                        $data['notes']
                    );
                    Notification::make()->title('Booking Confirmed')->success()->send();
                    $this->refreshRecord();
                }),

            // Mark complete (confirmed → completed)
            Actions\Action::make('mark_complete')
                ->label('Mark Complete')
                ->icon('heroicon-o-flag')
                ->color('gray')
                ->visible(fn () => $this->record->status === Booking::STATUS_CONFIRMED)
                ->form([
                    Forms\Components\Textarea::make('notes')
                        ->label('Completion Notes')
                        ->placeholder('e.g. Journey completed on 20 Aug. Guest departed safely from LOP.')
                        ->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->transitionTo(
                        Booking::STATUS_COMPLETED,
                        Auth::user()?->email ?? 'admin',
                        $data['notes'] ?? null
                    );
                    Notification::make()->title('Journey marked Complete')->success()->send();
                    $this->refreshRecord();
                }),

            // Create invoice (any active booking)
            Actions\Action::make('create_invoice')
                ->label('Create Invoice')
                ->icon('heroicon-o-plus-circle')
                ->color('warning')
                ->visible(fn () => !in_array($this->record->status, [
                    Booking::STATUS_CANCELLED, Booking::STATUS_COMPLETED,
                ]))
                ->form([
                    Forms\Components\Select::make('type')
                        ->options(Invoice::types())
                        ->required()
                        ->default('proposal'),
                    Forms\Components\TextInput::make('total_amount')
                        ->label('Total Amount (IDR)')
                        ->numeric()->prefix('Rp')
                        ->default(fn () => $this->record->total_amount ?: null),
                    Forms\Components\Textarea::make('notes')
                        ->label('Invoice Notes')->rows(3),
                ])
                ->action(function (array $data) {
                    $total   = (int) ($data['total_amount'] ?? $this->record->total_amount ?? 0);
                    $deposit = (int) round($total * 0.30 / 1000) * 1000;
                    $balance = $total - $deposit;
                    $suffix  = now()->format('Ymd');
                    $prefix  = match ($data['type']) {
                        'proposal'        => 'PRO',
                        'quote'           => 'QT',
                        'deposit_invoice' => 'INV-D',
                        'final_receipt'   => 'RCP',
                        default           => 'DOC',
                    };

                    Invoice::create([
                        'booking_ref'    => $this->record->ref,
                        'invoice_number' => "{$prefix}-{$this->record->ref}-{$suffix}",
                        'type'           => $data['type'],
                        'status'         => 'draft',
                        'total_amount'   => $total,
                        'deposit_amount' => $deposit,
                        'balance_amount' => $balance,
                        'deposit_pct'    => 30,
                        'issued_at'      => now()->toDateString(),
                        'valid_until'    => now()->addDays(14)->toDateString(),
                        'due_deposit_at' => now()->addDays(7)->toDateString(),
                        'due_balance_at' => now()->addDays(30)->toDateString(),
                        'notes'          => $data['notes'] ?? null,
                    ]);

                    Notification::make()->title('Invoice created')->success()->send();
                    $this->refreshRecord();
                }),

            // Cancel booking
            Actions\Action::make('cancel_booking')
                ->label('Cancel')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => !in_array($this->record->status, [
                    Booking::STATUS_CANCELLED, Booking::STATUS_COMPLETED,
                ]))
                ->form([
                    Forms\Components\Textarea::make('cancellation_reason')
                        ->label('Reason for Cancellation')
                        ->required()->rows(3),
                ])
                ->action(function (array $data) {
                    $this->record->cancellation_reason = $data['cancellation_reason'];
                    $this->record->save();
                    $this->record->transitionTo(
                        Booking::STATUS_CANCELLED,
                        Auth::user()?->email ?? 'admin',
                        $data['cancellation_reason']
                    );
                    Notification::make()->title('Booking Cancelled')->warning()->send();
                    $this->refreshRecord();
                }),

            Actions\EditAction::make()->label('Edit Details'),
        ];
    }
}
