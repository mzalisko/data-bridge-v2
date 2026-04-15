<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteAddressController extends Controller
{
    public function store(Request $request, Site $site): RedirectResponse
    {
        $data = $request->all();

        $data['site_id']    = $site->id;
        $data['is_primary'] = $request->boolean('is_primary');

        SiteAddress::create($data);

        return redirect()->route('sites.show', [$site, 'tab' => 'addresses'])
            ->with('success', 'Адресу додано');
    }

    public function update(Request $request, Site $site, SiteAddress $address): RedirectResponse
    {
        $data = $request->all();

        $data['is_primary'] = $request->boolean('is_primary');

        $address->update($data);

        return redirect()->route('sites.show', [$site, 'tab' => 'addresses'])
            ->with('success', 'Адресу оновлено');
    }

    public function destroy(Site $site, SiteAddress $address): RedirectResponse
    {
        $address->delete();

        return redirect()->route('sites.show', [$site, 'tab' => 'addresses'])
            ->with('success', 'Адресу видалено');
    }
}
