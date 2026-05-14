@extends('layouts.app')

@section('title', 'Журнал змін')

@section('content')
@php
    $actIcons = [
        'phone'   => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.15 11a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.06 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>',
        'price'   => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
        'address' => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
        'social'  => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>',
        'field'   => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>',
        'geo'     => '<svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
    ];
    $actLabels  = ['phone'=>'Телефон','price'=>'Ціна','address'=>'Адреса','social'=>'Соцмережа','field'=>'Поле','geo'=>'Гео'];
    $actionLabel= ['create'=>'додано','update'=>'оновлено','delete'=>'видалено'];
    $fieldLabels= ['number'=>'Номер','label'=>'Мітка','geo_mode'=>'Гео-правило','geo_countries'=>'Країни','is_primary'=>'Основний','is_visible'=>'Видимий','amount'=>'Сума','currency'=>'Валюта','city'=>'Місто','street'=>'Вулиця','region'=>'Регіон','country_iso'=>'Країна','platform'=>'Платформа','handle'=>'Handle','url'=>'URL','field_key'=>'Ключ','field_value'=>'Значення','dial_code'=>'Код'];
    $skipFields = ['id','site_id','group_id','created_at','updated_at','sort_order'];
    $geoModes   = ['all'=>'Всім','include'=>'Тільки для','exclude'=>'Всім крім'];
    $tv         = fn($v) => is_array($v)
        ? implode(', ', array_map(fn($x) => $geoModes[$x] ?? $x, $v))
        : (is_bool($v) ? ($v ? 'Так' : 'Ні') : ($v === null ? '—' : ($geoModes[(string)$v] ?? (string)$v)));
@endphp

