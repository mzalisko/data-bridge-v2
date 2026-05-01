<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteGeoController extends Controller
{
    /**
     * Add a country to the site's active_geos list.
     */
    public function addGeo(Request $request, Site $site): RedirectResponse
    {
        $iso = strtoupper((string) $request->input('country_iso'));
        if (!preg_match('/^[A-Z]{2}$/', $iso)) {
            return back()->with('error', 'Invalid country code');
        }

        $list = $site->active_geos ?? [];
        if (!in_array($iso, $list, true)) {
            $list[] = $iso;
            $site->active_geos = $list;
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
        $iso = strtoupper($iso);
        $list = array_values(array_filter($site->active_geos ?? [], fn ($x) => $x !== $iso));
        $site->active_geos = $list;

        // Also drop the geo from any rules
        $rules = $site->geo_rules ?? [];
        unset($rules[$iso]);
        foreach ($rules as $k => $v) {
            $rules[$k] = array_values(array_filter((array) $v, fn ($x) => $x !== $iso));
        }
        $site->geo_rules = $rules;
        $site->save();

        return back()->with('success', "Geo {$iso} removed");
    }

    /**
     * Save geo rules: visitor-country → list of allowed data-country isos.
     * Posted as rules[VISITOR_ISO][] = ALLOWED_ISO
     */
    public function saveRules(Request $request, Site $site): RedirectResponse
    {
        $rules = (array) $request->input('rules', []);
        $clean = [];
        foreach ($rules as $visitor => $allowed) {
            $visitor = strtoupper((string) $visitor);
            if (!preg_match('/^[A-Z]{2}$/', $visitor)) continue;
            $clean[$visitor] = array_values(array_filter(
                array_map(fn ($x) => strtoupper((string) $x), (array) $allowed),
                fn ($x) => preg_match('/^[A-Z]{2}$/', $x)
            ));
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
