<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HotelResource\Pages;
use App\Filament\Resources\HotelResource\RelationManagers;
use App\Models\Hotel;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class HotelResource extends Resource
{
    protected static ?string $model = Hotel::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';
    protected static ?string $navigationLabel = 'Hotels';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Zone')->schema([
                Forms\Components\TextInput::make('zone')
                    ->required()->placeholder('Kuta Mandalika'),
                Forms\Components\TextInput::make('area')
                    ->placeholder('South Lombok'),
                Forms\Components\ColorPicker::make('zone_color'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                Forms\Components\Toggle::make('is_active')->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Properties')->schema([
                Forms\Components\Repeater::make('properties_inline')
                    ->label('Hotel Properties')
                    ->relationship('properties')
                    ->schema([
                        Forms\Components\TextInput::make('name')->required(),
                        Forms\Components\TextInput::make('type')->placeholder('Boutique Hotel'),
                        Forms\Components\TextInput::make('room_type')->placeholder('Garden Deluxe'),
                        Forms\Components\TextInput::make('features')->columnSpanFull(),
                        Forms\Components\TextInput::make('price_low')->placeholder('850.000'),
                        Forms\Components\TextInput::make('price_high')->placeholder('1.100.000'),
                        Forms\Components\TextInput::make('breakfast')->placeholder('Include (2 pax)'),
                        Forms\Components\TextInput::make('rating')->placeholder('4.6 Google'),
                        Forms\Components\Textarea::make('review_text')->rows(2)->columnSpanFull(),
                        Forms\Components\TextInput::make('contact'),
                        Forms\Components\TextInput::make('image_path')->placeholder('/uploads/hotels/hotel-name.jpg'),
                        Forms\Components\Toggle::make('is_active')->default(true),
                        Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
                    ])->columns(2)->collapsible()->defaultItems(0),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('zone')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('area'),
                Tables\Columns\ColorColumn::make('zone_color')->label('Color'),
                Tables\Columns\TextColumn::make('properties_count')
                    ->counts('properties')
                    ->label('Properties')->alignCenter(),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('#')->sortable(),
            ])
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListHotels::route('/'),
            'create' => Pages\CreateHotel::route('/create'),
            'edit'   => Pages\EditHotel::route('/{record}/edit'),
        ];
    }
}
