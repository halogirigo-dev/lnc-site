<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'ref', 'customer_id', 'assigned_guide_id', 'status',
        'package_id', 'package_title', 'package_duration', 'package_price_per_pax',
        'total_amount', 'deposit_amount', 'balance_amount',
        'guests', 'group_type', 'trip_purpose',
        'dates', 'arrival_date', 'departure_date',
        'flexibility', 'accommodation', 'accommodation_name', 'pickup_location',
        'arrival_flight', 'arrival_time', 'departure_flight', 'departure_time',
        'name', 'email', 'phone', 'country', 'nationality', 'age_range', 'source',
        'message', 'special', 'dietary_requirements', 'transport_requirements', 'budget',
        'emergency_contact_name', 'emergency_contact_phone',
        'admin_notes', 'cancellation_reason',
        'contacted_at', 'quoted_at', 'confirmed_at', 'cancelled_at', 'completed_at',
    ];

    protected $casts = [
        'package_price_per_pax' => 'integer',
        'total_amount'          => 'integer',
        'deposit_amount'        => 'integer',
        'balance_amount'        => 'integer',
        'guests'                => 'integer',
        'arrival_date'          => 'date',
        'departure_date'        => 'date',
        'contacted_at'          => 'datetime',
        'quoted_at'             => 'datetime',
        'confirmed_at'          => 'datetime',
        'cancelled_at'          => 'datetime',
        'completed_at'          => 'datetime',
    ];

    // ── Status constants ───────────────────────────────────────────
    const STATUS_NEW       = 'new';
    const STATUS_CONTACTED = 'contacted';
    const STATUS_QUOTED    = 'quoted';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    public static function statuses(): array
    {
        return [
            self::STATUS_NEW       => 'New',
            self::STATUS_CONTACTED => 'Contacted',
            self::STATUS_QUOTED    => 'Quoted',
            self::STATUS_CONFIRMED => 'Confirmed',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_COMPLETED => 'Completed',
        ];
    }

    public static function statusColors(): array
    {
        return [
            self::STATUS_NEW       => 'warning',
            self::STATUS_CONTACTED => 'info',
            self::STATUS_QUOTED    => 'primary',
            self::STATUS_CONFIRMED => 'success',
            self::STATUS_CANCELLED => 'danger',
            self::STATUS_COMPLETED => 'gray',
        ];
    }

    public static function groupTypes(): array
    {
        return [
            'solo'      => 'Solo Traveller',
            'couple'    => 'Couple',
            'family'    => 'Family',
            'friends'   => 'Friends Group',
            'corporate' => 'Corporate',
            'other'     => 'Other',
        ];
    }

    public static function tripPurposes(): array
    {
        return [
            'leisure'     => 'Leisure',
            'honeymoon'   => 'Honeymoon',
            'anniversary' => 'Anniversary',
            'birthday'    => 'Birthday',
            'corporate'   => 'Corporate Retreat',
            'bucket_list' => 'Bucket List',
            'other'       => 'Other',
        ];
    }

    // ── State machine ──────────────────────────────────────────────
    public static function allowedTransitions(): array
    {
        return [
            self::STATUS_NEW       => [self::STATUS_CONTACTED, self::STATUS_CANCELLED],
            self::STATUS_CONTACTED => [self::STATUS_QUOTED, self::STATUS_CANCELLED],
            // re-quote path: quoted → contacted (for negotiation rounds)
            self::STATUS_QUOTED    => [self::STATUS_CONFIRMED, self::STATUS_CONTACTED, self::STATUS_CANCELLED],
            self::STATUS_CONFIRMED => [self::STATUS_COMPLETED, self::STATUS_CANCELLED],
            self::STATUS_CANCELLED => [],
            self::STATUS_COMPLETED => [],
        ];
    }

    public function canTransitionTo(string $newStatus): bool
    {
        return in_array($newStatus, static::allowedTransitions()[$this->status] ?? []);
    }

    // ── Core lifecycle method ──────────────────────────────────────
    public function transitionTo(string $newStatus, ?string $changedBy = null, ?string $notes = null): void
    {
        // Enforce state machine
        if (!$this->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition booking {$this->ref} from '{$this->status}' to '{$newStatus}'."
            );
        }

        // Guide required before confirmation
        if ($newStatus === self::STATUS_CONFIRMED && !$this->assigned_guide_id) {
            throw new \RuntimeException(
                "A guide must be assigned to booking {$this->ref} before it can be confirmed."
            );
        }

        $changedBy ??= Auth::user()?->email ?? 'system';
        $oldStatus   = $this->status;

        $this->status = $newStatus;

        match ($newStatus) {
            self::STATUS_CONTACTED => $this->contacted_at = now(),
            self::STATUS_QUOTED    => $this->quoted_at    = now(),
            self::STATUS_CONFIRMED => $this->confirmed_at = now(),
            self::STATUS_CANCELLED => $this->cancelled_at = now(),
            self::STATUS_COMPLETED => $this->completed_at = now(),
            default                => null,
        };

        $this->save();

        BookingStatusLog::record(
            bookingRef:   $this->ref,
            fromStatus:   $oldStatus,
            toStatus:     $newStatus,
            changedBy:    $changedBy,
            notes:        $notes,
            changedById:  Auth::id(),
            ipAddress:    request()->ip(),
        );
    }

    // ── Operational helpers ────────────────────────────────────────
    public function isActiveTour(): bool
    {
        return $this->status === self::STATUS_CONFIRMED
            && $this->arrival_date !== null
            && $this->arrival_date->isPast()
            && ($this->departure_date === null || $this->departure_date->isFuture());
    }

    public function requiresEmergencyContact(): bool
    {
        $trekPackages = ['LNC-R3D', 'LNC-R4D', 'LNC-R5D', 'LNC-ADV'];
        return in_array($this->package_id, $trekPackages);
    }

    public function hasEmergencyContact(): bool
    {
        return !empty($this->emergency_contact_name) && !empty($this->emergency_contact_phone);
    }

    public function daysUntilArrival(): ?int
    {
        if (!$this->arrival_date) return null;
        return (int) now()->startOfDay()->diffInDays($this->arrival_date->startOfDay(), false);
    }

    public function nightsCount(): ?int
    {
        if (!$this->arrival_date || !$this->departure_date) return null;
        return (int) $this->arrival_date->diffInDays($this->departure_date);
    }

    // ── Relationships ──────────────────────────────────────────────
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignedGuide(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'assigned_guide_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'booking_ref', 'ref')
                    ->orderBy('created_at', 'desc');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(BookingStatusLog::class, 'booking_ref', 'ref')
                    ->orderBy('created_at', 'asc');
    }

    public function fieldLogs(): HasMany
    {
        return $this->hasMany(BookingFieldLog::class, 'booking_ref', 'ref')
                    ->orderBy('created_at', 'desc');
    }

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeActive($q)
    {
        return $q->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_COMPLETED]);
    }

    public function scopeArrivingToday($q)
    {
        return $q->whereDate('arrival_date', today())->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeArrivingTomorrow($q)
    {
        return $q->whereDate('arrival_date', today()->addDay())->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeActiveTours($q)
    {
        return $q->where('status', self::STATUS_CONFIRMED)
                 ->whereDate('arrival_date', '<=', today())
                 ->where(fn ($q) => $q->whereNull('departure_date')->orWhereDate('departure_date', '>=', today()));
    }

    // ── Formatters ────────────────────────────────────────────────
    public function getFormattedTotalAttribute(): string
    {
        return $this->total_amount ? 'Rp ' . number_format($this->total_amount, 0, ',', '.') : '—';
    }

    public function getFormattedDepositAttribute(): string
    {
        return $this->deposit_amount ? 'Rp ' . number_format($this->deposit_amount, 0, ',', '.') : '—';
    }

    public function hasPrice(): bool
    {
        return $this->total_amount > 0;
    }

    // ── Ref generation ────────────────────────────────────────────
    public static function generateRef(): string
    {
        $year   = now()->year;
        $prefix = "LNC-{$year}-";
        $n      = static::where('ref', 'like', $prefix . '%')->count();
        do {
            $n++;
            $ref = $prefix . str_pad($n, 5, '0', STR_PAD_LEFT);
        } while (static::where('ref', $ref)->exists());

        return $ref;
    }
}
