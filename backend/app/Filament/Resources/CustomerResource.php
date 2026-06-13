<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Filament\Resources\CustomerResource\RelationManagers;
use App\Models\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int    $navigationSort  = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Contact Information')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->required()->maxLength(200),
                    Forms\Components\TextInput::make('email')
                        ->email()->maxLength(200),
                    Forms\Components\TextInput::make('phone')
                        ->tel()->maxLength(50),
                    Forms\Components\TextInput::make('country')
                        ->maxLength(100),
                    Forms\Components\TextInput::make('nationality')
                        ->maxLength(100),
                    Forms\Components\Select::make('age_range')
                        ->options([
                            '18-25' => '18-25',
                            '26-35' => '26-35',
                            '36-45' => '36-45',
                            '46-55' => '46-55',
                            '55+'   => '55+',
                        ]),
                    Forms\Components\Select::make('source')
                        ->options([
                            'google'      => 'Google',
                            'instagram'   => 'Instagram',
                            'facebook'    => 'Facebook',
                            'tripadvisor' => 'TripAdvisor',
                            'referral'    => 'Referral',
                            'repeat'      => 'Repeat Guest',
                            'other'       => 'Other',
                        ]),
                ]),

            Forms\Components\Section::make('Admin Notes')
                ->schema([
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Internal Notes')
                        ->rows(4)
                        ->helperText('Not visible to the customer.'),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Profile')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('name')->weight('bold'),
                    Infolists\Components\TextEntry::make('email')
                        ->icon('heroicon-o-envelope')
                        ->copyable(),
                    Infolists\Components\TextEntry::make('phone')
                        ->icon('heroicon-o-phone')
                        ->copyable(),
                    Infolists\Components\TextEntry::make('country'),
                    Infolists\Components\TextEntry::make('nationality'),
                    Infolists\Components\TextEntry::make('age_range')->label('Age Range'),
                    Infolists\Components\TextEntry::make('source')
                        ->badge()
                        ->color('gray'),
                    Infolists\Components\TextEntry::make('last_booking_at')
                        ->label('Last Booking')
                        ->dateTime('d M Y'),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Customer Since')
                        ->dateTime('d M Y'),
                ]),

            Infolists\Components\Section::make('Admin Notes')
                ->collapsed()
                ->schema([
                    Infolists\Components\TextEntry::make('admin_notes')
                        ->label('')
                        ->placeholder('No notes recorded.')
                        ->prose(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()->sortable()->weight('semibold'),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()->copyable()
                    ->icon('heroicon-o-envelope'),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable()->copyable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('country')
                    ->badge()->color('gray')
                    ->searchable(),
                Tables\Columns\TextColumn::make('bookings_count')
                    ->label('Bookings')
                    ->counts('bookings')
                    ->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state === 0 => 'gray',
                        $state === 1 => 'info',
                        $state >= 2  => 'success',
                        default      => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('last_booking_at')
                    ->label('Last Booking')
                    ->date('d M Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('source')
                    ->badge()->color('gray')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('country')
                    ->options(fn () => Customer::query()
                        ->whereNotNull('country')
                        ->distinct()
                        ->orderBy('country')
                        ->pluck('country', 'country')
                        ->toArray()
                    ),
                Tables\Filters\SelectFilter::make('source')
                    ->options([
                        'google'      => 'Google',
                        'instagram'   => 'Instagram',
                        'facebook'    => 'Facebook',
                        'tripadvisor' => 'TripAdvisor',
                        'referral'    => 'Referral',
                        'repeat'      => 'Repeat Guest',
                        'other'       => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->defaultSort('last_booking_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\BookingsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'view'   => Pages\ViewCustomer::route('/{record}'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
