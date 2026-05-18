<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $user   = $request->user();
        $sort   = $request->query('sort', 'recent');   // recent | streak | success | progress
        $filter = $request->query('filter', 'active'); // active | today | completed | archived | all

        $query = $user->habits();
        if ($filter === 'archived') {
            $query->where('is_archived', true);
        } elseif ($filter !== 'all') {
            $query->where('is_archived', false);
        }

        $habits = $query->get()->map(function ($habit) {
            $habit->setAttribute('stats_30',       $habit->stats(30));
            $habit->setAttribute('schedule_label', $habit->scheduleLabel());
            $habit->setAttribute('days_remaining', $habit->daysRemaining());
            $habit->setAttribute('today_scheduled', $habit->isScheduledDay(today()));
            $habit->setAttribute('today_log', $habit->logs()->whereDate('log_date', today())->first());

            // Прогресс по сроку
            if ($habit->duration_days > 0) {
                $start = $habit->startDate();
                $passed = max(0, $start->diffInDays(today(), false) + 1);
                $passed = min($passed, $habit->duration_days);
                $habit->setAttribute('progress_pct', round($passed * 100 / $habit->duration_days, 1));
                $habit->setAttribute('is_completed', today()->gte($habit->endDate()));
            } else {
                $habit->setAttribute('progress_pct', null);
                $habit->setAttribute('is_completed', false);
            }
            return $habit;
        });

        // Фильтр по виду
        if ($filter === 'today') {
            $habits = $habits->filter(fn ($h) => $h->today_scheduled && $h->isWithinActivePeriod(today()))->values();
        } elseif ($filter === 'completed') {
            $habits = $habits->filter(fn ($h) => $h->is_completed)->values();
        }

        // Сортировка
        $habits = match ($sort) {
            'streak'   => $habits->sortByDesc(fn ($h) => $h->stats_30['streak'])->values(),
            'success'  => $habits->sortByDesc(fn ($h) => $h->stats_30['success_rate'])->values(),
            'progress' => $habits->sortByDesc(fn ($h) => $h->progress_pct ?? 0)->values(),
            default    => $habits->sortByDesc('created_at')->values(),
        };

        // Сводная статистика
        $summary = ['success' => 0, 'fail' => 0, 'skipped' => 0, 'total' => 0];
        foreach ($habits as $h) {
            $s = $h->stats_30;
            $summary['success'] += $s['success'];
            $summary['fail']    += $s['fail'];
            $summary['skipped'] += $s['skipped'];
            $summary['total']   += $s['total'];
        }
        $summary['success_rate'] = $summary['total'] > 0
            ? round($summary['success'] * 100 / $summary['total'], 1)
            : 0;

        // Сегодняшние задачи (отдельно от фильтра — всегда показываем)
        $allActive = $user->habits()->where('is_archived', false)->get()->map(function ($h) {
            $h->setAttribute('schedule_label', $h->scheduleLabel());
            $h->setAttribute('today_log', $h->logs()->whereDate('log_date', today())->first());
            return $h;
        });
        $todaysHabits = $allActive->filter(fn ($h) => $h->isScheduledDay(today()) && $h->isWithinActivePeriod(today()))->values();

        return view('dashboard.index', compact('habits', 'summary', 'todaysHabits', 'sort', 'filter'));
    }
}
