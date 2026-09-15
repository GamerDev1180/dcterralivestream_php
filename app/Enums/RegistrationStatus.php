<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';

    /**
     * Get the label shown in the admin panel.
     */
    public function label(): string
    {
        return ucfirst($this->value);
    }

    /**
     * Get the badge variant (see resources/views/components/ui/badge.blade.php).
     */
    public function badgeVariant(): string
    {
        return match ($this) {
            self::Confirmed => 'default',
            self::Pending => 'secondary',
            self::Cancelled => 'destructive',
        };
    }
}
