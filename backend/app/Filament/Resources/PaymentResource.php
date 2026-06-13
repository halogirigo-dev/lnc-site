<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?string $navigationLabel = 'Payments';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Operations';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('booking_ref')
                    ->label('Booking Ref')->searchable()->copyable()->fontFamily('mono'),
                Tables\Columns\TextColumn::make('payment_type')
                    ->badge()->color(fn ($state) => $state === 'deposit' ? 'warning' : 'success'),
                Tables\Columns\TextColumn::make('amount')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('midtrans_status')
                    ->badge()->color(fn ($state) => match($state) {
                        'settlement', 'capture' => 'success',
                        'pending'               => 'warning',
                        'expire', 'cancel'      => 'danger',
                        default                 => 'gray',
                    }),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime('d M Y H:i')->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('payment_type')
                    ->options(['deposit' => 'Deposit', 'balance' => 'Balance']),
                Tables\Filters\SelectFilter::make('midtrans_status')
                    ->options([
                        'settlement' => 'Settlement',
                        'capture'    => 'Capture',
                        'pending'    => 'Pending',
                        'expire'     => 'Expired',
                        'cancel'     => 'Cancelled',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
