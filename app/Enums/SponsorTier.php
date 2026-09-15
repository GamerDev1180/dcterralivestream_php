<?php

namespace App\Enums;

enum SponsorTier: string
{
    case Platinum = 'Platinum';
    case Gold = 'Gold';
    case Silver = 'Silver';
    case Bronze = 'Bronze';

    /**
     * Get the Dutch label shown on the public sponsors page.
     */
    public function label(): string
    {
        return match ($this) {
            self::Platinum => 'Platina',
            self::Gold => 'Goud',
            self::Silver => 'Zilver',
            self::Bronze => 'Brons',
        };
    }

    /**
     * Get the heading of this tier's section on the public sponsors page.
     */
    public function sectionTitle(): string
    {
        return match ($this) {
            self::Platinum => 'Platina Sponsors',
            self::Gold => 'Gouden Sponsors',
            self::Silver => 'Zilveren Sponsors',
            self::Bronze => 'Bronzen Sponsors',
        };
    }

    /**
     * Get the gradient classes used for the tier badge and chip on the public site.
     */
    public function gradientClasses(): string
    {
        return match ($this) {
            self::Platinum => 'bg-gradient-to-r from-slate-400 to-slate-600',
            self::Gold => 'bg-gradient-to-r from-yellow-400 to-yellow-600',
            self::Silver => 'bg-gradient-to-r from-gray-300 to-gray-500',
            self::Bronze => 'bg-gradient-to-r from-amber-600 to-amber-800',
        };
    }

    /**
     * Get the badge classes used in the admin panel.
     */
    public function adminBadgeClasses(): string
    {
        return match ($this) {
            self::Platinum => 'bg-slate-100 text-slate-800',
            self::Gold => 'bg-yellow-100 text-yellow-800',
            self::Silver => 'bg-gray-100 text-gray-800',
            self::Bronze => 'bg-amber-100 text-amber-800',
        };
    }
}
