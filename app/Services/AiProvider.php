<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AiProvider
{
    /**
     * Chat interfeiss priekš AiController un DocumentsController.
     *
     * @param array       $messages  [ ['role' => 'system'|'user'|'assistant', 'content' => '...'], ... ]
     * @param string|null $model     Modelis (ja atnāk no UI). Gemini gadījumā jābūt "gemini-..."
     * @param mixed       $third     Vēsturiski te nāk $provider vai $options (no dažādiem kontrolieriem)
     *
     * @return array ['content' => string, 'mock' => bool, 'error' => ?string, 'detail' => ?string]
     */
    public function chat(array $messages, ?string $model = null, $third = null): array
    {
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return [
                'content' => '',
                'mock'    => true,
                'error'   => 'GEMINI_API_KEY nav iestatīts .env failā',
                'detail'  => null,
            ];
        }

        // 3. parametrs var būt vai nu "provider" (string), vai "options" (array)
        $options  = is_array($third) ? $third : [];
        $provider = is_string($third) ? $third : null; // šobrīd neizmantojam, viss iet uz Gemini

        $defaultModel = env('GEMINI_MODEL', 'gemini-2.5-flash');

        // Ja UI iedod kaut ko "gpt-4o-mini" vai tukšu -> ignorējam un izmantojam noklusēto Gemini
        if (!is_string($model) || !str_starts_with($model, 'gemini-')) {
            $modelId = $defaultModel;
        } else {
            $modelId = $model;
        }

        // Apvienojam system + user ziņas vienā lielā promptā
        $systemParts = [];
        $userParts   = [];

        foreach ($messages as $msg) {
            $role    = $msg['role']    ?? 'user';
            $content = (string)($msg['content'] ?? '');

            if ($role === 'system') {
                $systemParts[] = $content;
            } else {
                $userParts[] = $content;
            }
        }

        $prompt = '';
        if ($systemParts) {
            $prompt .= implode("\n\n", $systemParts) . "\n\n";
        }
        $prompt .= implode("\n\n", $userParts);

        $timeout     = $options['_timeout']         ?? 90;
        $connectTime = $options['_connect_timeout'] ?? 20;
        $maxTokens   = $options['_max_tokens']      ?? 9999;
        $temperature = $options['temperature']      ?? 0.2;

        $url = sprintf(
            'https://generativelanguage.googleapis.com/v1/models/%s:generateContent',
            $modelId
        );

        $payload = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                // cik daudz TOKENS drīkst aiziet uz redzamo tekstu
                'maxOutputTokens' => $maxTokens,      // piem., 2048 vai 4096

                'temperature'     => $temperature,
            ],
        ];

        try {
            $response = Http::withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type'   => 'application/json',
                ])
                ->timeout($timeout)
                ->connectTimeout($connectTime)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            Log::error('Gemini HTTP exception', [
                'error'   => $e->getMessage(),
                'model'   => $modelId,
            ]);

            return [
                'content' => '',
                'mock'    => true,
                'error'   => 'AI pakalpojuma kļūda (network)',
                'detail'  => $e->getMessage(),
            ];
        }

        if (!$response->successful()) {
            $body   = $response->json();
            $detail = $body['error']['message'] ?? $response->body();

            Log::error('Gemini API error', [
                'status' => $response->status(),
                'body'   => $body,
            ]);

            return [
                'content' => '',
                'mock'    => true,
                'error'   => 'AI pakalpojuma kļūda',
                'detail'  => $detail,
            ];
        }

        $data = $response->json();

        // --- Šeit ļoti stingri pārbaudām, ka ir teksta kandidāti ---
        if (empty($data['candidates'][0]['content']['parts'])) {
            Log::warning('Gemini response without candidates', ['data' => $data]);

            return [
                'content' => '',
                'mock'    => true,
                'error'   => 'AI atbilde bez teksta',
                'detail'  => json_encode($data),
            ];
        }

        $text = '';
        foreach ($data['candidates'][0]['content']['parts'] as $part) {
            if (isset($part['text'])) {
                $text .= $part['text'];
            }
        }

        $text = trim($text);

        if ($text === '') {
            Log::warning('Gemini returned empty text', ['data' => $data]);

            return [
                'content' => '',
                'mock'    => true,
                'error'   => 'AI atbilde tukša',
                'detail'  => json_encode($data),
            ];
        }

        return [
            'content' => $text,
            'mock'    => false,
            'error'   => null,
            'detail'  => null,
        ];
    }
}
