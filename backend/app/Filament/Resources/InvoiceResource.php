<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Models\Booking;
use App\Models\Invoice;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon  = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int    $navigationSort  = 3;
    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Invoice')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('invoice_number')
                        ->label('Invoice #')
                        ->weight('bold')
                        ->copyable(),
                    Infolists\Components\TextEntry::make('type')
                        ->label('Type')
                        ->badge()
                        ->formatStateUsing(fn ($state) => Invoice::types()[$state] ?? ucfirst($state))
                        ->color('primary'),
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->color(fn ($state) => Invoice::statusColors()[$state] ?? 'gray'),
                    Infolists\Components\TextEntry::make('booking_ref')
                        ->label('Booking Ref')
                        ->url(fn (Invoice $record): string => route('filament.admin.resources.bookings.view', ['record' => $record->booking_ref]))
                        ->color('primary'),
                    Infolists\Components\TextEntry::make('booking.name')
                        ->label('Guest Name'),
                    Infolists\Components\TextEntry::make('booking.email')
                        ->label('Guest Email'),
                ]),

            Infolists\Components\Section::make('Amounts')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('total_amount')
                        ->label('Total')
                        ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '—')
                        ->weight('bold'),
                    Infolists\Components\TextEntry::make('deposit_amount')
                        ->label('Deposit (30%)')
                        ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '—'),
                    Infolists\Components\TextEntry::make('balance_amount')
                        ->label('Balance (70%)')
                        ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '—'),
                ]),

            Infolists\Components\Section::make('Dates')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('issued_at')
                        ->label('Issued')
                        ->date('d M Y'),
                    Infolists\Components\TextEntry::make('valid_until')
                        ->label('Valid Until')
                        ->date('d M Y'),
                    Infolists\Components\TextEntry::make('due_deposit_at')
                        ->label('Deposit Due')
                        ->date('d M Y'),
                    Infolists\Components\TextEntry::make('due_balance_at')
                        ->label('Balance Due')
                        ->date('d M Y'),
                    Infolists\Components\TextEntry::make('sent_at')
                        ->label('Sent At')
                        ->dateTime('d M Y, H:i')
                        ->placeholder('Not sent yet'),
                    Infolists\Components\TextEntry::make('viewed_at')
                        ->label('Viewed At')
                        ->dateTime('d M Y, H:i')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('accepted_at')
                        ->label('Accepted At')
                        ->dateTime('d M Y, H:i')
                        ->placeholder('—'),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Created')
                        ->dateTime('d M Y, H:i'),
                ]),

            Infolists\Components\Section::make('Notes')
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('notes')
                        ->label('')
                        ->placeholder('No notes.')
                        ->prose(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice #')
                    ->searchable()
                    ->weight('semibold')
                    ->copyable(),
                Tables\Columns\TextColumn::make('booking_ref')
                    ->label('Booking')
                    ->searchable()
                    ->url(fn (Invoice $record): string => route('filament.admin.resources.bookings.view', ['record' => $record->booking_ref]))
                    ->color('primary'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->formatStateUsing(fn ($state) => Invoice::types()[$state] ?? ucfirst($state))
                    ->color('primary'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => Invoice::statusColors()[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('issued_at')
                    ->label('Issued')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->date('d M Y')
                    ->sortable()
                    ->color(fn ($state) => $state && $state->isPast() ? 'danger' : null),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('Sent')
                    ->date('d M Y')
                    ->toggleable()
                    ->placeholder('—'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options(Invoice::types()),
                Tables\Filters\SelectFilter::make('status')
                    ->options(Invoice::statuses()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('mark_sent')
                    ->label('Mark Sent')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (Invoice $record) => $record->status === 'draft')
                    ->action(function (Invoice $record) {
                        $record->markSent();
                        Notification::make()->title('Invoice marked as Sent')->success()->send();
                    }),
                Tables\Actions\Action::make('mark_accepted')
                    ->label('Mark Accepted')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Invoice $record) => in_array($record->status, ['sent', 'viewed']))
                    ->action(function (Invoice $record) {
                        $record->markAccepted();
                        Notification::make()->title('Invoice marked as Accepted')->success()->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvoices::route('/'),
            'view'  => Pages\ViewInvoice::route('/{record}'),
        ];
    }
}
