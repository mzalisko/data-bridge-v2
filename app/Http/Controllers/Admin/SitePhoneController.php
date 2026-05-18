<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesSiteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PhoneRequest;
use App\Models\Site;
use App\Models\SitePhone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SitePhoneController extends Controller
{
    use ManagesSiteData;

    protected function entityType(): string
    {
        return 'phone';
    }

    protected function flashMessage(string $action): string
    {
        return match ($action) {
            'create' => 'Телефон додано',
            'update' => 'Телефон оновлено',
            'delete' => 'Телефон видалено',
        };
    }

    protected function logSummary(Model $record, string $action): string
    {
        $verb = match ($action) {
            'create' => 'додано',
            'update' => 'оновлено',
            'delete' => 'видалено',
        };

        return "Телефон {$record->number} {$verb}";
    }

    protected function preprocess(Request $request, array $data): array
    {
        $data['is_primary'] = $request->boolean('is_primary');

        return $data;
    }

    public function store(PhoneRequest $request, Site $site): RedirectResponse
    {
        return $this->createSiteRecord($site, $request, $request->validated());
    }

    public function update(PhoneRequest $request, Site $site, SitePhone $phone): RedirectResponse
    {
        return $this->updateSiteRecord($site, $request, $phone, $request->validated());
    }

    public function destroy(Site $site, SitePhone $phone): RedirectResponse
    {
        return $this->deleteSiteRecord($site, $phone);
    }

    public function reorder(Request $request, Site $site): JsonResponse
    {
        return $this->reorderSiteRecords($request, $site);
    }
}
