<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Site;
use App\Models\SiteGroup;
use App\Models\SyncLog;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LogController extends Controller
{
    public function system(Request $request): View
    {
        $query = SystemLog::with('user')->orderByDesc('created_at');

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('event')) {
            $query->where('event', 'like', '%' . $request->event . '%');
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.logs.system', compact('logs'));
    }

    public function sync(Request $request): View
    {
        $query = SyncLog::with('site')->orderByDesc('synced_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }

        if ($request->filled('search')) {
            $query->whereHas('site', fn($q) => $q->where('name', 'like', '%' . $request->search . '%'));
        }

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.logs.sync', compact('logs'));
    }

    public function activity(Request $request): View
    {
        $query = ActivityLog::with(['user:id,name', 'site:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->entity_type);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('site_id')) {
            $query->where('site_id', $request->site_id);
        }
        if ($request->filled('group_id')) {
            $query->where('group_id', $request->group_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $logs   = $query->paginate(40)->withQueryString();
        $sites  = Site::orderBy('name')->get(['id', 'name']);
        $groups = SiteGroup::orderBy('name')->get(['id', 'name']);

        return view('admin.logs.activity', compact('logs', 'sites', 'groups'));
    }
}
