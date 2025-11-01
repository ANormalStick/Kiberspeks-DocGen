<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\AiProvider;

class ReportController extends Controller
{
    public function policyToPdf(Request $r, AiProvider $ai)
    {
        $type   = $r->input('type','Informācijas drošības politika');
        $sector = $r->input('sector','-');
        $size   = $r->input('size','-');

        $out = $ai->chat([
            ['role' => 'system', 'content' =>
                'Tu raksti strukturētus dokumentus latviski, formāli un gramatiski pareizi. ' .
                'Nelieto markdown, HTML, un neizmanto <br>. Raksti tikai tīru tekstu.'
            ],
            ['role' => 'user', 'content' =>
                "Sagatavo dokumentu ar virsrakstu '$type' (ENISA/ISO27001 stils, virsraksti + aizzīmes). ".
                "Sektors: $sector; Izmērs: $size. Tīrs teksts, bez <br> un citām HTML zīmēm."
            ],
        ]);

        // --- CLEANUP SECTION ---
        $body = $out['content'] ?? '—';

        // Replace AI <br> & <br/> to newlines
        $body = str_replace(['<br>', '<br/>', '<br />'], "\n", $body);

        // Strip any other HTML
        $body = strip_tags($body);

        // Normalize double spaces and triple line breaks
        $body = preg_replace("/\n{3,}/", "\n\n", $body);

        // Escape & convert safe newlines to PDF line breaks
        $body = nl2br(e($body));
        // ------------------------

        $data = [
            'title'  => $type,
            'sector' => $sector,
            'size'   => $size,
            'notes'  => 'Auto ģenerēts no AI politikas',
            'body'   => $body,
        ];

        return Pdf::setOptions([
                'isHtml5ParserEnabled' => true,
                'defaultFont'          => 'DejaVu Sans',
                'dpi'                  => 96,
            ])
            ->loadView('onepager', $data)
            ->setPaper('a4', 'portrait')
            ->download('policy_onepager.pdf');
    }

    public function onePager(Request $r)
    {
        $body = $r->input('body', 'Īss kopsavilkums...');

        // Same cleanup rules
        $body = str_replace(['<br>', '<br/>', '<br />'], "\n", $body);
        $body = strip_tags($body);
        $body = preg_replace("/\n{3,}/", "\n\n", $body);
        $body = nl2br(e($body));

        $data = [
            'title'  => $r->input('type', 'Informācijas drošības politika'),
            'sector' => $r->input('sector', '-'),
            'size'   => $r->input('size', '-'),
            'notes'  => $r->input('notes', 'Auto ģenerēts'),
            'body'   => $body,
        ];

        return Pdf::setOptions([
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ])
            ->loadView('onepager', $data)
            ->setPaper('a4','portrait')
            ->download('onepager.pdf');
    }
}
