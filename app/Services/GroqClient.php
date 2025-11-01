<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GroqClient
{
    private string $base = 'https://api.groq.com/openai/v1/chat/completions';

    public function chat(array $messages, ?string $model = null): array
    {
        // best free model for LV right now
        $model = $model ?? 'mixtral-8x7b-32768';

        $resp = Http::withToken(env('GROQ_API_KEY'))
            ->timeout(25)
            ->post($this->base, [
                'model' => $model,
                'messages' => $messages,
                'temperature' => 0.2,
                'max_tokens' => 2000,
            ]);

        // If Groq failed → return a clean error message
        if (!$resp->ok()) {
            return [
                'error' => true,
                'content' => "AI kļūda (Groq): " . ($resp->json('error.message') ?? 'Nezināma kļūda')
            ];
        }

        $txt = $resp->json('choices.0.message.content') ?? '';

        return [
            'error'   => false,
            'model'   => $model,
            'content' => trim($txt),
        ];
    }
}
