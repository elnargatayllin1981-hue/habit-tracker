<?php

namespace App\Services;

use App\Models\Habit;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ClaudeService
{
    protected Client $http;

    public function __construct()
    {
        $this->http = new Client([
            'base_uri' => config('services.claude.url'),
            'timeout'  => 30.0,
        ]);
    }

    /**
     * Сформировать prompt и получить рекомендации Claude по конкретной привычке.
     *
     * @return array{summary:string,response:string}
     */
    public function suggestForHabit(Habit $habit): array
    {
        $apiKey = config('services.claude.key');
        if (empty($apiKey)) {
            throw new RuntimeException('CLAUDE_API_KEY не настроен в .env');
        }

        $prompt  = $this->buildPrompt($habit);
        $payload = [
            'model'      => config('services.claude.model'),
            'max_tokens' => (int) config('services.claude.max_tokens', 600),
            'system'     => 'Ты — терпеливый коуч по формированию полезных привычек. Отвечай кратко, структурированно, на русском языке. Используй маркированные списки и конкретные действия. Не давай медицинских/финансовых советов.',
            'messages'   => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];

        try {
            $response = $this->http->post('', [
                'headers' => [
                    'x-api-key'         => $apiKey,
                    'anthropic-version' => '2023-06-01',
                    'content-type'      => 'application/json',
                ],
                'json' => $payload,
            ]);
        } catch (GuzzleException $e) {
            Log::error('Claude API error', ['error' => $e->getMessage()]);
            throw new RuntimeException('Не удалось обратиться к Claude API: ' . $e->getMessage());
        }

        $body = json_decode((string) $response->getBody(), true);
        $text = $body['content'][0]['text'] ?? '';

        if ($text === '') {
            throw new RuntimeException('Claude вернул пустой ответ.');
        }

        return [
            'summary'  => mb_substr($prompt, 0, 500),
            'response' => $text,
        ];
    }

    protected function buildPrompt(Habit $habit): string
    {
        $stats = $habit->stats(30);

        $failures = $habit->logs()
            ->where('status', 'fail')
            ->whereNotNull('failure_note')
            ->latest('log_date')
            ->limit(10)
            ->get(['log_date', 'failure_note', 'value'])
            ->map(function ($log) {
                return sprintf(
                    '— %s: %s',
                    $log->log_date->format('d.m.Y'),
                    trim((string) $log->failure_note)
                );
            })
            ->implode("\n");

        $improvements = $habit->logs()
            ->whereNotNull('improvement_note')
            ->latest('log_date')
            ->limit(5)
            ->get(['log_date', 'improvement_note'])
            ->map(function ($log) {
                return sprintf(
                    '— %s: %s',
                    $log->log_date->format('d.m.Y'),
                    trim((string) $log->improvement_note)
                );
            })
            ->implode("\n");

        $target = $habit->target_value > 0
            ? "{$habit->target_value} {$habit->unit} в день"
            : 'без числового таргета';

        $failuresBlock = $failures !== ''
            ? "Заметки о провалах:\n{$failures}\n"
            : "Заметок о провалах пока нет.\n";

        $improvementsBlock = $improvements !== ''
            ? "Идеи самого пользователя по улучшению:\n{$improvements}\n"
            : '';

        return <<<PROMPT
Помоги человеку улучшить привычку.

Привычка: {$habit->title}
Цель: {$target}
Описание: {$habit->description}

Статистика за 30 дней:
— Выполнено: {$stats['success']}
— Провалено: {$stats['fail']}
— Пропущено по форс-мажору: {$stats['skipped']}
— Текущий стрик: {$stats['streak']}
— Процент успеха: {$stats['success_rate']}%

{$failuresBlock}
{$improvementsBlock}
Сформируй ответ из 4 коротких разделов:
1) "Что я вижу" — 2–3 пункта о паттернах в провалах.
2) "Что попробовать на этой неделе" — 3 конкретных действия.
3) "Снижение порога входа" — как сделать первый шаг лёгким.
4) "Чего избегать" — 1–2 типичных ловушки исходя из заметок.

Не повторяй заметки дословно, обобщай.
PROMPT;
    }
}
