<?php

namespace App\Http\Resources;

use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Sponsor
 */
class SponsorResource extends JsonResource
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
            'name' => $this->name,
            'tier' => $this->tier->value,
            'logo_url' => $this->logo_url,
            'description' => $this->description,
            'website' => $this->website,
            'contribution' => $this->contribution,
            'display_order' => $this->display_order,
            'is_active' => $this->is_active,
        ];
    }
}
