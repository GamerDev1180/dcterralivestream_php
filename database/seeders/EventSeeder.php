<?php

namespace Database\Seeders;

use App\Enums\ParticipationType;
use App\Enums\QuestionType;
use App\Enums\ScheduleEventType;
use App\Enums\SponsorTier;
use App\Models\EmailDomain;
use App\Models\FaqItem;
use App\Models\Registration;
use App\Models\RegistrationQuestion;
use App\Models\ScheduleEvent;
use App\Models\Setting;
use App\Models\Sponsor;
use App\Models\TeamSignup;
use Illuminate\Database\Seeder;

/**
 * Fills the database with the defaults of the original site plus some example data,
 * so every page has something to show during development.
 */
class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->seedDefaults();
        $this->seedExampleData();
    }

    /**
     * The allowed email domains and registration questions of the original site.
     */
    private function seedDefaults(): void
    {
        EmailDomain::firstOrCreate(['domain' => '@dcterra.nl']);
        EmailDomain::firstOrCreate(['domain' => '@student.dcterra.nl']);

        $questions = [
            ['What is your experience with being on camera?', QuestionType::Select, ParticipationType::OnCamera, 1, true, ['Beginner', 'Intermediate', 'Advanced', 'Professional']],
            ['Are you comfortable speaking in front of an audience?', QuestionType::Radio, ParticipationType::OnCamera, 2, true, ['Yes, very comfortable', 'Somewhat comfortable', 'Not very comfortable', 'Prefer not to speak']],
            ['What topics would you like to discuss during the livestream?', QuestionType::Textarea, ParticipationType::OnCamera, 3, true, null],
            ['Do you have any streaming equipment (microphone, camera, etc.)?', QuestionType::Checkbox, ParticipationType::OnCamera, 4, false, ['Microphone', 'HD Camera', 'Lighting Equipment', 'Streaming Software', 'None of the above']],
            ['How would you like to contribute to the event?', QuestionType::Select, ParticipationType::OffCamera, 1, true, ['Technical Support', 'Content Creation', 'Social Media', 'Event Coordination', 'Other']],
            ['What technical skills do you have?', QuestionType::Checkbox, ParticipationType::OffCamera, 2, false, ['Web Development', 'Graphic Design', 'Video Editing', 'Social Media Management', 'Project Management', 'Other']],
            ['Are you interested in helping with event moderation?', QuestionType::Radio, ParticipationType::OffCamera, 3, true, ['Yes, I would love to help', 'Maybe, depending on the time', 'No, I prefer other tasks']],
            ['What time slots are you available during the 24H event?', QuestionType::Textarea, ParticipationType::OffCamera, 4, true, null],
        ];

        foreach ($questions as [$text, $type, $category, $order, $required, $options]) {
            RegistrationQuestion::firstOrCreate(['question_text' => $text], [
                'question_type' => $type,
                'category' => $category,
                'order_index' => $order,
                'is_required' => $required,
                'options' => $options,
            ]);
        }
    }

    /**
     * Example settings, registrations, schedule, sponsors and FAQ items for local development.
     */
    private function seedExampleData(): void
    {
        $streamStartTime = now()->addMonths(3)->setTime(4, 0);

        Setting::setValues([
            'event_title' => '24H Livestream voor DCTerra',
            'event_description' => 'Doe je mee met de livestream!? Meld je nu aan!',
            'event_venue' => 'DCTerra ICT Lokaal',
            'stream_start_time' => $streamStartTime->format('Y-m-d H:i:s'),
            'registration_end_time' => $streamStartTime->subDay()->setTime(21, 59)->format('Y-m-d H:i:s'),
            'feature_registration_enabled' => true,
            'feature_schedule_enabled' => true,
            'feature_sponsors_enabled' => true,
            'feature_team_enabled' => true,
            'feature_faq_enabled' => true,
        ]);

        Registration::factory(8)->onCamera()->confirmed()->create();
        Registration::factory(3)->offCamera()->create();

        TeamSignup::factory(5)->create();
        TeamSignup::factory(2)->organisation()->create();

        foreach ([
            ['Opening van de stream', ScheduleEventType::Special, 0, 1],
            ['Mario Kart toernooi', ScheduleEventType::Gaming, 1, 3],
            ['Live muziek', ScheduleEventType::Music, 4, 2],
            ['Pizza pauze', ScheduleEventType::Break, 6, 1],
            ['Talkshow met docenten', ScheduleEventType::Talk, 7, 2],
        ] as [$title, $type, $startsAfterHours, $durationInHours]) {
            ScheduleEvent::factory()->create([
                'title' => $title,
                'event_type' => $type,
                'start_time' => $streamStartTime->addHours($startsAfterHours),
                'end_time' => $streamStartTime->addHours($startsAfterHours + $durationInHours),
            ]);
        }

        Sponsor::factory()->create(['name' => 'Voorbeeld Sponsor BV', 'tier' => SponsorTier::Platinum]);
        foreach ([SponsorTier::Gold, SponsorTier::Silver, SponsorTier::Silver, SponsorTier::Bronze] as $tier) {
            Sponsor::factory()->create(['tier' => $tier]);
        }

        FaqItem::create([
            'question' => 'Wie mag er meedoen?',
            'answer' => 'Alle studenten en medewerkers van DCTerra kunnen zich aanmelden.',
            'display_order' => 1,
        ]);
        FaqItem::create([
            'question' => 'Waar gaat het opgehaalde geld naartoe?',
            'answer' => 'Alle opbrengsten gaan naar het goede doel.',
            'display_order' => 2,
        ]);
    }
}
