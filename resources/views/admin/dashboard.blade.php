@extends('layouts.app')

@section('title', 'Dashboard — DataBridge CRM')

@section('content')
<div class="page-header">
    <h1 class="page-title">Dashboard</h1>
</div>

<div class="stat-grid">
    <div class="stat-card">
        <span class="stat-card__label">Сайти</span>
        <span class="stat-card__value">—</span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Групи</span>
        <span class="stat-card__value">—</span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Синхронізацій</span>
        <span class="stat-card__value">—</span>
    </div>
    <div class="stat-card">
        <span class="stat-card__label">Користувачів</span>
        <span class="stat-card__value">—</span>
    </div>
</div>
@endsection
