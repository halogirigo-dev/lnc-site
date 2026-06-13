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
        'guests', 'dates', 'flexibility', 'accommodation',
        'name', 'email', 'phone', 'country', 'nationality', 'age_range',
        'source', 'message', 'special', 'budget',
        'admin_notes', 'cancellation_reason',
        'contacted_at', 'quoted_at', 'confirmed_at', 'cancelled_at', 'completed_at',
    ];

    protected $casts = [
        'package_price_per_pax' => 'integer',
        'total_amount'          => 'integer',
        'deposit_amount'        => 'integer',
        'balance_amount'        => 'integer',
        'guests'                => 'integer',
        'contacted_at'          => 'datetime',
        'quoted_at'             => 'datetime',
        'confirmed_at'          => 'datetime',
        'cancelled_at'          => 'datetime',
        'completed_at'          => 'datetime',
    ];

    // ── Status lifecycle ───────────────────────────────────────────
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

    // Valid transitions from each status
    public static function allowedTransitions(): array
    {
        return [
            self::STATUS_NEW       => [self::STATUS_CONTACTED, self::STATUS_CANCELLED],
            self::STATUS_CONTACTED => [self::STATUS_QUOTED, self::STATUS_CANCELLED],
            self::STATUS_QUOTED    => [self::STATUS_CONFIRMED, self::STATUS_CANCELLED],
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

        BookingStatusLog::record($this->ref, $oldStatus, $newStatus, $changedBy, $notes);
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

    // ── Scopes ────────────────────────────────────────────────────
    public function scopeNew($query)      { return $query->where('status', self::STATUS_NEW); }
    public function scopeContacted($q)   { return $q->where('status', self::STATUS_CONTACTED); }
    public function scopeQuoted($q)      { return $q->where('status', self::STATUS_QUOTED); }
    public function scopeConfirmed($q)   { return $q->where('status', self::STATUS_CONFIRMED); }
    public function scopeActive($q)
    {
        return $q->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_COMPLETED]);
    }

    // ── Helpers ───────────────────────────────────────────────────
    public function getStatusLabelAttribute(): string
    {
        return static::statuses()[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusColorAttribute(): string
    {
        return static::statusColors()[$this->status] ?? 'gray';
    }

    public function hasPrice(): bool
    {
        return $this->total_amount > 0;
    }

    public function getFormattedTotalAttribute(): string
    {
        return $this->total_amount ? 'Rp ' . number_format($this->total_amount, 0, ',', '.') : '—';
    }

    public function getFormattedDepositAttribute(): string
    {
        return $this->deposit_amount ? 'Rp ' . number_format($this->deposit_amount, 0, ',', '.') : '—';
    }

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
