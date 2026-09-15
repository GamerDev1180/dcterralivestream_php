<?php

namespace App\Models;

use Database\Factories\EmailDomainFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * An email domain (e.g. "@student.dcterra.nl") that is allowed to register.
 *
 * @property int $id
 * @property string $domain
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['domain'])]
class EmailDomain extends Model
{
    /** @use HasFactory<EmailDomainFactory> */
    use HasFactory;

    /**
     * Determine if the email address may be used to register.
     *
     * When no domains are configured, nobody can register.
     */
    public static function allows(string $email): bool
    {
        return static::pluck('domain')
            ->contains(fn (string $domain) => Str::endsWith(Str::lower($email), Str::lower($domain)));
    }

    /**
     * Normalize a domain so it always starts with an "@".
     */
    public static function normalize(string $domain): string
    {
        return '@'.ltrim(Str::lower(trim($domain)), '@');
    }
}
