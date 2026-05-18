<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Финальный отчёт по привычке (доступен в любой момент, но особенно полезен после окончания). */
    public function show(Request $request, Habit $habit): View
    {
        if ($habit->user_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $start = $habit->startDate();
        $end   = $habit->endDate() ?? today();
        if ($end->gt(today())) $end = today();

        $stats = $habit->statsBetween($start, $end);

        $logs = $habit->logs()
            ->whereBetween('log_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn ($l) => $l->log_date->toDateString());

        // ----- Расчёт максимального стрика -----
        $longestStreak = 0;
        $currentRun    = 0;
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if (!$habit->isScheduledDay($cursor)) {
                $cursor->addDay();
                continue;
            }
            $log = $logs->get($cursor->toDateString());
            if ($log && $log->status === 'success') {
                $currentRun++;
                $longestStreak = max($longestStreak, $currentRun);
            } else {
                $currentRun = 0;
            }
            $cursor->addDay();
        }

        // ----- Лучший / худший день недели -----
        $weekdayStats = [];
        for ($d = 1; $d <= 7; $d++) {
            $weekdayStats[$d] = ['success' => 0, 'fail' => 0, 'skipped' => 0, 'expected' => 0];
        }
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            if ($habit->isScheduledDay($cursor)) {
                $dow = $cursor->dayOfWeekIso; // 1=Mon..7=Sun
                $weekdayStats[$dow]['expected']++;
                $log = $logs->get($cursor->toDateString());
                if ($log) {
                    $weekdayStats[$dow][$log->status]++;
                }
            }
            $cursor->addDay();
        }
        foreach ($weekdayStats as &$w) {
            $w['rate'] = $w['expected'] > 0 ? round($w['success'] * 100 / $w['expected'], 1) : null;
        }
        unset($w);

        $weekdayNames = [
            1 => 'Понедельник', 2 => 'Вторник', 3 => 'Среда', 4 => 'Четверг',
            5 => 'Пятница', 6 => 'Суббота', 7 => 'Воскресенье',
        ];

        $ranked = collect($weekdayStats)
            ->filter(fn ($v) => $v['rate'] !== null)
            ->sortByDesc('rate');
        $bestDow  = $ranked->keys()->first();
        $worstDow = $ranked->keys()->last();

        // ----- Ачивки -----
        $achievements = [];

        if ($stats['success_rate'] >= 80) {
            $achievements[] = ['icon' => '🏆', 'title' => 'Чемпион', 'desc' => 'Успех 80% или выше'];
        } elseif ($stats['success_rate'] >= 50) {
            $achievements[] = ['icon' => '🥈', 'title' => 'Стабильный путь', 'desc' => 'Успех 50% или выше'];
        }
        if ($longestStreak >= 21) {
            $achievements[] = ['icon' => '🔥', 'title' => '21-дневный стрик', 'desc' => 'Привычка автоматизирована'];
        } elseif ($longestStreak >= 7) {
            $achievements[] = ['icon' => '💪', 'title' => 'Недельный стрик', 'desc' => 'Семь дней подряд'];
        }
        if ($stats['success'] >= 30) {
            $achievements[] = ['icon' => '🌟', 'title' => '30 побед', 'desc' => 'Выполнено 30+ раз'];
        }
        if ($habit->duration_days > 0 && today()->gte($habit->endDate())) {
            $achievements[] = ['icon' => '✅', 'title' => 'Финиш', 'desc' => 'Дошли до конца срока'];
        }
        if ($stats['fail'] === 0 && $stats['success'] > 0) {
            $achievements[] = ['icon' => '💎', 'title' => 'Без провалов', 'desc' => 'Ни одного — ' . $stats['fail']];
        }

        // ----- Топ-5 провалов с заметками -----
        $topFailures = $habit->logs()
            ->where('status', 'fail')
            ->whereNotNull('failure_note')
            ->whereBetween('log_date', [$start->toDateString(), $end->toDateString()])
            ->latest('log_date')
            ->limit(5)
            ->get();

        // Завершена ли уже привычка
        $isCompleted = $habit->endDate() !== null && today()->gte($habit->endDate());

        return view('habits.report', compact(
            'habit', 'stats', 'longestStreak', 'weekdayStats', 'weekdayNames',
            'bestDow', 'worstDow', 'achievements', 'topFailures',
            'start', 'end', 'isCompleted'
        ));
    }
}
