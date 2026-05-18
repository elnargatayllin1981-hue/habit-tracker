<?php

namespace App\Http\Controllers;

use App\Models\AiSuggestion;
use App\Models\Habit;
use App\Services\ClaudeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;

class AiSuggestionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function generate(Request $request, Habit $habit, ClaudeService $claude): JsonResponse
    {
        if ($habit->user_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        try {
            $result = $claude->suggestForHabit($habit);
        } catch (RuntimeException $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage(),
            ], 422);
        }

        $suggestion = AiSuggestion::create([
            'user_id'        => $request->user()->id,
            'habit_id'       => $habit->id,
            'prompt_summary' => $result['summary'],
            'response'       => $result['response'],
        ]);

        return response()->json([
            'ok'         => true,
            'id'         => $suggestion->id,
            'response'   => $suggestion->response,
            'created_at' => $suggestion->created_at->format('d.m.Y H:i'),
        ]);
    }

    public function history(Request $request, Habit $habit): JsonResponse
    {
        if ($habit->user_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $items = $habit->aiSuggestions ?? AiSuggestion::where('habit_id', $habit->id)
            ->where('user_id', $request->user()->id)
            ->latest()
            ->limit(10)
            ->get();

        return response()->json([
            'ok'    => true,
            'items' => $items->map(fn ($s) => [
                'id'         => $s->id,
                'response'   => $s->response,
                'created_at' => $s->created_at->format('d.m.Y H:i'),
            ]),
        ]);
    }
}
