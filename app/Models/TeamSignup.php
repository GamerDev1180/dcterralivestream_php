<?php

namespace App\Models;

use App\Enums\TeamSignupStatus;
use App\Enums\TeamSignupType;
use Database\Factories\TeamSignupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A volunteer or organisation that wants to help organise the event.
 *
 * @property int $id
 * @property TeamSignupType $type
 * @property string $name
 * @property string $email
 * @property string|null $discord_name
 * @property string|null $organization_name
 * @property string|null $phone
 * @property string|null $message
 * @property TeamSignupStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['type', 'name', 'email', 'discord_name', 'organization_name', 'phone', 'message', 'status'])]
class TeamSignup extends Model
{
    /** @use HasFactory<TeamSignupFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => TeamSignupType::class,
            'status' => TeamSignupStatus::class,
        ];
    }
}
