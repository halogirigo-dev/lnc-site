<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TestimonialResource\Pages;
use App\Models\Testimonial;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-right';
    protected static ?string $navigationLabel = 'Testimonials';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Textarea::make('quote')
                ->required()->rows(4)->columnSpanFull(),
            Forms\Components\TextInput::make('guest_name')
                ->required()->placeholder('James & Emma Thornton'),
            Forms\Components\TextInput::make('guest_origin')
                ->placeholder('London, United Kingdom'),
            Forms\Components\TextInput::make('experience')
                ->placeholder('LNC-01 Lombok Signature · 3 Days'),
            Forms\Components\Select::make('rating')
                ->options([5 => '5 Stars', 4 => '4 Stars', 3 => '3 Stars'])
                ->default(5),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('guest_name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('guest_origin'),
                Tables\Columns\TextColumn::make('quote')->limit(60)->searchable(),
                Tables\Columns\TextColumn::make('experience')->limit(40),
                Tables\Columns\TextColumn::make('rating')
                    ->badge()->color('warning'),
                Tables\Columns\IconColumn::make('is_active')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('#'),
            ])
            ->reorderable('sort_order')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTestimonials::route('/'),
            'create' => Pages\CreateTestimonial::route('/create'),
            'edit'   => Pages\EditTestimonial::route('/{record}/edit'),
        ];
    }
}
