<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSocialRequest;
use App\Http\Requests\Admin\UpdateSocialRequest;
use App\Models\Site;
use App\Models\SiteSocial;
use Illuminate\Http\RedirectResponse;

class SiteSocialController extends Controller
{
    public function store(StoreSocialRequest $request, Site $site): RedirectResponse
    {
        $site->socials()->create($request->validated());
        return redirect(route('sites.show', $site) . '?tab=socials')
            ->with('success', 'Соцмережу додано');
    }

    public function update(UpdateSocialRequest $request, Site $site, SiteSocial $social): RedirectResponse
    {
        $social->update($request->validated());
        return redirect(route('sites.show', $site) . '?tab=socials')
            ->with('success', 'Соцмережу оновлено');
    }

    public function destroy(Site $site, SiteSocial $social): RedirectResponse
    {
        $social->delete();
        return redirect(route('sites.show', $site) . '?tab=socials')
            ->with('success', 'Соцмережу видалено');
    }
}
