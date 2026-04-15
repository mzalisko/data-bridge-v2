<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Site;
use App\Models\SiteGroup;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BatchController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $ids = array_filter(array_map('intval', (array) $request->query('ids', [])));

        if (empty($ids)) {
            return redirect()->route('sites.index')
                ->with('error', 'Оберіть хоча б один сайт для batch-редагування.');
        }

        $sites     = Site::with('siteGroup')->whereIn('id', $ids)->get();
        $groups    = SiteGroup::orderBy('name')->get();
        $countries = Country::orderBy('sort_order')->orderBy('iso')->get(['iso', 'dial_code', 'name']);

        return view('admin.sites.batch', compact('sites', 'groups', 'countries'));
    }

    public function apply(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer', 'exists:sites,id'],
            'action' => ['required', 'in:status,group,phone,price,address,social,delete'],
        ]);

        $ids    = $data['ids'];
        $action = $data['action'];
        $count  = count($ids);
        $sites  = Site::whereIn('id', $ids)->get();

        switch ($action) {
            case 'status':
                $request->validate(['value' => ['required', 'in:active,inactive']]);
                $isActive = $request->input('value') === 'active';
                Site::whereIn('id', $ids)->update(['is_active' => $isActive]);
                $label = $isActive ? 'Active' : 'Disabled';
                return redirect()->route('sites.index')
                    ->with('success', "Batch: {$count} сайтів → статус «{$label}»");

            case 'group':
                $request->validate(['value' => ['required', 'string']]);
                $value = $request->input('value');
                if ($value === 'none') {
                    Site::whereIn('id', $ids)->update(['group_id' => null]);
                    return redirect()->route('sites.index')
                        ->with('success', "Batch: {$count} сайтів → група знята");
                }
                $group = SiteGroup::findOrFail((int) $value);
                Site::whereIn('id', $ids)->update(['group_id' => $group->id]);
                return redirect()->route('sites.index')
                    ->with('success', "Batch: {$count} сайтів → група «{$group->name}»");

            case 'phone':
                $request->validate([
                    'phone_country_iso' => ['required', 'string', 'max:2'],
                    'phone_dial_code'   => ['required', 'string', 'max:8'],
                    'phone_number'      => ['required', 'string', 'max:32'],
                    'phone_label'       => ['nullable', 'string', 'max:100'],
                ]);
                foreach ($sites as $site) {
                    $site->phones()->create([
                        'country_iso' => strtoupper($request->input('phone_country_iso')),
                        'dial_code'   => $request->input('phone_dial_code'),
                        'number'      => $request->input('phone_number'),
                        'label'       => $request->input('phone_label') ?: null,
                        'is_primary'  => false,
                        'sort_order'  => 0,
                    ]);
                }
                return redirect()->route('sites.index')
                    ->with('success', "Batch: телефон додано до {$count} сайтів");

            case 'price':
                $request->validate([
                    'price_amount'   => ['required', 'numeric', 'min:0'],
                    'price_currency' => ['required', 'in:UAH,USD,EUR'],
                    'price_label'    => ['nullable', 'string', 'max:100'],
                ]);
                foreach ($sites as $site) {
                    $site->prices()->create([
                        'amount'     => $request->input('price_amount'),
                        'currency'   => $request->input('price_currency'),
                        'label'      => $request->input('price_label') ?: null,
                        'is_visible' => true,
                        'sort_order' => 0,
                    ]);
                }
                return redirect()->route('sites.index')
                    ->with('success', "Batch: ціну додано до {$count} сайтів");

            case 'address':
                $request->validate([
                    'address_country_iso' => ['required', 'string', 'max:2'],
                    'address_city'        => ['required', 'string', 'max:100'],
                    'address_street'      => ['nullable', 'string', 'max:255'],
                ]);
                foreach ($sites as $site) {
                    $site->addresses()->create([
                        'country_iso' => strtoupper($request->input('address_country_iso')),
                        'city'        => $request->input('address_city'),
                        'street'      => $request->input('address_street') ?: null,
                        'sort_order'  => 0,
                    ]);
                }
                return redirect()->route('sites.index')
                    ->with('success', "Batch: адресу додано до {$count} сайтів");

            case 'social':
                $request->validate([
                    'social_platform' => ['required', 'in:instagram,facebook,telegram,youtube,tiktok,twitter,linkedin,viber,whatsapp,other'],
                    'social_url'      => ['required', 'string', 'max:255'],
                ]);
                foreach ($sites as $site) {
                    $site->socials()->create([
                        'platform'   => $request->input('social_platform'),
                        'url'        => $request->input('social_url'),
                        'sort_order' => 0,
                    ]);
                }
                return redirect()->route('sites.index')
                    ->with('success', "Batch: соцмережу додано до {$count} сайтів");

            case 'delete':
                Site::whereIn('id', $ids)->delete();
                return redirect()->route('sites.index')
                    ->with('success', "Видалено {$count} сайтів");
        }

        return redirect()->route('sites.index');
    }
}
