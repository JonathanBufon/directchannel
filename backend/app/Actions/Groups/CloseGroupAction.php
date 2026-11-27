<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class CloseGroupAction
{
    public function __construct(private readonly GroupContext $context) {}

    public function execute(Group $group, User $actor): void
    {
        DB::transaction(function () use ($group, $actor): void {
            $group = $this->context->lock($group, $actor);
            $group->conversation->participants()->active()->update(['left_at' => now()->format('Y-m-d H:i:s.uP')]);
        }, 3);
    }
}
