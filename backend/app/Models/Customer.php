<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country',
        'nationality',
        'age_range',
        'source',
        'admin_notes',
        'last_booking_at',
    ];

    protected $casts = [
        'last_booking_at' => 'datetime',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class)->orderBy('created_at', 'desc');
    }

    public function activeBookings(): HasMany
    {
        return $this->hasMany(Booking::class)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->orderBy('created_at', 'desc');
    }

    public function getBookingCountAttribute(): int
    {
        return $this->bookings()->count();
    }

    // Upsert by email — used by PHP frontend and API
    public static function upsertFromBooking(array $data): self
    {
        $email = $data['email'] ?? null;

        if ($email) {
            $customer = static::firstOrNew(['email' => $email]);
        } else {
            $customer = new static();
        }

        $customer->fill([
            'name'        => $data['name']        ?? $customer->name,
            'phone'       => $data['phone']        ?? $customer->phone,
            'country'     => $data['country']      ?? $customer->country,
            'nationality' => $data['nationality']  ?? $customer->nationality,
            'age_range'   => $data['age_range']    ?? $customer->age_range,
            'source'      => $data['source']       ?? $customer->source,
            'last_booking_at' => now(),
        ]);

        $customer->save();

        return $customer;
    }
}
