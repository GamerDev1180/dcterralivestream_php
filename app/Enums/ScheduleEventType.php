<?php

namespace App\Enums;

enum ScheduleEventType: string
{
    case Gaming = 'gaming';
    case Music = 'music';
    case Talk = 'talk';
    case Break = 'break';
    case Special = 'special';

    /**
     * Get the Dutch label shown on the public schedule.
     */
    public function label(): string
    {
        return match ($this) {
            self::Gaming => 'Gaming',
            self::Music => 'Muziek',
            self::Talk => 'Talk',
            self::Break => 'Pauze',
            self::Special => 'Speciaal',
        };
    }

    /**
     * Get the Flux icon name for this event type.
     */
    public function icon(): string
    {
        return match ($this) {
            self::Gaming => 'puzzle-piece',
            self::Music => 'musical-note',
            self::Talk => 'microphone',
            self::Break => 'pause-circle',
            self::Special => 'trophy',
        };
    }

    /**
     * Get the Tailwind classes that color this event type.
     */
    public function colorClasses(): string
    {
        return match ($this) {
            self::Gaming => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
            self::Music => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
            self::Talk => 'bg-green-500/10 text-green-400 border-green-500/20',
            self::Break => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
            self::Special => 'bg-primary/10 text-primary border-primary/20',
        };
    }
}
