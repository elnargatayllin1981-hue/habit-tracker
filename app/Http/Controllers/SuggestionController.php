<?php

namespace App\Http\Controllers;

use App\Models\Habit;
use App\Services\HabitTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SuggestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request): View
    {
        $grouped = HabitTemplateService::templatesByCategory();

        // Какие шаблоны уже приняты — отметим
        $adoptedTitles = $request->user()->habits()
            ->where('is_archived', false)
            ->pluck('title')
            ->map(fn ($t) => mb_strtolower($t))
            ->toArray();

        foreach ($grouped as &$g) {
            foreach ($g['items'] as &$it) {
                $it['adopted'] = in_array(mb_strtolower($it['title']), $adoptedTitles);
            }
        }

        return view('suggestions.index', compact('grouped'));
    }

    public function adopt(Request $request, string $slug): RedirectResponse
    {
        $template = HabitTemplateService::find($slug);
        if (!$template) {
            return redirect()->route('suggestions.index')->with('flash', 'Шаблон не найден.');
        }

        $habit = Habit::create([
            'user_id'       => $request->user()->id,
            'title'         => $template['title'],
            'description'   => $template['description'] . "\n\n" . ($template['why'] ?? ''),
            'target_value'  => $template['target_value'],
            'unit'          => $template['unit'],
            'color'         => $template['color'],
            'start_date'    => now()->toDateString(),
            'duration_days' => $template['duration_days'],
            'schedule'      => $template['schedule'],
        ]);

        return redirect()
            ->route('habits.show', $habit)
            ->with('flash', "Привычка «{$habit->title}» принята! Старт сегодня.");
    }
}
