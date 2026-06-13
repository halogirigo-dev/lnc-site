<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TourPackageResource\Pages;
use App\Models\TourPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TourPackageResource extends Resource
{
    protected static ?string $model = TourPackage::class;
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Tour Packages';
    protected static ?int $navigationSort = 2;
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')->schema([
                Forms\Components\TextInput::make('package_code')
                    ->required()->unique(ignoreRecord: true)
                    ->placeholder('LNC-01')->maxLength(20),
                Forms\Components\TextInput::make('title')
                    ->required()->maxLength(255),
                Forms\Components\TextInput::make('subtitle')
                    ->maxLength(500),
                Forms\Components\Select::make('category')
                    ->options([
                        'culture'   => 'Culture & Heritage',
                        'island'    => 'Island Escape',
                        'adventure' => 'Adventure & Active',
                        'honeymoon' => 'Honeymoon & Romance',
                        'long'      => 'Long Stay',
                    ])->required(),
                Forms\Components\TextInput::make('duration')
                    ->placeholder('3 Days / 2 Nights'),
                Forms\Components\TextInput::make('image_path')
                    ->label('Image Path')
                    ->placeholder('/uploads/lombok-signature.jpg'),
            ])->columns(2),

            Forms\Components\Section::make('Pricing')->schema([
                Forms\Components\TextInput::make('price_per_pax')
                    ->label('Price per Pax (IDR)')
                    ->numeric()->prefix('Rp')
                    ->helperText('Set to 0 for "Request Quote"'),
                Forms\Components\TextInput::make('price_label')
                    ->label('Price Label (if 0)')
                    ->placeholder('Request Quote'),
                Forms\Components\TextInput::make('min_pax')
                    ->label('Min Pax')->numeric()->default(2),
            ])->columns(3),

            Forms\Components\Section::make('Flags')->schema([
                Forms\Components\Toggle::make('is_active')->default(true),
                Forms\Components\Toggle::make('is_long_stay'),
                Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
            ])->columns(3),

            Forms\Components\Section::make('Package Details')->schema([
                Forms\Components\Repeater::make('includes')
                    ->label('What\'s Included')
                    ->schema([
                        Forms\Components\TextInput::make('item')->required(),
                    ])->simple(Forms\Components\TextInput::make('item'))
                    ->defaultItems(0)->reorderable(),

                Forms\Components\Repeater::make('excludes')
                    ->label('Not Included')
                    ->schema([
                        Forms\Components\TextInput::make('item')->required(),
                    ])->simple(Forms\Components\TextInput::make('item'))
                    ->defaultItems(0)->reorderable(),
            ])->columns(2),

            Forms\Components\Section::make('Itinerary')->schema([
                Forms\Components\Repeater::make('itinerary')
                    ->label('Day-by-Day Itinerary')
                    ->schema([
                        Forms\Components\TextInput::make('day')
                            ->required()->placeholder('Day 1'),
                        Forms\Components\TextInput::make('title')
                            ->required()->placeholder('The Heritage Trail'),
                        Forms\Components\Repeater::make('items')
                            ->label('Activities')
                            ->simple(Forms\Components\TextInput::make('activity'))
                            ->defaultItems(1),
                    ])->columns(2)->defaultItems(0)->reorderable()->collapsible(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('package_code')
                    ->label('Code')->searchable()->badge()->color('primary'),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category')
                    ->badge()->color(fn ($state) => match($state) {
                        'honeymoon' => 'pink',
                        'adventure' => 'warning',
                        'culture'   => 'success',
                        'island'    => 'info',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('duration'),
                Tables\Columns\TextColumn::make('price_per_pax')
                    ->label('Price/pax')
                    ->formatStateUsing(fn ($state) => $state ? 'Rp ' . number_format($state, 0, ',', '.') : 'Quote')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')->boolean(),
                Tables\Columns\IconColumn::make('is_long_stay')
                    ->label('Long Stay')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')->sortable()->alignCenter(),
            ])
            ->reorderable('sort_order')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active'),
                Tables\Filters\TernaryFilter::make('is_long_stay')->label('Long Stay'),
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'culture' => 'Culture', 'island' => 'Island',
                        'adventure' => 'Adventure', 'honeymoon' => 'Honeymoon',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTourPackages::route('/'),
            'create' => Pages\CreateTourPackage::route('/create'),
            'edit'   => Pages\EditTourPackage::route('/{record}/edit'),
        ];
    }
}
