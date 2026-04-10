<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSiteRequest;
use App\Http\Requests\Admin\UpdateSiteRequest;
use App\Models\Site;
use App\Models\SiteGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SiteController extends Controller
{
    public function index(Request $request): View
    {
        $query = Site::with('siteGroup')->orderByDesc('created_at');

        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }

        $sites  = $query->paginate(25)->withQueryString();
        $groups = SiteGroup::withCount('sites')->orderBy('name')->get();

        return view('admin.sites.index', compact('sites', 'groups'));
    }

    public function show(Site $site): View
    {
        $site->load('siteGroup');
        $groups = SiteGroup::orderBy('name')->get(['id', 'name', 'color']);

        return view('admin.sites.show', compact('site', 'groups'));
    }

    public function store(StoreSiteRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);

        Site::create($data);

        return redirect()->route('sites.index')
            ->with('success', 'Сайт додано');
    }

    public function update(UpdateSiteRequest $request, Site $site): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');

        $site->update($data);

        return redirect()->route('sites.index')
            ->with('success', 'Сайт оновлено');
    }

    public function destroy(Site $site): RedirectResponse
    {
        $site->delete();

        return redirect()->route('sites.index')
            ->with('success', 'Сайт видалено');
    }
}
