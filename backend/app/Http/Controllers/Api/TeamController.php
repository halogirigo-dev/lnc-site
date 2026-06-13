<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\JsonResponse;

class TeamController extends Controller
{
    public function index(): JsonResponse
    {
        $team = TeamMember::active()
            ->orderBy('sort_order')
            ->get()
            ->map(fn ($m) => $m->toPhpArray());

        return response()->json([
            'data'  => $team,
            'total' => $team->count(),
        ]);
    }
}
