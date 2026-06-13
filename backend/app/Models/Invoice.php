<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_ref', 'invoice_number', 'type', 'status',
        'total_amount', 'deposit_amount', 'balance_amount', 'deposit_pct',
        'issued_at', 'valid_until', 'due_deposit_at', 'due_balance_at',
        'sent_at', 'viewed_at', 'accepted_at', 'notes',
    ];

    protected $casts = [
        'total_amount'   => 'integer',
        'deposit_amount' => 'integer',
        'balance_amount' => 'integer',
        'deposit_pct'    => 'integer',
        'issued_at'      => 'date',
        'valid_until'    => 'date',
        'due_deposit_at' => 'date',
        'due_balance_at' => 'date',
        'sent_at'        => 'datetime',
        'viewed_at'      => 'datetime',
        'accepted_at'    => 'datetime',
    ];

    public static function types(): array
    {
        return [
            'proposal'       => 'Journey Proposal',
            'quote'          => 'Quote',
            'deposit_invoice'=> 'Deposit Invoice',
            'final_receipt'  => 'Final Receipt',
        ];
    }

    public static function statuses(): array
    {
        return [
            'draft'     => 'Draft',
            'sent'      => 'Sent',
            'viewed'    => 'Viewed',
            'accepted'  => 'Accepted',
            'cancelled' => 'Cancelled',
        ];
    }

    public static function statusColors(): array
    {
        return [
            'draft'     => 'gray',
            'sent'      => 'info',
            'viewed'    => 'warning',
            'accepted'  => 'success',
            'cancelled' => 'danger',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_ref', 'ref');
    }

    public static function createProposal(Booking $booking): self
    {
        return static::create([
            'booking_ref'    => $booking->ref,
            'invoice_number' => 'PRO-' . $booking->ref,
            'type'           => 'proposal',
            'status'         => 'draft',
            'total_amount'   => $booking->total_amount,
            'deposit_amount' => $booking->deposit_amount,
            'balance_amount' => $booking->balance_amount,
            'deposit_pct'    => 30,
            'issued_at'      => now()->toDateString(),
            'valid_until'    => now()->addDays(14)->toDateString(),
            'due_deposit_at' => now()->addDays(7)->toDateString(),
            'due_balance_at' => now()->addDays(30)->toDateString(),
        ]);
    }

    public static function createQuote(Booking $booking, int $totalAmount): self
    {
        $deposit = (int) round($totalAmount * 0.30 / 1000) * 1000;
        $balance = $totalAmount - $deposit;

        $invoice = static::create([
            'booking_ref'    => $booking->ref,
            'invoice_number' => 'QT-' . $booking->ref . '-' . now()->format('Ymd'),
            'type'           => 'quote',
            'status'         => 'draft',
            'total_amount'   => $totalAmount,
            'deposit_amount' => $deposit,
            'balance_amount' => $balance,
            'deposit_pct'    => 30,
            'issued_at'      => now()->toDateString(),
            'valid_until'    => now()->addDays(14)->toDateString(),
            'due_deposit_at' => now()->addDays(7)->toDateString(),
            'due_balance_at' => now()->addDays(30)->toDateString(),
        ]);

        // Update booking pricing to match quote
        $booking->update([
            'total_amount'   => $totalAmount,
            'deposit_amount' => $deposit,
            'balance_amount' => $balance,
        ]);

        return $invoice;
    }

    public function markSent(): void
    {
        $this->update(['status' => 'sent', 'sent_at' => now()]);
    }

    public function markAccepted(): void
    {
        $this->update(['status' => 'accepted', 'accepted_at' => now()]);
    }

    public function getTypeLabel(): string
    {
        return static::types()[$this->type] ?? ucfirst($this->type);
    }
}
