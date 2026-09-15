<?php

namespace App\Models;

use App\Enums\SponsorTier;
use Database\Factories\SponsorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property SponsorTier $tier
 * @property string|null $logo_url
 * @property string|null $description
 * @property string|null $website
 * @property string|null $contribution
 * @property int $display_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'tier', 'logo_url', 'description', 'website', 'contribution', 'display_order', 'is_active'])]
class Sponsor extends Model
{
    /** @use HasFactory<SponsorFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tier' => SponsorTier::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * Only include active sponsors, in the order they should be shown.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function activeInOrder(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('display_order')->orderBy('name');
    }
}
