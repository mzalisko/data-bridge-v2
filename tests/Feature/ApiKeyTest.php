<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiKeyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(PreventRequestForgery::class);
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    // ── generate ──────────────────────────────────────────────────

    public function test_generate_creates_api_key_for_site(): void
    {
        $site = Site::factory()->create();

        $this->actingAs($this->admin)
            ->post("/sites/{$site->id}/api-key/generate")
            ->assertRedirect("/sites/{$site->id}");

        $this->assertDatabaseHas('api_keys', [
            'site_id'    => $site->id,
            'revoked_at' => null,
        ]);
    }

    public function test_generate_replaces_existing_key(): void
    {
        $site = Site::factory()->create();
        $this->actingAs($this->admin)
            ->post("/sites/{$site->id}/api-key/generate");

        $firstPrefix = ApiKey::where('site_id', $site->id)->value('key_prefix');

        $this->actingAs($this->admin)
            ->post("/sites/{$site->id}/api-key/generate");

        $this->assertDatabaseCount('api_keys', 1);
        $newPrefix = ApiKey::where('site_id', $site->id)->value('key_prefix');
        $this->assertNotEquals($firstPrefix, $newPrefix);
    }

    public function test_generate_flashes_raw_key_to_session(): void
    {
        $site = Site::factory()->create();

        $this->actingAs($this->admin)
            ->post("/sites/{$site->id}/api-key/generate")
            ->assertSessionHas('api_key_raw');

        $raw = session('api_key_raw');
        $this->assertStringStartsWith('dbapi_', $raw);
        $this->assertEquals(38, strlen($raw));
    }

    public function test_generate_requires_auth(): void
    {
        $site = Site::factory()->create();

        $this->post("/sites/{$site->id}/api-key/generate")
            ->assertRedirect('/login');
    }

    // ── revoke ────────────────────────────────────────────────────

    public function test_revoke_sets_revoked_at(): void
    {
        $site = Site::factory()->create();
        $this->actingAs($this->admin)
            ->post("/sites/{$site->id}/api-key/generate");

        $this->actingAs($this->admin)
            ->post("/sites/{$site->id}/api-key/revoke")
            ->assertRedirect("/sites/{$site->id}");

        $this->assertNotNull(
            ApiKey::where('site_id', $site->id)->value('revoked_at')
        );
    }

    public function test_revoke_on_missing_key_redirects_without_error(): void
    {
        $site = Site::factory()->create();

        $this->actingAs($this->admin)
            ->post("/sites/{$site->id}/api-key/revoke")
            ->assertRedirect("/sites/{$site->id}");
    }

    public function test_revoke_requires_auth(): void
    {
        $site = Site::factory()->create();

        $this->post("/sites/{$site->id}/api-key/revoke")
            ->assertRedirect('/login');
    }
}
