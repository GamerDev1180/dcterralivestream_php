<?php

namespace App\Enums;

enum TeamSignupStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'In behandeling',
            self::Approved => 'Goedgekeurd',
            self::Rejected => 'Afgewezen',
        };
    }

    /**
     * Get the badge variant (see resources/views/components/ui/badge.blade.php).
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Approved => 'default',
            self::Pending => 'secondary',
            self::Rejected => 'destructive',
        };
    }
}
