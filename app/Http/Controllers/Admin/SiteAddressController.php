<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAddressRequest;
use App\Http\Requests\Admin\UpdateAddressRequest;
use App\Models\Site;
use App\Models\SiteAddress;
use App\Services\ActivityService;
use Illuminate\Http\RedirectResponse;

class SiteAddressController extends Controller
{
    public function store(StoreAddressRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary']    = $request->boolean('is_primary');
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $address = $site->addresses()->create($data);
        ActivityService::log('address', 'create', $address, "Адресу {$address->city} додано", $site);
        return back()->with('success', 'Адресу додано');
    }

    public function update(UpdateAddressRequest $request, Site $site, SiteAddress $address): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary']    = $request->boolean('is_primary');
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $before = $address->toArray();
        $address->update($data);
        ActivityService::log('address', 'update', $address, "Адресу {$address->city} оновлено", $site, $before);
        return back()->with('success', 'Адресу оновлено');
    }

    public function destroy(Site $site, SiteAddress $address): RedirectResponse
    {
        ActivityService::log('address', 'delete', $address, "Адресу {$address->city} видалено", $site);
        $address->delete();
        return back()->with('success', 'Адресу видалено');
    }
}
