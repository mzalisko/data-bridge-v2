@extends('layouts.app')

@section('title', 'Користувачі')

@section('content')

<div class="page-toolbar">
    <h1 class="page-title">Користувачі</h1>
    <button class="btn-primary" onclick="openDrawer('drawer-user-create')">
        + Новий користувач
    </button>
</div>

@if(session('success'))
    <div class="alert alert--success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert alert--error">{{ session('error') }}</div>
@endif

<div class="users-list">
    @foreach($users as $user)
    <div class="user-row" onclick="openDrawer('drawer-user-{{ $user->id }}')">
        <a href="{{ route('users.permissions.show', $user) }}"
           class="perm-btn"
           title="Права доступу"
           onclick="event.stopPropagation()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
        </a>
        <div class="user-row__avatar">
            {{ strtoupper(substr($user->name, 0, 1)) }}
        </div>
        <div class="user-row__info">
            <span class="user-row__name">
                {{ $user->name }}
                @if($user->id === auth()->id())
                    <span class="user-row__you">ви</span>
                @endif
            </span>
            <span class="user-row__email">{{ $user->email }}</span>
        </div>
        <span class="role-badge role-badge--{{ $user->role }}">{{ $user->role }}</span>
        <span class="status-dot status-dot--{{ $user->is_active ? 'ok' : 'off' }}"></span>
    </div>
    @endforeach
</div>

<div class="pagination-wrap">
    {{ $users->links() }}
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
        <form method="POST"
              action="{{ route('users.update', $user) }}"
              class="form-stack"
              id="form-user-{{ $user->id }}">
            @csrf
            @method('PUT')
            @include('admin.users._form', ['user' => $user])
        </form>
    </div>
    <div class="drawer__footer">
        @if($user->id !== auth()->id())
        <form method="POST" action="{{ route('users.destroy', $user) }}" class="drawer__footer-left">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-danger"
                    onclick="return confirm('Видалити «{{ $user->name }}»?')">
                Видалити
            </button>
        </form>
        @endif
        <button type="button" class="btn-ghost" onclick="closeDrawer('drawer-user-{{ $user->id }}')">Скасувати</button>
        <button type="submit" form="form-user-{{ $user->id }}" class="btn-primary">Зберегти</button>
    </div>
</div>
@endforeach

@push('scripts')
<script>
    // Auto-generate password when create drawer opens
    document.querySelector('[onclick="openDrawer(\'drawer-user-create\')"]')
        ?.addEventListener('click', function() {
            setTimeout(function() { generatePassword('password-new'); }, 50);
        });
</script>
@endpush

@endsection
