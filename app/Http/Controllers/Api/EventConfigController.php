<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class EventConfigController extends Controller
{
    /**
     * Get all public event settings (texts, dates, feature flags and stats).
     */
    public function show(): JsonResponse
    {
        return response()->json(Setting::values());
    }
}
