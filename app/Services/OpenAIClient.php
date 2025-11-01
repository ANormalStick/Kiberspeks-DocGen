<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;

class OpenAIClient
{
    private string $base = 'https://api.openai.com/v1';

    public function chat(array $messages, string $model = 'gpt-4.1-mini'): array
    {
        $key = config('services.openai.key');

        if (!$key) {
            return ['mock' => true, 'content' => "Demo: nav OPENAI_API_KEY."];
        }

        try {
            $res = Http::withToken($key)
                ->timeout(30)
                ->acceptJson()
                ->asJson()
                ->post("$this->base/chat/completions", [
                    'model'    => $model,
                    'messages' => $messages,
                ]);

            if ($res->failed()) {
                return [
                    'mock' => false,
                    'error' => true,
                    'status' => $res->status(),
                    'content' => $res->body(), // raw error body from OpenAI
                ];
            }

            return [
                'mock' => false,
                'content' => data_get($res->json(), 'choices.0.message.content', ''),
            ];
        } catch (RequestException $e) {
            return [
                'mock' => false,
                'error' => true,
                'status' => $e->response?->status(),
                'content' => $e->response?->body() ?? $e->getMessage(),
            ];
        } catch (\Throwable $e) {
            return [
                'mock' => false,
                'error' => true,
                'status' => null,
                'content' => $e->getMessage(),
            ];
        }
    }
}
