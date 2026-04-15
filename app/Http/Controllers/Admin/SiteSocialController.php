<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteSocial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSocialController extends Controller
{
    public function store(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'platform'   => ['required', 'string', 'max:50'],
            'handle'     => ['nullable', 'string', 'max:100'],
            'url'        => ['nullable', 'url', 'max:500'],
            'sort_order' => ['integer'],
        ]);

        $data['site_id'] = $site->id;

        SiteSocial::create($data);

        return redirect()->route('sites.show', [$site, 'tab' => 'socials'])
            ->with('success', 'Соцмережу додано');
    }

    public function update(Request $request, Site $site, SiteSocial $social): RedirectResponse
    {
        $data = $request->validate([
            'platform'   => ['required', 'string', 'max:50'],
            'handle'     => ['nullable', 'string', 'max:100'],
            'url'        => ['nullable', 'url', 'max:500'],
            'sort_order' => ['integer'],
        ]);

        $social->update($data);

        return redirect()->route('sites.show', [$site, 'tab' => 'socials'])
            ->with('success', 'Соцмережу оновлено');
    }

    public function destroy(Site $site, SiteSocial $social): RedirectResponse
    {
        $social->delete();

        return redirect()->route('sites.show', [$site, 'tab' => 'socials'])
            ->with('success', 'Соцмережу видалено');
    }
}
