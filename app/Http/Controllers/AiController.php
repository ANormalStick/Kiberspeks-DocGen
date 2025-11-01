<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AiProvider;

class AiController extends Controller
{
    public function __construct(private AiProvider $ai) {}

    /**
     * 1) Compliance AI — short tips
     */
    public function compliance(Request $r)
    {
        $provider = $r->input('provider', null);
        $model    = $r->input('model', null);

        $prompt = "Izveido TIEŠI 5 īsas, izpildāmas kiberdrošības rekomendācijas latviešu valodā (numurētas punkti). "
                . "Sektors: {$r->input('sector','-')}; Izmērs: {$r->input('size','-')}; Tech: {$r->input('stack','-')}. "
                . "NIS2 un ISO27001 konteksts.";

        $out = $this->ai->chat([
            ['role' => 'system', 'content' => 'Tu esi latviešu kiberdrošības konsultants. Atbildi konkrēti.'],
            ['role' => 'user',   'content' => $prompt],
        ], $model, $provider);

        $content = $out['content'] ?? '';

        return [
            'type'  => 'compliance',
            'items' => $content,
            'mock'  => $out['mock'] ?? false,
        ];
    }

    /**
     * 2) AI risk register — ALWAYS JSON array
     */
    public function risks(Request $r)
    {
        $provider = $r->input('provider', null);
        $model    = $r->input('model', null);
        $ctx      = $r->input('context', 'mazs IT uzņēmums; publiskais mākonis; VPN bez MFA');

        $prompt = <<<TXT
Atgriez tikai derīgu JSON masīvu (bez markdown, bez ```).
Katra objekta lauki:
- "risk": string
- "likelihood": "LOW"|"MEDIUM"|"HIGH"
- "impact": "LOW"|"MEDIUM"|"HIGH"
- "mitigation": string

Konteksts: {$ctx}
TXT;

        $out = $this->ai->chat([
            ['role' => 'system', 'content' => 'Tu atgriez TIKAI derīgu JSON masīvu. Nekādas markdown atzīmes.'],
            ['role' => 'user',   'content' => $prompt],
        ], $model, $provider);

        $text = (string)($out['content'] ?? '');
        $text = trim($text);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

        // remove ```json fences if AI added them
        $text = preg_replace('/```json/i', '', $text);
        $text = preg_replace('/```/i', '', $text);

        // Try parse as JSON
        $json = json_decode($text, true);
        if (is_array($json)) {
            if (array_is_list($json)) {
                return response()->json($json, 200, [], JSON_UNESCAPED_UNICODE);
            }
            if (isset($json['items']) && is_array($json['items'])) {
                return response()->json($json['items'], 200, [], JSON_UNESCAPED_UNICODE);
            }
            return response()->json($json, 200, [], JSON_UNESCAPED_UNICODE);
        }

        // If JSON parsing fails, fallback extractor (rare)
        $lines = preg_split('/\R+/', $text);
        $items = [];
        foreach ($lines as $line) {
            $line = trim(preg_replace('/^[-*]\s*/', '', $line));
            if (!$line) continue;

            if (preg_match('/^(.*?)\s*\(L\s*[:=]\s*([A-Z]+)\/I\s*[:=]\s*([A-Z]+)\)\s*[—-]\s*(.+)$/u', $line, $m)) {
                $items[] = [
                    'risk'       => trim($m[1]),
                    'likelihood' => strtoupper($m[2]),
                    'impact'     => strtoupper($m[3]),
                    'mitigation' => trim($m[4]),
                ];
            }
        }

        return response()->json($items, 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * 3) LV Chatbot
     */
    public function chat(Request $r)
    {
        $q        = $r->input('q', 'Kas ir NIS2?');
        $provider = $r->input('provider', null);
        $model    = $r->input('model', null);

        $out = $this->ai->chat([
            ['role' => 'system', 'content' => 'Tu atbildi īsi, skaidri un latviski.'],
            ['role' => 'user',   'content' => $q],
        ], $model, $provider);

        return [
            'reply' => $out['content'] ?? '',
            'mock'  => $out['mock'] ?? false,
        ];
    }

    /**
     * 4) Policy generator
     */
    public function policy(Request $r)
    {
        $provider = $r->input('provider', null);
        $model    = $r->input('model', null);

        $type   = $r->input('type', 'Informācijas drošības politika');
        $sector = $r->input('sector', '-');
        $size   = $r->input('size', '-');

        $prompt = "Sagatavo īsu dokumentu latviešu valodā ar virsrakstu '{$type}', "
                . "ENISA/ISO27001 stilā (virsraksti + aizzīmes). Sektors: {$sector}; Izmērs: {$size}. "
                . "Raksti tikai tekstu/Markdown bez instrukcijām.";

        $out = $this->ai->chat([
            ['role' => 'system', 'content' => 'Tu raksti strukturētus dokumentus latviski.'],
            ['role' => 'user',   'content' => $prompt],
        ], $model, $provider);

        return [
            'title'   => $type,
            'content' => $out['content'] ?? '',
            'mock'    => $out['mock'] ?? false,
        ];
    }
}
