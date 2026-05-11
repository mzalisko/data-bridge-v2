<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteFailoverLog;
use App\Services\FailoverService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteFailoverController extends Controller
{
    /** Mark a phone/social as standby (toggle). */
    public function toggleStandby(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:phone,social'],
            'id'   => ['required', 'integer'],
        ]);

        $model = $data['type'] === 'phone'
            ? \App\Models\SitePhone::class
            : \App\Models\SiteSocial::class;

        $record = $model::where('site_id', $site->id)->findOrFail($data['id']);
        $record->update(['is_standby' => !$record->is_standby]);

        return back()->with('success', 'Статус резерву оновлено.');
    }

    /** Manually trigger failover from CRM. */
    public function trigger(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'type'       => ['required', 'in:phone,social'],
            'primary_id' => ['required', 'integer'],
            'reason'     => ['required', 'string', 'max:255'],
        ]);

        try {
            FailoverService::trigger(
                site:        $site,
                type:        $data['type'],
                primaryId:   (int) $data['primary_id'],
                reason:      $data['reason'],
                triggeredBy: 'manual',
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Failover активовано — резервний запис замінив заблокований.');
    }

    /** Rollback a specific failover log from CRM. */
    public function rollback(Site $site, SiteFailoverLog $log): RedirectResponse
    {
        abort_if($log->site_id !== $site->id, 403);

        try {
            FailoverService::rollback($log);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Відкат виконано — оригінальний запис відновлено.');
    }
}
