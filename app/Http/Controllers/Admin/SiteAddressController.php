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
        $data = $request->validate([
            'label'       => ['nullable', 'string', 'max:100'],
            'country_iso' => ['nullable', 'string', 'max:2'],
            'city'        => ['nullable', 'string', 'max:100'],
            'street'      => ['nullable', 'string', 'max:200'],
            'building'    => ['nullable', 'string', 'max:50'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude'    => ['nullable', 'numeric'],
            'longitude'   => ['nullable', 'numeric'],
            'is_primary'  => ['boolean'],
            'sort_order'  => ['integer'],
        ]);

        $data['site_id']    = $site->id;
        $data['is_primary'] = $request->boolean('is_primary');

        SiteAddress::create($data);

        return redirect()->route('sites.show', [$site, 'tab' => 'addresses'])
            ->with('success', 'Адресу додано');
    }

    public function update(Request $request, Site $site, SiteAddress $address): RedirectResponse
    {
        $data = $request->validate([
            'label'       => ['nullable', 'string', 'max:100'],
            'country_iso' => ['nullable', 'string', 'max:2'],
            'city'        => ['nullable', 'string', 'max:100'],
            'street'      => ['nullable', 'string', 'max:200'],
            'building'    => ['nullable', 'string', 'max:50'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'latitude'    => ['nullable', 'numeric'],
            'longitude'   => ['nullable', 'numeric'],
            'is_primary'  => ['boolean'],
            'sort_order'  => ['integer'],
        ]);

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
