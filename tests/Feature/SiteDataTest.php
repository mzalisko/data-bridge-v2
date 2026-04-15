<?php
namespace Tests\Feature;

use App\Models\Site;
use App\Models\SitePhone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Tests\TestCase;

class SiteDataTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Site $site;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->user = User::factory()->create(['role' => 'admin']);
        $this->site = Site::factory()->create();
    }

    public function test_store_phone_creates_record(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('phones.store', $this->site), [
                'label'       => 'Main',
                'country_iso' => 'UA',
                'dial_code'   => '380',
                'number'      => '671234567',
                'is_primary'  => '1',
                'sort_order'  => 0,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('site_phones', [
            'site_id'     => $this->site->id,
            'number'      => '671234567',
            'country_iso' => 'UA',
        ]);
    }

    public function test_store_price_creates_record(): void
    {
        $this->actingAs($this->user)
            ->post(route('prices.store', $this->site), [
                'label'      => 'Базовий',
                'amount'     => '1500.00',
                'currency'   => 'UAH',
                'period'     => 'month',
                'is_visible' => '1',
                'sort_order' => 0,
            ]);

        $this->assertDatabaseHas('site_prices', [
            'site_id'  => $this->site->id,
            'label'    => 'Базовий',
            'currency' => 'UAH',
        ]);
    }

    public function test_store_address_creates_record(): void
    {
        $this->actingAs($this->user)
            ->post(route('addresses.store', $this->site), [
                'label'       => 'Головний офіс',
                'country_iso' => 'UA',
                'city'        => 'Kyiv',
                'street'      => 'Хрещатик',
                'building'    => '1',
                'is_primary'  => '1',
                'sort_order'  => 0,
            ]);

        $this->assertDatabaseHas('site_addresses', [
            'site_id' => $this->site->id,
            'city'    => 'Kyiv',
        ]);
    }

    public function test_store_social_creates_record(): void
    {
        $this->actingAs($this->user)
            ->post(route('socials.store', $this->site), [
                'platform'   => 'instagram',
                'handle'     => 'test_handle',
                'url'        => 'https://instagram.com/test_handle',
                'sort_order' => 0,
            ]);

        $this->assertDatabaseHas('site_socials', [
            'site_id'  => $this->site->id,
            'platform' => 'instagram',
            'handle'   => 'test_handle',
        ]);
    }
}
