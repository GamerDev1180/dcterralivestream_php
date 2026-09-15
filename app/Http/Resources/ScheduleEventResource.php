<?php

namespace App\Http\Resources;

use App\Models\ScheduleEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ScheduleEvent
 */
class ScheduleEventResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'start_time' => $this->start_time->toIso8601String(),
            'end_time' => $this->end_time->toIso8601String(),
            'event_type' => $this->event_type->value,
            'participants' => $this->participants ?? [],
            'display_order' => $this->display_order,
        ];
    }
}
