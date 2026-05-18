<?php

namespace App\Http\Controllers;

use App\Models\HabitLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JournalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        [$from, $to, $rangeLabel, $range] = $this->resolvePeriod($request);

        $userId    = $request->user()->id;
        $statusF   = $request->query('status'); // success|fail|skipped|null
        $habitIdF  = $request->query('habit_id'); // numeric or null

        $habits = $request->user()->habits()
            ->orderBy('title')
            ->get(['id', 'title', 'color']);

        $query = HabitLog::query()
            ->whereHas('habit', fn ($q) => $q->where('user_id', $userId))
            ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->with('habit:id,title,color,unit')
            ->orderByDesc('log_date')
            ->orderBy('habit_id');

        if ($statusF && in_array($statusF, ['success', 'fail', 'skipped'])) {
            $query->where('status', $statusF);
        }
        if ($habitIdF) {
            $query->where('habit_id', (int) $habitIdF);
        }

        $logs = $query->get();

        // Группируем по дате
        $grouped = $logs->groupBy(fn ($l) => $l->log_date->toDateString())
            ->sortKeysDesc();

        $summary = [
            'success' => $logs->where('status', 'success')->count(),
            'fail'    => $logs->where('status', 'fail')->count(),
            'skipped' => $logs->where('status', 'skipped')->count(),
            'total'   => $logs->count(),
        ];

        return view('journal.index', compact(
            'grouped', 'summary', 'habits',
            'from', 'to', 'rangeLabel', 'range',
            'statusF', 'habitIdF'
        ));
    }

    /** Экспорт в CSV за тот же период/фильтры. */
    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->resolvePeriod($request);

        $userId   = $request->user()->id;
        $statusF  = $request->query('status');
        $habitIdF = $request->query('habit_id');

        $query = HabitLog::query()
            ->whereHas('habit', fn ($q) => $q->where('user_id', $userId))
            ->whereBetween('log_date', [$from->toDateString(), $to->toDateString()])
            ->with('habit:id,title,unit')
            ->orderByDesc('log_date');

        if ($statusF) $query->where('status', $statusF);
        if ($habitIdF) $query->where('habit_id', (int) $habitIdF);

        $filename = 'journal-' . $from->toDateString() . '_' . $to->toDateString() . '.csv';

        return response()->streamDownload(function () use ($query) {
            $h = fopen('php://output', 'w');
            // BOM для корректного открытия в Excel
            fwrite($h, "\xEF\xBB\xBF");
            fputcsv($h, ['Дата', 'Привычка', 'Статус', 'Значение', 'Ед.', 'Заметка о провале', 'Идея улучшения'], ';');
            foreach ($query->cursor() as $log) {
                fputcsv($h, [
                    $log->log_date->format('d.m.Y'),
                    $log->habit->title,
                    ['success' => 'Выполнено', 'fail' => 'Провал', 'skipped' => 'Форс-мажор'][$log->status] ?? $log->status,
                    $log->value,
                    $log->habit->unit,
                    $log->failure_note,
                    $log->improvement_note,
                ], ';');
            }
            fclose($h);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    /**
     * @return array{0:Carbon,1:Carbon,2:string,3:string}
     */
    protected function resolvePeriod(Request $request): array
    {
        $today = today();
        $range = $request->query('range', '30');

        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->query('from'))->startOfDay();
            $to   = Carbon::parse($request->query('to'))->startOfDay();
            if ($from->gt($to)) [$from, $to] = [$to, $from];
            return [$from, $to, $from->format('d.m.Y') . ' – ' . $to->format('d.m.Y'), 'custom'];
        }
        if ($range === 'all') {
            $from = $today->copy()->subYears(5);
            return [$from, $today->copy(), 'Всё время', 'all'];
        }
        $days = (int) $range > 0 ? (int) $range : 30;
        return [$today->copy()->subDays($days - 1), $today->copy(), "Последние {$days} дней", (string) $days];
    }
}
