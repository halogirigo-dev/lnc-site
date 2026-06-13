<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingStatusLog extends Model
{
    protected $fillable = [
        'booking_ref',
        'from_status',
        'to_status',
        'changed_by',
        'changed_by_id',
        'ip_address',
        'notes',
    ];

    // Audit logs are append-only — prevent updates
    public static function boot(): void
    {
        parent::boot();
        static::updating(fn () => false);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_ref', 'ref');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'changed_by_id');
    }

    public static function record(
        string $bookingRef,
        ?string $fromStatus,
        string $toStatus,
        string $changedBy = 'system',
        ?string $notes = null,
        ?int $changedById = null,
        ?string $ipAddress = null
    ): self {
        return static::create([
            'booking_ref'    => $bookingRef,
            'from_status'    => $fromStatus,
            'to_status'      => $toStatus,
            'changed_by'     => $changedBy,
            'changed_by_id'  => $changedById,
            'ip_address'     => $ipAddress,
            'notes'          => $notes,
        ]);
    }
}
