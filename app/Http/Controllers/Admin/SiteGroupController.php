<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSiteGroupRequest;
use App\Http\Requests\Admin\UpdateSiteGroupRequest;
use App\Models\SiteGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteGroupController extends Controller
{
    public function index(Request $request): View
    {
        $query = SiteGroup::withCount('sites');

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($qb) use ($q) {
                $qb->where('name', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $query->orderByDesc('created_at');
        $groups = $query->paginate(20)->withQueryString();

        return view('admin.site-groups.index', compact('groups'));
    }

    public function show(SiteGroup $siteGroup): View
    {
        $siteGroup->loadCount('sites');
        $sites = $siteGroup->sites()->orderByDesc('created_at')->get();
        $allGroups = SiteGroup::orderBy('name')->get(['id', 'name', 'color']);

        return view('admin.site-groups.show', compact('siteGroup', 'sites', 'allGroups'));
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
