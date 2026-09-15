<?php

namespace App\Enums;

enum ParticipationType: string
{
    case OnCamera = 'on_camera';
    case OffCamera = 'off_camera';

    /**
     * Get the label shown in the admin panel.
     */
    public function label(): string
    {
        return match ($this) {
            self::OnCamera => 'On Camera',
            self::OffCamera => 'Behind Scenes',
        };
    }

    /**
     * Get the Flux icon name for this participation type.
     */
    public function icon(): string
    {
        return match ($this) {
            self::OnCamera => 'video-camera',
            self::OffCamera => 'video-camera-slash',
        };
    }
}
