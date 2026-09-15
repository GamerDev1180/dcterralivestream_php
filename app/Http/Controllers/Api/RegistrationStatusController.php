<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class RegistrationStatusController extends Controller
{
    /**
     * Tell the frontend whether people can register right now.
     */
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'isOpen' => Setting::isRegistrationOpen(),
            'endTime' => Setting::getValue('registration_end_time'),
            'manuallyDisabled' => ! Setting::getBoolean('registration_open'),
            'featureEnabled' => Setting::isFeatureEnabled('registration'),
        ]);
    }
}
