@props(['status' => 'Online'])
@php
    $kind = match($status) {
        'Online', 'success', 'Онлайн' => 'success',
        'Pending', 'warning', 'Conflict', 'Очікує' => 'warning',
        'Offline', 'Error', 'error', 'danger', 'Офлайн' => 'danger',
        default => 'neutral',
    };
    $labelMap = [
        'Online'  => 'Онлайн',
        'Offline' => 'Офлайн',
        'Pending' => 'Очікує',
        'Error'   => 'Помилка',
        'success' => 'Успішно',
        'error'   => 'Помилка',
        'warning' => 'Попередження',
        'danger'  => 'Помилка',
    ];
    $label = $labelMap[$status] ?? ucfirst($status);
@endphp
<span class="pill pill--{{ $kind }}">
    <span class="dot dot--{{ $kind }}"></span>
    {{ $label }}
</span>
