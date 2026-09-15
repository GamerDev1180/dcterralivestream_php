<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleEventResource;
use App\Models\ScheduleEvent;
use Illuminate\Http\JsonResponse;

class ScheduleEventController extends Controller
{
    /**
     * List the active programme items of the livestream.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'events' => ScheduleEventResource::collection(ScheduleEvent::activeInOrder()->get()),
        ]);
    }
}
