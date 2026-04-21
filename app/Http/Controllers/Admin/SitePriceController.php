<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePriceRequest;
use App\Http\Requests\Admin\UpdatePriceRequest;
use App\Models\Site;
use App\Models\SitePrice;
use App\Services\PluginSyncService;
use Illuminate\Http\RedirectResponse;

class SitePriceController extends Controller
{
    public function store(StorePriceRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_visible'] = $request->boolean('is_visible', true);
        $site->prices()->create($data);
        $site->touch();
        PluginSyncService::ping($site);
        return redirect(route('sites.show', $site) . '?tab=prices')
            ->with('success', 'Ціну додано');
    }

    public function update(UpdatePriceRequest $request, Site $site, SitePrice $price): RedirectResponse
    {
        $data = $request->validated();
        $data['is_visible'] = $request->boolean('is_visible', true);
        $price->update($data);
        $site->touch();
        PluginSyncService::ping($site);
        return redirect(route('sites.show', $site) . '?tab=prices')
            ->with('success', 'Ціну оновлено');
    }

    public function destroy(Site $site, SitePrice $price): RedirectResponse
    {
        $price->delete();
        $site->touch();
        PluginSyncService::ping($site);
        return redirect(route('sites.show', $site) . '?tab=prices')
            ->with('success', 'Ціну видалено');
    }
}
