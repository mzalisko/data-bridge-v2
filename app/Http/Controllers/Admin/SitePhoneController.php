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
        $data = $request->all();

        $data['site_id']    = $site->id;
        $data['is_primary'] = $request->boolean('is_primary');

        SitePhone::create($data);

        return redirect()->route('sites.show', [$site, 'tab' => 'phones'])
            ->with('success', 'Телефон додано');
    }

    public function update(Request $request, Site $site, SitePhone $phone): RedirectResponse
    {
        $data = $request->all();

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
