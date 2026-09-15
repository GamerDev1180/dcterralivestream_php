<?php

namespace App\Mail;

use App\Models\Registration;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Registration $registration) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Welcome to the '.Setting::getDisplayValue('event_title', '24H Livestream').'!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $streamStartTime = Setting::getDate('stream_start_time');

        return new Content(
            view: 'mail.registration-confirmation',
            with: [
                'orgName' => Setting::getDisplayValue('org_name', 'DCTerra'),
                'eventTitle' => Setting::getDisplayValue('event_title', '24H Livestream'),
                'eventDateLabel' => $streamStartTime?->translatedFormat('l j F Y \o\m H:i') ?? 'Binnenkort bekend',
                'discordInviteLink' => Setting::getValue('discord_invite_link'),
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
