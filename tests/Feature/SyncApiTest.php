<?php

namespace Tests\Feature;

use App\Models\ApiKey;
use App\Models\Site;
use App\Models\SitePhone;
use App\Models\SiteGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SyncApiTest extends TestCase
{
    use RefreshDatabase;

    private Site   $site;
    private string $rawKey;

    protected function setUp(): void
    {
        parent::setUp();

        $this->site = Site::factory()->create(['is_active' => true]);

        $this->rawKey = 'dbapi_' . bin2hex(random_bytes(16));

        ApiKey::create([
            'site_id'     => $this->site->id,
            'key_hash'    => Hash::make($this->rawKey),
            'key_prefix'  => substr($this->rawKey, 0, 12),
            'permissions' => ['phones.read', 'prices.read', 'addresses.read', 'socials.read'],
        ]);
    }

    // ── health ────────────────────────────────────────────────────

    public function test_health_returns_ok(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonFragment(['status' => 'ok']);
    }

    // ── auth ──────────────────────────────────────────────────────

    public function test_sync_requires_bearer_token(): void
    {
        $this->getJson('/api/v1/sync')
            ->assertUnauthorized();
    }

    public function test_sync_rejects_invalid_key(): void
    {
        $this->withToken('dbapi_invalid_key_here_1234567890xx')
            ->getJson('/api/v1/sync')
            ->assertUnauthorized();
    }

    public function test_sync_rejects_inactive_site(): void
    {
        $inactiveSite = Site::factory()->create(['is_active' => false]);
        $raw          = 'dbapi_' . bin2hex(random_bytes(16));

        ApiKey::create([
            'site_id'     => $inactiveSite->id,
            'key_hash'    => Hash::make($raw),
            'key_prefix'  => substr($raw, 0, 12),
            'permissions' => ['phones.read'],
        ]);

        $this->withToken($raw)
            ->getJson('/api/v1/sync')
            ->assertForbidden();
    }

    public function test_sync_rejects_revoked_key(): void
    {
        $site = Site::factory()->create(['is_active' => true]);
        $raw  = 'dbapi_' . bin2hex(random_bytes(16));

        ApiKey::create([
            'site_id'    => $site->id,
            'key_hash'   => Hash::make($raw),
            'key_prefix' => substr($raw, 0, 12),
            'permissions' => ['phones.read'],
            'revoked_at' => now(),
        ]);

        $this->withToken($raw)
            ->getJson('/api/v1/sync')
            ->assertUnauthorized();
    }

    // ── pull full ─────────────────────────────────────────────────

    public function test_pull_returns_all_data_types(): void
    {
        SitePhone::create([
            'site_id'    => $this->site->id,
            'country_iso' => 'UA',
            'dial_code'  => '+380',
            'number'     => '44 123-45-67',
            'is_primary' => true,
            'sort_order' => 0,
        ]);

        $response = $this->withToken($this->rawKey)
            ->getJson('/api/v1/sync')
            ->assertOk();

        $response->assertJsonStructure([
            'status', 'site_id', 'synced_at', 'checksum',
            'data' => ['phones', 'prices', 'addresses', 'socials'],
        ]);

        $this->assertCount(1, $response->json('data.phones'));
        $this->assertEquals('ok', $response->json('status'));
    }

    public function test_pull_creates_sync_log(): void
    {
        $this->withToken($this->rawKey)
            ->getJson('/api/v1/sync')
            ->assertOk();

        $this->assertDatabaseHas('sync_logs', [
            'site_id' => $this->site->id,
            'status'  => 'ok',
        ]);
    }

    public function test_pull_since_filters_by_timestamp(): void
    {
        SitePhone::create([
            'site_id'    => $this->site->id,
            'country_iso' => 'UA',
            'dial_code'  => '+380',
            'number'     => '44 111-11-11',
            'sort_order' => 0,
        ]);

        // ?since=far-future should return nothing
        $futureTs = strtotime('+1 hour');

        $response = $this->withToken($this->rawKey)
            ->getJson("/api/v1/sync?since={$futureTs}")
            ->assertOk();

        $this->assertCount(0, $response->json('data.phones'));
    }

    // ── pull single type ──────────────────────────────────────────

    public function test_pull_phones_returns_phones_only(): void
    {
        $this->withToken($this->rawKey)
            ->getJson('/api/v1/sync/phones')
            ->assertOk()
            ->assertJsonStructure(['status', 'site_id', 'synced_at', 'data', 'checksum']);
    }

    public function test_pull_phones_denied_without_permission(): void
    {
        $site = Site::factory()->create(['is_active' => true]);
        $raw  = 'dbapi_' . bin2hex(random_bytes(16));

        ApiKey::create([
            'site_id'    => $site->id,
            'key_hash'   => Hash::make($raw),
            'key_prefix' => substr($raw, 0, 12),
            'permissions' => [], // no permissions
        ]);

        $this->withToken($raw)
            ->getJson('/api/v1/sync/phones')
            ->assertForbidden();
    }

    // ── write ops ─────────────────────────────────────────────────

    public function test_store_phone_requires_write_permission(): void
    {
        $this->withToken($this->rawKey) // read-only key
            ->postJson('/api/v1/phones', [
                'country_iso' => 'UA',
                'dial_code'  => '+380',
                'number'     => '97 999-99-99',
            ])
            ->assertForbidden();
    }

    public function test_store_phone_with_write_permission(): void
    {
        $site = Site::factory()->create(['is_active' => true]);
        $raw  = 'dbapi_' . bin2hex(random_bytes(16));

        ApiKey::create([
            'site_id'    => $site->id,
            'key_hash'   => Hash::make($raw),
            'key_prefix' => substr($raw, 0, 12),
            'permissions' => ['phones.read', 'phones.write'],
        ]);

        $this->withToken($raw)
            ->postJson('/api/v1/phones', [
                'country_iso' => 'UA',
                'dial_code'  => '+380',
                'number'     => '97 999-99-99',
                'is_primary' => false,
            ])
            ->assertCreated()
            ->assertJsonFragment(['status' => 'ok']);

        $this->assertDatabaseHas('site_phones', [
            'site_id' => $site->id,
            'number'  => '97 999-99-99',
        ]);
    }

    public function test_delete_phone_with_write_permission(): void
    {
        $site = Site::factory()->create(['is_active' => true]);
        $raw  = 'dbapi_' . bin2hex(random_bytes(16));

        ApiKey::create([
            'site_id'    => $site->id,
            'key_hash'   => Hash::make($raw),
            'key_prefix' => substr($raw, 0, 12),
            'permissions' => ['phones.write'],
        ]);

        $phone = SitePhone::create([
            'site_id'    => $site->id,
            'country_iso' => 'UA',
            'dial_code'  => '+380',
            'number'     => '44 555-55-55',
            'sort_order' => 0,
        ]);

        $this->withToken($raw)
            ->deleteJson("/api/v1/phones/{$phone->id}")
            ->assertOk()
            ->assertJsonFragment(['deleted_id' => $phone->id]);

        $this->assertDatabaseMissing('site_phones', ['id' => $phone->id]);
    }
}
