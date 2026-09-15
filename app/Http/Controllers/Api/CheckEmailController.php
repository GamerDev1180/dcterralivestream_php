<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EmailDomain;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CheckEmailController extends Controller
{
    /**
     * Check if an email address may register and whether it is already registered.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        if (! EmailDomain::allows($validated['email'])) {
            return response()->json(['success' => false, 'error' => 'Invalid email domain'], 400);
        }

        return response()->json([
            'success' => true,
            'exists' => Registration::where('email', $validated['email'])->exists(),
        ]);
    }
}
