<?php

declare(strict_types=1);

namespace Tests\Feature\Attachments;

use App\Models\Attachment;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Admin\AuthenticatesApi;
use Tests\TestCase;

final class AttachmentsTest extends TestCase
{
    use AuthenticatesApi, RefreshDatabase;

    private function attachment(User $author): Attachment
    {
        $sender = ConversationParticipant::factory()->for($author, 'user')->create();
        $message = Message::factory()->create(['sender_participant_id' => $sender->id, 'conversation_id' => $sender->conversation_id]);

        return Attachment::factory()->for($message)->create();
    }

    public function test_active_participant_can_read_metadata_and_stream_unchanged_ciphertext(): void
    {
        Storage::fake('local');
        $reader = $this->signIn();
        $attachment = $this->attachment(User::factory()->create());
        ConversationParticipant::factory()->for($reader, 'user')->create(['conversation_id' => $attachment->message->conversation_id, 'joined_at' => now()->subDay()]);
        $bytes = random_bytes(1024);
        Storage::disk('local')->put($attachment->storage_key, $bytes);
        $this->getJson('/api/attachments/'.$attachment->id)->assertExactJson(['data' => [
            'id' => $attachment->id, 'message_id' => $attachment->message_id, 'size_bytes' => $attachment->size_bytes,
        ]]);
        $response = $this->getJson('/api/attachments/'.$attachment->id.'/download')->assertOk()
            ->assertDownload('anexo-'.$attachment->id)->assertHeader('content-type', 'application/octet-stream')
            ->assertHeader('x-content-type-options', 'nosniff');
        $this->assertSame($bytes, $response->streamedContent());
    }

    public static function operations(): array
    {
        return ['metadata' => ['GET', ''], 'download' => ['GET', '/download'], 'patch' => ['PATCH', ''], 'delete' => ['DELETE', '']];
    }

    #[DataProvider('operations')]
    public function test_nonparticipant_and_former_participant_cannot_access_attachment(string $method, string $suffix): void
    {
        $outsider = $this->signIn(User::factory()->admin()->create());
        $attachment = $this->attachment(User::factory()->create());
        $uri = '/api/attachments/'.$attachment->id.$suffix;
        $this->json($method, $uri)->assertNotFound();
        ConversationParticipant::factory()->left()->for($outsider, 'user')->create(['conversation_id' => $attachment->message->conversation_id, 'joined_at' => now()->subDay()]);
        $this->json($method, $uri)->assertNotFound();
    }

    #[DataProvider('operations')]
    public function test_attachment_endpoints_require_authentication(string $method, string $suffix): void
    {
        $attachment = Attachment::factory()->create();
        $this->json($method, '/api/attachments/'.$attachment->id.$suffix)->assertUnauthorized();
    }

    #[DataProvider('operations')]
    public function test_missing_attachment_returns_404(string $method, string $suffix): void
    {
        $this->signIn();
        $this->json($method, '/api/attachments/999999'.$suffix)->assertNotFound();
    }

    public function test_author_gets_501_for_patch_and_delete_without_mutating_attachment_or_file(): void
    {
        Storage::fake('local');
        $author = $this->signIn();
        $attachment = $this->attachment($author);
        Storage::disk('local')->put($attachment->storage_key, 'ciphertext');
        $this->patchJson('/api/attachments/'.$attachment->id, ['storage_key' => 'public/evil'])->assertStatus(501);
        $this->patchJson('/api/attachments/'.$attachment->id, [])->assertStatus(501);
        $this->deleteJson('/api/attachments/'.$attachment->id)->assertStatus(501);
        $this->assertSame($attachment->storage_key, $attachment->fresh()->storage_key);
        Storage::disk('local')->assertExists($attachment->storage_key);
    }

    public function test_participant_cannot_mutate_another_authors_attachment(): void
    {
        $reader = $this->signIn();
        $attachment = $this->attachment(User::factory()->create());
        ConversationParticipant::factory()->for($reader, 'user')->create(['conversation_id' => $attachment->message->conversation_id, 'joined_at' => now()->subDay()]);
        $this->patchJson('/api/attachments/'.$attachment->id)->assertForbidden();
        $this->deleteJson('/api/attachments/'.$attachment->id)->assertForbidden();
    }

    public function test_former_author_cannot_mutate_attachment(): void
    {
        $author = $this->signIn();
        $attachment = $this->attachment($author);
        $attachment->message->sender->update(['left_at' => now()]);
        $this->patchJson('/api/attachments/'.$attachment->id)->assertNotFound();
        $this->deleteJson('/api/attachments/'.$attachment->id)->assertNotFound();
    }

    public function test_missing_file_returns_404_to_authorized_participant(): void
    {
        Storage::fake('local');
        $attachment = $this->attachment($this->signIn());
        $this->getJson('/api/attachments/'.$attachment->id.'/download')->assertNotFound();
    }
}
