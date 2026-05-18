<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Site;
use App\Services\ActivityService;
use App\Services\SyncPushService;
use App\Support\EntityTypeRegistry;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Shared CRUD orchestration for the per-site data controllers
 * (phones / prices / addresses / socials) — audit S1. The four
 * controllers were near-identical: same geo-default normalization,
 * same ActivityService::log + SyncPushService::push + back()->with()
 * shape. Behaviour is preserved exactly; only the per-type bits
 * (entity type, flash text, log summary, field pre-processing) are
 * left to the controller via the three hooks below.
 */
trait ManagesSiteData
{
    /** Canonical singular entity type, e.g. 'phone'. */
    abstract protected function entityType(): string;

    /** Flash text for 'create' | 'update' | 'delete'. */
    abstract protected function flashMessage(string $action): string;

    /** Activity-log summary line for the given record + action. */
    abstract protected function logSummary(Model $record, string $action): string;

    /** Per-type field massaging before persist. Default: identity. */
    protected function preprocess(Request $request, array $data): array
    {
        return $data;
    }

    private function siteRelation(): string
    {
        return EntityTypeRegistry::relation($this->entityType());
    }

    private function applyGeoDefaults(array $data): array
    {
        $data['geo_mode']      = $data['geo_mode'] ?? 'all';
        $data['geo_countries'] = $data['geo_mode'] !== 'all'
            ? ($data['geo_countries'] ?? [])
            : [];

        return $data;
    }

    protected function createSiteRecord(Site $site, Request $request, array $validated): RedirectResponse
    {
        $data   = $this->applyGeoDefaults($this->preprocess($request, $validated));
        $record = $site->{$this->siteRelation()}()->create($data);

        ActivityService::log($this->entityType(), 'create', $record, $this->logSummary($record, 'create'), $site);
        SyncPushService::push($site);

        return back()->with('success', $this->flashMessage('create'));
    }

    protected function updateSiteRecord(Site $site, Request $request, Model $record, array $validated): RedirectResponse
    {
        $data   = $this->applyGeoDefaults($this->preprocess($request, $validated));
        $before = $record->toArray();
        $record->update($data);

        ActivityService::log($this->entityType(), 'update', $record, $this->logSummary($record, 'update'), $site, $before);
        SyncPushService::push($site);

        return back()->with('success', $this->flashMessage('update'));
    }

    protected function deleteSiteRecord(Site $site, Model $record): RedirectResponse
    {
        ActivityService::log($this->entityType(), 'delete', $record, $this->logSummary($record, 'delete'), $site);
        $record->delete();
        SyncPushService::push($site);

        return back()->with('success', $this->flashMessage('delete'));
    }

    protected function reorderSiteRecords(Request $request, Site $site): JsonResponse
    {
        $data = $request->validate([
            'items'              => ['required', 'array'],
            'items.*.id'         => ['required', 'integer'],
            'items.*.sort_order' => ['required', 'integer'],
        ]);

        $model = EntityTypeRegistry::modelOrFail($this->entityType());
        foreach ($data['items'] as $item) {
            $model::where('site_id', $site->id)->where('id', $item['id'])
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['ok' => true]);
    }
}
