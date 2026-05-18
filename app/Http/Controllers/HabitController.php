<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class HabitController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function create(): View
    {
        return view('habits.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'         => ['required', 'string', 'max:120'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'target_value'  => ['nullable', 'integer', 'min:0', 'max:100000'],
            'unit'          => ['nullable', 'string', 'max:32'],
            'color'         => ['nullable', 'string', 'max:16'],
            'start_date'    => ['nullable', 'date'],
            'duration_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'schedule'      => ['nullable', 'in:daily,weekdays,weekends,alternate'],
        ]);

        $data['user_id']       = $request->user()->id;
        $data['unit']          = $data['unit']     ?? 'минут';
        $data['color']         = $data['color']    ?? '#7c5cff';
        $data['schedule']      = $data['schedule'] ?? 'daily';
        $data['duration_days'] = $data['duration_days'] ?? 0;

        Habit::create($data);

        return redirect()
            ->route('dashboard')
            ->with('flash', 'Привычка создана.');
    }

    public function show(Request $request, Habit $habit): View
    {
        $this->authorizeHabit($habit);

        // ----- Период статистики -----
        // ?range=7|30|90|all  ИЛИ  ?from=Y-m-d&to=Y-m-d
        $range  = $request->query('range', '30');
        $today  = today();
        $start  = $habit->startDate();

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->query('from'))->startOfDay();
            $to   = Carbon::parse($request->query('to'))->startOfDay();
            if ($from->gt($to)) { [$from, $to] = [$to, $from]; }
            $rangeLabel = $from->format('d.m.Y') . ' – ' . $to->format('d.m.Y');
            $range = 'custom';
        } elseif ($range === 'all') {
            $from = $start;
            $to   = $habit->endDate() && $habit->endDate()->lt($today) ? $habit->endDate() : $today;
            $rangeLabel = 'Всё время';
        } else {
            $days = (int) $range > 0 ? (int) $range : 30;
            $from = $today->copy()->subDays($days - 1);
            $to   = $today->copy();
            $rangeLabel = "Последние {$days} дней";
        }

        $stats = $habit->statsBetween($from, $to);

        // ----- Календарь от старта до конца (или до сегодня + 7) -----
        $calStart = $start->copy();
        $calEnd   = $habit->endDate() ?? $today->copy()->addDays(7);
        if ($calEnd->lt($today)) $calEnd = $today->copy(); // если срок прошёл, всё равно показываем до сегодня

        // Для GitHub-style heatmap начинаем с понедельника недели старта
        $heatStart = $calStart->copy()->startOfWeek(Carbon::MONDAY);
        $heatEnd   = $calEnd->copy()->endOfWeek(Carbon::SUNDAY);
        $cap = (int) config('app.calendar_max_days', 365 * 2);
        if ($heatStart->diffInDays($heatEnd) > $cap) {
            $heatEnd = $heatStart->copy()->addDays($cap);
        }

        $logsByDate = $habit->logs()
            ->whereBetween('log_date', [$heatStart->toDateString(), $heatEnd->toDateString()])
            ->get()
            ->keyBy(fn ($l) => $l->log_date->toDateString());

        // Группируем по неделям: array of weeks, каждая = массив из 7 дней (Пн–Вс)
        $weeks = [];
        $cursor = $heatStart->copy();
        while ($cursor->lte($heatEnd)) {
            $week = [];
            for ($i = 0; $i < 7; $i++) {
                $date = $cursor->toDateString();
                $log  = $logsByDate->get($date);

                $isActive    = $habit->isWithinActivePeriod($cursor);
                $isScheduled = $isActive && $habit->isScheduledDay($cursor);
                $isFuture    = $cursor->gt($today);
                $isToday     = $cursor->isSameDay($today);

                $cellClass = 'off';                          // вне периода или вне расписания
                if ($isActive && $isScheduled) {
                    if ($log) {
                        $cellClass = $log->status;           // success | fail | skipped
                    } elseif ($isFuture) {
                        $cellClass = 'future';
                    } else {
                        $cellClass = 'pending';              // прошедший запланированный без отметки
                    }
                } elseif ($isActive && !$isScheduled) {
                    $cellClass = 'rest';                     // активный период, но не по расписанию
                }

                $week[] = [
                    'date'        => $date,
                    'day'         => $cursor->day,
                    'month'       => $cursor->month,
                    'class'       => $cellClass,
                    'is_today'    => $isToday,
                    'is_active'   => $isActive,
                    'is_scheduled'=> $isScheduled,
                    'is_future'   => $isFuture,
                    'log_status'  => $log?->status,
                    'tooltip'     => $cursor->format('d.m.Y') . $this->tooltipSuffix($cellClass, $log),
                ];
                $cursor->addDay();
            }
            $weeks[] = $week;
        }

        // ----- График: ежедневный за период (или агрегация если >60 дней) -----
        $chart = $this->buildChart($habit, $from, $to);

        // Последние заметки о провалах в выбранном периоде
        $failures = $habit->logs()
            ->where('status', 'fail')
            ->whereNotNull('failure_note')
            ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->latest('log_date')
            ->limit(5)
            ->get();

        return view('habits.show', compact(
            'habit', 'stats', 'chart', 'failures',
            'weeks', 'calStart', 'calEnd', 'heatStart', 'heatEnd',
            'range', 'rangeLabel', 'from', 'to'
        ));
    }

    public function edit(Habit $habit): View
    {
        $this->authorizeHabit($habit);
        return view('habits.edit', compact('habit'));
    }

    public function update(Request $request, Habit $habit): RedirectResponse
    {
        $this->authorizeHabit($habit);

        $data = $request->validate([
            'title'         => ['required', 'string', 'max:120'],
            'description'   => ['nullable', 'string', 'max:1000'],
            'target_value'  => ['nullable', 'integer', 'min:0', 'max:100000'],
            'unit'          => ['nullable', 'string', 'max:32'],
            'color'         => ['nullable', 'string', 'max:16'],
            'start_date'    => ['nullable', 'date'],
            'duration_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'schedule'      => ['nullable', 'in:daily,weekdays,weekends,alternate'],
            'is_archived'   => ['nullable', 'boolean'],
        ]);

        $habit->update($data);

        return redirect()
            ->route('habits.show', $habit)
            ->with('flash', 'Изменения сохранены.');
    }

    public function destroy(Habit $habit): RedirectResponse
    {
        $this->authorizeHabit($habit);
        $habit->delete();

        return redirect()
            ->route('dashboard')
            ->with('flash', 'Привычка удалена.');
    }

    /* ---------- helpers ---------- */

    protected function authorizeHabit(Habit $habit): void
    {
        if ($habit->user_id !== auth()->id()) {
            abort(Response::HTTP_FORBIDDEN);
        }
    }

    protected function tooltipSuffix(string $cellClass, $log): string
    {
        return match ($cellClass) {
            'success' => ' — выполнено',
            'fail'    => ' — провал',
            'skipped' => ' — форс-мажор',
            'pending' => ' — день запланирован',
            'future'  => ' — впереди',
            'rest'    => ' — выходной (не по расписанию)',
            'off'     => ' — вне периода',
            default   => '',
        };
    }

    protected function buildChart(Habit $habit, Carbon $from, Carbon $to): array
    {
        $rangeDays = $from->diffInDays($to) + 1;

        $logs = $habit->logs()
            ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->get()
            ->keyBy(fn ($l) => $l->log_date->toDateString());

        // Если период длиннее 60 дней — агрегируем по неделям, иначе по дням
        $aggregateByWeek = $rangeDays > 60;

        $labels   = [];
        $success  = [];
        $fail     = [];
        $skipped  = [];

        if ($aggregateByWeek) {
            $cursor = $from->copy()->startOfWeek(Carbon::MONDAY);
            while ($cursor->lte($to)) {
                $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY);
                $labels[] = $cursor->format('d.m');
                $s = $f = $sk = 0;
                $c2 = $cursor->copy();
                while ($c2->lte($weekEnd) && $c2->lte($to)) {
                    $log = $logs->get($c2->toDateString());
                    if ($log) {
                        if ($log->status === 'success') $s++;
                        elseif ($log->status === 'fail') $f++;
                        elseif ($log->status === 'skipped') $sk++;
                    }
                    $c2->addDay();
                }
                $success[] = $s;
                $fail[] = $f;
                $skipped[] = $sk;
                $cursor->addWeek();
            }
        } else {
            $cursor = $from->copy();
            while ($cursor->lte($to)) {
                $labels[] = $cursor->format('d.m');
                $log = $logs->get($cursor->toDateString());
                $success[] = $log && $log->status === 'success' ? 1 : 0;
                $fail[]    = $log && $log->status === 'fail'    ? 1 : 0;
                $skipped[] = $log && $log->status === 'skipped' ? 1 : 0;
                $cursor->addDay();
            }
        }

        $stats = $habit->statsBetween($from, $to);

        return [
            'labels'      => $labels,
            'success'     => $success,
            'fail'        => $fail,
            'skipped'     => $skipped,
            'aggregated'  => $aggregateByWeek,
            'pie'         => [
                'success' => $stats['success'],
                'fail'    => $stats['fail'],
                'skipped' => $stats['skipped'],
            ],
        ];
    }
}
