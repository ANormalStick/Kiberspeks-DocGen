<?php

namespace App\Http\Controllers;

use App\Services\AiProvider;
use App\Docs\PromptFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DocumentsController extends Controller
{
    public function __construct(private AiProvider $ai) {}

    public function generate(Request $request)
    {
        @set_time_limit(180);

        $validated = $request->validate([
            'type'    => 'required|string',
            'profile' => 'required|array',
            'meta'    => 'nullable|array',
            'model'   => 'nullable|string', // optional; ignored if empty
        ]);

        $type    = $validated['type'];
        $profile = $validated['profile'];
        $meta    = $validated['meta']   ?? [];
        $model   = trim((string)($validated['model'] ?? '')); // allow empty

        if (!in_array($type, ['self_assessment','isms_policy','crypto_policy','bcp_drp','asset_catalog'], true)) {
            return response()->json(['error' => 'Neatbalstīts tips'], 422);
        }

        $title = match ($type) {
            'self_assessment' => 'Pašnovērtējums',
            'isms_policy'     => 'ISMS politika',
            'crypto_policy'   => 'Šifrēšanas (kriptogrāfijas) politika',
            'bcp_drp'         => 'BCP/DRP plāns',
            'asset_catalog'   => 'Aktīvu katalogs',
        };

        try {
            $prompt = PromptFactory::make($type, $profile, $meta);

            $out = $this->ai->chat([
                ['role' => 'system', 'content' => 'Tu raksti TIK... TIKAI Markdown. Nelieto HTML un nelieto ``` žogus. Bez <br>.'],
                ['role' => 'user',   'content' => $prompt],
            ], $model /* can be empty */, [
                '_timeout'         => 90,
                '_connect_timeout' => 20,
                '_max_tokens'      => 9999,
                'temperature'      => 0.2,
            ]);

            if (($out['mock'] ?? false) || !empty($out['error'])) {
                throw new \RuntimeException($out['detail'] ?? $out['error'] ?? 'AI pakalpojuma kļūda');
            }

            $content = (string)($out['content'] ?? '');
            $content = preg_replace('/^```.*?\n|\n```$/s', '', trim($content));

            return response()->json([
                'type'    => $type,
                'title'   => $title,
                'content' => $content !== '' ? $content : "# {$title}\n\n(bez satura)",
            ]);

        } catch (\Throwable $e) {
            Log::error('AI generate failed', ['err' => $e, 'type' => $type]);

            $stub = "# {$title}\n\n## Ievads\n\n## Termini un definīcijas\n\n## Sadaļas…";

            return response()->json([
                'type'    => $type,
                'title'   => $title,
                'content' => $stub,
                'mock'    => true,
                'error'   => 'AI pakalpojuma kļūda',
                'detail'  => $e->getMessage(),
            ], 504);
        }
    }
}
