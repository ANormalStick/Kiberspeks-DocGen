<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiClient
{
    private string $key;

    public function __construct()
    {
        $this->key = (string) config('services.google.key');
    }

    public function chat(array $messages, ?string $model = null): array
    {
        if (!$this->key) {
            return ['error' => true, 'message' => 'Gemini error: missing GOOGLE_API_KEY'];
        }

        $model = $model ?: 'models/gemini-2.5-flash';

        // Join your messages into one prompt (simple + effective)
        $prompt = '';
        foreach ($messages as $m) {
            $role = strtoupper($m['role'] ?? 'USER');
            $text = (string) ($m['content'] ?? '');
            $prompt .= "{$role}: {$text}\n";
        }

        $url = "https://generativelanguage.googleapis.com/v1/{$model}:generateContent";

        try {
            $resp = Http::timeout(30)
                ->withQueryParameters(['key' => $this->key])   // put the key in the query!
                ->post($url, [
                    'contents' => [[
                        'role'  => 'user',
                        'parts' => [['text' => $prompt]],
                    ]],
                ]);

            if (!$resp->ok()) {
                Log::error('Gemini HTTP error', ['status' => $resp->status(), 'body' => $resp->body()]);
                $msg = $resp->json('error.message') ?? $resp->body();
                return ['error' => true, 'message' => "Gemini error: {$msg}"];
            }

            $text = $resp->json('candidates.0.content.parts.0.text') ?? '';
            return ['provider' => 'gemini', 'content' => trim($text)];
        } catch (\Throwable $e) {
            Log::error('Gemini exception', ['exception' => $e]);
            return ['error' => true, 'message' => 'Gemini error: '.$e->getMessage()];
        }
    }
}
