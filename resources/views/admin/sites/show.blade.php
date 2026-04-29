@extends('layouts.app')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/pages/sites.css') }}?v={{ filemtime(public_path('assets/css/pages/sites.css')) }}">
@endpush

@section('title', $site->name)

@section('content')

@php
    $color  = $site->siteGroup?->color ?? '#708499';
    $letter = strtoupper(substr($site->name, 0, 1));
    $syncLog  = $site->latestSyncLog;
    $syncOk   = $syncLog?->status === 'success';
    $syncColor = $syncOk ? 'var(--success)' : ($syncLog ? 'var(--warning)' : 'var(--text-3)');
@endphp

{{-- Page head --}}
<div class="page-toolbar">
    <div>
        <div class="page-subtitle" style="margin-bottom:4px;">
            <a href="{{ route('sites.index') }}" style="color:var(--text-3);text-decoration:none;">Sites</a>
            <span style="color:var(--text-3);margin:0 6px;">/</span>
            <span>{{ $site->name }}</span>
        </div>
        <h1 class="page-title" style="display:flex;align-items:center;gap:10px;">
            <span class="site-card__favicon" data-site-favicon="{{ $site->name }}"
                  style="width:28px;height:28px;font-size:11px;border-radius:8px;background:{{ $color }}22;color:{{ $color }};">
                {{ $letter }}
            </span>
            {{ $site->name }}
        </h1>
        <div class="page-subtitle" style="font-family:var(--font-mono);">{{ $site->url }}</div>
    </div>
    <div style="display:flex;align-items:center;gap:8px;">
        <a href="{{ $site->url }}" target="_blank" class="btn-ghost">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M14 4h6v6"/><path d="M20 4 10 14"/>
                <path d="M20 14v4a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h4"/>
            </svg>
            Open site
        </a>
        <button class="btn-primary" onclick="openDrawer('drawer-site-edit')">Edit site</button>
    </div>
</div>

{{-- Stats row (5 mini cards) --}}
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:20px;">
    <div class="card" style="padding:14px 16px;">
        <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Status</div>
        <div>
            <span class="status-badge {{ $site->is_active ? 'status-badge--active' : 'status-badge--disabled' }}" style="font-size:12px;">
                <span class="status-badge__dot"></span>
                {{ $site->is_active ? 'Active' : 'Disabled' }}
            </span>
        </div>
    </div>
    <div class="card" style="padding:14px 16px;">
        <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Group</div>
        <div style="font-size:14px;font-weight:600;">
            @if($site->siteGroup)
                <span class="group-pill" style="--pill-color:{{ $color }};font-size:12px;">{{ $site->siteGroup->name }}</span>
            @else
                <span style="color:var(--text-3);">—</span>
            @endif
        </div>
    </div>
    <div class="card" style="padding:14px 16px;">
        <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Phones</div>
        <div style="font-size:18px;font-weight:600;">{{ $site->phones->count() }}</div>
    </div>
    <div class="card" style="padding:14px 16px;">
        <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Last sync</div>
        <div style="font-size:13px;font-weight:600;color:{{ $syncColor }};">
            {{ $syncLog?->created_at?->diffForHumans() ?? '—' }}
        </div>
    </div>
    <div class="card" style="padding:14px 16px;">
        <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">Added</div>
        <div style="font-size:13px;font-weight:600;">{{ $site->created_at->format('d.m.Y') }}</div>
    </div>
</div>

