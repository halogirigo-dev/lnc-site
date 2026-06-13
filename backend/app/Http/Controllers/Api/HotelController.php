<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hotel;
use Illuminate\Http\JsonResponse;

class HotelController extends Controller
{
    public function index(): JsonResponse
    {
        $hotels = Hotel::active()
            ->with(['properties' => fn ($q) => $q->active()->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($hotel) => $hotel->toPhpArray());

        return response()->json([
            'data'  => $hotels,
            'total' => $hotels->count(),
        ]);
    }
}
