<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Key/value store for everything that can be changed from the admin panel
 * (event texts, dates, feature flags and stats) without touching code.
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    /**
     * The cache key that holds all settings.
     */
    private const CACHE_KEY = 'settings';

    /**
     * The value of each setting when it has not been saved yet.
     *
     * Dates use the "Y-m-d H:i:s" format in the application's timezone.
     * Booleans are stored as the strings "true" and "false".
     *
     * @var array<string, string>
     */
    public const DEFAULTS = [
        // Event timing
        'stream_start_time' => '',
        'registration_end_time' => '',
        'event_title' => '24H Livestream',
        'event_description' => 'Join us for a 24-hour livestream event',
        'registration_open' => 'true',

        // Feature flags: control public nav + page availability without touching code
        'feature_registration_enabled' => 'false',
        'feature_schedule_enabled' => 'false',
        'feature_sponsors_enabled' => 'false',
        'feature_team_enabled' => 'true',
        'feature_faq_enabled' => 'false',

        // Organisation / content strings
        'org_name' => 'DCTerra',
        'org_website_url' => 'https://example.org',
        'org_description' => 'A charity we are proud to support.',
        'event_venue' => 'To be announced',
        'discord_invite_link' => '',
        'stream_platform_url' => '',
        'contact_email' => 'contact@example.org',

        // Manually entered stats (there is no payment integration)
        'stat_funds_raised_amount' => '0',
        'stat_funds_raised_mode' => 'manual',
        'stat_funds_goal_amount' => '0',
        'stat_expected_participants' => '0',
    ];

    /**
     * Get all settings, using the default for every setting that was never saved.
     *
     * @return array<string, string>
     */
    public static function values(): array
    {
        $saved = Cache::rememberForever(self::CACHE_KEY, fn () => static::pluck('value', 'key')->all());

        return array_merge(self::DEFAULTS, array_filter($saved, fn ($value) => $value !== null));
    }

    /**
     * Get the value of a single setting.
     */
    public static function getValue(string $key): string
    {
        return static::values()[$key] ?? '';
    }

    /**
     * Save the value of a single setting.
     */
    public static function setValue(string $key, string|bool|null $value): void
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }

        static::updateOrCreate(['key' => $key], ['value' => $value ?? '']);

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Save multiple settings at once.
     *
     * @param  array<string, string|bool|null>  $values
     */
    public static function setValues(array $values): void
    {
        foreach ($values as $key => $value) {
            static::setValue($key, $value);
        }
    }

    /**
     * Get a display value, or the fallback when the setting is empty or "0".
     */
    public static function getDisplayValue(string $key, string $fallback): string
    {
        $value = static::getValue($key);

        return in_array($value, ['', '0'], true) ? $fallback : $value;
    }

    /**
     * Get a boolean setting.
     */
    public static function getBoolean(string $key): bool
    {
        return static::getValue($key) === 'true';
    }

    /**
     * Get a date setting, or null when it is empty or invalid.
     */
    public static function getDate(string $key): ?CarbonImmutable
    {
        $value = static::getValue($key);

        if ($value === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Determine if a public feature (registration, schedule, sponsors, team, faq) is turned on.
     */
    public static function isFeatureEnabled(string $feature): bool
    {
        return static::getBoolean("feature_{$feature}_enabled");
    }

    /**
     * Determine if people can currently register for the livestream.
     */
    public static function isRegistrationOpen(): bool
    {
        if (! static::isFeatureEnabled('registration') || ! static::getBoolean('registration_open')) {
            return false;
        }

        $registrationEndTime = static::getDate('registration_end_time');

        return $registrationEndTime === null || now()->lt($registrationEndTime);
    }
}
