<?php

namespace App\Docs\Builders;

final class SelfAssessmentBuilder
{
    public static function prompt(array $profile, array $meta): string
    {
        $p = json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        $m = json_encode($meta ?? [], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        return <<<MD
Tu esi informācijas drošības konsultants. Sagatavo **Pašnovērtējumu** latviešu valodā un tikai Markdown (bez HTML, bez ``` žogiem).

## Konteksts
**Subjekta profils (JSON):**
{$p}

**Papildu meta (JSON):**
{$m}

## Uzdevums
Izveido strukturētu dokumentu ar šādām sadaļām (pielāgo uzņēmuma profilam):
1. Ievads
2. Termini un definīcijas
3. Tiesiskais ietvars un standarti (ISO/IEC 27001, NIS2 u.c., ja piemērojams)
4. Organizatoriskā drošība
5. Cilvēkresursu drošība
6. Aktīvu pārvaldība
7. Piekļuves kontrole
8. Kriptogrāfija (ja tiek lietota)
9. Fiziskā un apkārtējās vides drošība
10. Operāciju drošība (backup, žurnālfaili, ievainojamības)
11. Komunikācijas drošība
12. Iegāde, izstrāde un uzturēšana
13. Piegādes ķēdes drošība
14. Incidentu vadība
15. Darbības nepārtrauktība
16. Atbilstība un auditi
17. Secinājumi un uzlabošanas plāns (konkrēti, ar prioritātēm un termiņiem)

## Formāts
- Virsraksti ar `#`, `##`, `###`.
- Konkrētas, īstenojamas rekomendācijas; tabulas, ja tas palīdz.
- Nekādu koda žogu un HTML.
MD;
    }
}
