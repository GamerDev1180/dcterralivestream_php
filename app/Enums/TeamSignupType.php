<?php

namespace App\Enums;

enum TeamSignupType: string
{
    case Volunteer = 'vrijwilliger';
    case Organisation = 'organisatie';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Volunteer => 'Vrijwilliger',
            self::Organisation => 'Organisatie',
        };
    }

    /**
     * Get the Flux icon name for this signup type.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Volunteer => 'hand-raised',
            self::Organisation => 'building-office-2',
        };
    }
}
