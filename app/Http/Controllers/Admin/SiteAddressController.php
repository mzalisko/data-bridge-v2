<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAddressRequest;
use App\Http\Requests\Admin\UpdateAddressRequest;
use App\Models\Site;
use App\Models\SiteAddress;
use App\Services\PluginSyncService;
use Illuminate\Http\RedirectResponse;

class SiteAddressController extends Controller
{
    public function store(StoreAddressRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary'] = $request->boolean('is_primary');
        $site->addresses()->create($data);
        PluginSyncService::ping($site);
        return redirect(route('sites.show', $site) . '?tab=addresses')
            ->with('success', 'Адресу додано');
    }

    public function update(UpdateAddressRequest $request, Site $site, SiteAddress $address): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary'] = $request->boolean('is_primary');
        $address->update($data);
        PluginSyncService::ping($site);
        return redirect(route('sites.show', $site) . '?tab=addresses')
            ->with('success', 'Адресу оновлено');
    }

    public function destroy(Site $site, SiteAddress $address): RedirectResponse
    {
        $address->delete();
        PluginSyncService::ping($site);
        return redirect(route('sites.show', $site) . '?tab=addresses')
            ->with('success', 'Адресу видалено');
    }
}
