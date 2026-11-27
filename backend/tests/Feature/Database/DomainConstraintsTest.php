<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\AccessGrant;
use App\Models\AccessGrantToken;
use App\Models\AccountBlock;
use App\Models\Attachment;
use App\Models\Block;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\GroupMember;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\Report;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class DomainConstraintsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_one_active_grant_per_user(): void
    {
        $grant = AccessGrant::factory()->create();
        $this->expectException(QueryException::class);
        AccessGrant::factory()->create(['user_id' => $grant->user_id]);
    }

    public function test_only_one_active_participation_per_user_and_conversation(): void
    {
        $participant = ConversationParticipant::factory()->create();
        $this->expectException(QueryException::class);
        ConversationParticipant::factory()->create($participant->only(['conversation_id', 'user_id']));
    }

    public function test_only_one_open_account_block_per_user(): void
    {
        $block = AccountBlock::factory()->open()->create();
        $this->expectException(QueryException::class);
        AccountBlock::factory()->open()->create(['user_id' => $block->user_id]);
    }

    public function test_partial_uniques_preserve_history_and_allow_new_current_records(): void
    {
        $user = User::factory()->create();
        AccessGrant::factory()->revoked()->count(2)->for($user)->create();
        AccessGrant::factory()->for($user)->create();
        $conversation = Conversation::factory()->create();
        ConversationParticipant::factory()->left()->count(2)->for($user)->for($conversation)->create();
        ConversationParticipant::factory()->for($user)->for($conversation)->create();
        AccountBlock::factory()->closed()->count(2)->for($user)->create();
        AccountBlock::factory()->open()->for($user)->create();
        $this->assertSame(1, $user->accessGrants()->active()->count());
        $this->assertSame(1, $user->participations()->active()->count());
        $this->assertSame(1, $user->accountBlocks()->open()->count());
    }

    public function test_message_sender_must_belong_to_its_conversation(): void
    {
        $sender = ConversationParticipant::factory()->create();
        $otherConversation = Conversation::factory()->create();
        $this->expectException(QueryException::class);
        Message::factory()->create(['sender_participant_id' => $sender->id, 'conversation_id' => $otherConversation->id]);
    }

    public function test_username_expression_index_reserves_deactivated_names(): void
    {
        User::factory()->create(['username' => 'Maria'])->delete();
        $this->expectException(QueryException::class);
        User::factory()->create(['username' => 'MARIA']);
    }

    public static function invalidChecks(): array
    {
        return [
            'conversation kind' => ['conversation_kind'],
            'report status' => ['report_status'],
            'accent is display only' => ['report_accent'],
            'conclusion missing fields' => ['conclusion_missing'],
            'received with partial decision' => ['received_partial'],
            'received with complete decision' => ['received_complete'],
            'self report' => ['self_report'],
            'self block' => ['self_block'],
            'unblock missing actor' => ['unblock_missing_actor'],
            'unblock missing timestamp' => ['unblock_missing_timestamp'],
            'grant period' => ['grant_period'],
            'participation period' => ['participation_period'],
            'attachment zero size' => ['attachment_size'],
            'temporary upload zero size' => ['upload_size'],
            'empty encrypted envelope' => ['empty_body'],
        ];
    }

    #[DataProvider('invalidChecks')]
    public function test_database_rejects_invalid_domain_values(string $case): void
    {
        [$table, $id, $attributes] = match ($case) {
            'conversation_kind' => ['conversations', Conversation::factory()->create()->id, ['kind' => 'channel']],
            'report_status' => ['reports', Report::factory()->create()->id, ['status' => 'analise']],
            'report_accent' => ['reports', Report::factory()->concluded()->create()->id, ['status' => 'concluída']],
            'conclusion_missing' => ['reports', Report::factory()->create()->id, ['status' => 'concluida']],
            'received_partial' => ['reports', Report::factory()->create()->id, ['decision' => 'Parcial']],
            'received_complete' => ['reports', Report::factory()->concluded()->create()->id, ['status' => 'recebida']],
            'self_report' => $this->selfReport(),
            'self_block' => $this->selfBlock(),
            'unblock_missing_actor' => ['account_blocks', AccountBlock::factory()->open()->create()->id, ['unblocked_at' => now()]],
            'unblock_missing_timestamp' => ['account_blocks', AccountBlock::factory()->open()->create()->id, ['unblocked_by_user_id' => User::factory()->admin()->create()->id]],
            'grant_period' => ['access_grants', AccessGrant::factory()->create()->id, ['revoked_at' => now()->subDay()]],
            'participation_period' => ['conversation_participants', ConversationParticipant::factory()->create()->id, ['left_at' => now()->subDay()]],
            'attachment_size' => ['attachments', Attachment::factory()->create()->id, ['size_bytes' => 0]],
            'upload_size' => ['temporary_uploads', TemporaryUpload::factory()->create()->id, ['size_bytes' => 0]],
            'empty_body' => ['messages', Message::factory()->create()->id, ['encrypted_body' => '']],
        };
        $this->expectException(QueryException::class);
        DB::table($table)->where($table === 'blocks' ? 'blocker_user_id' : 'id', $id)->update($attributes);
    }

    private function selfReport(): array
    {
        $report = Report::factory()->create();

        return ['reports', $report->id, ['reported_user_id' => $report->reporter_user_id]];
    }

    private function selfBlock(): array
    {
        $block = Block::factory()->create();

        return ['blocks', $block->blocker_user_id, ['blocked_user_id' => $block->blocker_user_id]];
    }

    public static function primaryKeys(): array
    {
        return [
            'grant token' => [AccessGrantToken::class, ['personal_access_token_id', 'access_grant_id']],
            'group member' => [GroupMember::class, ['participant_id', 'group_id']],
            'receipt' => [MessageReceipt::class, ['message_id', 'recipient_participant_id']],
            'block' => [Block::class, ['blocker_user_id', 'blocked_user_id']],
        ];
    }

    #[DataProvider('primaryKeys')]
    public function test_shared_and_composite_primary_keys_reject_duplicates(string $model, array $fields): void
    {
        $existing = $model::factory()->create();
        $this->expectException(QueryException::class);
        $model::factory()->create($existing->only($fields));
    }
}
