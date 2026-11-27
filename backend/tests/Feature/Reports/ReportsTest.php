<?php

declare(strict_types=1);

namespace Tests\Feature\Reports;

use App\Enums\ReportReason;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Admin\AuthenticatesApi;
use Tests\TestCase;

final class ReportsTest extends TestCase
{
    use AuthenticatesApi, RefreshDatabase;

    public static function reasons(): array
    {
        return array_map(fn (ReportReason $reason): array => [$reason->value], ReportReason::cases());
    }

    #[DataProvider('reasons')]
    public function test_user_can_report_existing_account_with_canonical_reason(string $reason): void
    {
        $user = $this->signIn();
        $target = User::factory()->create();
        $response = $this->postJson('/api/reports', [
            'reported_user_id' => $target->id, 'reason' => $reason, 'narrative' => 'Contato insistente.',
            'reporter_user_id' => $target->id, 'status' => 'concluida', 'decision' => 'Injetada',
        ])->assertCreated()->assertJsonPath('data.status', 'recebida')
            ->assertJsonPath('data.reason', $reason)->assertJsonPath('data.reporter_user_id', $user->id)
            ->assertJsonPath('data.reported_user_id', $target->id)->assertJsonPath('data.decision', null);
        $this->getJson('/api/reports/'.$response->json('data.id'))->assertOk()->assertJsonPath('data.narrative', 'Contato insistente.');
    }

    public function test_narrative_is_optional_and_account_may_be_deactivated(): void
    {
        $this->signIn();
        $target = User::factory()->create();
        $target->delete();
        $this->postJson('/api/reports', ['reported_user_id' => $target->id, 'reason' => ReportReason::Spam->value])
            ->assertCreated()->assertJsonPath('data.narrative', null);
    }

    public static function invalidReports(): array
    {
        return [
            'missing target' => ['reported_user_id', null],
            'unknown target' => ['reported_user_id', 999999],
            'invalid target' => ['reported_user_id', 'invalido'],
            'missing reason' => ['reason', null],
            'invalid reason' => ['reason', 'Outro'],
            'long narrative' => ['narrative', str_repeat('a', 2001)],
            'invalid narrative' => ['narrative', ['texto']],
        ];
    }

    #[DataProvider('invalidReports')]
    public function test_report_validation(string $field, mixed $value): void
    {
        $this->signIn();
        $target = User::factory()->create();
        $data = ['reported_user_id' => $target->id, 'reason' => ReportReason::Spam->value];
        $data[$field] = $value;
        $this->postJson('/api/reports', $data)->assertUnprocessable()->assertJsonValidationErrors($field);
        $this->assertDatabaseCount('reports', 0);
    }

    public function test_user_cannot_report_self(): void
    {
        $user = $this->signIn();
        $this->postJson('/api/reports', ['reported_user_id' => $user->id, 'reason' => ReportReason::Spam->value])
            ->assertUnprocessable()->assertJsonValidationErrors('reported_user_id');
    }

    public function test_list_only_contains_own_unarchived_reports_and_is_paginated(): void
    {
        $user = $this->signIn();
        $reports = Report::factory()->count(21)->create(['reporter_user_id' => $user->id]);
        Report::factory()->create(['reporter_user_id' => $user->id])->delete();
        Report::factory()->create();
        $this->getJson('/api/reports')->assertOk()->assertJsonCount(20, 'data')->assertJsonPath('meta.total', 21)
            ->assertJsonPath('data.0.id', $reports->last()->id);
        $this->getJson('/api/reports?page=2')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/reports?page=-1')->assertUnprocessable()->assertJsonValidationErrors('page');
    }

    public function test_only_owner_or_admin_can_view_report_and_archived_reports_are_hidden(): void
    {
        $report = Report::factory()->create();
        $this->signIn(User::findOrFail($report->reported_user_id));
        $this->getJson('/api/reports/'.$report->id)->assertNotFound();
        $this->signIn(User::factory()->admin()->create());
        $this->getJson('/api/reports/'.$report->id)->assertOk();
        $this->getJson('/api/reports')->assertJsonCount(0, 'data');
        $this->signIn(User::findOrFail($report->reporter_user_id));
        $this->getJson('/api/reports/'.$report->id)->assertOk();
        $report->delete();
        $this->getJson('/api/reports/'.$report->id)->assertNotFound();
        $this->getJson('/api/reports/999999')->assertNotFound();
    }

    public function test_reports_are_throttled_per_authenticated_user(): void
    {
        $this->signIn();
        $data = ['reported_user_id' => User::factory()->create()->id, 'reason' => ReportReason::Fraud->value];
        for ($i = 0; $i < 6; $i++) {
            $this->postJson('/api/reports', $data)->assertCreated();
        }
        $this->postJson('/api/reports', $data)->assertStatus(429)->assertHeader('Retry-After');
        $this->signIn();
        $this->postJson('/api/reports', $data)->assertCreated();
    }

    public function test_report_endpoints_require_authentication(): void
    {
        $report = Report::factory()->create();
        $this->getJson('/api/reports')->assertUnauthorized();
        $this->postJson('/api/reports')->assertUnauthorized();
        $this->getJson('/api/reports/'.$report->id)->assertUnauthorized();
    }
}
