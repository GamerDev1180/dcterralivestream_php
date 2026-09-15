<?php

namespace App\Models;

use App\Enums\ScheduleEventType;
use Database\Factories\ScheduleEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A single programme item during the livestream.
 *
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property ScheduleEventType $event_type
 * @property array<int, string>|null $participants
 * @property int $display_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'description', 'start_time', 'end_time', 'event_type', 'participants', 'display_order', 'is_active'])]
class ScheduleEvent extends Model
{
    /** @use HasFactory<ScheduleEventFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'event_type' => ScheduleEventType::class,
            'participants' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Only include active events, in chronological order.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function activeInOrder(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('start_time')->orderBy('display_order');
    }

    /**
     * Determine if the event is happening right now.
     */
    public function isLive(): bool
    {
        return now()->between($this->start_time, $this->end_time);
    }
}
