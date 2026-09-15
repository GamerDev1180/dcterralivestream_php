<?php

namespace App\Http\Resources;

use App\Models\RegistrationQuestion;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin RegistrationQuestion
 */
class RegistrationQuestionResource extends JsonResource
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
            'question_text' => $this->question_text,
            'question_type' => $this->question_type->value,
            'options' => $this->options,
            'required' => $this->is_required,
            'category' => $this->category->value,
            'order_index' => $this->order_index,
        ];
    }
}
