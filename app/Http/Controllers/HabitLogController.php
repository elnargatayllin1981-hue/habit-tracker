<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Models\HabitLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HabitLogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Создать или обновить запись на конкретный день.
     */
    public function upsert(Request $request, Habit $habit): RedirectResponse
    {
        if ($habit->user_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $data = $request->validate([
            'log_date'         => ['required', 'date'],
            'status'           => ['required', 'in:success,fail,skipped'],
            'value'            => ['nullable', 'integer', 'min:0', 'max:100000'],
            'failure_note'     => ['nullable', 'string', 'max:2000'],
            'improvement_note' => ['nullable', 'string', 'max:2000'],
        ]);

        HabitLog::updateOrCreate(
            [
                'habit_id' => $habit->id,
                'log_date' => $data['log_date'],
            ],
            [
                'status'           => $data['status'],
                'value'            => $data['value'] ?? null,
                'failure_note'     => $data['failure_note'] ?? null,
                'improvement_note' => $data['improvement_note'] ?? null,
            ]
        );

        return redirect()
            ->route('habits.show', $habit)
            ->with('flash', 'День отмечен.');
    }

    public function destroy(Request $request, Habit $habit, HabitLog $log): RedirectResponse
    {
        if ($habit->user_id !== $request->user()->id || $log->habit_id !== $habit->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $log->delete();

        return redirect()
            ->route('habits.show', $habit)
            ->with('flash', 'Отметка удалена.');
    }
}
