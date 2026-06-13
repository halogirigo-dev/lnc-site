<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingFieldLog extends Model
{
    // Append-only — no updated_at
    public $timestamps = false;

    protected $fillable = [
        'booking_ref',
        'field_name',
        'old_value',
        'new_value',
        'changed_by',
        'changed_by_id',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // Prevent application-level updates and deletes to preserve immutability
    public static function boot(): void
    {
        parent::boot();
        static::updating(fn () => false);
        static::deleting(fn () => false);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_ref', 'ref');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by_id');
    }

    public static function fields(): array
    {
        return [
            'total_amount', 'deposit_amount', 'balance_amount',
            'guests', 'dates', 'arrival_date', 'departure_date',
            'assigned_guide_id',
            'name', 'email', 'phone',
            'accommodation', 'accommodation_name',
            'dietary_requirements', 'emergency_contact_name', 'emergency_contact_phone',
            'admin_notes',
        ];
    }
}
