<div class="stat-card">
    <span class="stat-card__label">{{ $label }}</span>
    <span class="stat-card__value">{{ $value }}</span>
    @if(isset($sub))
        <span class="stat-card__sub">{{ $sub }}</span>
    @endif
</div>
