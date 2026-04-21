<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSocialRequest;
use App\Http\Requests\Admin\UpdateSocialRequest;
use App\Models\Site;
use App\Models\SiteSocial;
use App\Services\PluginSyncService;
use Illuminate\Http\RedirectResponse;

class SiteSocialController extends Controller
{
    public function store(StoreSocialRequest $request, Site $site): RedirectResponse
    {
        $site->socials()->create($request->validated());
        $site->touch();
        PluginSyncService::ping($site);
        return redirect(route('sites.show', $site) . '?tab=socials')
            ->with('success', 'Соцмережу додано');
    }

    public function update(UpdateSocialRequest $request, Site $site, SiteSocial $social): RedirectResponse
    {
        $social->update($request->validated());
        $site->touch();
        PluginSyncService::ping($site);
        return redirect(route('sites.show', $site) . '?tab=socials')
            ->with('success', 'Соцмережу оновлено');
    }

    public function destroy(Site $site, SiteSocial $social): RedirectResponse
    {
        $social->delete();
        $site->touch();
        PluginSyncService::ping($site);
        return redirect(route('sites.show', $site) . '?tab=socials')
            ->with('success', 'Соцмережу видалено');
    }
}
