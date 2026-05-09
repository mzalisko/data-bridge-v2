<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSiteCustomFieldRequest;
use App\Http\Requests\Admin\UpdateSiteCustomFieldRequest;
use App\Models\Site;
use App\Models\SiteCustomField;
use Illuminate\Http\RedirectResponse;

class SiteCustomFieldController extends Controller
{
    public function store(StoreSiteCustomFieldRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['field_type'] = $data['field_type'] ?? 'text';
        $site->customFields()->create($data);
        return back()->with('success', 'Поле додано');
    }

    public function update(UpdateSiteCustomFieldRequest $request, Site $site, SiteCustomField $field): RedirectResponse
    {
        $field->update($request->validated());
        return back()->with('success', 'Поле оновлено');
    }

    public function destroy(Site $site, SiteCustomField $field): RedirectResponse
    {
        $field->delete();
        return back()->with('success', 'Поле видалено');
    }
}
