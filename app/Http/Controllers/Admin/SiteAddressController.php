<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAddressRequest;
use App\Http\Requests\Admin\UpdateAddressRequest;
use App\Models\Site;
use App\Models\SiteAddress;
use Illuminate\Http\RedirectResponse;

class SiteAddressController extends Controller
{
    public function store(StoreAddressRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary']    = $request->boolean('is_primary');
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $site->addresses()->create($data);
        return back()
            ->with('success', 'Адресу додано');
    }

    public function update(UpdateAddressRequest $request, Site $site, SiteAddress $address): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary']    = $request->boolean('is_primary');
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $address->update($data);
        return back()
            ->with('success', 'Адресу оновлено');
    }

    public function destroy(Site $site, SiteAddress $address): RedirectResponse
    {
        $address->delete();
        return back()
            ->with('success', 'Адресу видалено');
    }
}
