@php $u = $user ?? null; $uid = $u?->id ?? 'new'; @endphp

<div class="field">
    <label class="field__label" for="u-name-{{ $uid }}">Full name</label>
    <input type="text" id="u-name-{{ $uid }}" name="name" class="field__input" required value="{{ old('name', $u?->name) }}">
    @error('name')<span style="font-size:12px;color:var(--danger);">{{ $message }}</span>@enderror
</div>

<div class="field">
    <label class="field__label" for="u-email-{{ $uid }}">Email</label>
    <input type="email" id="u-email-{{ $uid }}" name="email" class="field__input" required value="{{ old('email', $u?->email) }}">
    @error('email')<span style="font-size:12px;color:var(--danger);">{{ $message }}</span>@enderror
</div>

<div class="field">
    <label class="field__label" for="u-pass-{{ $uid }}">
        Password {{ $u ? '(leave blank to keep current)' : '' }}
    </label>
    <div style="display:flex;gap:6px;">
        <input type="password" id="u-pass-{{ $uid }}" name="password" class="field__input"
               {{ $u ? '' : 'required' }} autocomplete="new-password" placeholder="min 8 chars" style="flex:1;">
        <button type="button" class="btn btn--secondary btn--md" title="Generate" onclick="(function(i){var c='ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789',s='';for(var x=0;x<14;x++)s+=c[Math.floor(Math.random()*c.length)];i.type='text';i.value=s;})(document.getElementById('u-pass-{{ $uid }}'))">↻</button>
    </div>
    @error('password')<span style="font-size:12px;color:var(--danger);">{{ $message }}</span>@enderror
</div>

<div class="field">
    <label class="field__label" for="u-role-{{ $uid }}">Role</label>
    <select id="u-role-{{ $uid }}" name="role" class="field__input" required>
        @foreach(['admin' => 'Admin', 'manager' => 'Manager', 'editor' => 'Editor', 'viewer' => 'Viewer'] as $value => $label)
            <option value="{{ $value }}" {{ old('role', $u?->role) === $value ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    @error('role')<span style="font-size:12px;color:var(--danger);">{{ $message }}</span>@enderror
</div>

<div style="display:flex;align-items:center;gap:8px;margin-top:6px;">
    <input type="hidden" name="is_active" value="0">
    <input id="u-active-{{ $uid }}" type="checkbox" name="is_active" value="1"
           {{ old('is_active', $u?->is_active ?? true) ? 'checked' : '' }}
           style="accent-color:var(--accent);width:16px;height:16px;">
    <label for="u-active-{{ $uid }}" style="font-size:13px;color:var(--text-2);cursor:pointer;">Active account</label>
</div>
