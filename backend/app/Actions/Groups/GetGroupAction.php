<?php

declare(strict_types=1);

namespace App\Actions\Groups;

use App\Models\Group;
use Illuminate\Support\Facades\Gate;

final class GetGroupAction
{
    public function __construct(private readonly GroupContext $context) {}

    public function execute(Group $group): Group
    {
        Gate::authorize('view', $group);

        return $this->context->details($group);
    }
}
