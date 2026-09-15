<?php

namespace App\Http\Controllers\Api;

use App\Actions\RegisterParticipant;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegistrationRequest;
use App\Models\EmailDomain;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;

class RegistrationController extends Controller
{
    /**
     * Register a participant for the livestream.
     */
    public function store(StoreRegistrationRequest $request, RegisterParticipant $registerParticipant): JsonResponse
    {
        $email = $request->string('email')->value();

        if (! EmailDomain::allows($email)) {
            return response()->json(['success' => false, 'error' => 'Invalid email domain'], 400);
        }

        if (Registration::where('email', $email)->exists()) {
            return response()->json(['success' => false, 'error' => 'Email already registered'], 409);
        }

        $registration = $registerParticipant->handle(
            name: $request->string('name')->value(),
            email: $email,
            answers: $request->validated('answers', []),
        );

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'discordCode' => $registration->discord_code,
        ]);
    }
}
