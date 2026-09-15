<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SponsorResource;
use App\Models\Sponsor;
use Illuminate\Http\JsonResponse;

class SponsorController extends Controller
{
    /**
     * List the active sponsors.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'sponsors' => SponsorResource::collection(Sponsor::activeInOrder()->get()),
        ]);
    }
}
