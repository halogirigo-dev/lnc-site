<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

class TestimonialController extends Controller
{
    public function index(): JsonResponse
    {
        $testimonials = Testimonial::active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($t) => $t->toPhpArray());

        return response()->json([
            'data'  => $testimonials,
            'total' => $testimonials->count(),
        ]);
    }
}
