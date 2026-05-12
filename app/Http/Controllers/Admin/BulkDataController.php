<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Bulk data operations across multiple sites.
 *
 * Flow:
 *   1. User selects N sites on sites/index (checkboxes with data-site-id)
 *   2. Floating bulk bar appears with action buttons
 *   3. User picks action + fills compact form
 *   4. POST here with site_ids[] + payload
 *   5. We loop sites, apply, return JSON summary
 *
 * All actions require auth (inherited from middleware group in routes/web.php).
 * All write operations are wrapped in a DB transaction.
 */
class BulkDataController extends Controller
{
    /**
     * Add identical phone to multiple sites.
     * POST /bulk/phones
     * Body: site_ids[], number, label?, dial_code?, country_iso?,
     *       is_primary?, geo_mode?, geo_countries[]
     */
    public function addPhone(Request $request): JsonResponse
    {
        $request->validate([
            'site_ids'        => ['required', 'array', 'min:1'],
            'site_ids.*'      => ['integer', 'exists:sites,id'],
            'number'          => ['required', 'string', 'max:32'],
            'label'           => ['nullable', 'string', 'max:128'],
            'dial_code'       => ['nullable', 'string', 'max:8'],
            'country_iso'     => ['nullable', 'string', 'size:2'],
            'is_primary'      => ['nullable', 'boolean'],
            'geo_mode'        => ['nullable', 'string', 'in:all,include,exclude'],
            'geo_countries'   => ['nullable', 'array'],
            'geo_countries.*' => ['string', 'regex:/^[A-Z]{2}$/'],
        ]);

        $payload = [
            'number'        => $request->number,
            'label'         => $request->label,
            'dial_code'     => $request->dial_code,
            'country_iso'   => $request->country_iso,
            'is_primary'    => $request->boolean('is_primary', false),
            'is_visible'    => true,
            'sort_order'    => 0,
            'geo_mode'      => $request->input('geo_mode', 'all'),
            'geo_countries' => $request->input('geo_mode', 'all') !== 'all'
                ? ($request->input('geo_countries', []))
                : [],
        ];

        $results = $this->applyToSites(
            $request->site_ids,
            fn(Site $site) => $site->phones()->create($payload)
        );

        return response()->json($results);
    }

    /**
     * Add identical price to multiple sites.
     * POST /bulk/prices
     * Body: site_ids[], label, amount, currency, period?,
     *       is_visible?, geo_mode?, geo_countries[]
     */
    public function addPrice(Request $request): JsonResponse
    {
        $request->validate([
            'site_ids'        => ['required', 'array', 'min:1'],
            'site_ids.*'      => ['integer', 'exists:sites,id'],
            'label'           => ['required', 'string', 'max:255'],
            'amount'          => ['required', 'numeric', 'min:0'],
            'currency'        => ['required', 'string', 'max:3'],
            'period'          => ['nullable', 'string', 'max:64'],
            'is_visible'      => ['nullable', 'boolean'],
            'geo_mode'        => ['nullable', 'string', 'in:all,include,exclude'],
            'geo_countries'   => ['nullable', 'array'],
            'geo_countries.*' => ['string', 'regex:/^[A-Z]{2}$/'],
        ]);

        $payload = [
            'label'         => $request->label,
            'amount'        => $request->amount,
            'currency'      => strtoupper($request->currency),
            'period'        => $request->period,
            'is_visible'    => $request->boolean('is_visible', true),
            'sort_order'    => 0,
            'geo_mode'      => $request->input('geo_mode', 'all'),
            'geo_countries' => $request->input('geo_mode', 'all') !== 'all'
                ? ($request->input('geo_countries', []))
                : [],
        ];

        $results = $this->applyToSites(
            $request->site_ids,
            fn(Site $site) => $site->prices()->create($payload)
        );

        return response()->json($results);
    }

    /**
     * Add social link to multiple sites.
     * POST /bulk/socials
     */
    public function addSocial(Request $request): JsonResponse
    {
        $request->validate([
            'site_ids'        => ['required', 'array', 'min:1'],
            'site_ids.*'      => ['integer', 'exists:sites,id'],
            'platform'        => ['required', 'string', 'max:32'],
            'handle'          => ['required', 'string', 'max:255'],
            'url'             => ['required', 'url', 'max:512'],
            'geo_mode'        => ['nullable', 'string', 'in:all,include,exclude'],
            'geo_countries'   => ['nullable', 'array'],
            'geo_countries.*' => ['string', 'regex:/^[A-Z]{2}$/'],
        ]);

        $payload = [
            'platform'      => $request->platform,
            'handle'        => $request->handle,
            'url'           => $request->url,
            'is_visible'    => true,
            'sort_order'    => 0,
            'geo_mode'      => $request->input('geo_mode', 'all'),
            'geo_countries' => $request->input('geo_mode', 'all') !== 'all'
                ? ($request->input('geo_countries', []))
                : [],
        ];

        $results = $this->applyToSites(
            $request->site_ids,
            fn(Site $site) => $site->socials()->create($payload)
        );

        return response()->json($results);
    }

