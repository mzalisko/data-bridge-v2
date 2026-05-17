<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomPlatform;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomPlatformController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'label'    => ['required', 'string', 'max:50'],
            'category' => ['required', 'in:messenger,social'],
        ]);

        $slug     = Str::slug($data['label'], '_');
        $platform = CustomPlatform::firstOrCreate(
            ['slug' => $slug],
            ['label' => trim($data['label']), 'category' => $data['category']]
        );

        return response()->json(['slug' => $platform->slug, 'label' => $platform->label]);
    }
}
