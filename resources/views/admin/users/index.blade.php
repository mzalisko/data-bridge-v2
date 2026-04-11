@extends('layouts.app')

@section('title', 'Користувачі')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Користувачі</h1>
    <div style="display:flex;align-items:center;gap:var(--space-sm);">
        <div class="view-toggle">
            <button id="btn-uview-list" class="view-toggle__btn is-active" title="Список">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/>
                    <line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/>
                </svg>
            </button>
            <button id="btn-uview-grid" class="view-toggle__btn" title="Картки">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
            </button>
        </div>
        <button class="btn-primary" onclick="openDrawer('drawer-user-create')">+ Новий</button>
    </div>
</div>

{{-- Controls bar --}}
<div class="page-controls">
    <div class="page-controls__search-row">
        <div class="page-controls__search">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" class="page-controls__search-input"
                   placeholder="Пошук по імені або email…"
                   value="{{ request('search') }}" id="user-search">
        </div>
        <span class="page-controls__count">{{ $users->total() }} користувачів</span>
    </div>
    <div class="page-controls__pills">
        <a href="{{ request()->fullUrlWithQuery(['role' => null, 'status' => null, 'page' => null]) }}"
           class="filter-pill {{ !request('role') && !request('status') ? 'is-active' : '' }}">
            Всі
        </a>

        <div class="filter-pill-sep"></div>
        <a href="{{ request()->fullUrlWithQuery(['role'=>'admin','page'=>null]) }}"
           class="filter-pill {{ request('role')==='admin' ? 'is-active' : '' }}">Admin</a>
        <a href="{{ request()->fullUrlWithQuery(['role'=>'manager','page'=>null]) }}"
           class="filter-pill {{ request('role')==='manager' ? 'is-active' : '' }}">Manager</a>
        <a href="{{ request()->fullUrlWithQuery(['role'=>'editor','page'=>null]) }}"
           class="filter-pill {{ request('role')==='editor' ? 'is-active' : '' }}">Editor</a>
        <a href="{{ request()->fullUrlWithQuery(['role'=>'viewer','page'=>null]) }}"
           class="filter-pill {{ request('role')==='viewer' ? 'is-active' : '' }}">Viewer</a>

        <div class="filter-pill-sep"></div>
        <a href="{{ request()->fullUrlWithQuery(['status'=>'active','page'=>null]) }}"
           class="filter-pill {{ request('status')==='active' ? 'is-active' : '' }}">
            <span class="filter-pill__dot" style="background:var(--dot-ok)"></span> Active
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status'=>'inactive','page'=>null]) }}"
           class="filter-pill {{ request('status')==='inactive' ? 'is-active' : '' }}">
            <span class="filter-pill__dot" style="background:var(--dot-off)"></span> Disabled
        </a>

        @if(request('role') || request('status'))
            <a href="{{ request()->fullUrlWithQuery(['role' => null, 'status' => null, 'page' => null]) }}" class="filter-pill">✕ Очистити</a>
        @endif

        <select class="page-controls__sort" onchange="applyQueryParam('sort', this.value)">
            <option value="date" {{ request('sort','date')==='date' ? 'selected':'' }}>За датою ↓</option>
            <option value="name" {{ request('sort','date')==='name' ? 'selected':'' }}>За іменем A→Z</option>
            <option value="role" {{ request('sort','date')==='role' ? 'selected':'' }}>За роллю</option>
        </select>
    </div>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--error">{{ session('error') }}</div>
@endif

<div class="users-list" id="users-list">
@forelse($users as $user)

    {{-- ── List row ── --}}
    <div class="user-row" data-searchable="{{ $user->name }} {{ $user->email }} {{ $user->role }}" onclick="openDrawer('drawer-user-{{ $user->id }}')">
        <div class="user-row__avatar" style="background:{{ match($user->role){ 'admin'=>'#818cf8','manager'=>'#f59e0b','editor'=>'#34d399',default=>'#9ca3af' } }}">
            {{ mb_strtoupper(mb_substr($user->name, 0, 1, 'UTF-8'), 'UTF-8') }}
        </div>
        <div class="user-row__info">
            <span class="user-row__name">
                {{ $user->name }}
                @if($user->id===auth()->id()) <span class="user-row__you">ви</span> @endif
            </span>
            <span class="user-row__email">{{ $user->email }}</span>
        </div>
        <div class="user-row__role">
            <span class="role-badge role-badge--{{ $user->role }}">{{ $user->role }}</span>
        </div>
        <div class="user-row__status">
            <span class="status-badge status-badge--{{ $user->is_active ? 'active' : 'disabled' }}">
                <span class="status-badge__dot"></span>{{ $user->is_active ? 'Active' : 'Disabled' }}
            </span>
        </div>
        <span class="user-row__date">з {{ $user->created_at->format('d.m.Y') }}</span>
        <div class="user-row__actions" onclick="event.stopPropagation()">
            <button class="btn-icon" title="Права доступу"
                    onclick="openPermDrawer({{ $user->id }}, '{{ addslashes($user->name) }}')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </button>
            <button class="btn-icon" title="Редагувати"
                    onclick="openDrawer('drawer-user-{{ $user->id }}')">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ── Card ── --}}
    <div class="user-card" data-searchable="{{ $user->name }} {{ $user->email }} {{ $user->role }}">
        <div class="user-card__top">
            <div class="user-card__avatar" style="background:{{ match($user->role){ 'admin'=>'#818cf8','manager'=>'#f59e0b','editor'=>'#34d399',default=>'#9ca3af' } }}">
                {{ mb_strtoupper(mb_substr($user->name, 0, 1, 'UTF-8'), 'UTF-8') }}
            </div>
            <div class="user-card__info">
                <div class="user-card__name">
                    {{ $user->name }}
                    @if($user->id===auth()->id()) <span class="user-row__you">ви</span> @endif
                </div>
                <div class="user-card__email">{{ $user->email }}</div>
            </div>
        </div>

        <div class="user-card__badges">
            <span class="role-badge role-badge--{{ $user->role }}">{{ $user->role }}</span>
            <span class="status-badge status-badge--{{ $user->is_active ? 'active' : 'disabled' }}">
                <span class="status-badge__dot"></span>{{ $user->is_active ? 'Active' : 'Disabled' }}
            </span>
        </div>

        <div class="user-card__footer">
            <div class="user-card__date">з {{ $user->created_at->format('d.m.Y') }}</div>
            <div class="user-card__actions">
                <button class="btn-icon" title="Права доступу"
                        onclick="openPermDrawer({{ $user->id }}, '{{ addslashes($user->name) }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    </svg>
                </button>
                <button class="btn-icon" title="Редагувати" onclick="openDrawer('drawer-user-{{ $user->id }}')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

