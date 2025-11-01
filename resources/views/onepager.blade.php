<!DOCTYPE html>
<html lang="lv">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Dokuments' }}</title>
    <style>
        @page { margin: 24mm 18mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color:#111; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        h2 { font-size: 16px; margin-top: 16px; }
        .meta { font-size: 11px; color:#555; margin-bottom: 16px; }
        .hr { height:1px; background:#ddd; margin: 10px 0; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        Sektors: {{ $sector ?? '-' }}<br>
        Izmērs: {{ $size ?? '-' }}<br>
        Piezīmes: {{ $notes ?? '-' }}
    </div>
    <div class="hr"></div>
    <div>{!! $body !!}</div>
</body>
</html>
