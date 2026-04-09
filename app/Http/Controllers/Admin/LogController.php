<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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

        $logs = $query->paginate(50)->withQueryString();

        return view('admin.logs.sync', compact('logs'));
    }
}
