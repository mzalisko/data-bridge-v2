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
        $data['is_primary'] = $request->boolean('is_primary');
        $site->phones()->create($data);

        return redirect(route('sites.show', $site) . '?tab=phones')
            ->with('success', 'Телефон додано');
    }

    public function update(UpdatePhoneRequest $request, Site $site, SitePhone $phone): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary'] = $request->boolean('is_primary');
        $phone->update($data);

        return redirect(route('sites.show', $site) . '?tab=phones')
            ->with('success', 'Телефон оновлено');
    }

    public function destroy(Site $site, SitePhone $phone): RedirectResponse
    {
        $phone->delete();

        return redirect(route('sites.show', $site) . '?tab=phones')
            ->with('success', 'Телефон видалено');
    }
}
