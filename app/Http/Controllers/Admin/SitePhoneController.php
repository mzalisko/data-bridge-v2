<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SitePhone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SitePhoneController extends Controller
{
    public function store(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'label'       => ['nullable', 'string', 'max:100'],
            'country_iso' => ['nullable', 'string', 'max:2'],
            'dial_code'   => ['nullable', 'string', 'max:10'],
            'number'      => ['required', 'string', 'max:50'],
            'is_primary'  => ['boolean'],
            'sort_order'  => ['integer'],
        ]);

        $data['site_id']   = $site->id;
        $data['is_primary'] = $request->boolean('is_primary');

        SitePhone::create($data);

        return redirect()->route('sites.show', [$site, 'tab' => 'phones'])
            ->with('success', 'Телефон додано');
    }

    public function update(Request $request, Site $site, SitePhone $phone): RedirectResponse
    {
        $data = $request->validate([
            'label'       => ['nullable', 'string', 'max:100'],
            'country_iso' => ['nullable', 'string', 'max:2'],
            'dial_code'   => ['nullable', 'string', 'max:10'],
            'number'      => ['required', 'string', 'max:50'],
            'is_primary'  => ['boolean'],
            'sort_order'  => ['integer'],
        ]);

        $data['is_primary'] = $request->boolean('is_primary');

        $phone->update($data);

        return redirect()->route('sites.show', [$site, 'tab' => 'phones'])
            ->with('success', 'Телефон оновлено');
    }

    public function destroy(Site $site, SitePhone $phone): RedirectResponse
    {
        $phone->delete();

        return redirect()->route('sites.show', [$site, 'tab' => 'phones'])
            ->with('success', 'Телефон видалено');
    }
}
