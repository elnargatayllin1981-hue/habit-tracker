<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HabitLog extends Model
{
    use HasFactory;

    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAIL    = 'fail';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'habit_id',
        'log_date',
        'status',
        'value',
        'failure_note',
        'improvement_note',
    ];

    protected $casts = [
        'log_date' => 'date',
        'value'    => 'integer',
    ];

    public function habit(): BelongsTo
    {
        return $this->belongsTo(Habit::class);
    }

    public function symbol(): string
    {
        return match ($this->status) {
            self::STATUS_SUCCESS => '+',
            self::STATUS_FAIL    => '−',
            self::STATUS_SKIPPED => '∗',
            default              => '·',
        };
    }
}
