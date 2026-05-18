<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Habit extends Model
{
    use HasFactory;

    public const SCHEDULE_DAILY     = 'daily';
    public const SCHEDULE_WEEKDAYS  = 'weekdays';
    public const SCHEDULE_WEEKENDS  = 'weekends';
    public const SCHEDULE_ALTERNATE = 'alternate';

    public const SCHEDULES = [
        self::SCHEDULE_DAILY     => 'Каждый день',
        self::SCHEDULE_WEEKDAYS  => 'Будни (Пн–Пт)',
        self::SCHEDULE_WEEKENDS  => 'Выходные (Сб–Вс)',
        self::SCHEDULE_ALTERNATE => 'Через день',
    ];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'target_value',
        'unit',
        'color',
        'start_date',
        'duration_days',
        'schedule',
        'is_archived',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'is_archived'   => 'boolean',
        'target_value'  => 'integer',
        'duration_days' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(HabitLog::class)->orderByDesc('log_date');
    }

    public function aiSuggestions(): HasMany
    {
        return $this->hasMany(AiSuggestion::class);
    }

    /* ---------- Расписание ---------- */

    /** Дата старта (или дата создания, если start_date не задан). */
    public function startDate(): Carbon
    {
        return ($this->start_date ?? $this->created_at)->copy()->startOfDay();
    }

    /** Дата окончания (или null, если бессрочно). */
    public function endDate(): ?Carbon
    {
        if (!$this->duration_days) return null;
        return $this->startDate()->addDays($this->duration_days - 1);
    }

    /** Сколько дней осталось до конца срока (null = бессрочно, 0 = последний день). */
    public function daysRemaining(): ?int
    {
        $end = $this->endDate();
        if (!$end) return null;
        return max(0, today()->diffInDays($end, false));
    }

    /** Запланирован ли этот день по расписанию. */
    public function isScheduledDay(Carbon $date): bool
    {
        return match ($this->schedule) {
            self::SCHEDULE_WEEKDAYS  => !$date->isWeekend(),
            self::SCHEDULE_WEEKENDS  => $date->isWeekend(),
            self::SCHEDULE_ALTERNATE => $this->startDate()->diffInDays($date) % 2 === 0,
            default                  => true, // daily
        };
    }

    /** Лежит ли дата в активном периоде привычки (от старта до конца, если задан). */
    public function isWithinActivePeriod(Carbon $date): bool
    {
        $date = $date->copy()->startOfDay();
        if ($date->lt($this->startDate())) return false;
        $end = $this->endDate();
        if ($end && $date->gt($end)) return false;
        return true;
    }

    /** Сколько запланированных дней между двумя датами включительно. */
    public function scheduledDaysCount(Carbon $from, Carbon $to): int
    {
        $count = 0;
        $cursor = $from->copy()->startOfDay();
        $end    = $to->copy()->startOfDay();
        while ($cursor->lte($end)) {
            if ($this->isWithinActivePeriod($cursor) && $this->isScheduledDay($cursor)) {
                $count++;
            }
            $cursor->addDay();
        }
        return $count;
    }

    public function scheduleLabel(): string
    {
        return self::SCHEDULES[$this->schedule] ?? $this->schedule;
    }

    /* ---------- Статистика ---------- */

    /**
     * Статистика за произвольный период.
     * expected — количество запланированных дней, прошедших к концу периода (или сегодня).
     */
    public function statsBetween(Carbon $from, Carbon $to): array
    {
        $logs = $this->logs()
            ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->get();

        $success = $logs->where('status', 'success')->count();
        $fail    = $logs->where('status', 'fail')->count();
        $skipped = $logs->where('status', 'skipped')->count();
        $total   = $success + $fail + $skipped;

        // Запланированных дней до конца периода (но не позже сегодня)
        $effectiveTo = $to->copy()->isAfter(today()) ? today() : $to->copy();
        $expected = $this->scheduledDaysCount($from, $effectiveTo);

        return [
            'success'      => $success,
            'fail'         => $fail,
            'skipped'      => $skipped,
            'total'        => $total,
            'expected'     => $expected,
            'success_rate' => $expected > 0 ? round($success * 100 / $expected, 1) : 0,
            'streak'       => $this->currentStreak(),
            'from'         => $from->toDateString(),
            'to'           => $to->toDateString(),
        ];
    }

    /** Алиас для совместимости: статистика за последние N дней. */
    public function stats(int $days = 30): array
    {
        return $this->statsBetween(today()->subDays($days - 1), today());
    }

    /** Текущий стрик: подряд идущих запланированных дней со статусом success. */
    public function currentStreak(): int
    {
        $streak = 0;
        $cursor = today();
        $byDate = $this->logs()->get()->keyBy(fn ($l) => $l->log_date->toDateString());
        $start  = $this->startDate();

        // Идём назад до старта
        while ($cursor->gte($start)) {
            // Пропускаем не-запланированные дни (например, выходные при weekdays-режиме)
            if (!$this->isScheduledDay($cursor)) {
                $cursor->subDay();
                continue;
            }
            $key = $cursor->toDateString();
            if (!isset($byDate[$key])) break;
            if ($byDate[$key]->status !== 'success') break;
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }
}
