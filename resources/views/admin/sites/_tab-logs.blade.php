<div class="tab-section">
    <div class="tab-section__header">
        <h3 class="tab-section__title">Журнал синхронізації</h3>

        <form method="GET" action="{{ route('sites.show', $site) }}" style="display:flex;gap:8px;align-items:center;">
            <input type="hidden" name="tab" value="logs">
            <select name="log_status" onchange="this.form.submit()" class="form-select form-select--sm">
                <option value="">Всі статуси</option>
                @foreach(['ok', 'error', 'no_changes'] as $st)
                    <option value="{{ $st }}" {{ $logStatus === $st ? 'selected' : '' }}>{{ $st }}</option>
                @endforeach
            </select>
            @if($logStatus)
                <a href="{{ route('sites.show', $site) }}?tab=logs" class="btn-link">Скинути</a>
            @endif
        </form>
    </div>

    @if($syncLogs->isEmpty())
        <p class="empty-state">Записів не знайдено.</p>
    @else
        <div class="data-list">
            @foreach($syncLogs as $log)
                <div class="data-row">
                    <div class="data-row__col1">
                        <span class="status-badge status-badge--{{ $log->status }}">{{ $log->status }}</span>
                    </div>
                    <div class="data-row__col2">
                        <span class="log-time">{{ $log->synced_at }}</span>
                        @if($log->duration_ms)
                            <span class="log-duration">{{ $log->duration_ms }} ms</span>
                        @endif
                    </div>
                    <div class="data-row__col3">
                        @if($log->checksum)
                            <code class="log-checksum" title="{{ $log->checksum }}">
                                {{ Str::limit($log->checksum, 20) }}
                            </code>
                        @endif
                        @if($log->error_msg)
                            <span class="log-error">{{ $log->error_msg }}</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        {{ $syncLogs->appends(['tab' => 'logs', 'log_status' => $logStatus])->links() }}
    @endif
</div>
