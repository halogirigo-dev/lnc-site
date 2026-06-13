<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name'          => 'required|string|min:2|max:255',
                'email'         => 'required|email|max:255',
                'phone'         => 'nullable|regex:/^[+\d\s\-().]{7,20}$/',
                'package_id'    => 'nullable|string|max:20',
                'package_title' => 'nullable|string|max:255',
                'guests'        => 'nullable|integer|min:1|max:50',
                'dates'         => 'nullable|string|max:255',
                'total_amount'  => 'nullable|integer|min:0',
                'deposit_amount'=> 'nullable|integer|min:0',
                'balance_amount'=> 'nullable|integer|min:0',
                'message'       => 'nullable|string|max:5000',
                'special'       => 'nullable|string|max:2000',
                'budget'        => 'nullable|string|max:100',
                'accommodation' => 'nullable|string|max:255',
                'flexibility'   => 'nullable|string|max:100',
                'country'       => 'nullable|string|max:100',
                'nationality'   => 'nullable|string|max:100',
                'age_range'     => 'nullable|string|max:20',
                'source'        => 'nullable|string|max:100',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Upsert customer
            $customer = Customer::updateOrCreate(
                ['email' => $validated['email']],
                [
                    'name'        => $validated['name'],
                    'phone'       => $validated['phone'] ?? null,
                    'country'     => $validated['country'] ?? null,
                    'nationality' => $validated['nationality'] ?? null,
                    'age_range'   => $validated['age_range'] ?? null,
                    'source'      => $validated['source'] ?? null,
                ]
            );

            $ref = Booking::generateRef();

            $booking = Booking::create([
                'ref'                   => $ref,
                'customer_id'           => $customer->id,
                'status'                => Booking::STATUS_PENDING,
                'package_id'            => $validated['package_id'] ?? null,
                'package_title'         => $validated['package_title'] ?? null,
                'package_price_per_pax' => 0,
                'total_amount'          => $validated['total_amount'] ?? 0,
                'deposit_amount'        => $validated['deposit_amount'] ?? 0,
                'balance_amount'        => $validated['balance_amount'] ?? 0,
                'guests'                => $validated['guests'] ?? 1,
                'dates'                 => $validated['dates'] ?? null,
                'flexibility'           => $validated['flexibility'] ?? null,
                'accommodation'         => $validated['accommodation'] ?? null,
                'name'                  => $validated['name'],
                'email'                 => $validated['email'],
                'phone'                 => $validated['phone'] ?? null,
                'country'               => $validated['country'] ?? null,
                'nationality'           => $validated['nationality'] ?? null,
                'age_range'             => $validated['age_range'] ?? null,
                'source'                => $validated['source'] ?? null,
                'message'               => $validated['message'] ?? null,
                'special'               => $validated['special'] ?? null,
                'budget'                => $validated['budget'] ?? null,
            ]);

            // Auto-create invoice record
            if ($booking->total_amount > 0) {
                Invoice::createFromBooking($booking);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'ref'     => $ref,
                'booking' => $booking->only([
                    'ref', 'status', 'package_title', 'total_amount',
                    'deposit_amount', 'balance_amount', 'guests',
                ]),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create booking: ' . $e->getMessage()], 500);
        }
    }

    public function show(string $ref): JsonResponse
    {
        $ref = preg_replace('/[^A-Z0-9\-]/', '', strtoupper($ref));

        $booking = Booking::where('ref', $ref)
            ->with(['payments'])
            ->first();

        if (!$booking) {
            return response()->json(['error' => 'Booking not found'], 404);
        }

        $depositPayment = $booking->payments->where('payment_type', 'deposit')->first();
        $balancePayment = $booking->payments->where('payment_type', 'balance')->first();

        return response()->json([
            'data' => array_merge($booking->toArray(), [
                'dep_paid' => $depositPayment?->isPaid() ?? false,
                'bal_paid' => $balancePayment?->isPaid() ?? false,
            ]),
        ]);
    }
}
