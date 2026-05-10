<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePriceRequest;
use App\Http\Requests\Admin\UpdatePriceRequest;
use App\Models\Site;
use App\Models\SitePrice;
use App\Services\ActivityService;
use App\Services\SyncPushService;
use Illuminate\Http\RedirectResponse;

class SitePriceController extends Controller
{
    public function store(StorePriceRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_visible']    = $request->boolean('is_visible', true);
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $price = $site->prices()->create($data);
        ActivityService::log('price', 'create', $price, "Ціна «{$price->label}» додана", $site);
        SyncPushService::push($site);
        return back()->with('success', 'Ціну додано');
    }

    public function update(UpdatePriceRequest $request, Site $site, SitePrice $price): RedirectResponse
    {
        $data = $request->validated();
        $data['is_visible']    = $request->boolean('is_visible', true);
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $before = $price->toArray();
        $price->update($data);
        ActivityService::log('price', 'update', $price, "Ціна «{$price->label}» оновлена", $site, $before);
        SyncPushService::push($site);
        return back()->with('success', 'Ціну оновлено');
    }

    public function destroy(Site $site, SitePrice $price): RedirectResponse
    {
        ActivityService::log('price', 'delete', $price, "Ціна «{$price->label}» видалена", $site);
        $price->delete();
        SyncPushService::push($site);
        return back()->with('success', 'Ціну видалено');
    }
}
