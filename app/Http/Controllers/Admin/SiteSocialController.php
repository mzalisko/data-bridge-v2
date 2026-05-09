<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSocialRequest;
use App\Http\Requests\Admin\UpdateSocialRequest;
use App\Models\Site;
use App\Models\SiteSocial;
use App\Services\ActivityService;
use Illuminate\Http\RedirectResponse;

class SiteSocialController extends Controller
{
    public function store(StoreSocialRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $social = $site->socials()->create($data);
        ActivityService::log('social', 'create', $social, "{$social->platform} {$social->handle} додано", $site);
        return back()->with('success', 'Соцмережу додано');
    }

    public function update(UpdateSocialRequest $request, Site $site, SiteSocial $social): RedirectResponse
    {
        $data = $request->validated();
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $social->update($data);
        ActivityService::log('social', 'update', $social, "{$social->platform} {$social->handle} оновлено", $site);
        return back()->with('success', 'Соцмережу оновлено');
    }

    public function destroy(Site $site, SiteSocial $social): RedirectResponse
    {
        ActivityService::log('social', 'delete', $social, "{$social->platform} {$social->handle} видалено", $site);
        $social->delete();
        return back()->with('success', 'Соцмережу видалено');
    }
}
