<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SiteFailoverLog;
use App\Models\SitePhone;
use App\Models\SiteSocial;
use App\Services\FailoverService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteFailoverController extends Controller
{
    /** Mark a phone/social as standby (toggle) and optionally link to a primary. */
    public function toggleStandby(Request $request, Site $site): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'type'           => ['required', 'in:phone,social'],
            'id'             => ['required', 'integer'],
            'standby_for_id' => ['nullable', 'integer'],
        ]);

        $model = $data['type'] === 'phone' ? SitePhone::class : SiteSocial::class;

        $record = $model::where('site_id', $site->id)->findOrFail($data['id']);
        $newStandby = !$record->is_standby;

        $record->update([
            'is_standby'     => $newStandby,
            'standby_for_id' => $newStandby ? ($data['standby_for_id'] ?? null) : null,
        ]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'is_standby' => $newStandby]);
        }

        return back()->with('success', $newStandby ? 'Позначено як резервний.' : 'Знято з резерву.');
    }

    /** Manually trigger failover from CRM. */
    public function trigger(Request $request, Site $site): RedirectResponse
    {
        $data = $request->validate([
            'type'       => ['required', 'in:phone,social'],
            'primary_id' => ['required', 'integer'],
            'standby_id' => ['nullable', 'integer'],
            'reason'     => ['required', 'string', 'max:255'],
        ]);

        try {
            FailoverService::trigger(
                site:        $site,
                type:        $data['type'],
                primaryId:   (int) $data['primary_id'],
                reason:      $data['reason'],
                triggeredBy: 'manual',
                standbyId:   isset($data['standby_id']) ? (int)$data['standby_id'] : null,
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Failover активовано — резервний запис замінив заблокований.');
    }

    /** Re-link a standby to a different primary (or move to general pool). */
    public function linkStandby(Request $request, Site $site): RedirectResponse|JsonResponse
    {
        $data = $request->validate([
            'type'           => ['required', 'in:phone,social'],
            'id'             => ['required', 'integer'],
            'standby_for_id' => ['nullable', 'integer'],
        ]);

        $model = $data['type'] === 'phone' ? SitePhone::class : SiteSocial::class;
        $record = $model::where('site_id', $site->id)->where('is_standby', true)->findOrFail($data['id']);
        $record->update(['standby_for_id' => $data['standby_for_id'] ?: null]);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back()->with('success', 'Прив\'язку резерву оновлено.');
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
