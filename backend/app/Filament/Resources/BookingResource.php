<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Models\Booking;
use App\Models\Invoice;
use App\Models\TeamMember;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Bookings';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Operations';

    // ── FORM (Edit page) ──────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Booking Reference')
                ->schema([
                    Forms\Components\TextInput::make('ref')
                        ->disabled()->dehydrated(false)->columnSpan(1),
                    Forms\Components\TextInput::make('status')
                        ->disabled()->dehydrated(false)->columnSpan(1),
                ])->columns(2),

            Forms\Components\Section::make('Operations')
                ->description('Admin-managed fields — editable at any stage')
                ->schema([
                    Forms\Components\Select::make('assigned_guide_id')
                        ->label('Assigned Guide')
                        ->options(fn () => TeamMember::where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id'))
                        ->searchable()
                        ->nullable(),
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Internal Notes')
                        ->rows(4)
                        ->placeholder('Internal notes only — not visible to guest.'),
                ])->columns(1),

            Forms\Components\Section::make('Pricing')
                ->description('Admin can adjust quote pricing')
                ->schema([
                    Forms\Components\TextInput::make('total_amount')
                        ->label('Total (IDR)')
                        ->numeric()->prefix('Rp'),
                    Forms\Components\TextInput::make('deposit_amount')
                        ->label('Deposit 30% (IDR)')
                        ->numeric()->prefix('Rp'),
                    Forms\Components\TextInput::make('balance_amount')
                        ->label('Balance 70% (IDR)')
                        ->numeric()->prefix('Rp'),
                ])->columns(3),

            Forms\Components\Section::make('Journey Details')
                ->description('Refine travel details')
                ->schema([
                    Forms\Components\TextInput::make('package_id')->label('Package Code'),
                    Forms\Components\TextInput::make('package_title')->label('Package Title'),
                    Forms\Components\TextInput::make('package_duration')->label('Duration'),
                    Forms\Components\TextInput::make('guests')->numeric()->label('Guests'),
                    Forms\Components\TextInput::make('dates')->label('Travel Dates'),
                    Forms\Components\TextInput::make('accommodation')->label('Accommodation'),
                ])->columns(3),

            Forms\Components\Section::make('Guest Info')
                ->description('Guest-submitted — edit with care')
                ->schema([
                    Forms\Components\TextInput::make('name'),
                    Forms\Components\TextInput::make('email')->email(),
                    Forms\Components\TextInput::make('phone'),
                    Forms\Components\TextInput::make('country'),
                    Forms\Components\TextInput::make('nationality'),
                    Forms\Components\TextInput::make('source'),
                ])->columns(3)->collapsed(),

            Forms\Components\Section::make('Guest Vision')
                ->schema([
                    Forms\Components\Textarea::make('message')->rows(4)->label('Journey Request'),
                    Forms\Components\Textarea::make('special')->rows(3)->label('Special Requirements'),
                    Forms\Components\TextInput::make('budget'),
                ])->collapsed(),
        ]);
    }

    // ── TABLE (List page) ─────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ref')
                    ->searchable()->copyable()->fontFamily('mono')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Guest')
                    ->searchable()->sortable()
                    ->description(fn (Booking $r) => $r->email),
                Tables\Columns\TextColumn::make('package_id')
                    ->label('Package')
                    ->badge()->color('info'),
                Tables\Columns\TextColumn::make('guests')
                    ->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('dates')
                    ->label('Travel Dates')
                    ->limit(22)->placeholder('—'),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($state) => $state
                        ? 'Rp ' . number_format($state, 0, ',', '.')
                        : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => Booking::statusColors()[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => Booking::statuses()[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime('d M Y')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Booking::statuses())
                    ->label('Status'),
                Tables\Filters\Filter::make('has_price')
                    ->label('Has Pricing')
                    ->query(fn (Builder $query) => $query->where('total_amount', '>', 0)),
                Tables\Filters\Filter::make('unassigned')
                    ->label('No Guide Assigned')
                    ->query(fn (Builder $query) => $query->whereNull('assigned_guide_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label(''),

                // Contact action (new → contacted)
                Tables\Actions\Action::make('contact')
                    ->label('Contact')
                    ->icon('heroicon-o-phone')
                    ->color('info')
                    ->visible(fn (Booking $r) => $r->status === Booking::STATUS_NEW)
                    ->requiresConfirmation()
                    ->modalHeading('Mark as Contacted')
                    ->modalDescription('Record that you\'ve reached out to this guest.')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notes (optional)')
                            ->placeholder('e.g. Sent WhatsApp introduction')
                            ->rows(3),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $record->transitionTo(
                            Booking::STATUS_CONTACTED,
                            Auth::user()?->email ?? 'admin',
                            $data['notes'] ?? null
                        );
                        Notification::make()->title('Marked as Contacted')->success()->send();
                    }),

                // Quote action (contacted → quoted)
                Tables\Actions\Action::make('send_quote')
                    ->label('Send Quote')
                    ->icon('heroicon-o-document-text')
                    ->color('primary')
                    ->visible(fn (Booking $r) => $r->status === Booking::STATUS_CONTACTED)
                    ->form([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Quoted Total (IDR)')
                            ->numeric()->prefix('Rp')
                            ->default(fn (Booking $r) => $r->total_amount ?: null)
                            ->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Quote Notes')
                            ->placeholder('e.g. Sent proposal via email, awaiting response')
                            ->rows(3),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $total = (int) $data['total_amount'];
                        Invoice::createQuote($record, $total);
                        $record->transitionTo(
                            Booking::STATUS_QUOTED,
                            Auth::user()?->email ?? 'admin',
                            $data['notes'] ?? null
                        );
                        Notification::make()->title('Quote created and status updated')->success()->send();
                    }),

                // Confirm action (quoted → confirmed)
                Tables\Actions\Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (Booking $r) => $r->status === Booking::STATUS_QUOTED)
                    ->requiresConfirmation()
                    ->modalHeading('Confirm Booking')
                    ->modalDescription('Guest has agreed to proceed. Mark booking as confirmed.')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Confirmation Notes')
                            ->placeholder('e.g. Guest accepted quote via email on 14 Jun')
                            ->rows(3),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $record->transitionTo(
                            Booking::STATUS_CONFIRMED,
                            Auth::user()?->email ?? 'admin',
                            $data['notes'] ?? null
                        );
                        Notification::make()->title('Booking Confirmed')->success()->send();
                    }),

                // Complete action (confirmed → completed)
                Tables\Actions\Action::make('complete')
                    ->label('Complete')
                    ->icon('heroicon-o-flag')
                    ->color('gray')
                    ->visible(fn (Booking $r) => $r->status === Booking::STATUS_CONFIRMED)
                    ->requiresConfirmation()
                    ->modalHeading('Mark Journey Complete')
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Completion Notes')
                            ->placeholder('e.g. Journey completed on 20 Aug. Guest departed from LOP.')
                            ->rows(3),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $record->transitionTo(
                            Booking::STATUS_COMPLETED,
                            Auth::user()?->email ?? 'admin',
                            $data['notes'] ?? null
                        );
                        Notification::make()->title('Journey Marked Complete')->success()->send();
                    }),

                // Cancel action (any active status → cancelled)
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (Booking $r) => !in_array($r->status, [
                        Booking::STATUS_CANCELLED, Booking::STATUS_COMPLETED,
                    ]))
                    ->form([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Cancellation Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $record->cancellation_reason = $data['cancellation_reason'];
                        $record->save();
                        $record->transitionTo(
                            Booking::STATUS_CANCELLED,
                            Auth::user()?->email ?? 'admin',
                            $data['cancellation_reason']
                        );
                        Notification::make()->title('Booking Cancelled')->warning()->send();
                    }),

                Tables\Actions\EditAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ── INFOLIST (View page) ───────────────────────────────────────
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            // Header: status + key details
            Infolists\Components\Section::make('Booking Overview')
                ->schema([
                    Infolists\Components\TextEntry::make('ref')
                        ->label('Reference')->copyable()->weight('bold')->fontFamily('mono'),
                    Infolists\Components\TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn (string $state): string => Booking::statusColors()[$state] ?? 'gray')
                        ->formatStateUsing(fn (string $state): string => Booking::statuses()[$state] ?? ucfirst($state)),
                    Infolists\Components\TextEntry::make('created_at')
                        ->label('Received')->dateTime('d M Y H:i'),
                    Infolists\Components\TextEntry::make('assignedGuide.name')
                        ->label('Guide')->default('Not assigned'),
                ])->columns(4),

            // Two-column layout: journey left, guest right
            Infolists\Components\Grid::make(2)->schema([
                Infolists\Components\Section::make('Journey')
                    ->schema([
                        Infolists\Components\TextEntry::make('package_id')
                            ->label('Package')->badge()->color('info'),
                        Infolists\Components\TextEntry::make('package_title')
                            ->label('Title'),
                        Infolists\Components\TextEntry::make('package_duration')
                            ->label('Duration'),
                        Infolists\Components\TextEntry::make('guests')
                            ->label('Guests'),
                        Infolists\Components\TextEntry::make('dates')
                            ->label('Travel Dates')->placeholder('Not specified'),
                        Infolists\Components\TextEntry::make('flexibility')
                            ->label('Flexibility')->placeholder('—'),
                        Infolists\Components\TextEntry::make('accommodation')
                            ->label('Accommodation')->placeholder('—'),
                    ])->columns(2),

                Infolists\Components\Section::make('Guest')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')->label('Name'),
                        Infolists\Components\TextEntry::make('email')
                            ->label('Email')->copyable(),
                        Infolists\Components\TextEntry::make('phone')
                            ->label('Phone')->copyable()->placeholder('—'),
                        Infolists\Components\TextEntry::make('country')->label('Country')->placeholder('—'),
                        Infolists\Components\TextEntry::make('nationality')->label('Nationality')->placeholder('—'),
                        Infolists\Components\TextEntry::make('source')->label('Source')->placeholder('—'),
                    ])->columns(2),
            ]),

            // Pricing
            Infolists\Components\Section::make('Pricing')
                ->schema([
                    Infolists\Components\TextEntry::make('total_amount')
                        ->label('Total')
                        ->formatStateUsing(fn ($s) => $s ? 'Rp ' . number_format($s, 0, ',', '.') : 'TBC'),
                    Infolists\Components\TextEntry::make('deposit_amount')
                        ->label('Deposit (30%)')
                        ->formatStateUsing(fn ($s) => $s ? 'Rp ' . number_format($s, 0, ',', '.') : '—'),
                    Infolists\Components\TextEntry::make('balance_amount')
                        ->label('Balance (70%)')
                        ->formatStateUsing(fn ($s) => $s ? 'Rp ' . number_format($s, 0, ',', '.') : '—'),
                ])->columns(3),

            // Guest vision
            Infolists\Components\Section::make('Guest Vision')
                ->schema([
                    Infolists\Components\TextEntry::make('message')
                        ->label('Journey Request')->markdown()->columnSpanFull()->placeholder('—'),
                    Infolists\Components\TextEntry::make('special')
                        ->label('Special Requirements')->columnSpanFull()->placeholder('—'),
                    Infolists\Components\TextEntry::make('budget')
                        ->label('Budget Range')->placeholder('Not specified'),
                ])->columns(2)->collapsed(),

            // Admin section
            Infolists\Components\Section::make('Admin Notes')
                ->schema([
                    Infolists\Components\TextEntry::make('admin_notes')
                        ->label('Internal Notes')->columnSpanFull()->placeholder('No notes yet.'),
                    Infolists\Components\TextEntry::make('cancellation_reason')
                        ->label('Cancellation Reason')->columnSpanFull()->placeholder('—')
                        ->visible(fn (Booking $r) => $r->status === Booking::STATUS_CANCELLED),
                ]),

            // Status Timeline
            Infolists\Components\Section::make('Status Timeline')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('statusLogs')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('from_status')
                                ->label('From')
                                ->formatStateUsing(fn ($s) => $s ? (Booking::statuses()[$s] ?? ucfirst($s)) : 'Created')
                                ->badge()->color(fn ($s) => $s ? (Booking::statusColors()[$s] ?? 'gray') : 'gray'),
                            Infolists\Components\TextEntry::make('to_status')
                                ->label('To')
                                ->formatStateUsing(fn ($s) => Booking::statuses()[$s] ?? ucfirst($s))
                                ->badge()->color(fn ($s) => Booking::statusColors()[$s] ?? 'gray'),
                            Infolists\Components\TextEntry::make('changed_by')
                                ->label('By'),
                            Infolists\Components\TextEntry::make('created_at')
                                ->label('At')->dateTime('d M Y H:i'),
                            Infolists\Components\TextEntry::make('notes')
                                ->label('Notes')->columnSpanFull()->placeholder('—'),
                        ])
                        ->columns(4)
                        ->columnSpanFull(),
                ]),

            // Invoices
            Infolists\Components\Section::make('Invoices')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('invoices')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('invoice_number')
                                ->label('Number')->copyable()->fontFamily('mono'),
                            Infolists\Components\TextEntry::make('type')
                                ->label('Type')
                                ->formatStateUsing(fn ($s) => Invoice::types()[$s] ?? ucfirst($s))
                                ->badge()->color('info'),
                            Infolists\Components\TextEntry::make('status')
                                ->label('Status')
                                ->badge()
                                ->color(fn ($s) => Invoice::statusColors()[$s] ?? 'gray'),
                            Infolists\Components\TextEntry::make('total_amount')
                                ->label('Total')
                                ->formatStateUsing(fn ($s) => 'Rp ' . number_format($s, 0, ',', '.')),
                            Infolists\Components\TextEntry::make('issued_at')
                                ->label('Issued')->date('d M Y'),
                            Infolists\Components\TextEntry::make('valid_until')
                                ->label('Valid Until')->date('d M Y'),
                        ])
                        ->columns(3)
                        ->columnSpanFull(),
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
            'index'  => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'view'   => Pages\ViewBooking::route('/{record}'),
            'edit'   => Pages\EditBooking::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) Booking::whereIn('status', [Booking::STATUS_NEW, Booking::STATUS_CONTACTED])->count() ?: null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }
}
