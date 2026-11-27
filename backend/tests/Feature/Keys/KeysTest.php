<?php

declare(strict_types=1);

namespace Tests\Feature\Keys;

use App\Models\Block;
use App\Models\PublicKeyMaterial;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Users\ApiTestCase;

final class KeysTest extends ApiTestCase
{
    public function test_publish_roundtrips_binary_key_and_latest_key_has_sha256_of_bytes(): void
    {
        $user = $this->user();
        $this->signIn($user);
        $old = PublicKeyMaterial::factory()->create(['user_id' => $user->id, 'published_at' => now()->subMinute()]);
        $bytes = "\x00\xff\x80".random_bytes(8189);
        $response = $this->api('POST', '/api/me/keys', ['public_payload' => base64_encode($bytes), 'user_id' => $this->user()->id])
            ->assertCreated()->assertJsonPath('data.public_payload', base64_encode($bytes))->assertJsonPath('data.fingerprint', hash('sha256', $bytes))->assertJsonPath('data.user_id', $user->id)->assertJsonStructure(['data' => ['published_at']]);
        $this->assertSame($bytes, PublicKeyMaterial::findOrFail($response->json('data.id'))->public_payload);
        $this->assertDatabaseHas('public_key_material', ['id' => $old->id]);
        $this->api('GET', '/api/users/'.$user->id.'/keys/current')->assertOk()->assertJsonPath('data.id', $response->json('data.id'));
    }

    public function test_current_key_is_available_to_contacts_and_uses_id_to_break_timestamp_ties(): void
    {
        $this->signIn($this->user());
        $target = $this->user();
        $timestamp = now();
        PublicKeyMaterial::factory()->create(['user_id' => $target->id, 'published_at' => $timestamp]);
        $latest = PublicKeyMaterial::factory()->create(['user_id' => $target->id, 'published_at' => $timestamp]);
        $this->api('GET', '/api/users/'.$target->id.'/keys/current')->assertOk()->assertJsonPath('data.id', $latest->id);
    }

    #[DataProvider('invalidPayloads')]
    public function test_publish_rejects_invalid_empty_or_oversized_base64(mixed $payload): void
    {
        $this->signIn($this->user());
        $this->api('POST', '/api/me/keys', ['public_payload' => $payload])->assertUnprocessable()->assertJsonValidationErrors('public_payload');
        $this->assertDatabaseCount('public_key_material', 0);
    }

    public static function invalidPayloads(): array
    {
        return [[null], [''], ['%%%'], ['YWJj\n'], ['Zg='], [123], [base64_encode(str_repeat('k', 8193))]];
    }

    public function test_keys_are_not_visible_for_missing_deleted_or_blocking_users(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $missingKey = $this->user();
        $deleted = $this->user();
        PublicKeyMaterial::factory()->create(['user_id' => $deleted->id]);
        $deleted->delete();
        $blocking = $this->user();
        PublicKeyMaterial::factory()->create(['user_id' => $blocking->id]);
        Block::factory()->create(['blocker_user_id' => $blocking->id, 'blocked_user_id' => $me->id]);
        foreach ([$missingKey->id, $deleted->id, $blocking->id, 999999] as $id) {
            $this->api('GET', '/api/users/'.$id.'/keys/current')->assertNotFound();
        }
    }
}