    /**
     * Add address to multiple sites.
     * POST /bulk/addresses
     * Body: site_ids[], city, country_iso?, street?, label?, geo_mode?, geo_countries[]
     */
    public function addAddress(Request $request): JsonResponse
    {
        $request->validate([
            'site_ids'        => ['required', 'array', 'min:1'],
            'site_ids.*'      => ['integer', 'exists:sites,id'],
            'city'            => ['required', 'string', 'max:128'],
            'country_iso'     => ['nullable', 'string', 'size:2'],
            'street'          => ['nullable', 'string', 'max:255'],
            'building'        => ['nullable', 'string', 'max:32'],
            'postal_code'     => ['nullable', 'string', 'max:20'],
            'label'           => ['nullable', 'string', 'max:128'],
            'geo_mode'        => ['nullable', 'string', 'in:all,include,exclude'],
            'geo_countries'   => ['nullable', 'array'],
            'geo_countries.*' => ['string', 'regex:/^[A-Z]{2}$/'],
        ]);

        $payload = [
            'city'          => $request->city,
            'country_iso'   => $request->country_iso ? strtoupper($request->country_iso) : null,
            'street'        => $request->street,
            'building'      => $request->building,
            'postal_code'   => $request->postal_code,
            'label'         => $request->label,
            'is_visible'    => true,
            'sort_order'    => 0,
            'geo_mode'      => $request->input('geo_mode', 'all'),
            'geo_countries' => $request->input('geo_mode', 'all') !== 'all'
                ? ($request->input('geo_countries', []))
                : [],
        ];

        $results = $this->applyToSites(
            $request->site_ids,
            fn(Site $site) => $site->addresses()->create($payload)
        );

        return response()->json($results);
    }

    /**
     * Add geo to multiple sites.
     * POST /bulk/geos
     * Body: site_ids[], iso, name?
     */
    public function addGeo(Request $request): JsonResponse
    {
        $request->validate([
            'site_ids'   => ['required', 'array', 'min:1'],
            'site_ids.*' => ['integer', 'exists:sites,id'],
            'iso'        => ['required', 'string', 'size:2'],
            'name'       => ['nullable', 'string', 'max:64'],
        ]);

        $iso  = strtoupper($request->iso);
        $name = $request->input('name', '');

        $results = $this->applyToSites(
            $request->site_ids,
            function (Site $site) use ($iso, $name) {
                $geos = (array) ($site->active_geos ?? []);
                if (!isset($geos[$iso])) {
                    $geos[$iso] = $name;
                    $site->update(['active_geos' => $geos]);
                }
            }
        );

        return response()->json($results);
    }

    /**
     * Delete specific data entries from multiple sites by matching criteria.
     * POST /bulk/delete
     * Body: site_ids[], type (phones|prices|socials|addresses), match_field, match_value
     *
     * Example: delete phone number "+380671234567" from all selected sites.
     */
    public function deleteMatching(Request $request): JsonResponse
    {
        $request->validate([
            'site_ids'    => ['required', 'array', 'min:1'],
            'site_ids.*'  => ['integer', 'exists:sites,id'],
            'type'        => ['required', 'string', 'in:phones,prices,socials,addresses'],
            'match_field' => ['required', 'string', 'max:32'],
            'match_value' => ['required', 'string', 'max:255'],
        ]);

        $type  = $request->type;
        $field = $request->match_field;
        $value = $request->match_value;

        $results = $this->applyToSites(
            $request->site_ids,
            function (Site $site) use ($type, $field, $value) {
                $site->{$type}()->where($field, $value)->delete();
            }
        );

        return response()->json($results);
    }

    /**
     * Run a callable against each site, collect results.
     * Returns: { ok: count, failed: count, errors: {site_id: message} }
     */
    private function applyToSites(array $siteIds, callable $action): array
    {
        $ok     = 0;
        $failed = 0;
        $errors = [];

        foreach ($siteIds as $siteId) {
            try {
                $site = Site::findOrFail($siteId);
                $action($site);
                $ok++;
            } catch (\Throwable $e) {
                $failed++;
                $errors[$siteId] = $e->getMessage();
            }
        }

        return compact('ok', 'failed', 'errors');
    }
}
