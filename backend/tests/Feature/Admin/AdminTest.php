<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AccountBlock;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class AdminTest extends TestCase
{
    use AuthenticatesApi, RefreshDatabase;

    public static function endpoints(): array
    {
        return [
            ['GET', '/api/admin/dashboard'], ['GET', '/api/admin/users'],
            ['GET', '/api/admin/users/{user}'], ['POST', '/api/admin/users/{user}/block'],
            ['POST', '/api/admin/users/{user}/unblock'], ['GET', '/api/admin/reports'],
            ['GET', '/api/admin/reports/{report}'], ['POST', '/api/admin/reports/{report}/conclude'],
            ['DELETE', '/api/admin/reports/{report}'], ['GET', '/api/admin/settings'], ['PATCH', '/api/admin/settings'],
        ];
    }

    #[DataProvider('endpoints')]
    public function test_all_admin_endpoints_require_admin_gate(string $method, string $uri): void
    {
        $target = User::factory()->create();
        $report = Report::factory()->create();
        $uri = str_replace(['{user}', '{report}'], [(string) $target->id, (string) $report->id], $uri);
        $this->json($method, $uri)->assertUnauthorized();
        $this->signIn();
        $this->json($method, $uri)->assertForbidden();
    }

    public function test_dashboard_counts_active_accounts_received_reports_and_open_blocks(): void
    {
        $admin = $this->signIn(User::factory()->admin()->create());
        $active = User::factory()->create();
        User::factory()->create()->delete();
        $target = User::factory()->create();
        AccountBlock::factory()->for($target)->create(['blocked_by_user_id' => $admin->id]);
        AccountBlock::factory()->closed()->for($target)->create(['blocked_by_user_id' => $admin->id, 'unblocked_by_user_id' => $admin->id]);
        $actors = ['reporter_user_id' => $active->id, 'reported_user_id' => $target->id];
        Report::factory()->create($actors);
        Report::factory()->concluded()->create($actors + ['reviewer_user_id' => $admin->id]);
        Report::factory()->create($actors)->delete();
        $this->getJson('/api/admin/dashboard')->assertOk()->assertExactJson(['data' => [
            'active_users' => 2, 'received_reports' => 1, 'blocked_accounts' => 1,
        ]]);
    }

    public function test_admin_users_include_deactivated_accounts_and_are_paginated(): void
    {
        $this->signIn(User::factory()->admin()->create());
        User::factory()->count(21)->create();
        $deleted = User::factory()->create();
        $deleted->delete();
        $this->getJson('/api/admin/users')->assertOk()->assertJsonCount(20, 'data')->assertJsonPath('meta.total', 23);
        $this->getJson('/api/admin/users?page=2')->assertOk()->assertJsonCount(3, 'data');
        $response = $this->getJson('/api/admin/users/'.$deleted->id)->assertOk()->assertJsonPath('data.status', 'desativados');
        $this->assertArrayNotHasKey('password', $response->json('data'));
        $this->assertArrayNotHasKey('remember_token', $response->json('data'));
        $this->getJson('/api/admin/users/999999')->assertNotFound();
    }

    public function test_admin_can_search_by_name_or_username_and_filter_each_account_state(): void
    {
        $admin = $this->signIn(User::factory()->admin()->create());
        $active = User::factory()->create(['name' => 'Marina Costa', 'username' => 'marina_costa']);
        $blocked = User::factory()->create(['username' => 'blocked_person']);
        AccountBlock::factory()->create(['user_id' => $blocked->id, 'blocked_by_user_id' => $admin->id]);
        $deleted = User::factory()->create();
        $deleted->delete();
        $this->getJson('/api/admin/users?search=MARINA')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $active->id);
        $this->getJson('/api/admin/users?search=blocked_person')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $blocked->id);
        $this->getJson('/api/admin/users?status=ativos')->assertJsonCount(2, 'data');
        $this->getJson('/api/admin/users?status=bloqueados')->assertJsonCount(1, 'data')->assertJsonPath('data.0.is_blocked', true);
        $this->getJson('/api/admin/users?status=desativados')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $deleted->id);
        $this->getJson('/api/admin/users?search=%25')->assertJsonCount(0, 'data');
    }

    public static function invalidFilters(): array
    {
        return [
            ['/api/admin/users?status=desconhecido', 'status'],
            ['/api/admin/users?search[]=x', 'search'],
            ['/api/admin/users?page=0', 'page'],
            ['/api/admin/reports?status=pendente', 'status'],
            ['/api/admin/reports?page=x', 'page'],
        ];
    }

    #[DataProvider('invalidFilters')]
    public function test_admin_filter_validation(string $uri, string $field): void
    {
        $this->signIn(User::factory()->admin()->create());
        $this->getJson($uri)->assertUnprocessable()->assertJsonValidationErrors($field);
    }

    public function test_block_and_unblock_preserve_audited_history_and_existing_credentials(): void
    {
        $user = User::factory()->create(['password' => 'senha-segura']);
        $token = $this->postJson('/api/login', ['email' => $user->email, 'password' => 'senha-segura', 'device_name' => 'testes'])->json('data.token');
        $admin = $this->signIn(User::factory()->admin()->create());
        $response = $this->postJson('/api/admin/users/'.$user->id.'/block', ['reason' => 'Spam persistente.'])
            ->assertCreated()->assertJsonPath('data.blocked_by_user_id', $admin->id)->assertJsonPath('data.unblocked_at', null);
        $id = $response->json('data.id');
        $this->postJson('/api/admin/users/'.$user->id.'/block', ['reason' => 'Outra decisão.'])->assertUnprocessable();
        $this->assertDatabaseCount('account_blocks', 1);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/me')->assertOk();
        $this->signIn($admin);
        $this->postJson('/api/admin/users/'.$user->id.'/unblock')->assertOk()
            ->assertJsonPath('data.id', $id)->assertJsonPath('data.unblocked_by_user_id', $admin->id)
            ->assertJsonPath('data.reason', 'Spam persistente.');
        $this->assertFalse($user->isBlocked());
        $this->postJson('/api/admin/users/'.$user->id.'/unblock')->assertUnprocessable();
        $this->postJson('/api/admin/users/'.$user->id.'/block', ['reason' => 'Nova ocorrência.'])->assertCreated();
        $this->assertDatabaseCount('account_blocks', 2);
    }

    public function test_block_validates_reason_and_rejects_self_block(): void
    {
        $admin = $this->signIn(User::factory()->admin()->create());
        $target = User::factory()->create();
        foreach ([[], ['reason' => ' '], ['reason' => str_repeat('a', 2001)], ['reason' => ['texto']]] as $data) {
            $this->postJson('/api/admin/users/'.$target->id.'/block', $data)->assertUnprocessable()->assertJsonValidationErrors('reason');
        }
        $this->postJson('/api/admin/users/'.$admin->id.'/block', ['reason' => 'Motivo.'])->assertUnprocessable()->assertJsonValidationErrors('user');
        $this->postJson('/api/admin/users/999999/block', ['reason' => 'Motivo.'])->assertNotFound();
        $this->postJson('/api/admin/users/999999/unblock')->assertNotFound();
        $this->assertDatabaseCount('account_blocks', 0);
    }

    public function test_admin_can_block_and_unblock_deactivated_account(): void
    {
        $this->signIn(User::factory()->admin()->create());
        $target = User::factory()->create();
        $target->delete();
        $this->postJson('/api/admin/users/'.$target->id.'/block', ['reason' => 'Motivo.'])->assertCreated();
        $this->postJson('/api/admin/users/'.$target->id.'/unblock')->assertOk();
        $this->assertSoftDeleted($target);
    }

    public function test_admin_lists_reports_by_status_and_sees_details(): void
    {
        $this->signIn(User::factory()->admin()->create());
        $received = Report::factory()->create();
        $concluded = Report::factory()->concluded()->create();
        Report::factory()->create()->delete();
        $this->getJson('/api/admin/reports')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.total', 2);
        $this->getJson('/api/admin/reports?status=recebida')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $received->id);
        $this->getJson('/api/admin/reports?status=concluida')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $concluded->id);
        $this->getJson('/api/admin/reports/'.$received->id)->assertOk()->assertJsonPath('data.id', $received->id);
        $this->getJson('/api/admin/reports/999999')->assertNotFound();
    }

    public function test_conclusion_is_final_and_archiving_preserves_decision_and_reviewer(): void
    {
        $this->freezeSecond();
        $admin = $this->signIn(User::factory()->admin()->create());
        $report = Report::factory()->create();
        $uri = '/api/admin/reports/'.$report->id;
        $this->postJson($uri.'/conclude', ['decision' => 'Conduta analisada.'])->assertOk()
            ->assertJsonPath('data.status', 'concluida')->assertJsonPath('data.reviewer_user_id', $admin->id)
            ->assertJsonPath('data.decided_at', now()->toISOString());
        $original = $report->fresh();
        $this->signIn(User::factory()->admin()->create());
        $this->postJson($uri.'/conclude', ['decision' => 'Substituição.'])->assertStatus(409);
        $this->deleteJson($uri)->assertOk();
        $this->assertSoftDeleted($report);
        $archived = Report::withTrashed()->findOrFail($report->id);
        $this->assertSame($original->reviewer_user_id, $archived->reviewer_user_id);
        $this->assertSame('Conduta analisada.', $archived->decision);
        $this->assertTrue($original->decided_at->equalTo($archived->decided_at));
        $this->getJson($uri)->assertNotFound();
        $this->postJson($uri.'/conclude', ['decision' => 'Reabertura.'])->assertNotFound();
        $this->deleteJson($uri)->assertNotFound();
        $this->getJson('/api/admin/reports')->assertJsonCount(0, 'data');
    }

    public function test_admin_can_archive_received_report_without_creating_decision(): void
    {
        $this->signIn(User::factory()->admin()->create());
        $report = Report::factory()->create();
        $this->deleteJson('/api/admin/reports/'.$report->id)->assertOk();
        $archived = Report::withTrashed()->findOrFail($report->id);
        $this->assertNull($archived->decision);
        $this->assertTrue($archived->isReceived());
    }

    public function test_conclude_requires_nonempty_text_decision(): void
    {
        $this->signIn(User::factory()->admin()->create());
        $report = Report::factory()->create();
        foreach ([[], ['decision' => ' '], ['decision' => str_repeat('a', 2001)], ['decision' => ['texto']]] as $data) {
            $this->postJson('/api/admin/reports/'.$report->id.'/conclude', $data)->assertUnprocessable()->assertJsonValidationErrors('decision');
        }
        $this->assertTrue($report->fresh()->isReceived());
        $this->postJson('/api/admin/reports/999999/conclude', ['decision' => 'Motivo.'])->assertNotFound();
        $this->deleteJson('/api/admin/reports/999999')->assertNotFound();
    }
}
