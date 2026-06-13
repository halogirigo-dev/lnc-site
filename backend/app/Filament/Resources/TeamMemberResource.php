<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMemberResource\Pages;
use App\Models\TeamMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Team';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationGroup = 'Content';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->required(),
            Forms\Components\TextInput::make('role')->placeholder('Founder & Lead Guide'),
            Forms\Components\TextInput::make('specialization')
                ->placeholder('Mount Rinjani · Cultural Expeditions'),
            Forms\Components\TextInput::make('years_experience')->numeric(),
            Forms\Components\TextInput::make('origin')->placeholder('Senaru, North Lombok'),
            Forms\Components\TextInput::make('languages')
                ->placeholder('Indonesian, English, Basic French'),
            Forms\Components\Textarea::make('certifications')->rows(2),
            Forms\Components\Textarea::make('bio')->rows(4)->columnSpanFull(),
            Forms\Components\TextInput::make('image_path')->placeholder('/uploads/team/name.jpg'),
            Forms\Components\Toggle::make('is_active')->default(true),
            Forms\Components\TextInput::make('sort_order')->numeric()->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('role'),
                Tables\Columns\TextColumn::make('years_experience')
                    ->label('Years')->alignCenter(),
                Tables\Columns\TextColumn::make('origin'),
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
            'index'  => Pages\ListTeamMembers::route('/'),
            'create' => Pages\CreateTeamMember::route('/create'),
            'edit'   => Pages\EditTeamMember::route('/{record}/edit'),
        ];
    }
}
