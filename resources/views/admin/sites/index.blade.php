@extends('layouts.app')

@section('title', 'Сайти')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Сайти</h1>
    <button class="btn-primary" onclick="openDrawer('drawer-site-create')">
        + Новий сайт
    </button>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($sites->isEmpty())
    <div class="empty-page">
        <p>Сайтів ще немає.</p>
        <button class="btn-primary" onclick="openDrawer('drawer-site-create')">
            Додати перший сайт
        </button>
    </div>
@else
    <div class="sites-list">
        @foreach($sites as $site)
        <div class="site-card" onclick="openDrawer('drawer-site-{{ $site->id }}')">
            <span class="status-dot status-dot--{{ $site->is_active ? 'ok' : 'off' }}"></span>
            <div class="site-card__info">
                <span class="site-card__name">{{ $site->name }}</span>
                <a href="{{ $site->url }}" target="_blank" class="site-card__url" onclick="event.stopPropagation()">
                    {{ $site->url }}
                </a>
            </div>
            <div class="site-card__group">
                @if($site->siteGroup)
                    <span class="group-pill" style="--pill-color:{{ $site->siteGroup->color ?? '#706f70' }}">
                        {{ $site->siteGroup->name }}
                    </span>
                @endif
            </div>
            <span class="site-card__meta">{{ $site->created_at->format('d.m.Y') }}</span>
        </div>
        @endforeach
    </div>

    <div class="pagination-wrap">
        {{ $sites->links() }}
    </div>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-site-create-overlay" onclick="closeDrawer('drawer-site-create')"></div>
<div class="drawer" id="drawer-site-create">
    <div class="drawer__header">
        <span class="drawer__title">Новий сайт</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('sites.store') }}" class="form-stack" id="form-site-create">
            @csrf
            @include('admin.sites._form', ['site' => null, 'groups' => $groups])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-create')">Скасувати</button>
        <button type="submit" form="form-site-create" class="btn-primary">Додати</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($sites as $site)
<div class="drawer-overlay" id="drawer-site-{{ $site->id }}-overlay" onclick="closeDrawer('drawer-site-{{ $site->id }}')"></div>
<div class="drawer" id="drawer-site-{{ $site->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $site->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-site-{{ $site->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST"
              action="{{ route('sites.update', $site) }}"
              class="form-stack"
              id="form-site-{{ $site->id }}">
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
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-site-{{ $site->id }}')">Скасувати</button>
        <button type="submit" form="form-site-{{ $site->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach

@endsection
