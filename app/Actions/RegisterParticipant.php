<?php

namespace App\Actions;

use App\Enums\ParticipationType;
use App\Enums\RegistrationStatus;
use App\Mail\RegistrationConfirmation;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Saves a (validated) registration with its answers and emails the participant their Discord code.
 */
class RegisterParticipant
{
    /**
     * Register a participant for the livestream.
     *
     * @param  array<int|string, string|array<int, string>|null>  $answers  Answers keyed by question id
     */
    public function handle(string $name, string $email, array $answers = []): Registration
    {
        $registration = DB::transaction(function () use ($name, $email, $answers) {
            $registration = Registration::create([
                'name' => $name,
                'email' => $email,
                'participation_type' => ParticipationType::OnCamera,
                'status' => RegistrationStatus::Confirmed,
                'discord_code' => Registration::generateDiscordCode(),
            ]);

            foreach ($answers as $questionId => $answer) {
                $answer = is_array($answer) ? implode(',', $answer) : trim((string) $answer);

                if ($answer === '') {
                    continue;
                }

                $registration->answers()->create([
                    'registration_question_id' => $questionId,
                    'answer' => $answer,
                ]);
            }

            return $registration;
        });

        // A failing mail server should not undo the registration, so the error is only logged.
        try {
            Mail::to($registration->email)->send(new RegistrationConfirmation($registration));
        } catch (Throwable $exception) {
            report($exception);
        }

        return $registration;
    }
}
