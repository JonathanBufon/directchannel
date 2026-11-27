<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Message;
use App\Models\MessageReceipt;
use App\Models\User;
use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create(['name' => 'Administrador', 'email' => 'admin@example.com', 'username' => 'admin']);
        $users = User::factory()->count(3)->create();

        $direct = Conversation::factory()->direct()->create();
        $sender = ConversationParticipant::factory()->for($direct)->for($users[0])->create();
        $recipient = ConversationParticipant::factory()->for($direct)->for($users[1])->create();
        $message = Message::factory()->for($sender, 'sender')->create();
        MessageReceipt::factory()->for($message)->for($recipient, 'recipient')->create();

        $conversation = Conversation::factory()->group()->create();
        $group = Group::factory()->for($conversation)->create(['name' => 'Equipe DirectChannel']);
        foreach ([$admin, ...$users] as $user) {
            $participant = ConversationParticipant::factory()->for($conversation)->for($user)->create();
            GroupMember::factory()->for($group)->for($participant, 'participant')->create(['is_responsible' => $user->is($admin)]);
            Message::factory()->for($participant, 'sender')->create();
        }
    }
}
