<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomFieldRequest;
use App\Http\Requests\Admin\UpdateCustomFieldRequest;
use App\Models\Site;
use App\Models\SiteCustomField;
use Illuminate\Http\RedirectResponse;

class SiteCustomFieldController extends Controller
{
    public function store(StoreCustomFieldRequest $request, Site $site): RedirectResponse
    {
        $site->customFields()->create($request->validated());

        return redirect(route('sites.show', $site) . '?tab=custom_fields')
            ->with('success', 'Кастомне поле додано');
    }

    public function update(UpdateCustomFieldRequest $request, Site $site, SiteCustomField $customField): RedirectResponse
    {
        $customField->update($request->validated());

        return redirect(route('sites.show', $site) . '?tab=custom_fields')
            ->with('success', 'Кастомне поле оновлено');
    }

    public function destroy(Site $site, SiteCustomField $customField): RedirectResponse
    {
        $customField->delete();

        return redirect(route('sites.show', $site) . '?tab=custom_fields')
            ->with('success', 'Кастомне поле видалено');
    }
}
