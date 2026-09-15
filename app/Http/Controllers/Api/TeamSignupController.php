<?php

namespace App\Http\Controllers\Api;

use App\Enums\TeamSignupStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTeamSignupRequest;
use App\Models\TeamSignup;
use Illuminate\Http\JsonResponse;

class TeamSignupController extends Controller
{
    /**
     * Sign up as a volunteer or organisation.
     */
    public function store(StoreTeamSignupRequest $request): JsonResponse
    {
        TeamSignup::create([
            ...$request->validated(),
            'status' => TeamSignupStatus::Pending,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Aanmelding succesvol ontvangen',
        ]);
    }
}
