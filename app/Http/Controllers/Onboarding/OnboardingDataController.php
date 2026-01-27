<?php

namespace App\Http\Controllers\Onboarding;

use App\Enums\SchoolType;
use App\Http\Controllers\Controller;
use App\Models\SchoolLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OnboardingDataController extends Controller
{
    public function schoolTypes(): JsonResponse
    {
        return response()->json([
            'data' => SchoolType::options(),
        ]);
    }

    public function countries(): JsonResponse
    {
        return response()->json([
            'data' => SchoolLocation::countries(),
        ]);
    }

    public function regions(Request $request): JsonResponse
    {
        $request->validate([
            'country' => ['required', 'string', 'exists:school_locations,country'],
        ]);

        return response()->json([
            'data' => SchoolLocation::regions($request->country),
        ]);
    }

    public function districts(Request $request): JsonResponse
    {
        $request->validate([
            'country' => ['required', 'string', 'exists:school_locations,country'],
            'region' => ['required', 'string'],
        ]);

        return response()->json([
            'data' => SchoolLocation::districts($request->country, $request->region),
        ]);
    }
}
