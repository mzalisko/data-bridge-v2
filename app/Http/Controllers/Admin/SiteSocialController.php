<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesSiteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SocialRequest;
use App\Models\CustomPlatform;
use App\Models\Site;
use App\Models\SiteSocial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteSocialController extends Controller
{
    use ManagesSiteData;

    protected function entityType(): string
    {
        return 'social';
    }

    protected function flashMessage(string $action): string
    {
        return match ($action) {
            'create' => 'Соцмережу додано',
            'update' => 'Соцмережу оновлено',
            'delete' => 'Соцмережу видалено',
        };
    }

    protected function logSummary(Model $record, string $action): string
    {
        $verb = match ($action) {
            'create' => 'додано',
            'update' => 'оновлено',
            'delete' => 'видалено',
        };

        return "{$record->platform} {$record->handle} {$verb}";
    }

    protected function preprocess(Request $request, array $data): array
    {
        if ($request->input('platform') === '__new__' && $request->input('platform_custom')) {
            $platform = CustomPlatform::fromLabel($request->input('platform_custom'), 'messenger');
            $data['platform'] = $platform->slug;
        }
        unset($data['platform_custom']);

        return $data;
    }

    public function store(SocialRequest $request, Site $site): RedirectResponse
    {
        return $this->createSiteRecord($site, $request, $request->validated());
    }

    public function update(SocialRequest $request, Site $site, SiteSocial $social): RedirectResponse
    {
        return $this->updateSiteRecord($site, $request, $social, $request->validated());
    }

    public function destroy(Site $site, SiteSocial $social): RedirectResponse
    {
        return $this->deleteSiteRecord($site, $social);
    }

    public function reorder(Request $request, Site $site): JsonResponse
    {
        return $this->reorderSiteRecords($request, $site);
    }
}
