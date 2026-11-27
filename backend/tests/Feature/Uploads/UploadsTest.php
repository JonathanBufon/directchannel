<?php

declare(strict_types=1);

namespace Tests\Feature\Uploads;

use App\Models\AppSetting;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\TemporaryUpload;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Admin\AuthenticatesApi;
use Tests\TestCase;

final class UploadsTest extends TestCase
{
    use AuthenticatesApi, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function conversation(User $user): Conversation
    {
        return ConversationParticipant::factory()->for($user)->create()->conversation;
    }

    public function test_upload_stores_opaque_bytes_privately_with_24_hour_expiration(): void
    {
        $this->freezeSecond();
        $user = $this->signIn();
        $conversation = $this->conversation($user);
        $ciphertext = random_bytes(1024);
        $file = UploadedFile::fake()->createWithContent('cifrado.bin', $ciphertext);
        $response = $this->postJson('/api/conversations/'.$conversation->id.'/uploads', ['file' => $file]);
        $response->assertCreated()->assertJsonPath('data.size_bytes', 1024)
            ->assertJsonPath('data.original_name', 'cifrado.bin')
            ->assertJsonPath('data.mime_type', 'application/octet-stream')
            ->assertJsonPath('data.expires_at', now()->addHours(24)->toISOString());
        $upload = TemporaryUpload::findOrFail($response->json('data.id'));
        $this->assertSame($user->id, $upload->user_id);
        $this->assertSame($conversation->id, $upload->conversation_id);
        $this->assertNull($upload->consumed_at);
        $this->assertMatchesRegularExpression('#^uploads/'.$user->id.'/[0-9a-f-]{36}$#', $upload->storage_key);
        $this->assertSame($ciphertext, Storage::disk('local')->get($upload->storage_key));
        Storage::disk('public')->assertDirectoryEmpty('/');
        $this->assertArrayNotHasKey('storage_key', $response->json('data'));
    }

    public function test_upload_accepts_exact_byte_limit_and_rejects_one_byte_more(): void
    {
        $user = $this->signIn();
        $conversation = $this->conversation($user);
        AppSetting::create(['key' => 'max_upload_bytes', 'value' => 1025]);
        $this->postJson('/api/conversations/'.$conversation->id.'/uploads', [
            'file' => UploadedFile::fake()->createWithContent('file.bin', random_bytes(1025)),
        ])->assertCreated();
        $this->postJson('/api/conversations/'.$conversation->id.'/uploads', [
            'file' => UploadedFile::fake()->createWithContent('file.bin', random_bytes(1026)),
        ])->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->assertDatabaseCount('temporary_uploads', 1);
    }

    public static function invalidFiles(): array
    {
        return ['missing' => ['missing'], 'empty' => ['empty'], 'large' => ['large'], 'mime' => ['mime'], 'name' => ['name']];
    }

    #[DataProvider('invalidFiles')]
    public function test_upload_validation(string $case): void
    {
        $user = $this->signIn();
        $conversation = $this->conversation($user);
        $file = match ($case) {
            'empty' => UploadedFile::fake()->create('file.bin', 0, 'application/octet-stream'),
            'large' => UploadedFile::fake()->create('file.bin', 25601, 'application/octet-stream'),
            'mime' => UploadedFile::fake()->create('file.exe', 1, 'application/x-executable'),
            'name' => UploadedFile::fake()->create(str_repeat('a', 256).'.bin', 1, 'application/octet-stream'),
            default => null,
        };
        $this->postJson('/api/conversations/'.$conversation->id.'/uploads', $file === null ? [] : ['file' => $file])
            ->assertUnprocessable()->assertJsonValidationErrors('file');
        $this->assertDatabaseCount('temporary_uploads', 0);
        Storage::disk('local')->assertDirectoryEmpty('/');
    }

    public function test_upload_requires_active_membership_even_for_admin(): void
    {
        $user = $this->signIn(User::factory()->admin()->create());
        $conversation = Conversation::factory()->create();
        $uri = '/api/conversations/'.$conversation->id.'/uploads';
        $this->postJson($uri, ['file' => UploadedFile::fake()->create('a.bin', 1)])->assertNotFound();
        ConversationParticipant::factory()->left()->for($user)->for($conversation)->create();
        $this->postJson($uri, ['file' => UploadedFile::fake()->create('a.bin', 1)])->assertNotFound();
        $this->postJson('/api/conversations/999999/uploads')->assertNotFound();
    }

    public function test_owner_can_delete_unconsumed_upload_and_file(): void
    {
        $user = $this->signIn();
        $upload = TemporaryUpload::factory()->for($user)->create();
        Storage::disk('local')->put($upload->storage_key, 'ciphertext');
        $this->deleteJson('/api/uploads/'.$upload->id)->assertOk();
        $this->assertModelMissing($upload);
        Storage::disk('local')->assertMissing($upload->storage_key);
        $this->deleteJson('/api/uploads/'.$upload->id)->assertNotFound();
    }

    public function test_consumed_upload_is_preserved_and_cannot_be_deleted(): void
    {
        $user = $this->signIn();
        $upload = TemporaryUpload::factory()->for($user)->create(['consumed_at' => now()]);
        Storage::disk('local')->put($upload->storage_key, 'ciphertext');
        $this->deleteJson('/api/uploads/'.$upload->id)->assertUnprocessable()->assertJsonValidationErrors('upload');
        $this->assertModelExists($upload);
        Storage::disk('local')->assertExists($upload->storage_key);
    }

    public function test_other_user_cannot_delete_upload(): void
    {
        $this->signIn();
        $upload = TemporaryUpload::factory()->create();
        $this->deleteJson('/api/uploads/'.$upload->id)->assertNotFound();
        $this->assertModelExists($upload);
    }

    public function test_upload_endpoints_require_authentication(): void
    {
        $conversation = Conversation::factory()->create();
        $upload = TemporaryUpload::factory()->create();
        $this->postJson('/api/conversations/'.$conversation->id.'/uploads')->assertUnauthorized();
        $this->deleteJson('/api/uploads/'.$upload->id)->assertUnauthorized();
    }

    public function test_prune_removes_only_expired_unconsumed_uploads_and_is_repeatable(): void
    {
        $this->freezeSecond();
        $expired = TemporaryUpload::factory()->create(['expires_at' => now()]);
        $valid = TemporaryUpload::factory()->create(['expires_at' => now()->addSecond()]);
        $consumed = TemporaryUpload::factory()->create(['expires_at' => now()->subDay(), 'consumed_at' => now()->subDays(2)]);
        foreach ([$expired, $valid, $consumed] as $upload) {
            Storage::disk('local')->put($upload->storage_key, 'ciphertext');
        }
        $this->artisan('uploads:prune')->expectsOutput('1 upload(s) temporário(s) removido(s).')->assertSuccessful();
        $this->assertModelMissing($expired);
        Storage::disk('local')->assertMissing($expired->storage_key);
        foreach ([$valid, $consumed] as $upload) {
            $this->assertModelExists($upload);
            Storage::disk('local')->assertExists($upload->storage_key);
        }
        $this->artisan('uploads:prune')->expectsOutput('0 upload(s) temporário(s) removido(s).')->assertSuccessful();
        $events = app(Schedule::class)->events();
        $scheduled = array_filter($events, fn ($event): bool => str_contains($event->command ?? '', 'uploads:prune') && $event->expression === '0 0 * * *');
        $this->assertCount(1, $scheduled);
    }
}
