<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Enums\ConversationKind;
use App\Models\Conversation;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CreateGroupAction
{
    public function __construct(private readonly GroupContext $context) {}

    public function execute(User $creator, string $name, array $memberIds): Group
    {
        return DB::transaction(function () use ($creator, $name, $memberIds): Group {
            $users = $this->context->eligibleUsers($memberIds);
            $conversation = Conversation::create(['kind' => ConversationKind::Group]);
            $group = Group::create(['conversation_id' => $conversation->id, 'name' => $name]);
            $this->context->join($group, $creator, true);
            foreach ($users as $user) {
                $this->context->join($group, $user);
            }

            return $this->context->details($group);
        }, 3);
    }
}
