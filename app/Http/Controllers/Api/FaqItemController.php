<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqItemResource;
use App\Models\FaqItem;
use Illuminate\Http\JsonResponse;

class FaqItemController extends Controller
{
    /**
     * List the active FAQ items.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'items' => FaqItemResource::collection(FaqItem::activeInOrder()->get()),
        ]);
    }
}
