<?php

declare(strict_types=1);

namespace Tests\Feature\Blocks;

use App\Models\Block;
use Tests\Feature\Users\ApiTestCase;

final class BlocksTest extends ApiTestCase
{
    public function test_block_is_idempotent_and_list_is_scoped_to_the_authenticated_user(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $target = $this->user();
        $this->api('POST', '/api/blocks', ['user_id' => $target->id])->assertOk()->assertJsonPath('data.id', $target->id)->assertJsonMissingPath('data.email');
        $this->api('POST', '/api/blocks', ['user_id' => $target->id])->assertOk();
        $this->assertDatabaseCount('blocks', 1);
        Block::factory()->create();
        $this->api('GET', '/api/blocks')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $target->id)->assertJsonMissingPath('data.0.email');
    }

    public function test_block_rejects_self_missing_unknown_and_deleted_targets(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $deleted = $this->user();
        $deleted->delete();
        foreach ([$me->id, 999999, $deleted->id, 'inválido', null] as $targetId) {
            $this->api('POST', '/api/blocks', ['user_id' => $targetId])->assertUnprocessable()->assertJsonValidationErrors('user_id');
        }
        $this->assertDatabaseCount('blocks', 0);
    }

    public function test_unblock_only_removes_own_direction_and_is_idempotent(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $target = $this->user();
        Block::factory()->create(['blocker_user_id' => $me->id, 'blocked_user_id' => $target->id]);
        Block::factory()->create(['blocker_user_id' => $target->id, 'blocked_user_id' => $me->id]);
        $this->api('DELETE', '/api/blocks/'.$target->id)->assertOk();
        $this->api('DELETE', '/api/blocks/'.$target->id)->assertOk();
        $this->assertDatabaseCount('blocks', 1);
        $this->assertDatabaseHas('blocks', ['blocker_user_id' => $target->id, 'blocked_user_id' => $me->id]);
        $this->api('DELETE', '/api/blocks/999999')->assertNotFound();
    }

    public function test_deactivated_contacts_can_still_be_listed_and_unblocked(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $target = $this->user();
        Block::factory()->create(['blocker_user_id' => $me->id, 'blocked_user_id' => $target->id]);
        $target->delete();
        $this->api('GET', '/api/blocks')->assertOk()->assertJsonPath('data.0.id', $target->id);
        $this->api('DELETE', '/api/blocks/'.$target->id)->assertOk();
        $this->assertDatabaseCount('blocks', 0);
    }

    public function test_list_rejects_invalid_page(): void
    {
        $this->signIn($this->user());
        $this->api('GET', '/api/blocks?page=0')->assertUnprocessable()->assertJsonValidationErrors('page');
    }
}
