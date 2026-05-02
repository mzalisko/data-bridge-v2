<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteGeoController extends Controller
{
    /**
     * Add a country to the site's active_geos map: { "UA": "Ukraine", "RO": "Romania" }
     */
    public function addGeo(Request $request, Site $site): RedirectResponse
    {
        $iso  = strtoupper(trim((string) $request->input('country_iso')));
        $name = trim((string) $request->input('country_name', ''));

        if (!preg_match('/^[A-Z]{2}$/', $iso)) {
            return back()->with('error', 'Invalid ISO code (must be 2 uppercase letters)');
        }

        $geos = $this->normalizeGeos($site->active_geos);
        if (!isset($geos[$iso])) {
            $geos[$iso] = $name ?: $iso;
            $site->active_geos = $geos;
            $site->save();
        }

        return redirect(route('sites.show', $site) . '?tab=data&country=' . $iso)
            ->with('success', "Geo {$iso} added");
    }

    /**
     * Remove a country from active_geos. Does NOT delete tagged data records.
     */
    public function removeGeo(Site $site, string $iso): RedirectResponse
    {
        $iso  = strtoupper($iso);
        $geos = $this->normalizeGeos($site->active_geos);
        unset($geos[$iso]);
        $site->active_geos = $geos;

        // Drop from rules
        $rules = $site->geo_rules ?? [];
        unset($rules[$iso]);
        foreach ($rules as $k => $v) {
            if (isset($v['countries'])) {
                $rules[$k]['countries'] = array_values(
                    array_filter((array) $v['countries'], fn ($x) => $x !== $iso)
                );
            }
        }
        $site->geo_rules = $rules;
        $site->save();

        return back()->with('success', "Geo {$iso} removed");
    }

    /** Normalize active_geos: old ["UA","RO"] → new {"UA":"UA","RO":"RO"} */
    private function normalizeGeos(mixed $raw): array
    {
        $arr = (array) ($raw ?? []);
        if (array_is_list($arr)) {
            return array_fill_keys($arr, '');
        }
        return $arr;
    }

    /**
     * Save geo rules: data-geo → { mode, countries[] } — who can see each geo tab's data.
     * Posted as geo[ISO][mode] = all|include|exclude and geo[ISO][countries][] = ISO
     */
    public function saveRules(Request $request, Site $site): RedirectResponse
    {
        $input = (array) $request->input('geo', []);
        $clean = [];
        foreach ($input as $dataIso => $cfg) {
            $dataIso = strtoupper((string) $dataIso);
            if (!preg_match('/^[A-Z]{2}$/', $dataIso)) continue;
            $mode = in_array($cfg['mode'] ?? '', ['all', 'include', 'exclude'])
                ? $cfg['mode']
                : 'all';
            $countries = array_values(array_filter(
                array_map(fn ($x) => strtoupper((string) $x), (array) ($cfg['countries'] ?? [])),
                fn ($x) => preg_match('/^[A-Z]{2}$/', $x)
            ));
            $clean[$dataIso] = ['mode' => $mode, 'countries' => $countries];
        }
        $site->geo_rules = $clean;
        $site->save();

        return back()->with('success', 'Geo rules updated');
    }

    /**
     * Toggle is_visible on a phone/price/address/social row.
     * type = phones|prices|addresses|socials
     */
    public function toggleVisibility(Site $site, string $type, int $id): RedirectResponse
    {
        $map = [
            'phones'    => \App\Models\SitePhone::class,
            'prices'    => \App\Models\SitePrice::class,
            'addresses' => \App\Models\SiteAddress::class,
            'socials'   => \App\Models\SiteSocial::class,
        ];
        if (!isset($map[$type])) abort(404);

        $row = $map[$type]::where('site_id', $site->id)->findOrFail($id);
        $row->is_visible = !($row->is_visible ?? true);
        $row->save();

        return back();
    }
}
