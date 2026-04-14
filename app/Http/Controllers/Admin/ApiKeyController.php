<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;

class ApiKeyController extends Controller
{
    public function generate(Site $site): RedirectResponse
    {
        $site->apiKey()->delete();

        $key = ApiKey::generate();

        $site->apiKey()->create([
            'key_hash'   => $key['hash'],
            'key_prefix' => $key['prefix'],
        ]);

        session()->flash('api_key_raw', $key['raw']);

        return redirect()->route('sites.show', $site);
    }

    public function revoke(Site $site): RedirectResponse
    {
        $site->apiKey?->update(['revoked_at' => now()]);

        return redirect()->route('sites.show', $site);
    }
}
