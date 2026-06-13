<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TourPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = TourPackage::active()->orderBy('sort_order');

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        if ($request->boolean('long_stay')) {
            $query->longStay();
        } elseif ($request->boolean('short_stay')) {
            $query->shortStay();
        }

        $packages = $query->get()->map(fn ($pkg) => $pkg->toPhpArray());

        return response()->json([
            'data'  => $packages,
            'total' => $packages->count(),
        ]);
    }

    public function show(string $code): JsonResponse
    {
        $package = TourPackage::where('package_code', strtoupper($code))
            ->active()
            ->firstOrFail();

        return response()->json([
            'data' => $package->toPhpArray(),
        ]);
    }
}
