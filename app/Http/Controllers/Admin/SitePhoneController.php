<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePhoneRequest;
use App\Http\Requests\Admin\UpdatePhoneRequest;
use App\Models\Site;
use App\Models\SitePhone;
use App\Services\ActivityService;
use App\Services\SyncPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SitePhoneController extends Controller
{
    public function store(StorePhoneRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary']    = $request->boolean('is_primary');
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $phone = $site->phones()->create($data);
        ActivityService::log('phone', 'create', $phone, "Телефон {$phone->number} додано", $site);
        SyncPushService::push($site);

        return back()->with('success', 'Телефон додано');
    }

    public function update(UpdatePhoneRequest $request, Site $site, SitePhone $phone): RedirectResponse
    {
        $data = $request->validated();
        $data['is_primary']    = $request->boolean('is_primary');
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all' ? ($data['geo_countries'] ?? []) : [];
        $before = $phone->toArray();
        $phone->update($data);
        ActivityService::log('phone', 'update', $phone, "Телефон {$phone->number} оновлено", $site, $before);
        SyncPushService::push($site);

        return back()->with('success', 'Телефон оновлено');
    }

    public function destroy(Site $site, SitePhone $phone): RedirectResponse
    {
        ActivityService::log('phone', 'delete', $phone, "Телефон {$phone->number} видалено", $site);
        $phone->delete();
        SyncPushService::push($site);

        return back()->with('success', 'Телефон видалено');
    }

    public function reorder(Request $request, Site $site): JsonResponse
    {
        $data = $request->validate([
            'items'              => ['required', 'array'],
            'items.*.id'         => ['required', 'integer'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);
        foreach ($data['items'] as $item) {
            SitePhone::where('site_id', $site->id)->where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }
        return response()->json(['ok' => true]);
    }
}
