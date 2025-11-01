<?php

namespace App\Docs\Builders;

final class CryptoPolicyBuilder
{
    public static function prompt(array $profile, array $meta): string
    {
        $p = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $m = json_encode($meta ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<MD
Tu esi kriptogrāfijas arhitekts. Sagatavo **Šifrēšanas (kriptogrāfijas) politiku** latviski, tikai Markdown.

## Profils (JSON)
{$p}

## Meta (JSON)
{$m}

## Iekļauj
1. Ievads, mērķis, darbības joma
2. Termini un definīcijas
3. Datu klasifikācija un šifrēšanas prasības:
   - Dati miera stāvoklī (piemērs: AES-256-GCM)
   - Dati pārvadē (TLS 1.2+ vai TLS 1.3; drošas šifru kopas)
4. Atslēgu pārvaldība: ģenerēšana, glabāšana (KMS/HSM), rotācija, atsaukšana, auditēšana
5. Sertifikātu pārvaldība
6. Pieejas kontrole un pienākumu nodalīšana
7. Incidentu vadība (atslēgu kompromitācija, rotācijas plāns)
8. Atbilstība (piem. ISO 27001, PCI DSS, NIS2, GDPR)
9. Lomas un atbildības
10. Pārskatīšana un uzturēšana

## Formāts
- Praktiskas tabulas (piemēram, “algoritmi un izmantošana”, “atslēgu dzīvescikls”).
- Bez HTML un bez koda žogiem.
MD;
    }
}
