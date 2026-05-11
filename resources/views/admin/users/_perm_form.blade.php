{{-- Permissions form fragment (loaded via fetch into drawer) --}}
<form method="POST" action="{{ route('users.permissions.update', $user) }}" id="perm-drawer-form" style="padding:4px 0;">
    @csrf

    {{-- Admin notice --}}
    @if($user->isAdmin())
        <div style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:var(--accent-2);color:var(--accent-text);border-radius:var(--radius);font-size:13px;margin-bottom:18px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span><b>Роль Адмін</b> — має повний доступ до всіх ресурсів. Перемикачі нижче лише для довідки.</span>
        </div>
    @endif

    {{-- Legend / column headers --}}
    <div style="display:grid;grid-template-columns:1fr repeat(4,42px);gap:6px;align-items:center;padding:0 12px 8px;border-bottom:1px solid var(--border-2);">
        <span style="font-size:11px;font-weight:600;color:var(--text-3);text-transform:uppercase;letter-spacing:.05em;">Ресурс</span>
        @foreach([
            ['view',    'Перегляд',  '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>'],
            ['edit',    'Редагувати','<path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>'],
            ['delete',  'Видалення', '<polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/>'],
            ['api_key', 'API ключ',  '<path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5"/>'],
        ] as [$key, $title, $svgPath])
            <span title="{{ $title }}" style="display:inline-flex;align-items:center;justify-content:center;color:var(--text-3);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">{!! $svgPath !!}</svg>
            </span>
        @endforeach
    </div>

    {{-- Global row --}}
    <div style="display:grid;grid-template-columns:1fr repeat(4,42px);gap:6px;align-items:center;padding:10px 12px;background:var(--accent-2);border-radius:var(--radius);margin:8px 0;">
        <span style="display:inline-flex;align-items:center;gap:8px;font-weight:600;font-size:13px;color:var(--accent-text);">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a13 13 0 0 1 0 18M12 3a13 13 0 0 0 0 18"/></svg>
            Всі ресурси
        </span>
        @foreach(['view','edit','delete','api_key'] as $p)
            <label style="display:inline-flex;align-items:center;justify-content:center;cursor:pointer;height:24px;">
                <input type="checkbox" name="perms[global][{{ $p }}]" value="1"
                       {{ $user->isAdmin() || !empty($perms["global|{$p}"]) ? 'checked' : '' }}
                       {{ $user->isAdmin() ? 'disabled' : '' }}
                       style="accent-color:var(--accent);width:16px;height:16px;cursor:pointer;">
            </label>
        @endforeach
    </div>

    {{-- Groups + sites tree --}}
    @if($groups->isEmpty())
        <div style="padding:24px;text-align:center;color:var(--text-3);font-size:13px;">Груп та сайтів ще немає</div>
    @else
        @foreach($groups as $group)
            <div style="display:grid;grid-template-columns:1fr repeat(4,42px);gap:6px;align-items:center;padding:8px 12px;border-bottom:1px solid var(--border-2);">
                <span style="display:inline-flex;align-items:center;gap:8px;font-size:13px;font-weight:500;color:var(--text);">
                    <span style="width:10px;height:10px;border-radius:3px;background:{{ $group->color ?? '#71717a' }};flex-shrink:0;"></span>
                    {{ $group->name }}
                </span>
                @foreach(['view','edit','delete','api_key'] as $p)
                    <label style="display:inline-flex;align-items:center;justify-content:center;cursor:pointer;height:24px;">
                        <input type="checkbox" name="perms[group_{{ $group->id }}][{{ $p }}]" value="1"
                               {{ $user->isAdmin() || !empty($perms["group_{$group->id}|{$p}"]) ? 'checked' : '' }}
                               {{ $user->isAdmin() ? 'disabled' : '' }}
                               style="accent-color:var(--accent);width:16px;height:16px;cursor:pointer;">
                    </label>
                @endforeach
            </div>
            @foreach($group->sites as $site)
                <div style="display:grid;grid-template-columns:1fr repeat(4,42px);gap:6px;align-items:center;padding:6px 12px 6px 28px;font-size:12px;color:var(--text-2);">
                    <span style="display:inline-flex;align-items:center;gap:8px;">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" style="color:var(--text-3);"><path d="M9 18l6-6-6-6"/></svg>
                        <span style="font-family:var(--font-mono);font-size:11px;">{{ $site->name }}</span>
                    </span>
                    @foreach(['view','edit','delete','api_key'] as $p)
                        <label style="display:inline-flex;align-items:center;justify-content:center;cursor:pointer;height:22px;">
                            <input type="checkbox" name="perms[site_{{ $site->id }}][{{ $p }}]" value="1"
                                   {{ $user->isAdmin() || !empty($perms["site_{$site->id}|{$p}"]) ? 'checked' : '' }}
                                   {{ $user->isAdmin() ? 'disabled' : '' }}
                                   style="accent-color:var(--accent);width:14px;height:14px;cursor:pointer;">
                        </label>
                    @endforeach
                </div>
            @endforeach
        @endforeach
    @endif

    {{-- Hint --}}
    @if(!$user->isAdmin())
        <div style="margin-top:14px;padding:10px 12px;background:var(--panel-2);border-radius:var(--radius);font-size:12px;color:var(--text-3);">
            <b>Підказка:</b> «Всі ресурси» надає глобальний доступ. Чекбокси для груп/сайтів обмежують доступ до конкретного об'єкта.
        </div>
    @endif
</form>
