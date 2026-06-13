<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function webhook(Request $request): Response
    {
        $notif = $request->all();

        if (empty($notif) || empty($notif['order_id'])) {
            return response('', 200);
        }

        // Verify Midtrans signature
        $serverKey = config('services.midtrans.server_key');
        if ($serverKey && !str_contains($serverKey, 'PLACEHOLDER')) {
            $expected = hash('sha512',
                ($notif['order_id']     ?? '') .
                ($notif['status_code']  ?? '') .
                ($notif['gross_amount'] ?? '') .
                $serverKey
            );
            if (!hash_equals($expected, $notif['signature_key'] ?? '')) {
                Log::warning('Midtrans webhook: invalid signature', ['order_id' => $notif['order_id']]);
                return response('', 200);
            }
        }

        $orderId           = $notif['order_id'] ?? '';
        $transactionStatus = $notif['transaction_status'] ?? '';
        $fraudStatus       = $notif['fraud_status'] ?? 'accept';

        // Parse ref and payment type from order_id (e.g. LNC-2026-ABCDE-DEP)
        if (!preg_match('/^(LNC-\d{4}-[A-Z0-9]{5})-(DEP|BAL)$/', $orderId, $m)) {
            return response('', 200);
        }

        $ref   = $m[1];
        $ptype = $m[2] === 'DEP' ? 'deposit' : 'balance';

        $success = ($transactionStatus === 'capture' && $fraudStatus === 'accept')
                || $transactionStatus === 'settlement';

        if (!$success) {
            // Update to failed status
            if (in_array($transactionStatus, ['deny', 'cancel', 'expire', 'failure'])) {
                Payment::where('booking_ref', $ref)
                    ->where('payment_type', $ptype)
                    ->update(['midtrans_status' => $transactionStatus]);
            }
            return response('', 200);
        }

        // Update payment record
        Payment::updateOrCreate(
            ['booking_ref' => $ref, 'payment_type' => $ptype],
            [
                'midtrans_order_id'       => $orderId,
                'midtrans_transaction_id' => $notif['transaction_id'] ?? null,
                'midtrans_status'         => $transactionStatus,
                'payment_method'          => $notif['payment_type'] ?? null,
                'raw_notification'        => $notif,
                'paid_at'                 => now(),
            ]
        );

        // Update booking status
        $newStatus = ($ptype === 'deposit') ? Booking::STATUS_DEPOSIT : Booking::STATUS_CONFIRMED;
        Booking::where('ref', $ref)->update([
            'status'     => $newStatus,
            'updated_at' => now(),
        ]);

        Log::info("Payment processed: {$ref} — {$ptype} — {$transactionStatus}");

        return response('', 200);
    }
}
