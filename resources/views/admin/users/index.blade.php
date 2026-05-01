@extends('layouts.app')

@section('title', 'Team')

@section('content')
@php
    $rolePalette = [
        'admin'   => ['bg' => 'var(--accent-2)',  'fg' => 'var(--accent-text)', 'label' => 'Admin'],
        'manager' => ['bg' => 'var(--warning-bg)','fg' => 'var(--warning)',     'label' => 'Manager'],
        'editor'  => ['bg' => 'var(--success-bg)','fg' => 'var(--success)',     'label' => 'Editor'],
        'viewer'  => ['bg' => 'var(--panel-2)',   'fg' => 'var(--text-3)',      'label' => 'Viewer'],
    ];
    $totalCount    = \App\Models\User::count();
    $activeCount   = \App\Models\User::where('is_active', true)->count();
    $inactiveCount = $totalCount - $activeCount;
@endphp

<div class="page-stack">

    {{-- ========= PAGE HEAD ========= --}}
    <div class="page-head">
        <div>
            <h1 class="page-head__title">Team</h1>
            <p class="page-head__subtitle">{{ $totalCount }} members · {{ $activeCount }} active</p>
        </div>
        <div class="page-head__actions">
            <button class="btn btn--primary btn--md" onclick="openDrawer('drawer-user-create')">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                Invite member
            </button>
        </div>
    </div>

    @if(session('success'))<div class="alert alert--success">{{ session('success') }}</div>@endif
    @if(session('error'))  <div class="alert alert--error">{{ session('error') }}</div>@endif

    {{-- ========= MAIN CARD ========= --}}
    <div class="card card--flush">

        {{-- Toolbar --}}
        <form method="GET" style="display:flex;align-items:center;gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-2);">
            <div class="input" style="flex:1;max-width:380px;">
                <span class="input__icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/>
                    </svg>
                </span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or email…">
            </div>
            <div class="select-wrap">
                <select name="role" onchange="this.form.submit()">
                    <option value="">All roles</option>
                    @foreach($rolePalette as $key => $r)
                        <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $r['label'] }}</option>
                    @endforeach
                </select>
                <span class="select-wrap__chevron"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 9 6 6 6-6"/></svg></span>
            </div>
            <div class="select-wrap">
                <select name="status" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Disabled</option>
                </select>
                <span class="select-wrap__chevron"><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m6 9 6 6 6-6"/></svg></span>
            </div>
            <div style="flex:1"></div>
            <span style="font-size:12px;color:var(--text-3);">{{ $users->total() }} of {{ $totalCount }}</span>
        </form>

        {{-- Table --}}
        <div style="overflow:auto;">
            <table class="crm-table">
                <thead>
                    <tr>
                        <th>Member</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Added</th>
                        <th style="width:80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        @php
                            $r = $rolePalette[$user->role] ?? $rolePalette['viewer'];
                            $isMe = $user->id === auth()->id();
                            $initial = mb_strtoupper(mb_substr($user->name, 0, 1, 'UTF-8'), 'UTF-8');
                        @endphp
                        <tr onclick="openDrawer('drawer-user-{{ $user->id }}')">
                            <td>
                                <div style="display:flex;align-items:center;gap:10px;">
                                    <span class="avatar" style="width:32px;height:32px;font-size:12px;background:{{ $r['bg'] }};color:{{ $r['fg'] }};">{{ $initial }}</span>
                                    <div>
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <span style="font-weight:500;color:var(--text);">{{ $user->name }}</span>
                                            @if($isMe)<span class="pill pill--accent" style="font-size:9px;padding:1px 6px;">you</span>@endif
                                        </div>
                                        <div style="color:var(--text-3);font-size:11px;font-family:var(--font-mono);">{{ $user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="pill" style="background:{{ $r['bg'] }};color:{{ $r['fg'] }};">{{ $r['label'] }}</span>
                            </td>
                            <td>
                                @if($user->is_active)
                                    <span class="pill pill--success"><span class="dot dot--success"></span>Active</span>
                                @else
                                    <span class="pill pill--neutral"><span class="dot dot--muted"></span>Disabled</span>
                                @endif
                            </td>
                            <td style="color:var(--text-3);font-size:12px;">{{ $user->created_at->format('d M Y') }}</td>
                            <td onclick="event.stopPropagation()">
                                <div style="display:flex;gap:4px;">
                                    <button class="icon-btn" title="Permissions" onclick="openPermDrawer({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                                        </svg>
                                    </button>
                                    <button class="icon-btn" title="Edit" onclick="openDrawer('drawer-user-{{ $user->id }}')">
                                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 20h4l11-11-4-4L4 16v4z"/><path d="m13.5 6.5 4 4"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="padding:32px 20px;text-align:center;color:var(--text-3);font-size:13px;">No users match the current filters</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div>{{ $users->appends(request()->query())->links() }}</div>
        @endif
    </div>
</div>

{{-- ========= CREATE DRAWER ========= --}}
<div class="drawer-overlay" id="drawer-user-create-overlay" onclick="closeDrawer('drawer-user-create')"></div>
<div class="drawer" id="drawer-user-create">
    <div class="drawer__header">
        <span class="drawer__title">Invite team member</span>
        <button class="icon-btn" onclick="closeDrawer('drawer-user-create')">✕</button>
    </div>
    <div class="drawer__body">
        <form method="POST" action="{{ route('users.store') }}" id="form-user-create">
            @csrf
            @include('admin.users._form', ['user' => null])
        </form>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-user-create')">Cancel</button>
        <button type="submit" form="form-user-create" class="btn btn--primary btn--md">Create user</button>
    </div>
</div>

{{-- ========= EDIT DRAWERS ========= --}}
@foreach($users as $user)
    <div class="drawer-overlay" id="drawer-user-{{ $user->id }}-overlay" onclick="closeDrawer('drawer-user-{{ $user->id }}')"></div>
    <div class="drawer" id="drawer-user-{{ $user->id }}">
        <div class="drawer__header">
            <span class="drawer__title">{{ $user->name }}</span>
            <button class="icon-btn" onclick="closeDrawer('drawer-user-{{ $user->id }}')">✕</button>
        </div>
        <div class="drawer__body">
            <form method="POST" action="{{ route('users.update', $user) }}" id="form-user-{{ $user->id }}">
                @csrf @method('PUT')
                @include('admin.users._form', ['user' => $user])
            </form>
        </div>
        <div class="drawer__footer">
            @if($user->id !== auth()->id())
                <form method="POST" action="{{ route('users.destroy', $user) }}" class="drawer__footer-left" onsubmit="return confirm('Delete user «{{ $user->name }}»?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn--danger btn--md">Delete</button>
                </form>
            @endif
            <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-user-{{ $user->id }}')">Cancel</button>
            <button type="submit" form="form-user-{{ $user->id }}" class="btn btn--primary btn--md">Save</button>
        </div>
    </div>
@endforeach

{{-- ========= PERMISSIONS DRAWER (loaded via fetch) ========= --}}
<div class="drawer-overlay" id="drawer-perm-overlay" onclick="closeDrawer('drawer-perm')"></div>
<div class="drawer" id="drawer-perm">
    <div class="drawer__header">
        <span class="drawer__title" id="drawer-perm-title">Permissions</span>
        <button class="icon-btn" onclick="closeDrawer('drawer-perm')">✕</button>
    </div>
    <div class="drawer__body" id="drawer-perm-body">
        <div style="display:flex;align-items:center;justify-content:center;height:120px;color:var(--text-3);font-size:13px;">Select a user</div>
    </div>
    <div class="drawer__footer">
        <button type="button" class="btn btn--ghost btn--md" onclick="closeDrawer('drawer-perm')">Cancel</button>
        <button type="button" class="btn btn--primary btn--md" id="btn-perm-save" onclick="savePermDrawer()">Save permissions</button>
    </div>
</div>

@push('scripts')
<script>
function openPermDrawer(userId, userName) {
    document.getElementById('drawer-perm-title').textContent = 'Permissions: ' + userName;
    document.getElementById('drawer-perm-body').innerHTML =
        '<div style="display:flex;align-items:center;justify-content:center;height:120px;color:var(--text-3);font-size:13px;">Loading…</div>';
    document.getElementById('btn-perm-save').dataset.action = '/users/' + userId + '/permissions';
    openDrawer('drawer-perm');

    fetch('/users/' + userId + '/permissions/form', { headers: { 'Accept': 'text/html' } })
        .then(r => r.text())
        .then(html => { document.getElementById('drawer-perm-body').innerHTML = html; })
        .catch(() => { document.getElementById('drawer-perm-body').innerHTML = '<div style="padding:20px;color:var(--danger);">Failed to load.</div>'; });
}
function savePermDrawer() {
    var form = document.getElementById('perm-drawer-form');
    if (form) form.submit();
}
</script>
@endpush

@endsection
