<?php

namespace App\Docs\Builders;

final class BcpDrpBuilder
{
    public static function prompt(array $profile, array $meta): string
    {
        $p = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $m = json_encode($meta ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<MD
Tu esi nepārtrauktības/plūdu atjaunošanas speciālists. Sagatavo **BCP/DRP plānu** latviešu valodā, tikai Markdown.

## Profils (JSON)
{$p}

## Meta (JSON)
{$m}

## Sadaļas
1. Mērķis, darbības joma, pieņēmumi
2. Kritisko procesu saraksts un atkarības (cilvēki, sistēmas, piegādātāji)
3. Riska scenāriji un ietekmes novērtējums
4. RTO/RPO mērķi un atjaunošanas stratēģijas
5. Datu rezerves kopijas un atjaunošanas procedūras
6. Incidentu/pārtraukumu eskalācija un komunikācijas plāns
7. Testēšana, vingrinājumi, mācības
8. Lomas un atbildības
9. Uzturēšana un pārskatīšana

## Formāts
- Tabulas un kontrolsaraksti, kur noder.
- Bez HTML un bez koda žogiem.
MD;
    }
}
