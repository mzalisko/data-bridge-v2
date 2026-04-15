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
}
