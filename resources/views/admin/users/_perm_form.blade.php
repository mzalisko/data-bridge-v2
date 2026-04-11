{{-- Permissions form fragment (loaded via fetch into drawer) --}}
<form method="POST" action="{{ route('users.permissions.update', $user) }}" id="perm-drawer-form">
    @csrf

    @if($user->isAdmin())
        <div class="perm-notice" style="margin:var(--space-md) var(--space-md) 0;">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Admin має повний доступ — обмеження ігноруються.
        </div>
    @endif

    <table class="perm-table">
        <thead>
            <tr>
                <th class="perm-th-resource">Ресурс</th>
                <th class="perm-th-action" title="Перегляд">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                    </svg>
                </th>
                <th class="perm-th-action" title="Редагування">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </th>
                <th class="perm-th-action" title="Видалення">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                        <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
                    </svg>
                </th>
                <th class="perm-th-action" title="API ключ">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/>
                    </svg>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr class="perm-row perm-row--global">
                <td class="perm-resource">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>
                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
                    </svg>
                    Всі ресурси
                </td>
                @foreach(['view','edit','delete','api_key'] as $p)
                <td class="perm-cell">
                    <input type="checkbox" name="perms[global][{{ $p }}]" value="1" class="perm-checkbox"
                           {{ $user->isAdmin() || !empty($perms["global|{$p}"]) ? 'checked' : '' }}
                           {{ $user->isAdmin() ? 'disabled' : '' }}>
                </td>
                @endforeach
            </tr>

            @foreach($groups as $group)
            <tr class="perm-row perm-row--group">
                <td class="perm-resource perm-resource--group">
                    <span class="perm-group-dot" style="background:{{ $group->color ?? '#706f70' }}"></span>
                    {{ $group->name }}
                </td>
                @foreach(['view','edit','delete','api_key'] as $p)
                <td class="perm-cell">
                    <input type="checkbox" name="perms[group_{{ $group->id }}][{{ $p }}]" value="1" class="perm-checkbox"
                           {{ $user->isAdmin() || !empty($perms["group_{$group->id}|{$p}"]) ? 'checked' : '' }}
                           {{ $user->isAdmin() ? 'disabled' : '' }}>
                </td>
                @endforeach
            </tr>
            @foreach($group->sites as $site)
            <tr class="perm-row perm-row--site">
                <td class="perm-resource perm-resource--site">
                    <span class="perm-indent">↳</span> {{ $site->name }}
                </td>
                @foreach(['view','edit','delete','api_key'] as $p)
                <td class="perm-cell">
                    <input type="checkbox" name="perms[site_{{ $site->id }}][{{ $p }}]" value="1" class="perm-checkbox"
                           {{ $user->isAdmin() || !empty($perms["site_{$site->id}|{$p}"]) ? 'checked' : '' }}
                           {{ $user->isAdmin() ? 'disabled' : '' }}>
                </td>
                @endforeach
            </tr>
            @endforeach
            @endforeach
            
            @if($groups->isEmpty())
            <tr><td colspan="5" class="perm-empty">Групи та сайти ще не додані</td></tr>
            @endif
        </tbody>
    </table>
</form>
