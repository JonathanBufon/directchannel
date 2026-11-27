<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\User;
use Illuminate\Support\Arr;

final class UpdatePrivacyAction
{
    public function execute(User $user, array $data): User
    {
        $user->fill(Arr::only($data, ['read_receipts_enabled', 'notification_preview_enabled']))->save();

        return $user->refresh();
    }
}
