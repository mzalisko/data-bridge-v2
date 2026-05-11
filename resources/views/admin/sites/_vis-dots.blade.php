{{--
  Geo visibility dots for item rows.
  Props: $vdItem (model), $vdIsos (array of ISO codes to render)
--}}
@php
    $vdMode    = $vdItem->geo_mode ?? 'all';
    $vdCtries  = (array) ($vdItem->geo_countries ?? []);
@endphp
@if(count($vdIsos ?? []) > 0)
    <div style="display:flex;gap:2px;align-items:center;flex-shrink:0;">
        @foreach($vdIsos as $vdIso)
            @php
                $vdVis = match($vdMode) {
                    'include' => in_array($vdIso, $vdCtries),
                    'exclude' => !in_array($vdIso, $vdCtries),
                    default   => true,
                };
            @endphp
            <span style="font-size:9px;padding:1px 5px;border-radius:3px;font-weight:700;font-family:var(--font-mono);line-height:1.6;
                         {{ $vdVis ? 'background:rgba(52,211,153,.12);color:#34d399;' : 'background:rgba(248,113,113,.12);color:#f87171;' }}">{{ $vdIso }}</span>
        @endforeach
    </div>
@endif
