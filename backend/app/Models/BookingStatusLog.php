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
        'notes',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_ref', 'ref');
    }

    public static function record(
        string $bookingRef,
        ?string $fromStatus,
        string $toStatus,
        string $changedBy = 'system',
        ?string $notes = null
    ): self {
        return static::create([
            'booking_ref' => $bookingRef,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'changed_by'  => $changedBy,
            'notes'       => $notes,
        ]);
    }
}
