<?php

declare(strict_types=1);

namespace App\Http\Requests\Groups;

final class AddGroupMembersRequest extends GroupRequest
{
    public function rules(): array
    {
        return $this->memberRules();
    }
}
