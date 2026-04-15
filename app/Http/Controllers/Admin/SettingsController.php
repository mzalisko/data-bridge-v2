<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $countries = Country::orderBy('sort_order')->orderBy('iso')->get();
        return view('admin.settings.index', compact('countries'));
    }

    public function storeCountry(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'iso'       => ['required', 'string', 'size:2', 'unique:countries,iso'],
            'dial_code' => ['required', 'string', 'max:8'],
            'name'      => ['nullable', 'string', 'max:100'],
        ]);

        $data['iso'] = strtoupper($data['iso']);
        $data['sort_order'] = Country::max('sort_order') + 1;

        Country::create($data);

        return redirect()->route('settings.index')
            ->with('success', 'Країну додано');
    }

    public function destroyCountry(Country $country): RedirectResponse
    {
        $country->delete();

        return redirect()->route('settings.index')
            ->with('success', 'Країну видалено');
    }
}
