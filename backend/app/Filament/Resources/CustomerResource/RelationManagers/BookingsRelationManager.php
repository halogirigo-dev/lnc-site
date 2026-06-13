<?php

namespace App\Filament\Resources\CustomerResource\RelationManagers;

use App\Models\Booking;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class BookingsRelationManager extends RelationManager
{
    protected static string $relationship = 'bookings';

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('ref')
            ->heading('Booking History')
            ->columns([
                Tables\Columns\TextColumn::make('ref')
                    ->label('Reference')
                    ->searchable()
                    ->url(fn (Booking $record): string => route('filament.admin.resources.bookings.view', $record))
                    ->color('primary')
                    ->weight('semibold'),
                Tables\Columns\TextColumn::make('package_title')
                    ->label('Package')
                    ->limit(35),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => Booking::statusColors()[$state] ?? 'gray'),
                Tables\Columns\TextColumn::make('guests')
                    ->label('Pax'),
                Tables\Columns\TextColumn::make('dates')
                    ->label('Dates')
                    ->limit(25),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : '—'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Submitted')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
