<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSiteGroupRequest;
use App\Http\Requests\Admin\UpdateSiteGroupRequest;
use App\Models\SiteGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SiteGroupController extends Controller
{
    public function index(): View
    {
        $groups = SiteGroup::withCount('sites')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.site-groups.index', compact('groups'));
    }

    public function store(StoreSiteGroupRequest $request): RedirectResponse
    {
        SiteGroup::create($request->validated());

        return redirect()->route('site-groups.index')
            ->with('success', 'Групу створено');
    }

    public function update(UpdateSiteGroupRequest $request, SiteGroup $siteGroup): RedirectResponse
    {
        $siteGroup->update($request->validated());

        return redirect()->route('site-groups.index')
            ->with('success', 'Групу оновлено');
    }

    public function destroy(SiteGroup $siteGroup): RedirectResponse
    {
        $siteGroup->delete();

        return redirect()->route('site-groups.index')
            ->with('success', 'Групу видалено');
    }
}
