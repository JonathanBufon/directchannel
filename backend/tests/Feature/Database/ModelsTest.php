<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Enums\ConversationKind;
use App\Enums\ReportStatus;
use App\Models\AccessGrantToken;
use App\Models\AccountBlock;
use App\Models\Block;
use App\Models\GroupMember;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\PublicKeyMaterial;
use App\Models\Report;
use App\Models\TemporaryUpload;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_binary_payloads_roundtrip_arbitrary_bytes_and_are_hidden(): void
    {
        $bytes = "\x00\xff\x80\x5cconteúdo\x00";
        $message = Message::factory()->create(['encrypted_body' => $bytes])->refresh();
        $key = PublicKeyMaterial::factory()->create(['public_payload' => $bytes])->refresh();
        $this->assertSame($bytes, $message->encrypted_body);
        $this->assertSame($bytes, $message->encrypted_body);
        $this->assertSame($bytes, $key->public_payload);
        $this->assertArrayNotHasKey('encrypted_body', $message->toArray());
        $this->assertArrayNotHasKey('public_payload', $key->toArray());
        $this->assertJson($message->toJson());
        $this->assertJson($key->toJson());
    }

    public function test_factories_and_explicit_relations_share_the_correct_context(): void
    {
        $member = GroupMember::factory()->create();
        $this->assertFalse($member->incrementing);
        $this->assertSame($member->group->conversation_id, $member->participant->conversation_id);
        $this->assertSame(ConversationKind::Group, $member->group->conversation->kind);
        $this->assertTrue($member->participant->groupMember->is($member));
        $this->assertSame(1, $member->group->groupMembers()->count());
        $message = Message::factory()->for($member->participant, 'sender')->create();
        $this->assertTrue($message->sender->is($member->participant));
        $this->assertSame($message->conversation_id, $message->sender->conversation_id);
        $this->assertSame(1, $message->sender->messages()->count());
        $this->assertSame(1, $message->conversation->messages()->count());
        $receipt = MessageReceipt::factory()->for($message)->create();
        $this->assertSame($message->conversation_id, $receipt->recipient->conversation_id);
        $this->assertTrue($receipt->recipient->messageReceipts()->exists());
        $this->assertSame(2, $message->conversation->participants()->count());
        $this->assertInstanceOf(CarbonImmutable::class, $message->refresh()->accepted_at);
    }

    public function test_report_account_block_and_user_inverse_relations(): void
    {
        $report = Report::factory()->concluded()->create();
        $this->assertSame(ReportStatus::Concluded, $report->status);
        $this->assertSame('concluída', $report->status->label());
        $this->assertFalse($report->isReceived());
        $this->assertTrue($report->reporter->reportsMade()->whereKey($report->id)->exists());
        $this->assertTrue($report->reported->reportsReceived()->whereKey($report->id)->exists());
        $this->assertTrue($report->reviewer->is_admin);
        $this->assertSame(0, Report::received()->count());
        $received = Report::factory()->create();
        $this->assertTrue($received->isReceived());
        $this->assertSame(1, Report::received()->count());
        $report->delete();
        $this->assertNotNull(Report::withTrashed()->findOrFail($report->id)->decision);

        $block = AccountBlock::factory()->closed()->create();
        $this->assertFalse($block->isOpen());
        $this->assertTrue($block->blockedBy->is_admin);
        $this->assertTrue($block->unblockedBy->is_admin);
        $this->assertFalse($block->user->isBlocked());
        $open = AccountBlock::factory()->open()->for($block->user)->create();
        $this->assertTrue($open->isOpen());
        $this->assertTrue($open->user->isBlocked());

        $contactBlock = Block::factory()->create();
        $this->assertTrue($contactBlock->blocker->blocksMade()->exists());
        $this->assertTrue($contactBlock->blocked->blocksReceived()->exists());
    }

    public function test_token_bridge_uses_sanctum_token_owned_by_grant_user(): void
    {
        $bridge = AccessGrantToken::factory()->create();
        $this->assertFalse($bridge->incrementing);
        $this->assertSame($bridge->accessGrant->user_id, $bridge->personalAccessToken->tokenable_id);
        $this->assertTrue($bridge->accessGrant->user->accessGrants()->active()->exists());
        $this->assertInstanceOf(CarbonImmutable::class, $bridge->personalAccessToken->created_at);
    }

    public function test_upload_and_public_key_factories_have_valid_owners(): void
    {
        $upload = TemporaryUpload::factory()->create()->refresh();
        $this->assertTrue($upload->expires_at->isFuture());
        $this->assertNull($upload->consumed_at);
        $this->assertTrue($upload->user->temporaryUploads()->exists());
        $this->assertTrue($upload->conversation->temporaryUploads()->exists());
        $key = PublicKeyMaterial::factory()->create()->refresh();
        $this->assertTrue($key->user->publicKeys()->exists());
        $this->assertInstanceOf(CarbonImmutable::class, $key->published_at);
    }
}
