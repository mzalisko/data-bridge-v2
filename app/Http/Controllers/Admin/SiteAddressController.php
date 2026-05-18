<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Concerns\ManagesSiteData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AddressRequest;
use App\Models\Site;
use App\Models\SiteAddress;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SiteAddressController extends Controller
{
    use ManagesSiteData;

    protected function entityType(): string
    {
        return 'address';
    }

    protected function flashMessage(string $action): string
    {
        return match ($action) {
            'create' => 'Адресу додано',
            'update' => 'Адресу оновлено',
            'delete' => 'Адресу видалено',
        };
    }

    protected function logSummary(Model $record, string $action): string
    {
        $verb = match ($action) {
            'create' => 'додано',
            'update' => 'оновлено',
            'delete' => 'видалено',
        };

        return "Адресу {$record->city} {$verb}";
    }

    protected function preprocess(Request $request, array $data): array
    {
        $data['is_primary'] = $request->boolean('is_primary');

        return $data;
    }

    public function store(AddressRequest $request, Site $site): RedirectResponse
    {
        return $this->createSiteRecord($site, $request, $request->validated());
    }

    public function update(AddressRequest $request, Site $site, SiteAddress $address): RedirectResponse
    {
        return $this->updateSiteRecord($site, $request, $address, $request->validated());
    }

    public function destroy(Site $site, SiteAddress $address): RedirectResponse
    {
        return $this->deleteSiteRecord($site, $address);
    }
}
