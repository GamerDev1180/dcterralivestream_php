<?php

namespace App\Models;

use Database\Factories\ArchivedRecordFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * A copy of a registration or team signup from a previous event year.
 *
 * @property int $id
 * @property string $record_type
 * @property array<string, mixed> $data
 * @property int $archived_year
 * @property Carbon $archived_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['record_type', 'data', 'archived_year', 'archived_at'])]
class ArchivedRecord extends Model
{
    /** @use HasFactory<ArchivedRecordFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'archived_at' => 'datetime',
        ];
    }
}