{{-- Tab card --}}
<div class="crm-table__wrap">

    {{-- Tab bar --}}
    <div style="display:flex;border-bottom:1px solid var(--border-2);padding:0 16px;background:var(--panel);">
        @foreach([
            ['phones',    'Phones',    $site->phones->count()],
            ['prices',    'Prices',    $site->prices->count()],
            ['addresses', 'Addresses', $site->addresses->count()],
            ['socials',   'Socials',   $site->socials->count()],
        ] as [$key, $label, $count])
        <a href="{{ route('sites.show', $site) }}?tab={{ $key }}"
           style="display:inline-flex;align-items:center;gap:6px;padding:13px 14px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap;
                  border-bottom:2px solid {{ $tab === $key ? 'var(--accent)' : 'transparent' }};
                  margin-bottom:-1px;
                  color:{{ $tab === $key ? 'var(--text)' : 'var(--text-3)' }};
                  transition:color .15s;">
            {{ $label }}
            <span style="font-size:11px;font-family:var(--font-mono);color:{{ $tab === $key ? 'var(--accent)' : 'var(--text-3)' }};">{{ $count ?: '' }}</span>
        </a>
        @endforeach

        {{-- Overview tab --}}
        <a href="{{ route('sites.show', $site) }}?tab=overview"
           style="display:inline-flex;align-items:center;gap:6px;padding:13px 14px;font-size:13px;font-weight:500;text-decoration:none;white-space:nowrap;
                  border-bottom:2px solid {{ $tab === 'overview' ? 'var(--accent)' : 'transparent' }};
                  margin-bottom:-1px;
                  color:{{ $tab === 'overview' ? 'var(--text)' : 'var(--text-3)' }};
                  transition:color .15s;margin-left:auto;">
            Overview
        </a>
    </div>

    {{-- Tab content --}}
    @if($tab === 'overview')
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--border-2);">
        {{-- Site info --}}
        <div style="background:var(--panel);padding:20px;">
            <h4 style="margin:0 0 14px;font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Site info</h4>
            @php
            $kv = [
                ['Domain', $site->url, true],
                ['Group',  $site->siteGroup?->name ?? '—', false],
                ['Status', $site->is_active ? 'Active' : 'Disabled', false],
                ['Added',  $site->created_at->format('d M Y'), false],
            ];
            @endphp
            @foreach($kv as [$k, $v, $mono])
            <div style="display:grid;grid-template-columns:140px 1fr;gap:10px;padding:8px 0;font-size:13px;align-items:center;border-bottom:1px solid var(--border-2);">
                <span style="color:var(--text-3);">{{ $k }}</span>
                <span style="color:var(--text-2);{{ $mono ? 'font-family:var(--font-mono);font-size:12px;' : '' }}">{{ $v }}</span>
            </div>
            @endforeach
        </div>
        {{-- Sync / API --}}
        <div style="background:var(--panel);padding:20px;">
            <h4 style="margin:0 0 14px;font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.06em;font-weight:600;">Sync & API</h4>
            @include('admin.sites._api-key', ['site' => $site])
        </div>
    </div>
    @elseif($tab === 'phones')
        @include('admin.sites._tab-phones', ['site' => $site, 'phones' => $site->phones, 'countries' => $countries])
    @elseif($tab === 'prices')
        @include('admin.sites._tab-prices', ['site' => $site, 'prices' => $site->prices, 'countries' => $countries])
    @elseif($tab === 'addresses')
        @include('admin.sites._tab-addresses', ['site' => $site, 'addresses' => $site->addresses, 'countries' => $countries])
    @elseif($tab === 'socials')
        @include('admin.sites._tab-socials', ['site' => $site, 'socials' => $site->socials, 'countries' => $countries])
    @endif
</div>

{{-- Edit drawer --}}
<div class="drawer-overlay" id="drawer-site-edit-overlay" onclick="closeDrawer('drawer-site-edit')"></div>
<div class="drawer" id="drawer-site-edit">
    <div class="drawer__header">
        <span class="drawer__title">{{ $site->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-edit')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.update', $site) }}" class="form-stack" id="form-site-edit">
            @csrf @method('PUT')
            @include('admin.sites._form', ['site' => $site, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('sites.destroy', $site) }}" class="drawer__footer-left">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Delete site «{{ $site->name }}»?')">Delete</button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-edit')">Cancel</button>
        <button type="submit" form="form-site-edit" class="btn-primary">Save</button>
    </div>
</div>

@endsection
