<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Support\AppSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Feature\Admin\AuthenticatesApi;
use Tests\TestCase;

final class SettingsTest extends TestCase
{
    use AuthenticatesApi, RefreshDatabase;

    public function test_admin_can_read_typed_defaults_without_persisted_settings(): void
    {
        $this->signIn(User::factory()->admin()->create());
        $this->getJson('/api/admin/settings')->assertOk()->assertExactJson(['data' => [
            'max_upload_bytes' => 25 * 1024 * 1024, 'log_retention_days' => 30,
        ]]);
        $this->assertFalse(Schema::hasTable('app_settings'));
    }

    public function test_settings_are_read_from_configuration_and_mb_are_converted_to_bytes(): void
    {
        config(['directchannel.max_upload_mb' => 2, 'directchannel.log_retention_days' => 90]);
        $this->signIn(User::factory()->admin()->create());
        $this->getJson('/api/admin/settings')->assertOk()->assertExactJson(['data' => [
            'max_upload_bytes' => 2 * 1024 * 1024, 'log_retention_days' => 90,
        ]]);
        $this->assertSame(2 * 1024 * 1024, app(AppSettings::class)->maxUploadBytes());
        $this->assertSame(90, app(AppSettings::class)->logRetentionDays());
    }

    public function test_patch_settings_is_not_available_and_cannot_change_configuration(): void
    {
        $this->signIn(User::factory()->admin()->create());
        $this->patchJson('/api/admin/settings', ['max_upload_bytes' => 1024, 'log_retention_days' => 90])->assertStatus(405);
        $this->getJson('/api/admin/settings')->assertOk()->assertJsonPath('data.max_upload_bytes', 25 * 1024 * 1024)->assertJsonPath('data.log_retention_days', 30);
    }
}
