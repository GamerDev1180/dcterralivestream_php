<?php

namespace App\Http\Controllers\Api;

use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class LiveStatsController extends Controller
{
    /**
     * Get the live numbers: confirmed participants (counted) and funds raised (entered by an organizer).
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'confirmedParticipants' => Registration::where('status', RegistrationStatus::Confirmed)->count(),
            'fundsRaisedAmount' => Setting::getValue('stat_funds_raised_amount'),
        ]);
    }
}
