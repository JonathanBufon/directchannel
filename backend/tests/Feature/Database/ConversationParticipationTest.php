<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ConversationParticipationTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_participant_for_returns_only_the_current_participation(): void
    {
        $participant = ConversationParticipant::factory()->create();
        $conversation = $participant->conversation;
        $outsider = User::factory()->create();

        $this->assertTrue($conversation->activeParticipantFor($participant->user)?->is($participant));
        $this->assertNull($conversation->activeParticipantFor($outsider));

        $participant->update(['left_at' => now()]);

        $this->assertNull($conversation->activeParticipantFor($participant->user));
    }

    public function test_privacy_preferences_default_to_enabled(): void
    {
        $user = User::factory()->create()->refresh();

        $this->assertTrue($user->read_receipts_enabled);
        $this->assertTrue($user->notification_preview_enabled);
    }
}
