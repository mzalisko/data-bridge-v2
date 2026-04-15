<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Models\SitePhone;
use App\Models\SitePrice;
use App\Models\SiteAddress;
use App\Models\SiteSocial;
use App\Models\Country;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DataBrowserController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->get('type', 'phones');
        $q    = trim($request->get('q', ''));

        $rows     = collect();
        $sites    = Site::orderBy('name')->get(['id', 'name', 'url']);
        $countries = Country::orderBy('sort_order')->orderBy('iso')->get(['iso', 'dial_code', 'name']);

        switch ($type) {
            case 'phones':
                $query = SitePhone::with('site')->orderBy('id');
                if ($q) {
                    $query->where(function ($w) use ($q) {
                        $w->where('number', 'like', "%{$q}%")
                          ->orWhere('label', 'like', "%{$q}%")
                          ->orWhere('country_iso', 'like', "%{$q}%")
                          ->orWhere('dial_code', 'like', "%{$q}%")
                          ->orWhereHas('site', fn($s) => $s->where('name', 'like', "%{$q}%"));
                    });
                }
                $rows = $query->get();
                break;

            case 'prices':
                $query = SitePrice::with('site')->orderBy('id');
                if ($q) {
                    $query->where(function ($w) use ($q) {
                        $w->where('label', 'like', "%{$q}%")
                          ->orWhere('currency', 'like', "%{$q}%")
                          ->orWhere('amount', 'like', "%{$q}%")
                          ->orWhereHas('site', fn($s) => $s->where('name', 'like', "%{$q}%"));
                    });
                }
                $rows = $query->get();
                break;

            case 'addresses':
                $query = SiteAddress::with('site')->orderBy('id');
                if ($q) {
                    $query->where(function ($w) use ($q) {
                        $w->where('city', 'like', "%{$q}%")
                          ->orWhere('street', 'like', "%{$q}%")
                          ->orWhere('country_iso', 'like', "%{$q}%")
                          ->orWhere('label', 'like', "%{$q}%")
                          ->orWhereHas('site', fn($s) => $s->where('name', 'like', "%{$q}%"));
                    });
                }
                $rows = $query->get();
                break;

            case 'socials':
                $query = SiteSocial::with('site')->orderBy('id');
                if ($q) {
                    $query->where(function ($w) use ($q) {
                        $w->where('url', 'like', "%{$q}%")
                          ->orWhere('platform', 'like', "%{$q}%")
                          ->orWhere('handle', 'like', "%{$q}%")
                          ->orWhereHas('site', fn($s) => $s->where('name', 'like', "%{$q}%"));
                    });
                }
                $rows = $query->get();
                break;
        }

        return view('admin.data.index', compact('type', 'q', 'rows', 'sites', 'countries'));
    }

    public function bulkDelete(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:phones,prices,addresses,socials'],
            'ids'  => ['required', 'array', 'min:1'],
            'ids.*'=> ['integer'],
        ]);

        $model = $this->modelForType($data['type']);
        $count = $model::whereIn('id', $data['ids'])->delete();

        return redirect()->route('data.index', ['type' => $data['type'], 'q' => $request->get('q')])
            ->with('success', "Видалено {$count} записів");
    }

    public function bulkEdit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'  => ['required', 'in:phones,prices,addresses,socials'],
            'ids'   => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'field' => ['required', 'string'],
            'value' => ['nullable', 'string', 'max:255'],
        ]);

        $type    = $data['type'];
        $ids     = $data['ids'];
        $field   = $data['field'];
        $value   = $data['value'];
        $allowed = $this->editableFields($type);

        if (! in_array($field, $allowed)) {
            return back()->with('error', 'Поле не дозволено для редагування.');
        }

        // Cast value for specific fields
        if ($field === 'country_iso') {
            $value = strtoupper($value);
        }

        $model = $this->modelForType($type);
        $count = $model::whereIn('id', $ids)->update([$field => $value]);

        return redirect()->route('data.index', ['type' => $type, 'q' => $request->get('q')])
            ->with('success', "Оновлено {$count} записів → «{$field}»");
    }

    public function bulkCopy(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type'        => ['required', 'in:phones,prices,addresses,socials'],
            'ids'         => ['required', 'array', 'min:1'],
            'ids.*'       => ['integer'],
            'target_ids'  => ['required', 'array', 'min:1'],
            'target_ids.*'=> ['integer', 'exists:sites,id'],
        ]);

        $type      = $data['type'];
        $model     = $this->modelForType($type);
        $records   = $model::whereIn('id', $data['ids'])->get();
        $targetIds = $data['target_ids'];
        $copied    = 0;

        foreach ($targetIds as $siteId) {
            foreach ($records as $record) {
                $attrs = $record->toArray();
                unset($attrs['id']);
                $attrs['site_id'] = $siteId;
                $model::create($attrs);
                $copied++;
            }
        }

        return redirect()->route('data.index', ['type' => $type, 'q' => $request->get('q')])
            ->with('success', "Скопійовано {$copied} записів до " . count($targetIds) . " сайтів");
    }

    private function modelForType(string $type): string
    {
        return match($type) {
            'phones'    => SitePhone::class,
            'prices'    => SitePrice::class,
            'addresses' => SiteAddress::class,
            'socials'   => SiteSocial::class,
        };
    }

    private function editableFields(string $type): array
    {
        return match($type) {
            'phones'    => ['number', 'label', 'country_iso', 'dial_code', 'is_primary'],
            'prices'    => ['amount', 'currency', 'label', 'period'],
            'addresses' => ['country_iso', 'city', 'street', 'building', 'postal_code', 'label'],
            'socials'   => ['platform', 'url', 'handle'],
        };
    }
}
