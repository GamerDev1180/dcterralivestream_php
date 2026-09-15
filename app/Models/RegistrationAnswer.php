<?php

namespace App\Models;

use Database\Factories\RegistrationAnswerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $registration_id
 * @property int $registration_question_id
 * @property string|null $answer
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['registration_id', 'registration_question_id', 'answer'])]
class RegistrationAnswer extends Model
{
    /** @use HasFactory<RegistrationAnswerFactory> */
    use HasFactory;

    /**
     * The registration this answer belongs to.
     *
     * @return BelongsTo<Registration, $this>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * The question that was answered.
     *
     * @return BelongsTo<RegistrationQuestion, $this>
     */
    public function question(): BelongsTo
    {
        return $this->belongsTo(RegistrationQuestion::class, 'registration_question_id');
    }
}
