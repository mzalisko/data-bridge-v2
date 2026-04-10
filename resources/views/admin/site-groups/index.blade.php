@extends('layouts.app')

@section('title', 'Групи сайтів')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Групи сайтів</h1>
    <button class="btn-primary" onclick="openDrawer('drawer-group-create')">
        + Нова група
    </button>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif

@if($groups->isEmpty())
    <div class="empty-page">
        <p>Груп ще немає. Натисніть «+ Нова група» щоб розпочати.</p>
    </div>
@else
    <div class="group-grid">
        @foreach($groups as $group)
        <div class="group-card" onclick="window.location='{{ route('site-groups.show', $group) }}'"
             style="cursor:pointer;">
            <div class="group-card__actions" onclick="event.stopPropagation()">
                <button class="btn-icon" style="position:absolute;top:12px;right:12px;"
                        onclick="openDrawer('drawer-group-{{ $group->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </button>
            </div>
            <div class="group-card__header">
                <span class="group-card__dot" style="background:{{ $group->color ?? '#706f70' }}"></span>
                <span class="group-card__name">{{ $group->name }}</span>
            </div>
            @if($group->description)
                <p class="group-card__desc">{{ Str::limit($group->description, 80) }}</p>
            @endif
            <span class="group-card__count">{{ $group->sites_count }} сайтів</span>
        </div>
        @endforeach
    </div>

    <div class="pagination-wrap">
        {{ $groups->links() }}
    </div>
@endif

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-group-create-overlay" onclick="closeDrawer('drawer-group-create')"></div>
<div class="drawer" id="drawer-group-create">
    <div class="drawer__header">
        <span class="drawer__title">Нова група</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-group-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('site-groups.store') }}" class="form-stack" id="form-group-create">
            @csrf
            @include('admin.site-groups._form', ['group' => null])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-group-create')">Скасувати</button>
        <button type="submit" form="form-group-create" class="btn-primary">Створити</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($groups as $group)
<div class="drawer-overlay" id="drawer-group-{{ $group->id }}-overlay" onclick="closeDrawer('drawer-group-{{ $group->id }}')"></div>
<div class="drawer" id="drawer-group-{{ $group->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $group->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-group-{{ $group->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST"
              action="{{ route('site-groups.update', $group) }}"
              class="form-stack"
              id="form-group-{{ $group->id }}">
            @csrf
            @method('PUT')
            @include('admin.site-groups._form', ['group' => $group])
        </form>
    </div>
    <div class="drawer__footer">
        <form method="POST" action="{{ route('site-groups.destroy', $group) }}" class="drawer__footer-left">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити групу «{{ $group->name }}»?')">
                Видалити
            </button>
        </form>
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-group-{{ $group->id }}')">Скасувати</button>
        <button type="submit" form="form-group-{{ $group->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach

@endsection
