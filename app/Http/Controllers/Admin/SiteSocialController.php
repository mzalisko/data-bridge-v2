<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSocialRequest;
use App\Http\Requests\Admin\UpdateSocialRequest;
use App\Models\CustomPlatform;
use App\Models\Site;
use App\Models\SiteSocial;
use App\Services\ActivityService;
use App\Services\SyncPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSocialController extends Controller
{
    private function resolveCustomPlatform(array &$data, string $rawPlatform, ?string $customLabel): void
    {
        if ($rawPlatform === '__new__' && $customLabel) {
            $platform = CustomPlatform::fromLabel($customLabel, 'messenger');
            $data['platform'] = $platform->slug;
        }
        unset($data['platform_custom']);
    }

    public function store(StoreSocialRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $this->resolveCustomPlatform($data, $request->input('platform', ''), $request->input('platform_custom'));
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $social = $site->socials()->create($data);
        ActivityService::log('social', 'create', $social, "{$social->platform} {$social->handle} додано", $site);
        SyncPushService::push($site);
        return back()->with('success', 'Соцмережу додано');
    }

    public function update(UpdateSocialRequest $request, Site $site, SiteSocial $social): RedirectResponse
    {
        $data = $request->validated();
        $this->resolveCustomPlatform($data, $request->input('platform', ''), $request->input('platform_custom'));
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $before = $social->toArray();
        $social->update($data);
        ActivityService::log('social', 'update', $social, "{$social->platform} {$social->handle} оновлено", $site, $before);
        SyncPushService::push($site);
        return back()->with('success', 'Соцмережу оновлено');
    }

    public function destroy(Site $site, SiteSocial $social): RedirectResponse
    {
        ActivityService::log('social', 'delete', $social, "{$social->platform} {$social->handle} видалено", $site);
        $social->delete();
        SyncPushService::push($site);
        return back()->with('success', 'Соцмережу видалено');
    }

    public function reorder(Request $request, Site $site): JsonResponse
    {
        $data = $request->validate([
            'items'              => ['required', 'array'],
            'items.*.id'         => ['required', 'integer'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);
        foreach ($data['items'] as $item) {
            SiteSocial::where('site_id', $site->id)->where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }
        return response()->json(['ok' => true]);
    }
}
