@props(['status' => 'Online'])
@php
    $kind = match($status) {
        'Online', 'success' => 'success',
        'Pending', 'warning', 'Conflict' => 'warning',
        'Offline', 'Error', 'error', 'danger' => 'danger',
        default => 'neutral',
    };
    $label = ucfirst($status);
@endphp
<span class="pill pill--{{ $kind }}">
    <span class="dot dot--{{ $kind }}"></span>
    {{ $label }}
</span>
