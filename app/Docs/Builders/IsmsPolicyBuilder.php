<?php

namespace App\Docs\Builders;

final class IsmsPolicyBuilder
{
    public static function prompt(array $profile, array $meta): string
    {
        $p = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $m = json_encode($meta ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<MD
Tu esi ISMS konsultants. Sagatavo **ISMS politiku** (augsta līmeņa) latviešu valodā, tikai Markdown.

## Profils (JSON)
{$p}

## Meta (JSON)
{$m}

## Saturam jāiekļauj
1. Mērķis un darbības joma (scope)
2. Termini un definīcijas
3. Lomas un atbildības (vadība, ISMS īpašnieks, DPO, sistēmu īpašnieki)
4. Informācijas klasifikācija
5. Riska pārvaldības principi
6. Piekļuves kontroles principi
7. Kriptogrāfijas lietošana (augstā līmenī)
8. Aktīvu pārvaldības principi
9. Fiziskā drošība
10. Operāciju drošība (backup, žurnālfaili, izmaiņu vadība)
11. Piegādātāju un trešo pušu vadība
12. Incidentu vadība un ziņošana
13. Nepārtrauktības principi
14. Atbilstība (piem. ISO 27001, NIS2, GDPR)
15. Politikas pārskatīšana un uzturēšana

## Formāts
- Skaidri virsraksti, punkti, tabulas, kur noder.
- Pielāgo FinTech/konkrētajam sektoram, ja norādīts profilā.
- Bez HTML un bez koda žogiem.
MD;
    }
}
