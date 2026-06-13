<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_ref', 'payment_type', 'amount',
        'midtrans_order_id', 'midtrans_transaction_id', 'midtrans_status',
        'payment_method', 'snap_token', 'raw_notification', 'paid_at',
    ];

    protected $casts = [
        'amount'           => 'integer',
        'raw_notification' => 'array',
        'paid_at'          => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_ref', 'ref');
    }

    public function isPaid(): bool
    {
        return in_array($this->midtrans_status, ['settlement', 'capture']);
    }

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }
}
