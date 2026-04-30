@props(['name' => '?', 'size' => 22])
@php
    $h = 0;
    foreach (str_split($name) as $ch) { $h = ($h * 31 + ord($ch)) % 360; }
    $letter = mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
    $fontSize = round($size * 0.5);
@endphp
<span class="favicon" style="width:{{ $size }}px;height:{{ $size }}px;font-size:{{ $fontSize }}px;background:oklch(0.94 0.04 {{ $h }});color:oklch(0.4 0.1 {{ $h }});">{{ $letter }}</span>
