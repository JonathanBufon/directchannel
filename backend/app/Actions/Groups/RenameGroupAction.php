<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RenameGroupAction
{
    public function __construct(private readonly GroupContext $context) {}

    public function execute(Group $group, User $actor, string $name): Group
    {
        return DB::transaction(function () use ($group, $actor, $name): Group {
            $group = $this->context->lock($group, $actor);
            $group->update(['name' => $name]);

            return $this->context->details($group);
        }, 3);
    }
}
