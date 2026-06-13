<?php

namespace App\Observers;

use App\Models\Booking;
use App\Models\BookingFieldLog;
use Illuminate\Support\Facades\Auth;

class BookingObserver
{
    public function updating(Booking $booking): void
    {
        $trackedFields = BookingFieldLog::fields();
        $changedBy     = Auth::user()?->email ?? 'system';
        $changedById   = Auth::id();
        $ipAddress     = request()->ip();

        foreach ($trackedFields as $field) {
            if (!$booking->isDirty($field)) {
                continue;
            }

            $old = $booking->getOriginal($field);
            $new = $booking->getAttribute($field);

            // Skip if both are effectively empty/null
            if ($old === $new || ((string) $old === (string) $new)) {
                continue;
            }

            BookingFieldLog::create([
                'booking_ref'   => $booking->ref,
                'field_name'    => $field,
                'old_value'     => $old !== null ? (string) $old : null,
                'new_value'     => $new !== null ? (string) $new : null,
                'changed_by'    => $changedBy,
                'changed_by_id' => $changedById,
                'ip_address'    => $ipAddress,
                'created_at'    => now(),
            ]);
        }
    }
}
