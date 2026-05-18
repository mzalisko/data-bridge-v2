<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesSiteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PriceRequest;
use App\Models\Site;
use App\Models\SitePrice;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SitePriceController extends Controller
{
    use ManagesSiteData;

    protected function entityType(): string
    {
        return 'price';
    }

    protected function flashMessage(string $action): string
    {
        return match ($action) {
            'create' => 'Ціну додано',
            'update' => 'Ціну оновлено',
            'delete' => 'Ціну видалено',
        };
    }

    protected function logSummary(Model $record, string $action): string
    {
        $verb = match ($action) {
            'create' => 'додана',
            'update' => 'оновлена',
            'delete' => 'видалена',
        };

        return "Ціна «{$record->label}» {$verb}";
    }

    protected function preprocess(Request $request, array $data): array
    {
        $data['is_visible'] = $request->boolean('is_visible', true);

        return $data;
    }

    public function store(PriceRequest $request, Site $site): RedirectResponse
    {
        return $this->createSiteRecord($site, $request, $request->validated());
    }

    public function update(PriceRequest $request, Site $site, SitePrice $price): RedirectResponse
    {
        return $this->updateSiteRecord($site, $request, $price, $request->validated());
    }

    public function destroy(Site $site, SitePrice $price): RedirectResponse
    {
        return $this->deleteSiteRecord($site, $price);
    }
}
