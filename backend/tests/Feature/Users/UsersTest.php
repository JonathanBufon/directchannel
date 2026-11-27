<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Models\Block;
use PHPUnit\Framework\Attributes\DataProvider;

final class UsersTest extends ApiTestCase
{
    public function test_search_is_case_insensitive_paginated_and_hides_self_deleted_and_users_who_blocked_you(): void
    {
        $me = $this->user(['name' => 'Mariana']);
        $this->signIn($me);
        $visible = $this->user(['name' => 'Marina Costa', 'username' => 'outro_nome']);
        $byUsername = $this->user(['name' => 'Outra pessoa', 'username' => 'marcos']);
        $deleted = $this->user(['name' => 'Maria Desativada']);
        $deleted->delete();
        $hidden = $this->user(['name' => 'Maria Oculta']);
        Block::factory()->create(['blocker_user_id' => $hidden->id, 'blocked_user_id' => $me->id]);
        $this->user(['name' => 'Sem correspondência', 'username' => 'outro']);
        $response = $this->api('GET', '/api/users?search=mAR')->assertOk()->assertJsonCount(2, 'data')->assertJsonPath('meta.per_page', 20);
        $this->assertEqualsCanonicalizing([$visible->id, $byUsername->id], array_column($response->json('data'), 'id'));
        foreach ($response->json('data') as $user) {
            $this->assertSame(['id', 'name', 'username'], array_keys($user));
        }
    }

    public function test_search_returns_multiple_pages_and_treats_wildcards_literally(): void
    {
        $this->signIn($this->user());
        for ($i = 0; $i < 23; $i++) {
            $this->user(['name' => 'Contato '.$i]);
        }
        $this->api('GET', '/api/users?search=Contato')->assertJsonCount(20, 'data')->assertJsonPath('meta.total', 23);
        $this->api('GET', '/api/users?search=Contato&page=2')->assertJsonCount(3, 'data');
        $this->api('GET', '/api/users?search=%25%25')->assertJsonCount(0, 'data');
    }

    #[DataProvider('invalidSearches')]
    public function test_search_validates_minimum_length(string $query): void
    {
        $this->signIn($this->user());
        $this->api('GET', '/api/users'.$query)->assertUnprocessable()->assertJsonValidationErrors('search');
    }

    public static function invalidSearches(): array
    {
        return [[''], ['?search=a'], ['?search='], ['?search[]=ab']];
    }

    public function test_public_profile_never_returns_email_password_or_admin_role(): void
    {
        $this->signIn($this->user());
        $target = $this->user();
        $this->api('GET', '/api/users/'.$target->id)->assertOk()->assertExactJson(['data' => ['id' => $target->id, 'name' => $target->name, 'username' => $target->username]]);
    }

    public function test_unavailable_profiles_are_not_found(): void
    {
        $me = $this->user();
        $this->signIn($me);
        $deleted = $this->user();
        $deleted->delete();
        $target = $this->user();
        Block::factory()->create(['blocker_user_id' => $target->id, 'blocked_user_id' => $me->id]);
        foreach ([$deleted->id, $target->id, 999999] as $id) {
            $this->api('GET', '/api/users/'.$id)->assertNotFound();
        }
    }

    #[DataProvider('protectedEndpoints')]
    public function test_all_w1_endpoints_require_authentication(string $method, string $uri): void
    {
        $this->api($method, $uri)->assertUnauthorized();
    }

    public static function protectedEndpoints(): array
    {
        return [
            ['GET', '/api/users?search=ab'], ['GET', '/api/users/1'], ['PATCH', '/api/me'],
            ['PATCH', '/api/me/password'], ['DELETE', '/api/me'], ['GET', '/api/me/privacy'],
            ['PATCH', '/api/me/privacy'], ['GET', '/api/blocks'], ['POST', '/api/blocks'],
            ['DELETE', '/api/blocks/1'], ['POST', '/api/me/keys'], ['GET', '/api/users/1/keys/current'],
            ['GET', '/api/conversations'], ['POST', '/api/conversations'],
            ['GET', '/api/conversations/1'], ['DELETE', '/api/conversations/1'],
        ];
    }
}
