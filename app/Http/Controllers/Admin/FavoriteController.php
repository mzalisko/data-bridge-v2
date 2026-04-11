<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    public function toggle(Site $site): JsonResponse
    {
        $user = auth()->user();

        if ($user->favoriteSites()->where('site_id', $site->id)->exists()) {
            $user->favoriteSites()->detach($site->id);
            $isFav = false;
        } else {
            $user->favoriteSites()->attach($site->id);
            $isFav = true;
        }

        return response()->json(['favorite' => $isFav]);
    }
}
