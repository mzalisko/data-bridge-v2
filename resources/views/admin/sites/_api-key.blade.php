{{-- API Key block — sidebar partial
     Variables: $site (Site with apiKey loaded)
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

    {{-- Prefix row with copy icon --}}
    @if($key)
        <div class="api-key-block__prefix-row">
            <code class="api-key-block__prefix {{ $key->isRevoked() ? 'api-key-block__prefix--revoked' : '' }}"
                  id="api-key-prefix-val"
                  data-prefix="{{ $key->key_prefix }}">{{ $key->key_prefix }}...</code>
            @if($key->isActive() && !$apiKeyRaw)
            <button class="api-key-block__icon-btn" title="Скопіювати префікс" onclick="copyApiKeyPrefix(this)">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                    <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                </svg>
            </button>
            @endif
        </div>
    @endif

    {{-- Flash: full raw key shown once after generate --}}
    @if($apiKeyRaw)
        <div class="api-key-block__flash">
            <span class="api-key-block__flash-label">Скопіюйте зараз — показується один раз</span>
            <span class="api-key-block__flash-value" id="api-key-raw-value">{{ $apiKeyRaw }}</span>
            <button class="api-key-block__copy-btn" onclick="copyApiKey(this)">📋 Скопіювати</button>
        </div>
    @endif

    @if(!$key)
        {{-- No key yet --}}
        <div class="api-key-block__no-key">Ключ не згенеровано</div>
        <form method="POST" action="{{ route('sites.api-key.generate', $site) }}">
            @csrf
            <button type="submit" class="btn-xs btn-xs--primary btn-xs--full">Згенерувати</button>
        </form>

    @elseif($key->isRevoked())
        {{-- Revoked — generate new --}}
        <form method="POST" action="{{ route('sites.api-key.generate', $site) }}">
            @csrf
            <button type="submit" class="btn-xs btn-xs--primary btn-xs--full">Згенерувати нового</button>
        </form>

    @else
        {{-- Active — icon actions: regenerate + revoke --}}
        <div class="api-key-block__actions" id="api-actions-row">
            {{-- Regenerate --}}
            <form method="POST" action="{{ route('sites.api-key.generate', $site) }}" style="flex:1;display:flex;">
                @csrf
                <button type="submit"
                        class="api-key-block__action-btn api-key-block__action-btn--ghost"
                        title="Перегенерувати ключ (старий стане недійсним)"
                        onclick="return confirm('Перегенерувати ключ? Плагін на сайті потрібно буде оновити.')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 4 23 10 17 10"/>
                        <path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"/>
                    </svg>
                </button>
            </form>
            {{-- Revoke: first click shows confirm zone --}}
            <button type="button"
                    class="api-key-block__action-btn api-key-block__action-btn--danger"
                    title="Відкликати ключ"
                    onclick="showRevokeConfirm()"
                    style="flex:1;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                </svg>
            </button>
        </div>

        {{-- Revoke confirm zone (hidden until revoke icon clicked) --}}
        <div class="api-key-block__revoke-confirm" id="api-revoke-confirm" style="display:none;">
            <span class="api-key-block__revoke-warn">Відкликати? Плагін на сайті втратить доступ до API.</span>
            <form method="POST" action="{{ route('sites.api-key.revoke', $site) }}" style="display:flex;flex-direction:column;gap:4px;">
                @csrf
                <button type="submit" class="btn-xs btn-xs--danger btn-xs--full">Так, відкликати</button>
            </form>
            <button type="button" class="btn-xs btn-xs--ghost btn-xs--full" onclick="cancelRevoke()">Скасувати</button>
        </div>
    @endif
</div>

<script>
function copyApiKeyPrefix(btn) {
    var el = document.getElementById('api-key-prefix-val');
    var val = el.dataset.prefix || el.textContent.trim().replace(/\.+$/, '');
    navigator.clipboard.writeText(val).then(function() {
        var orig = btn.innerHTML;
        btn.innerHTML = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>';
        btn.style.color = 'var(--dot-ok)';
        setTimeout(function() { btn.innerHTML = orig; btn.style.color = ''; }, 2000);
    });
}

@if($apiKeyRaw)
function copyApiKey(btn) {
    var val = document.getElementById('api-key-raw-value').textContent.trim();
    navigator.clipboard.writeText(val).then(function() {
        btn.textContent = '✓ Скопійовано';
        setTimeout(function() { btn.textContent = '📋 Скопіювати'; }, 2000);
    });
}
@endif

function showRevokeConfirm() {
    document.getElementById('api-actions-row').style.display = 'none';
    document.getElementById('api-revoke-confirm').style.display = 'flex';
}

function cancelRevoke() {
    document.getElementById('api-revoke-confirm').style.display = 'none';
    document.getElementById('api-actions-row').style.display = 'flex';
}
</script>
