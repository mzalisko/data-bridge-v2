<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SitePrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SitePriceController extends Controller
{
    public function store(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'label'      => ['nullable', 'string', 'max:100'],
            'amount'     => ['required', 'numeric', 'min:0'],
            'currency'   => ['required', 'string', 'max:10'],
            'period'     => ['nullable', 'string', 'max:50'],
            'is_visible' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $data['site_id']    = $site->id;
        $data['is_visible'] = $request->boolean('is_visible', true);

        SitePrice::create($data);

        return redirect()->route('sites.show', [$site, 'tab' => 'prices'])
            ->with('success', 'Ціну додано');
    }

    public function update(Request $request, Site $site, SitePrice $price): RedirectResponse
    {
        $data = $request->validate([
            'label'      => ['nullable', 'string', 'max:100'],
            'amount'     => ['required', 'numeric', 'min:0'],
            'currency'   => ['required', 'string', 'max:10'],
            'period'     => ['nullable', 'string', 'max:50'],
            'is_visible' => ['boolean'],
            'sort_order' => ['integer'],
        ]);

        $data['is_visible'] = $request->boolean('is_visible');

        $price->update($data);

        return redirect()->route('sites.show', [$site, 'tab' => 'prices'])
            ->with('success', 'Ціну оновлено');
    }

    public function destroy(Site $site, SitePrice $price): RedirectResponse
    {
        $price->delete();

        return redirect()->route('sites.show', [$site, 'tab' => 'prices'])
            ->with('success', 'Ціну видалено');
    }
}
