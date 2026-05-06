<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePriceRequest;
use App\Http\Requests\Admin\UpdatePriceRequest;
use App\Models\Site;
use App\Models\SitePrice;
use Illuminate\Http\RedirectResponse;

class SitePriceController extends Controller
{
    public function store(StorePriceRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_visible']    = $request->boolean('is_visible', true);
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $site->prices()->create($data);
        return back()
            ->with('success', 'Ціну додано');
    }

    public function update(UpdatePriceRequest $request, Site $site, SitePrice $price): RedirectResponse
    {
        $data = $request->validated();
        $data['is_visible']    = $request->boolean('is_visible', true);
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $price->update($data);
        return back()
            ->with('success', 'Ціну оновлено');
    }

    public function destroy(Site $site, SitePrice $price): RedirectResponse
    {
        $price->delete();
        return back()
            ->with('success', 'Ціну видалено');
    }
}
