<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AccommodationType;
use Illuminate\Http\JsonResponse;

class AccommodationTypeController extends Controller
{
    /**
     * List all accommodation types with property counts.
     */
    public function index(): JsonResponse
    {
        $types = AccommodationType::withCount(['accommodations' => function ($query) {
            $query->where('estado', 'active');
        }])->get();

        return response()->json([
            'success' => true,
            'data' => $types,
        ]);
    }
}
