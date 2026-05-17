<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Site;
use Illuminate\View\View;

class SiteGeoVisController extends Controller
{
    public function show(Site $site): View
    {
        $site->load(['phones', 'prices', 'socials.phone', 'addresses']);

        $activeGeosRaw = (array) ($site->active_geos ?? []);
        $geoNames = array_is_list($activeGeosRaw)
            ? array_fill_keys($activeGeosRaw, '')
            : $activeGeosRaw;
        $usedIso = array_keys($geoNames);
        sort($usedIso);

        $gtab      = in_array(request('gtab'), ['data', 'overview', 'matrix']) ? request('gtab') : 'data';
        $filterIso = in_array(request('filter'), $usedIso) ? request('filter') : 'all';
        $visitorIso = in_array(request('vis'), $usedIso) ? request('vis') : ($usedIso[0] ?? null);

        $geoVis = function ($geoMode, $geoCountries, $visitorIso): bool {
            $mode   = $geoMode ?? 'all';
            $ctries = (array) ($geoCountries ?? []);
            return match($mode) {
                'include' => in_array($visitorIso, $ctries),
                'exclude' => !in_array($visitorIso, $ctries),
                default   => true,
            };
        };

        $countries = Country::orderBy('iso')->get();

        return view('admin.sites.geo-visibility', compact(
            'site', 'usedIso', 'geoNames', 'gtab', 'filterIso', 'visitorIso', 'geoVis', 'countries'
        ));
    }
}
