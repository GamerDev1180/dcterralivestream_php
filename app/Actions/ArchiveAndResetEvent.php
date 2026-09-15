<?php

namespace App\Actions;

use App\Models\ArchivedRecord;
use App\Models\Registration;
use App\Models\RegistrationAnswer;
use App\Models\TeamSignup;
use Illuminate\Support\Facades\DB;

/**
 * Copies every registration and team signup to the archive and then clears them,
 * so the site is ready for next year's event. Sponsors, questions, schedule,
 * FAQ, email domains and settings stay untouched.
 */
class ArchiveAndResetEvent
{
    /**
     * Archive and remove all registrations and team signups.
     *
     * @return array{archived_year: int, registrations: int, team_signups: int}
     */
    public function handle(): array
    {
        return DB::transaction(function () {
            $archivedAt = now();

            $registrations = Registration::with('answers.question')->get();

            foreach ($registrations as $registration) {
                ArchivedRecord::create([
                    'record_type' => 'registration',
                    'archived_year' => $archivedAt->year,
                    'archived_at' => $archivedAt,
                    'data' => [
                        ...$registration->only(['id', 'name', 'email', 'discord_code', 'created_at', 'updated_at']),
                        'participation_type' => $registration->participation_type->value,
                        'status' => $registration->status->value,
                        'answers' => $registration->answers
                            ->map(fn (RegistrationAnswer $answer) => [
                                'question_id' => $answer->registration_question_id,
                                'question' => $answer->question->question_text,
                                'answer' => $answer->answer,
                            ])
                            ->all(),
                    ],
                ]);
            }

            $teamSignups = TeamSignup::all();

            foreach ($teamSignups as $teamSignup) {
                ArchivedRecord::create([
                    'record_type' => 'team_signup',
                    'archived_year' => $archivedAt->year,
                    'archived_at' => $archivedAt,
                    'data' => $teamSignup->toArray(),
                ]);
            }

            RegistrationAnswer::query()->delete();
            Registration::query()->delete();
            TeamSignup::query()->delete();

            return [
                'archived_year' => $archivedAt->year,
                'registrations' => $registrations->count(),
                'team_signups' => $teamSignups->count(),
            ];
        });
    }
}