@empty
    <div class="empty-page" style="grid-column:1/-1;">
        <p>Користувачів не знайдено.</p>
    </div>
@endforelse
</div>

<div class="pagination-wrap">{{ $users->links() }}</div>

{{-- Permissions drawer (shared, loaded via fetch) --}}
<div class="drawer-overlay" id="drawer-perm-overlay" onclick="closeDrawer('drawer-perm')"></div>
<div class="drawer" id="drawer-perm">
    <div class="drawer__header">
        <span class="drawer__title" id="drawer-perm-title">Права доступу</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-perm')">✕</button>
    </div>
    <div class="drawer__body" id="drawer-perm-body" style="padding:0;">
        <div style="display:flex;align-items:center;justify-content:center;height:120px;color:var(--text-muted);font-size:var(--font-size-sm);">
            Виберіть користувача
        </div>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-perm')">Скасувати</button>
        <button type="button" class="btn-primary" id="btn-perm-save" onclick="savePermDrawer()">Зберегти права</button>
    </div>
</div>

{{-- Create drawer --}}
<div class="drawer-overlay" id="drawer-user-create-overlay" onclick="closeDrawer('drawer-user-create')"></div>
<div class="drawer" id="drawer-user-create">
    <div class="drawer__header">
        <span class="drawer__title">Новий користувач</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-user-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('users.store') }}" class="form-stack" id="form-user-create">
            @csrf
            @include('admin.users._form', ['user' => null])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-user-create')">Скасувати</button>
        <button type="submit" form="form-user-create" class="btn-primary">Створити</button>
    </div>
</div>

{{-- Edit drawers --}}
@foreach($users as $user)
<div class="drawer-overlay" id="drawer-user-{{ $user->id }}-overlay" onclick="closeDrawer('drawer-user-{{ $user->id }}')"></div>
<div class="drawer" id="drawer-user-{{ $user->id }}">
    <div class="drawer__header">
        <span class="drawer__title">{{ $user->name }}</span>
        <button class="btn-icon" onclick="closeDrawer('drawer-user-{{ $user->id }}')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('users.update', $user) }}" class="form-stack" id="form-user-{{ $user->id }}">
            @csrf @method('PUT')
            @include('admin.users._form', ['user' => $user])
        </form>
    </div>
    <div class="drawer__footer">
        @if($user->id !== auth()->id())
        <form method="POST" action="{{ route('users.destroy', $user) }}" class="drawer__footer-left">
            @csrf @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити «{{ $user->name }}»?')">Видалити</button>
        </form>
        @endif
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-user-{{ $user->id }}')">Скасувати</button>
        <button type="submit" form="form-user-{{ $user->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    initUserViewToggle('users-view', 'users-list', 'btn-uview-list', 'btn-uview-grid');
    initClientSearch('user-search', '.user-row, .user-card');

    // Permissions drawer
    function openPermDrawer(userId, userName) {
        document.getElementById('drawer-perm-title').textContent = 'Права: ' + userName;
        document.getElementById('drawer-perm-body').innerHTML =
            '<div style="display:flex;align-items:center;justify-content:center;height:120px;color:var(--text-muted);font-size:var(--font-size-sm);">Завантаження…</div>';
        document.getElementById('btn-perm-save').dataset.action = '/users/' + userId + '/permissions';
        openDrawer('drawer-perm');

        fetch('/users/' + userId + '/permissions/form')
            .then(function(r) { return r.text(); })
            .then(function(html) {
                document.getElementById('drawer-perm-body').innerHTML = html;
            });
    }

    function savePermDrawer() {
        var form = document.getElementById('perm-drawer-form');
        if (form) form.submit();
    }

    document.querySelector('[onclick="openDrawer(\'drawer-user-create\')"]')
        ?.addEventListener('click', function() {
            setTimeout(function() { generatePassword('password-new'); }, 50);
        });
</script>
@endpush

@endsection
