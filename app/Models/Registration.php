<?php

namespace App\Models;

use App\Enums\ParticipationType;
use App\Enums\RegistrationStatus;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * A participant who signed up for the livestream itself (on camera or behind the scenes).
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property ParticipationType $participation_type
 * @property RegistrationStatus $status
 * @property string $discord_code
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'participation_type', 'status', 'discord_code'])]
class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'participation_type' => ParticipationType::class,
            'status' => RegistrationStatus::class,
        ];
    }

    /**
     * The answers this participant gave to the registration questions.
     *
     * @return HasMany<RegistrationAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(RegistrationAnswer::class);
    }

    /**
     * Generate a unique code (e.g. "DCTerra-A1B2C3D4") the participant uses to join the Discord server.
     */
    public static function generateDiscordCode(): string
    {
        do {
            $code = 'DCTerra-'.Str::upper(Str::random(8));
        } while (static::where('discord_code', $code)->exists());

        return $code;
    }
}
