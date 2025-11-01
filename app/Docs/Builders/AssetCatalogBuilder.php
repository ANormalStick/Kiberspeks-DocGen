<?php

namespace App\Docs\Builders;

final class AssetCatalogBuilder
{
    public static function prompt(array $profile, array $meta): string
    {
        $p = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $m = json_encode($meta ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<MD
Tu esi aktīvu pārvaldības speciālists. Izveido **Aktīvu katalogu** latviešu valodā, tikai Markdown.

## Profils (JSON)
{$p}

## Meta (JSON)
{$m}

## Prasības
- Tabulē visus būtiskos aktīvus (informācija, sistēmas, lietotnes, datubāzes, serveri, galiekārtas, trešās puses, API, atslēgas).
- Lauki (piemērs): Nosaukums, Tips, Īpašnieks, Atrašanās vieta, Kritiskums, Konfidencialitāte/Integritāte/Pieejamība, Klasifikācija, Atkarības, Rezerves kopijas, Pārvaldītājs, Piezīmes.
- Pievieno īsu kopsavilkumu un uzturēšanas noteikumus.
- Bez HTML un bez koda žogiem.
MD;
    }
}
