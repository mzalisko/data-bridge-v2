<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePhoneRequest;
use App\Http\Requests\Admin\UpdatePhoneRequest;
use App\Models\Site;
use App\Models\SitePhone;
use Illuminate\Http\RedirectResponse;

class SitePhoneController extends Controller
{
    public function store(StorePhoneRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary']    = $request->boolean('is_primary');
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $site->phones()->create($data);

        return back()
            ->with('success', 'Телефон додано');
    }

    public function update(UpdatePhoneRequest $request, Site $site, SitePhone $phone): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary']    = $request->boolean('is_primary');
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $phone->update($data);

        return back()
            ->with('success', 'Телефон оновлено');
    }

    public function destroy(Site $site, SitePhone $phone): RedirectResponse
    {
        $phone->delete();

        return back()
            ->with('success', 'Телефон видалено');
    }
}
