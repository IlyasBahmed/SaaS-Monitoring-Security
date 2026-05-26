<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GroqSecurityAnalyzer
{
    public function analyze(array $logs): ?array
    {
        if (empty($logs)) {
            return null;
        }

        $response = Http::withToken(config('services.groq.key'))
            ->timeout(30)
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => config('services.groq.model'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a cybersecurity analyst. Return ONLY valid JSON. No markdown. No explanations.',
                    ],
                    [
                        'role' => 'user',
                        'content' => json_encode([
                            'task' => '
Analyze these WordPress security logs.

Generate alerts ONLY if:
- attacks are repeated
- malicious patterns exist
- suspicious behavior is detected
- high risk activity exists
- coordinated attacks are happening

Do NOT generate alerts for:
- normal admin activity
- isolated low-risk events
- single harmless logs

If no real threat exists return:
{
  "alert": false
}
',
                            'return_schema' => [
                                'alert' => 'boolean',
                                'type' => 'string',
                                'severity' => 'low|medium|high|critical',
                                'title' => 'string',
                                'summary' => 'string',
                                'ai_score' => '0-100',
                                'evidence' => [],
                                'recommendations' => [],
                            ],
                            'logs' => $logs,
                        ]),
                    ],
                ],
                'temperature' => 0.1,
            ]);

        if (!$response->successful()) {
            \Log::error('GROQ ERROR', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $content = $response->json('choices.0.message.content');

        \Log::info('GROQ RAW RESPONSE', [
            'content' => $content,
        ]);

        $content = trim((string) $content);

        $content = str_replace('```json', '', $content);
        $content = str_replace('```', '', $content);
        $content = trim($content);

        $start = strpos($content, '{');
        $end = strrpos($content, '}');

        if ($start !== false && $end !== false) {
            $content = substr($content, $start, $end - $start + 1);
        }

        $decoded = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \Log::error('GROQ JSON ERROR', [
                'error' => json_last_error_msg(),
                'content' => $content,
            ]);

            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}