{{-- API Key block — sidebar partial
     Variables: $site (Site with apiKey loaded), $apiKeyRaw (string|null from flash)
--}}

@php
    $key       = $site->apiKey;
    $apiKeyRaw = session('api_key_raw');
@endphp

<div class="api-key-block">
    <div class="api-key-block__header">
        <span class="api-key-block__label">API Key</span>

        @if($key && $key->isActive())
            <span class="api-key-block__status">
                <span class="api-key-block__dot api-key-block__dot--ok"></span>
                <span style="color:var(--dot-ok)">active</span>
            </span>
        @elseif($key && $key->isRevoked())
            <span class="api-key-block__status">
                <span class="api-key-block__dot api-key-block__dot--off"></span>
                <span style="color:var(--dot-off)">revoked</span>
            </span>
        @else
            <span class="api-key-block__status">
                <span class="api-key-block__dot api-key-block__dot--none"></span>
                <span style="color:var(--text-muted)">немає</span>
            </span>
        @endif
    </div>

    @if($key)
        <div class="api-key-block__prefix {{ $key->isRevoked() ? 'api-key-block__prefix--revoked' : '' }}">
            {{ $key->key_prefix }}...
        </div>
    @endif

    @if($apiKeyRaw)
        <div class="api-key-block__flash">
            <span class="api-key-block__flash-label">Скопіюйте зараз</span>
            <span class="api-key-block__flash-value" id="api-key-raw-value">{{ $apiKeyRaw }}</span>
            <button class="api-key-block__copy-btn" onclick="copyApiKey(this)">📋 Скопіювати</button>
        </div>
    @endif

    @if(!$key)
        <div class="api-key-block__no-key">Ключ не згенеровано</div>
        <form method="POST" action="{{ route('sites.api-key.generate', $site) }}">
            @csrf
            <button type="submit" class="btn-xs btn-xs--primary btn-xs--full">Згенерувати</button>
        </form>

    @elseif($key->isRevoked())
        <form method="POST" action="{{ route('sites.api-key.generate', $site) }}">
            @csrf
            <button type="submit" class="btn-xs btn-xs--primary btn-xs--full">Згенерувати нового</button>
        </form>

    @else
        <div class="api-key-block__btn-row">
            <form method="POST" action="{{ route('sites.api-key.generate', $site) }}">
                @csrf
                <button type="submit" class="btn-xs btn-xs--ghost"
                        onclick="return confirm('Перегенерувати ключ? Старий стане недійсним.')">
                    Перегенерувати
                </button>
            </form>
            <form method="POST" action="{{ route('sites.api-key.revoke', $site) }}">
                @csrf
                <button type="submit" class="btn-xs btn-xs--danger"
                        onclick="return confirm('Відкликати ключ?')">
                    Відкликати
                </button>
            </form>
        </div>
    @endif
</div>

@if($apiKeyRaw)
<script>
function copyApiKey(btn) {
    const val = document.getElementById('api-key-raw-value').textContent.trim();
    navigator.clipboard.writeText(val).then(() => {
        btn.textContent = '✓ Скопійовано';
        setTimeout(() => btn.textContent = '📋 Скопіювати', 2000);
    });
}
</script>
@endif
