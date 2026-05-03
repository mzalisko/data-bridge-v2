@extends('layouts.app')

@section('title', 'Групи сайтів')

@section('content')
<div class="page-stack">

    {{-- ========= PAGE HEAD ========= --}}
    <div class="page-head">
        <div>
            <h1 class="page-head__title">Групи сайтів</h1>
            <p class="page-head__subtitle">Організуйте сайти за агентством, клієнтом або призначенням.</p>
        </div>
        <div class="page-head__actions">
            <button class="btn btn--primary btn--md" onclick="openDrawer('drawer-group-create')">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                Нова група
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert--success">{{ session('success') }}</div>
    @endif

    {{-- ========= 2-COL CARDS ========= --}}
    @if($groups->isEmpty())
        <div class="card" style="text-align:center;color:var(--text-3);padding:40px;">
            Груп ще немає. Натисніть «Нова група» для створення.
        </div>
    @else
        <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:16px;">
            @foreach($groups as $group)
                @php
                    $color = $group->color ?? '#71717a';
                    $sites = \App\Models\Site::where('group_id', $group->id)->take(4)->get();
                    $extra = $group->sites_count - $sites->count();
                @endphp
                <div class="card card--flush" style="cursor:pointer;" onclick="window.location='{{ route('site-groups.show', $group) }}'">

                    {{-- Header --}}
                    <div style="padding:18px 20px;display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                        <div style="display:flex;gap:12px;align-items:flex-start;">
                            <span style="width:38px;height:38px;border-radius:8px;flex-shrink:0;background:{{ $color }}22;color:{{ $color }};display:inline-flex;align-items:center;justify-content:center;">
                                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/>
                                    <rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>
                                </svg>
                            </span>
                            <div>
                                <div style="font-size:15px;font-weight:600;color:var(--text);">{{ $group->name }}</div>
                                @if($group->description)
                                    <div style="font-size:12px;color:var(--text-3);margin-top:2px;">{{ \Illuminate\Support\Str::limit($group->description, 60) }}</div>
                                @endif
                            </div>
                        </div>
                        <button class="icon-btn" onclick="event.stopPropagation(); openDrawer('drawer-group-{{ $group->id }}')" title="Edit">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                                <circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Stats row --}}
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);border-top:1px solid var(--border-2);">
                        <div style="padding:12px 16px;">
                            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Сайти</div>
                            <div style="font-size:18px;font-weight:600;margin-top:4px;color:var(--text);">{{ $group->sites_count }}</div>
                        </div>
                        <div style="padding:12px 16px;border-left:1px solid var(--border-2);">
                            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Активних</div>
                            <div style="font-size:18px;font-weight:600;margin-top:4px;color:var(--text);">{{ $group->activeSitesCount() ?? $group->sites->where('is_active', true)->count() }}</div>
                        </div>
                        <div style="padding:12px 16px;border-left:1px solid var(--border-2);">
                            <div style="font-size:11px;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Колір</div>
                            <div style="margin-top:6px;display:flex;align-items:center;gap:6px;">
                                <span style="width:14px;height:14px;border-radius:3px;background:{{ $color }};"></span>
                                <span style="font-size:12px;font-family:var(--font-mono);color:var(--text-3);">{{ $color }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Site chips --}}
                    <div style="border-top:1px solid var(--border-2);padding:10px 16px;" onclick="event.stopPropagation()">
                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                            @forelse($sites as $s)
                                <a href="{{ route('sites.show', $s) }}" class="site-chip">
                                    <x-favicon :name="$s->name" :size="14"/>
                                    {{ $s->url ? (parse_url($s->url, PHP_URL_HOST) ?: $s->name) : $s->name }}
                                </a>
                            @empty
                                <span style="font-size:11px;color:var(--text-3);">Сайтів ще немає</span>
                            @endforelse
                            @if($extra > 0)
                                <span style="font-size:11px;color:var(--text-3);padding:4px 8px;">+{{ $extra }} ще</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($groups->hasPages())
            <div>{{ $groups->appends(request()->query())->links() }}</div>
        @endif
    @endif
</div>

{{-- ========= CREATE DRAWER ========= --}}
<div class="drawer-overlay" id="drawer-group-create-overlay" onclick="closeDrawer('drawer-group-create')"></div>
<div class="drawer" id="drawer-group-create">
    <div class="drawer__header">
        <span class="drawer__title">Нова група</span>
        <button class="icon-btn" onclick="closeDrawer('drawer-group-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('site-groups.store') }}" class="form-stack" id="form-group-create">
            @csrf
            @include('admin.site-groups._form', ['group' => null])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-group-create')">Скасувати</button>
        <button type="submit" form="form-group-create" class="btn btn--primary btn--md">Створити</button>
    </div>
</div>

{{-- ========= EDIT DRAWERS ========= --}}
@foreach($groups as $group)
    <div class="drawer-overlay" id="drawer-group-{{ $group->id }}-overlay" onclick="closeDrawer('drawer-group-{{ $group->id }}')"></div>
    <div class="drawer" id="drawer-group-{{ $group->id }}">
        <div class="drawer__header">
            <span class="drawer__title">{{ $group->name }}</span>
            <button class="icon-btn" onclick="closeDrawer('drawer-group-{{ $group->id }}')">✕</button>
        </div>
        <div class="drawer__body">
            <form method="POST" action="{{ route('site-groups.update', $group) }}" class="form-stack" id="form-group-{{ $group->id }}">
                @csrf @method('PUT')
                @include('admin.site-groups._form', ['group' => $group])
            </form>
        </div>
        <div class="drawer__footer">
            <form method="POST" action="{{ route('site-groups.destroy', $group) }}" class="drawer__footer-left">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn--danger btn--md"
                        onclick="return confirm('Видалити групу «{{ $group->name }}»?')">Видалити</button>
            </form>
            <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-group-{{ $group->id }}')">Скасувати</button>
            <button type="submit" form="form-group-{{ $group->id }}" class="btn btn--primary btn--md">Зберегти</button>
        </div>
    </div>
@endforeach

@endsection