<div class="page-stack">

    {{-- PAGE HEAD --}}
    <div class="page-head">
        <div>
            <h1 class="page-head__title">Журнал змін</h1>
            <p class="page-head__subtitle">Всі дії з даними сайтів — хто, що і коли змінив.</p>
        </div>
    </div>

    {{-- SOURCE TABS --}}
    <div class="region-tabs" style="border-bottom:none;background:transparent;padding:0;">
        <a href="{{ route('logs.system') }}"   class="{{ request()->routeIs('logs.system')   ? 'is-active' : '' }}">Системні події</a>
        <a href="{{ route('logs.sync') }}"     class="{{ request()->routeIs('logs.sync')     ? 'is-active' : '' }}">Синхронізації</a>
        <a href="{{ route('logs.activity') }}" class="{{ request()->routeIs('logs.activity') ? 'is-active' : '' }}">Зміни даних</a>
    </div>

    {{-- MAIN CARD --}}
    <div class="card card--flush">

        {{-- Filter bar with custom selects --}}
        <form method="GET" id="act-filter-form" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:12px 16px;border-bottom:1px solid var(--border-2);">

            {{-- Type --}}
            <div class="cselect" id="cs-type">
                <button type="button" class="cselect__trigger" onclick="csToggle('cs-type')">
                    <span class="cselect__label">{{ $actLabels[request('entity_type')] ?? 'Всі типи' }}</span>
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="cselect__menu">
                    <div class="cselect__option {{ !request('entity_type') ? 'is-active' : '' }}" onclick="csSelect('cs-type','','Всі типи')">Всі типи</div>
                    <div class="cselect__divider"></div>
                    @foreach($actLabels as $val => $lab)
                        <div class="cselect__option {{ request('entity_type') === $val ? 'is-active' : '' }}" onclick="csSelect('cs-type','{{ $val }}','{{ $lab }}')">{{ $lab }}</div>
                    @endforeach
                </div>
                <input type="hidden" name="entity_type" value="{{ request('entity_type','') }}">
            </div>

            {{-- Action --}}
            <div class="cselect" id="cs-action">
                <button type="button" class="cselect__trigger" onclick="csToggle('cs-action')">
                    <span class="cselect__label">{{ ['create'=>'Додано','update'=>'Оновлено','delete'=>'Видалено'][request('action')] ?? 'Всі дії' }}</span>
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="cselect__menu">
                    <div class="cselect__option {{ !request('action') ? 'is-active' : '' }}" onclick="csSelect('cs-action','','Всі дії')">Всі дії</div>
                    <div class="cselect__divider"></div>
                    @foreach(['create'=>'Додано','update'=>'Оновлено','delete'=>'Видалено'] as $val => $lab)
                        <div class="cselect__option {{ request('action') === $val ? 'is-active' : '' }}" onclick="csSelect('cs-action','{{ $val }}','{{ $lab }}')">{{ $lab }}</div>
                    @endforeach
                </div>
                <input type="hidden" name="action" value="{{ request('action','') }}">
            </div>

            {{-- Source --}}
            <div class="cselect" id="cs-source">
                <button type="button" class="cselect__trigger" onclick="csToggle('cs-source')">
                    <span class="cselect__label">{{ ['crm'=>'CRM','api'=>'API','bulk'=>'Bulk'][request('source')] ?? 'Всі джерела' }}</span>
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="cselect__menu">
                    <div class="cselect__option {{ !request('source') ? 'is-active' : '' }}" onclick="csSelect('cs-source','','Всі джерела')">Всі джерела</div>
                    <div class="cselect__divider"></div>
                    <div class="cselect__option {{ request('source') === 'crm'  ? 'is-active' : '' }}" onclick="csSelect('cs-source','crm','CRM')">CRM</div>
                    <div class="cselect__option {{ request('source') === 'api'  ? 'is-active' : '' }}" onclick="csSelect('cs-source','api','API')">API</div>
                    <div class="cselect__option {{ request('source') === 'bulk' ? 'is-active' : '' }}" onclick="csSelect('cs-source','bulk','Bulk')">Bulk</div>
                </div>
                <input type="hidden" name="source" value="{{ request('source','') }}">
            </div>

            {{-- Group --}}
            <div class="cselect" id="cs-group">
                <button type="button" class="cselect__trigger" onclick="csToggle('cs-group')">
                    <span class="cselect__label">{{ $groups->firstWhere('id', request('group_id'))?->name ?? 'Всі групи' }}</span>
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="cselect__menu">
                    <div class="cselect__option {{ !request('group_id') ? 'is-active' : '' }}" onclick="csSelect('cs-group','','Всі групи')">Всі групи</div>
                    <div class="cselect__divider"></div>
                    @foreach($groups as $g)
                        <div class="cselect__option {{ request('group_id') == $g->id ? 'is-active' : '' }}" onclick="csSelect('cs-group','{{ $g->id }}','{{ $g->name }}')">{{ $g->name }}</div>
                    @endforeach
                </div>
                <input type="hidden" name="group_id" value="{{ request('group_id','') }}">
            </div>

            {{-- Site --}}
            <div class="cselect" id="cs-site">
                <button type="button" class="cselect__trigger" onclick="csToggle('cs-site')">
                    <span class="cselect__label">{{ $sites->firstWhere('id', request('site_id'))?->name ?? 'Всі сайти' }}</span>
                    <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div class="cselect__menu">
                    <div class="cselect__option {{ !request('site_id') ? 'is-active' : '' }}" onclick="csSelect('cs-site','','Всі сайти')">Всі сайти</div>
                    <div class="cselect__divider"></div>
                    @foreach($sites as $s)
                        <div class="cselect__option {{ request('site_id') == $s->id ? 'is-active' : '' }}" onclick="csSelect('cs-site','{{ $s->id }}','{{ $s->name }}')">{{ $s->name }}</div>
                    @endforeach
                </div>
                <input type="hidden" name="site_id" value="{{ request('site_id','') }}">
            </div>

            @if(request()->anyFilled(['entity_type','action','source','site_id','group_id']))
                <a href="{{ route('logs.activity') }}" class="btn btn--ghost btn--sm">✕ Скинути</a>
            @endif
            <span style="margin-left:auto;font-size:12px;color:var(--text-3);">{{ $logs->total() }} подій</span>
        </form>

        {{-- Feed --}}
        @forelse($logs as $log)
        @php
            $hasDiff    = !empty($log->snapshot['diff']) || !empty($log->snapshot['before']) || !empty($log->snapshot['after']);
            $isDelete   = $log->action === 'delete';
            $beforeData = $log->snapshot['before'] ?? null;
        @endphp
        <div class="act-row act-row--{{ $log->action }} {{ $hasDiff ? '' : 'no-diff' }}" onclick="{{ $hasDiff ? 'actToggle(this)' : '' }}">
            <div class="act-row__icon act-row__icon--{{ $log->entity_type }}">
                {!! $actIcons[$log->entity_type] ?? $actIcons['field'] !!}
            </div>
            <div class="act-row__body">
                <span class="act-row__who">{{ $log->user?->name ?? 'Система' }}</span>
                <span class="act-row__verb act-row__verb--{{ $log->action }}">{{ $actionLabel[$log->action] ?? $log->action }}</span>
                <span class="act-row__summary">{{ $log->summary }}</span>
                @if($log->site)
                    <a href="{{ route('sites.show', $log->site_id) }}?tab=activity"
                       style="font-size:11px;color:var(--text-3);text-decoration:none;margin-left:4px;"
                       onclick="event.stopPropagation()">↗ {{ $log->site->name }}</a>
                @endif
            </div>
            <div class="act-row__meta">
                <span class="act-row__when" title="{{ $log->created_at->format('d.m.Y H:i:s') }}">{{ $log->created_at->diffForHumans() }}</span>
                <span class="act-badge act-badge--{{ $log->entity_type }}">{{ $actLabels[$log->entity_type] ?? $log->entity_type }}</span>
                @if(!in_array($log->source, ['crm', null, '']))
                    <span class="act-badge act-badge--src-{{ $log->source }}">{{ strtoupper($log->source) }}</span>
                @endif
                @if($isDelete && $log->snapshot)
                    <form method="POST" action="{{ route('sites.activity.restore', [$log->site_id, $log]) }}" style="margin:0;" onsubmit="event.stopPropagation();return confirm('Відновити запис?')">
                        @csrf
                        <button type="submit" class="btn btn--ghost btn--xs">↩ Відновити</button>
                    </form>
                @endif
                @if($hasDiff)
                    <svg class="act-chevron" viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" style="color:var(--text-3);transition:transform .15s;flex-shrink:0;"><polyline points="6 9 12 15 18 9"/></svg>
                @endif
            </div>

            @if($hasDiff)
            <div class="act-diff" style="grid-column:1/-1;" onclick="event.stopPropagation()">
                @if(!empty($log->snapshot['diff']) && count($log->snapshot['diff']))
                    {{-- Update: show field diff --}}
                    <div class="act-diff__grid">
                        <div class="act-diff__hdr">Параметр</div>
                        <div class="act-diff__hdr">Було</div>
                        <div class="act-diff__hdr">Стало</div>
                        @foreach($log->snapshot['diff'] as $field => $change)
                            <div class="act-diff__key">{{ $fieldLabels[$field] ?? $field }}</div>
                            <div class="act-diff__old">{{ $tv($change['before']) }}</div>
                            <div class="act-diff__new">{{ $tv($change['after']) }}</div>
                        @endforeach
                    </div>
                @elseif($isDelete && $beforeData)
                    {{-- Delete: show snapshot as field/value table --}}
                    <div class="act-diff__grid" style="grid-template-columns:140px 1fr;">
                        <div class="act-diff__hdr">Параметр</div>
                        <div class="act-diff__hdr">Значення</div>
                        @foreach($beforeData as $field => $value)
                            @if(!in_array($field, $skipFields) && $value !== null && $value !== '' && $value !== [])
                            <div class="act-diff__key">{{ $fieldLabels[$field] ?? $field }}</div>
                            <div class="act-diff__old" style="color:var(--text-2);">{{ $tv($value) }}</div>
                            @endif
                        @endforeach
                    </div>
                @elseif(!empty($log->snapshot['after']))
                    {{-- Create: show created fields --}}
                    <div class="act-diff__grid" style="grid-template-columns:140px 1fr;">
                        <div class="act-diff__hdr">Параметр</div>
                        <div class="act-diff__hdr">Значення</div>
                        @foreach($log->snapshot['after'] as $field => $value)
                            @if(!in_array($field, $skipFields) && $value !== null && $value !== '' && $value !== [])
                            <div class="act-diff__key">{{ $fieldLabels[$field] ?? $field }}</div>
                            <div class="act-diff__new">{{ $tv($value) }}</div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
            @endif
        </div>
        @empty
            <div style="padding:48px;text-align:center;color:var(--text-3);font-size:13px;">Подій ще немає</div>
        @endforelse

        @if($logs->hasPages())
        <div style="padding:14px 20px;border-top:1px solid var(--border-2);">
            {{ $logs->links() }}
        </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
function actToggle(row) {
    var diff = row.querySelector('.act-diff');
    var chev = row.querySelector('.act-chevron');
    if (!diff) return;
    var open = row.classList.toggle('is-open');
    if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
}
</script>
@endpush
