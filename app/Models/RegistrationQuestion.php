<?php

namespace App\Models;

use App\Enums\ParticipationType;
use App\Enums\QuestionType;
use Database\Factories\RegistrationQuestionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * An extra question shown on the registration form, per participation type.
 *
 * @property int $id
 * @property string $question_text
 * @property QuestionType $question_type
 * @property ParticipationType $category
 * @property array<int, string>|null $options
 * @property int $order_index
 * @property bool $is_required
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['question_text', 'question_type', 'category', 'options', 'order_index', 'is_required', 'is_active'])]
class RegistrationQuestion extends Model
{
    /** @use HasFactory<RegistrationQuestionFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'question_type' => QuestionType::class,
            'category' => ParticipationType::class,
            'options' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The answers given to this question.
     *
     * @return HasMany<RegistrationAnswer, $this>
     */
    public function answers(): HasMany
    {
        return $this->hasMany(RegistrationAnswer::class);
    }

    /**
     * Only include active questions, in the order they should be shown.
     *
     * @param  Builder<self>  $query
     */
    #[Scope]
    protected function activeInOrder(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('order_index')->orderBy('id');
    }
}
