@extends('layouts.app')

@section('title', $site->name)

@section('content')

<div class="page-toolbar">
    <div style="display:flex;align-items:center;gap:var(--space-md);">
        <a href="{{ route('sites.index') }}" class="btn-ghost">← Сайти</a>
        <span class="status-dot status-dot--{{ $site->is_active ? 'ok' : 'off' }}"></span>
        <h1 class="page-title">{{ $site->name }}</h1>
        @if($site->siteGroup)
            <span class="group-pill" style="--pill-color:{{ $site->siteGroup->color ?? '#706f70' }}">
                {{ $site->siteGroup->name }}
            </span>
        @endif
    </div>
    <div style="display:flex;gap:var(--space-sm);">
        <a href="{{ $site->url }}" target="_blank" class="btn-ghost">↗ Відкрити</a>
        <button class="btn-primary" onclick="openDrawer('drawer-site-edit')">Редагувати</button>
    </div>
</div>

{{-- Info cards --}}
<div class="stat-grid" style="margin-bottom:var(--space-lg);">
    <x-stat-card label="URL" :value="parse_url($site->url, PHP_URL_HOST)" />
    <x-stat-card label="Статус" :value="$site->is_active ? 'Активний' : 'Зупинено'" />
    <x-stat-card label="Група" :value="$site->siteGroup?->name ?? '—'" />
    <x-stat-card label="Додано" :value="$site->created_at->format('d.m.Y')" />
</div>

@if($site->description)
<div class="card" style="margin-bottom:var(--space-lg);">
    <p style="font-size:var(--font-size-sm);color:var(--text-secondary);line-height:1.6;">{{ $site->description }}</p>
</div>
@endif

{{-- Tabs placeholder (L011) --}}
<div class="card">
    <p style="color:var(--text-muted);font-size:var(--font-size-sm);text-align:center;padding:var(--space-lg) 0;">
        Вкладки (телефони, ціни, адреси) — будуть доступні в задачі L011
    </p>
</div>

{{-- Edit drawer --}}
<div class="drawer-overlay" id="drawer-site-edit-overlay" onclick="closeDrawer('drawer-site-edit')"></div>
<div class="drawer" id="drawer-site-edit">
    <div class="drawer__header">
        <span class="drawer__title">{{ $site->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-edit')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST"
              action="{{ route('sites.update', $site) }}"
              class="form-stack"
              id="form-site-edit">
            @csrf
            @method('PUT')
            @include('admin.sites._form', ['site' => $site, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('sites.destroy', $site) }}" class="drawer__footer-left">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити сайт «{{ $site->name }}»?')">
                Видалити
            </button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-edit')">Скасувати</button>
        <button type="submit" form="form-site-edit" class="btn-primary">Зберегти</button>
    </div>
</div>

@endsection
