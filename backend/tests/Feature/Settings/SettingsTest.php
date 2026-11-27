<?php

declare(strict_types=1);

namespace Tests\Feature\Settings;

use App\Models\AppSetting;
use App\Models\User;
use App\Support\AppSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Admin\AuthenticatesApi;
use Tests\TestCase;

final class SettingsTest extends TestCase
{
    use AuthenticatesApi, RefreshDatabase;

    public function test_admin_can_read_typed_defaults(): void
    {
        $this->signIn(User::factory()->admin()->create());
        $this->getJson('/api/admin/settings')->assertOk()->assertExactJson(['data' => [
            'max_upload_bytes' => 25 * 1024 * 1024, 'log_retention_days' => 30,
        ]]);
        $this->assertDatabaseCount('app_settings', 0);
    }

    public function test_partial_updates_persist_json_numbers_and_do_not_reset_other_settings(): void
    {
        $this->freezeSecond();
        $this->signIn(User::factory()->admin()->create());
        $this->patchJson('/api/admin/settings', ['max_upload_bytes' => 1024, 'log_retention_days' => 90])->assertOk()
            ->assertJsonPath('data.max_upload_bytes', 1024)->assertJsonPath('data.log_retention_days', 90);
        $this->patchJson('/api/admin/settings', ['max_upload_bytes' => 2048])->assertOk()->assertJsonPath('data.log_retention_days', 90);
        $this->getJson('/api/admin/settings')->assertJsonPath('data.max_upload_bytes', 2048);
        $setting = AppSetting::findOrFail('max_upload_bytes');
        $this->assertSame(2048, $setting->value);
        $this->assertTrue($setting->updated_at->equalTo(now()));
        $this->assertSame(2048, app(AppSettings::class)->maxUploadBytes());
        $this->assertSame(90, app(AppSettings::class)->logRetentionDays());
    }

    public static function invalidSettings(): array
    {
        return [
            [[], 'settings'], [['max_upload_bytes' => 0], 'max_upload_bytes'],
            [['log_retention_days' => -1], 'log_retention_days'],
            [['max_upload_bytes' => 1.5], 'max_upload_bytes'],
            [['log_retention_days' => 'trinta'], 'log_retention_days'],
            [['max_upload_bytes' => null], 'max_upload_bytes'],
            [['encryption_enabled' => false], 'encryption_enabled'],
            [['max_upload_bytes' => 1024, 'unexpected' => true], 'unexpected'],
        ];
    }

    #[DataProvider('invalidSettings')]
    public function test_invalid_or_unknown_settings_are_rejected_atomically(array $data, string $field): void
    {
        $this->signIn(User::factory()->admin()->create());
        $this->patchJson('/api/admin/settings', $data)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('app_settings', 0);
    }

    public function test_invalid_persisted_values_fall_back_to_safe_defaults(): void
    {
        AppSetting::create(['key' => 'max_upload_bytes', 'value' => ['invalid']]);
        AppSetting::create(['key' => 'log_retention_days', 'value' => -1]);
        $this->assertSame(25 * 1024 * 1024, app(AppSettings::class)->maxUploadBytes());
        $this->assertSame(30, app(AppSettings::class)->logRetentionDays());
    }
}
