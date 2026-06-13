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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;
    protected static ?string $navigationIcon  = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Bookings';
    protected static ?int    $navigationSort  = 1;
    protected static ?string $navigationGroup = 'Operations';

    // ── FORM ──────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Booking Reference')
                ->schema([
                    Forms\Components\TextInput::make('ref')->disabled()->dehydrated(false),
                    Forms\Components\TextInput::make('status')->disabled()->dehydrated(false),
                ])->columns(2),

            Forms\Components\Section::make('Operations')
                ->description('Admin-managed fields — editable at any stage')
                ->schema([
                    Forms\Components\Select::make('assigned_guide_id')
                        ->label('Assigned Guide')
                        ->options(fn () => TeamMember::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                        ->searchable()->nullable(),
                    Forms\Components\Select::make('group_type')
                        ->options(Booking::groupTypes())
                        ->label('Group Type'),
                    Forms\Components\Select::make('trip_purpose')
                        ->options(Booking::tripPurposes())
                        ->label('Trip Purpose'),
                    Forms\Components\Textarea::make('admin_notes')
                        ->label('Internal Notes')->rows(3)->columnSpanFull()
                        ->placeholder('Internal notes only — not visible to guest.'),
                ])->columns(3),

            Forms\Components\Section::make('Journey Dates')
                ->description('Structured dates — required for operations dashboard')
                ->schema([
                    Forms\Components\TextInput::make('dates')
                        ->label('Guest-Stated Dates')->placeholder('e.g. Aug 10–20, 2026'),
                    Forms\Components\DatePicker::make('arrival_date')
                        ->label('Arrival Date (confirmed)')->native(false),
                    Forms\Components\DatePicker::make('departure_date')
                        ->label('Departure Date (confirmed)')->native(false),
                    Forms\Components\TextInput::make('arrival_flight')
                        ->label('Arrival Flight')->placeholder('e.g. GA 400'),
                    Forms\Components\TextInput::make('arrival_time')
                        ->label('Arrival Time')->placeholder('e.g. 14:30 WITA'),
                    Forms\Components\TextInput::make('departure_flight')
                        ->label('Departure Flight')->placeholder('e.g. GA 401'),
                    Forms\Components\TextInput::make('departure_time')
                        ->label('Departure Time')->placeholder('e.g. 09:15 WITA'),
                ])->columns(3),

            Forms\Components\Section::make('Accommodation & Logistics')
                ->schema([
                    Forms\Components\TextInput::make('accommodation')
                        ->label('Tier Preference'),
                    Forms\Components\TextInput::make('accommodation_name')
                        ->label('Specific Property')->placeholder('e.g. Qunci Villas, Senggigi'),
                    Forms\Components\Textarea::make('pickup_location')
                        ->label('Pickup Location')->rows(2)->placeholder('e.g. LOP Airport / Qunci Villas, Senggigi'),
                    Forms\Components\Textarea::make('transport_requirements')
                        ->label('Transport Requirements')->rows(2)
                        ->placeholder('e.g. Wheelchair-accessible vehicle, private car throughout'),
                ])->columns(2),

            Forms\Components\Section::make('Pricing')
                ->schema([
                    Forms\Components\TextInput::make('total_amount')->label('Total (IDR)')->numeric()->prefix('Rp'),
                    Forms\Components\TextInput::make('deposit_amount')->label('Deposit 30% (IDR)')->numeric()->prefix('Rp'),
                    Forms\Components\TextInput::make('balance_amount')->label('Balance 70% (IDR)')->numeric()->prefix('Rp'),
                ])->columns(3),

            Forms\Components\Section::make('Emergency Contact')
                ->description('Required for trekking packages before departure')
                ->schema([
                    Forms\Components\TextInput::make('emergency_contact_name')
                        ->label('Emergency Contact Name'),
                    Forms\Components\TextInput::make('emergency_contact_phone')
                        ->label('Emergency Contact Phone')->tel(),
                ])->columns(2),

            Forms\Components\Section::make('Guest Requirements')
                ->schema([
                    Forms\Components\Textarea::make('dietary_requirements')
                        ->label('Dietary Requirements')->rows(3)
                        ->placeholder('e.g. Strict vegan. Peanut allergy — SEVERE. Halal only.')
                        ->columnSpanFull(),
                ]),

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
                    Forms\Components\Textarea::make('special')->rows(3)->label('Special Requests'),
                    Forms\Components\TextInput::make('budget'),
                ])->collapsed(),
        ]);
    }

    // ── TABLE ─────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ref')
                    ->searchable()->copyable()->fontFamily('mono')->weight('bold'),
                Tables\Columns\TextColumn::make('name')
                    ->label('Guest')->searchable()->sortable()
                    ->description(fn (Booking $r) => $r->email),
                Tables\Columns\TextColumn::make('package_id')
                    ->label('Package')->badge()->color('info')
                    ->description(fn (Booking $r) => $r->package_title ? mb_strimwidth($r->package_title, 0, 28, '…') : null),
                Tables\Columns\TextColumn::make('group_type')
                    ->label('Group')
                    ->formatStateUsing(fn ($s) => Booking::groupTypes()[$s] ?? ucfirst($s ?? ''))
                    ->badge()->color('gray')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('guests')
                    ->alignCenter()->sortable(),
                Tables\Columns\TextColumn::make('arrival_date')
                    ->label('Arrival')->date('d M Y')->sortable()
                    ->placeholder('TBC')
                    ->color(fn (Booking $r) => match(true) {
                        $r->arrival_date === null                      => null,
                        $r->arrival_date->isToday()                   => 'success',
                        $r->arrival_date->isTomorrow()                => 'warning',
                        $r->arrival_date->isPast()                    => 'gray',
                        $r->arrival_date->diffInDays(now()) <= 7      => 'warning',
                        default                                       => null,
                    }),
                Tables\Columns\TextColumn::make('departure_date')
                    ->label('Departure')->date('d M Y')->sortable()
                    ->placeholder('TBC')->toggleable(),
                Tables\Columns\TextColumn::make('assignedGuide.name')
                    ->label('Guide')
                    ->placeholder('—')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'danger')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->formatStateUsing(fn ($s) => $s ? 'Rp ' . number_format($s, 0, ',', '.') : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => Booking::statusColors()[$state] ?? 'gray')
                    ->formatStateUsing(fn (string $state): string => Booking::statuses()[$state] ?? ucfirst($state)),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')->dateTime('d M Y')->sortable()
                    ->color(fn (Booking $r) => match(true) {
                        $r->status !== Booking::STATUS_NEW => null,
                        now()->diffInHours($r->created_at) < 24 => 'success',
                        now()->diffInHours($r->created_at) < 48 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(Booking::statuses())->label('Status'),

                Tables\Filters\SelectFilter::make('assigned_guide_id')
                    ->label('Guide')
                    ->options(fn () => TeamMember::where('is_active', true)->orderBy('name')->pluck('name', 'id')),

                Tables\Filters\SelectFilter::make('group_type')
                    ->options(Booking::groupTypes())->label('Group Type'),

                Tables\Filters\SelectFilter::make('country')
                    ->label('Country')
                    ->options(fn () => Booking::whereNotNull('country')->distinct()
                        ->orderBy('country')->pluck('country', 'country')->toArray()),

                Tables\Filters\SelectFilter::make('package_id')
                    ->label('Package')
                    ->options(fn () => Booking::whereNotNull('package_id')->distinct()
                        ->orderBy('package_id')->pluck('package_id', 'package_id')->toArray()),

                Tables\Filters\Filter::make('arriving_today')
                    ->label('Arriving Today')
                    ->query(fn (Builder $q) => $q->whereDate('arrival_date', today())
                        ->where('status', Booking::STATUS_CONFIRMED)),

                Tables\Filters\Filter::make('arriving_this_week')
                    ->label('Arriving This Week')
                    ->query(fn (Builder $q) => $q->whereBetween('arrival_date', [today(), today()->addDays(7)])
                        ->where('status', Booking::STATUS_CONFIRMED)),

                Tables\Filters\Filter::make('active_tours')
                    ->label('Currently On Tour')
                    ->query(fn (Builder $q) => $q->activeTours()),

                Tables\Filters\Filter::make('received_this_week')
                    ->label('Received This Week')
                    ->query(fn (Builder $q) => $q->where('created_at', '>=', now()->startOfWeek())),

                Tables\Filters\Filter::make('has_price')
                    ->label('Has Pricing')
                    ->query(fn (Builder $q) => $q->where('total_amount', '>', 0)),

                Tables\Filters\Filter::make('unassigned')
                    ->label('No Guide Assigned')
                    ->query(fn (Builder $q) => $q->whereNull('assigned_guide_id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label(''),

                Tables\Actions\Action::make('contact')
                    ->label('Contact')->icon('heroicon-o-phone')->color('info')
                    ->visible(fn (Booking $r) => $r->status === Booking::STATUS_NEW)
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Contact Notes')->rows(3)
                            ->placeholder('e.g. Sent WhatsApp introduction'),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $record->transitionTo(Booking::STATUS_CONTACTED, Auth::user()?->email ?? 'admin', $data['notes'] ?? null);
                        Notification::make()->title('Marked as Contacted')->success()->send();
                    }),

                Tables\Actions\Action::make('send_quote')
                    ->label('Send Quote')->icon('heroicon-o-document-text')->color('primary')
                    ->visible(fn (Booking $r) => $r->status === Booking::STATUS_CONTACTED)
                    ->form([
                        Forms\Components\TextInput::make('total_amount')
                            ->label('Quoted Total (IDR)')->numeric()->prefix('Rp')
                            ->default(fn (Booking $r) => $r->total_amount ?: null)->required(),
                        Forms\Components\Textarea::make('notes')
                            ->label('Quote Notes')->rows(3)
                            ->placeholder('e.g. Sent full proposal PDF via email'),
                    ])
                    ->action(function (Booking $record, array $data) {
                        Invoice::createQuote($record, (int) $data['total_amount']);
                        $record->transitionTo(Booking::STATUS_QUOTED, Auth::user()?->email ?? 'admin', $data['notes'] ?? null);
                        Notification::make()->title('Quote created — status updated to Quoted')->success()->send();
                    }),

                Tables\Actions\Action::make('confirm')
                    ->label('Confirm')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (Booking $r) => $r->status === Booking::STATUS_QUOTED)
                    ->form([
                        Forms\Components\Select::make('assigned_guide_id')
                            ->label('Assign Guide')
                            ->options(fn () => TeamMember::where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->default(fn (Booking $r) => $r->assigned_guide_id)
                            ->required()
                            ->helperText('A guide must be assigned before confirming.'),
                        Forms\Components\Textarea::make('notes')
                            ->label('Confirmation Notes')->required()->rows(3)
                            ->placeholder('e.g. Guest accepted quote via WhatsApp on 14 Jun 2026'),
                    ])
                    ->action(function (Booking $record, array $data) {
                        if ($data['assigned_guide_id'] !== $record->assigned_guide_id) {
                            $record->assigned_guide_id = $data['assigned_guide_id'];
                        }
                        $record->transitionTo(Booking::STATUS_CONFIRMED, Auth::user()?->email ?? 'admin', $data['notes']);
                        Notification::make()->title('Booking Confirmed')->success()->send();
                    }),

                Tables\Actions\Action::make('re_quote')
                    ->label('Revise Quote')->icon('heroicon-o-arrow-uturn-left')->color('warning')
                    ->visible(fn (Booking $r) => $r->status === Booking::STATUS_QUOTED)
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Reason for Revision')->required()->rows(3)
                            ->placeholder('e.g. Guest requested fewer guests. Preparing revised pricing.'),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $record->transitionTo(Booking::STATUS_CONTACTED, Auth::user()?->email ?? 'admin', $data['notes']);
                        Notification::make()->title('Returned to Contacted — prepare revised quote')->warning()->send();
                    }),

                Tables\Actions\Action::make('complete')
                    ->label('Complete')->icon('heroicon-o-flag')->color('gray')
                    ->visible(fn (Booking $r) => $r->status === Booking::STATUS_CONFIRMED)
                    ->form([
                        Forms\Components\Textarea::make('notes')
                            ->label('Completion Notes')->rows(3)
                            ->placeholder('e.g. Journey completed on 20 Aug. Guest departed from LOP.'),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $record->transitionTo(Booking::STATUS_COMPLETED, Auth::user()?->email ?? 'admin', $data['notes'] ?? null);
                        Notification::make()->title('Journey Marked Complete')->success()->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (Booking $r) => !in_array($r->status, [Booking::STATUS_CANCELLED, Booking::STATUS_COMPLETED]))
                    ->form([
                        Forms\Components\Textarea::make('cancellation_reason')
                            ->label('Cancellation Reason')->required()->rows(3),
                    ])
                    ->action(function (Booking $record, array $data) {
                        $record->cancellation_reason = $data['cancellation_reason'];
                        $record->transitionTo(Booking::STATUS_CANCELLED, Auth::user()?->email ?? 'admin', $data['cancellation_reason']);
                        Notification::make()->title('Booking Cancelled')->warning()->send();
                    }),

                Tables\Actions\EditAction::make()->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_contacted')
                        ->label('Mark as Contacted')
                        ->icon('heroicon-o-phone')
                        ->color('info')
                        ->form([
                            Forms\Components\Textarea::make('notes')
                                ->label('Bulk Contact Notes')->rows(2)
                                ->placeholder('e.g. Sent WhatsApp introduction to all new bookings'),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->status === Booking::STATUS_NEW) {
                                    $record->transitionTo(Booking::STATUS_CONTACTED, Auth::user()?->email ?? 'admin', $data['notes'] ?? 'Bulk contact action');
                                    $count++;
                                }
                            }
                            Notification::make()->title("{$count} booking(s) marked as Contacted")->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\BulkAction::make('export_csv')
                        ->label('Export CSV')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->action(function (Collection $records) {
                            $filename = 'lnc-bookings-' . now()->format('Y-m-d-His') . '.csv';
                            return response()->streamDownload(function () use ($records) {
                                $handle = fopen('php://output', 'w');
                                fputcsv($handle, [
                                    'Ref', 'Status', 'Guest Name', 'Email', 'Phone',
                                    'Country', 'Package', 'Group Type', 'Guests',
                                    'Arrival Date', 'Departure Date',
                                    'Arrival Flight', 'Arrival Time',
                                    'Accommodation', 'Pickup Location',
                                    'Dietary Requirements', 'Emergency Contact', 'Emergency Phone',
                                    'Total IDR', 'Guide', 'Received At',
                                ]);
                                foreach ($records as $b) {
                                    fputcsv($handle, [
                                        $b->ref,
                                        $b->status,
                                        $b->name,
                                        $b->email,
                                        $b->phone,
                                        $b->country,
                                        $b->package_id,
                                        $b->group_type,
                                        $b->guests,
                                        $b->arrival_date?->format('Y-m-d'),
                                        $b->departure_date?->format('Y-m-d'),
                                        $b->arrival_flight,
                                        $b->arrival_time,
                                        $b->accommodation_name ?? $b->accommodation,
                                        $b->pickup_location,
                                        $b->dietary_requirements,
                                        $b->emergency_contact_name,
                                        $b->emergency_contact_phone,
                                        $b->total_amount,
                                        $b->assignedGuide?->name,
                                        $b->created_at?->format('Y-m-d H:i'),
                                    ]);
                                }
                                fclose($handle);
                            }, $filename, ['Content-Type' => 'text/csv']);
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ── INFOLIST ──────────────────────────────────────────────────
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Booking Overview')
                ->schema([
                    Infolists\Components\TextEntry::make('ref')
                        ->label('Reference')->copyable()->weight('bold')->fontFamily('mono'),
                    Infolists\Components\TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => Booking::statusColors()[$state] ?? 'gray')
                        ->formatStateUsing(fn (string $state): string => Booking::statuses()[$state] ?? ucfirst($state)),
                    Infolists\Components\TextEntry::make('created_at')->label('Received')->dateTime('d M Y H:i'),
                    Infolists\Components\TextEntry::make('assignedGuide.name')->label('Guide')->default('⚠ Not assigned')
                        ->color(fn (Booking $r) => $r->assigned_guide_id ? null : 'danger'),
                ])->columns(4),

            Infolists\Components\Grid::make(2)->schema([
                Infolists\Components\Section::make('Journey')
                    ->schema([
                        Infolists\Components\TextEntry::make('package_id')->label('Package')->badge()->color('info'),
                        Infolists\Components\TextEntry::make('package_title')->label('Title'),
                        Infolists\Components\TextEntry::make('package_duration')->label('Duration'),
                        Infolists\Components\TextEntry::make('guests')->label('Guests'),
                        Infolists\Components\TextEntry::make('group_type')->label('Group Type')
                            ->formatStateUsing(fn ($s) => Booking::groupTypes()[$s] ?? ucfirst($s ?? '—'))
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('trip_purpose')->label('Purpose')
                            ->formatStateUsing(fn ($s) => Booking::tripPurposes()[$s] ?? ucfirst($s ?? '—'))
                            ->placeholder('—'),
                        Infolists\Components\TextEntry::make('dates')->label('Guest-Stated Dates')->placeholder('—'),
                        Infolists\Components\TextEntry::make('arrival_date')->label('Arrival (confirmed)')->date('d M Y')->placeholder('TBC'),
                        Infolists\Components\TextEntry::make('departure_date')->label('Departure (confirmed)')->date('d M Y')->placeholder('TBC'),
                        Infolists\Components\TextEntry::make('arrival_flight')->label('Arrival Flight')->placeholder('—'),
                        Infolists\Components\TextEntry::make('arrival_time')->label('Arrival Time')->placeholder('—'),
                        Infolists\Components\TextEntry::make('departure_flight')->label('Dep. Flight')->placeholder('—'),
                    ])->columns(3),

                Infolists\Components\Section::make('Guest')
                    ->schema([
                        Infolists\Components\TextEntry::make('name')->label('Name'),
                        Infolists\Components\TextEntry::make('email')->label('Email')->copyable(),
                        Infolists\Components\TextEntry::make('phone')->label('Phone')->copyable()->placeholder('—'),
                        Infolists\Components\TextEntry::make('country')->label('Country')->placeholder('—'),
                        Infolists\Components\TextEntry::make('nationality')->label('Nationality')->placeholder('—'),
                        Infolists\Components\TextEntry::make('source')->label('Source')->placeholder('—'),
                        Infolists\Components\TextEntry::make('emergency_contact_name')
                            ->label('Emergency Contact')->placeholder('⚠ Not provided')
                            ->color(fn (Booking $r) => $r->emergency_contact_name ? null : 'warning'),
                        Infolists\Components\TextEntry::make('emergency_contact_phone')
                            ->label('Emergency Phone')->copyable()->placeholder('—'),
                    ])->columns(2),
            ]),

            Infolists\Components\Section::make('Logistics')
                ->schema([
                    Infolists\Components\TextEntry::make('accommodation')->label('Accommodation Tier')->placeholder('—'),
                    Infolists\Components\TextEntry::make('accommodation_name')->label('Property Name')->placeholder('—'),
                    Infolists\Components\TextEntry::make('pickup_location')->label('Pickup Location')->columnSpanFull()->placeholder('—'),
                    Infolists\Components\TextEntry::make('dietary_requirements')
                        ->label('Dietary Requirements')->columnSpanFull()->placeholder('None specified')
                        ->color(fn ($state) => $state ? 'warning' : null),
                    Infolists\Components\TextEntry::make('transport_requirements')->label('Transport')->columnSpanFull()->placeholder('—'),
                ])->columns(3),

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

            Infolists\Components\Section::make('Guest Vision')
                ->schema([
                    Infolists\Components\TextEntry::make('message')->label('Journey Request')->markdown()->columnSpanFull()->placeholder('—'),
                    Infolists\Components\TextEntry::make('special')->label('Special Requests')->columnSpanFull()->placeholder('—'),
                    Infolists\Components\TextEntry::make('budget')->label('Budget Range')->placeholder('Not specified'),
                ])->columns(2)->collapsed(),

            Infolists\Components\Section::make('Admin Notes')
                ->schema([
                    Infolists\Components\TextEntry::make('admin_notes')->label('Internal Notes')->columnSpanFull()->placeholder('No notes yet.'),
                    Infolists\Components\TextEntry::make('cancellation_reason')
                        ->label('Cancellation Reason')->columnSpanFull()->placeholder('—')
                        ->visible(fn (Booking $r) => $r->status === Booking::STATUS_CANCELLED),
                ]),

            Infolists\Components\Section::make('Status Timeline')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('statusLogs')->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('from_status')->label('From')
                                ->formatStateUsing(fn ($s) => $s ? (Booking::statuses()[$s] ?? ucfirst($s)) : 'Created')
                                ->badge()->color(fn ($s) => $s ? (Booking::statusColors()[$s] ?? 'gray') : 'gray'),
                            Infolists\Components\TextEntry::make('to_status')->label('To')
                                ->formatStateUsing(fn ($s) => Booking::statuses()[$s] ?? ucfirst($s))
                                ->badge()->color(fn ($s) => Booking::statusColors()[$s] ?? 'gray'),
                            Infolists\Components\TextEntry::make('changed_by')->label('By'),
                            Infolists\Components\TextEntry::make('created_at')->label('At')->dateTime('d M Y H:i'),
                            Infolists\Components\TextEntry::make('notes')->label('Notes')->columnSpanFull()->placeholder('—'),
                        ])
                        ->columns(4)->columnSpanFull(),
                ]),

            Infolists\Components\Section::make('Invoices')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('invoices')->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('invoice_number')->label('Number')->copyable()->fontFamily('mono'),
                            Infolists\Components\TextEntry::make('type')->label('Type')
                                ->formatStateUsing(fn ($s) => Invoice::types()[$s] ?? ucfirst($s))->badge()->color('info'),
                            Infolists\Components\TextEntry::make('status')->label('Status')
                                ->badge()->color(fn ($s) => Invoice::statusColors()[$s] ?? 'gray'),
                            Infolists\Components\TextEntry::make('total_amount')->label('Total')
                                ->formatStateUsing(fn ($s) => 'Rp ' . number_format($s, 0, ',', '.')),
                            Infolists\Components\TextEntry::make('issued_at')->label('Issued')->date('d M Y'),
                            Infolists\Components\TextEntry::make('due_deposit_at')->label('Deposit Due')->date('d M Y'),
                        ])
                        ->columns(3)->columnSpanFull(),
                ]),
        ]);
    }

    public static function getRelations(): array { return []; }

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
