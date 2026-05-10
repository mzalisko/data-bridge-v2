<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSiteCustomFieldRequest;
use App\Http\Requests\Admin\UpdateSiteCustomFieldRequest;
use App\Models\Site;
use App\Models\SiteCustomField;
use App\Services\ActivityService;
use App\Services\SyncPushService;
use Illuminate\Http\RedirectResponse;

class SiteCustomFieldController extends Controller
{
    public function store(StoreSiteCustomFieldRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['field_type'] = $data['field_type'] ?? 'text';
        $field = $site->customFields()->create($data);
        ActivityService::log('field', 'create', $field, "Поле «{$field->field_key}» додано", $site);
        SyncPushService::push($site);
        return back()->with('success', 'Поле додано');
    }

    public function update(UpdateSiteCustomFieldRequest $request, Site $site, SiteCustomField $field): RedirectResponse
    {
        $before = $field->toArray();
        $field->update($request->validated());
        ActivityService::log('field', 'update', $field, "Поле «{$field->field_key}» оновлено", $site, $before);
        SyncPushService::push($site);
        return back()->with('success', 'Поле оновлено');
    }

    public function destroy(Site $site, SiteCustomField $field): RedirectResponse
    {
        ActivityService::log('field', 'delete', $field, "Поле «{$field->field_key}» видалено", $site);
        $field->delete();
        SyncPushService::push($site);
        return back()->with('success', 'Поле видалено');
    }
}
