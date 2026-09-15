<?php

namespace App\Http\Controllers\Api;

use App\Concerns\RegistrationValidationRules;
use App\Http\Controllers\Controller;
use App\Http\Resources\RegistrationQuestionResource;
use Illuminate\Http\JsonResponse;

class QuestionController extends Controller
{
    use RegistrationValidationRules;

    /**
     * List the questions asked during registration.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'questions' => RegistrationQuestionResource::collection($this->registrationQuestions()),
        ]);
    }
}
