<?php

declare(strict_types=1);

namespace App\Http\Requests\Groups;

final class LeaveGroupRequest extends GroupRequest
{
    public function rules(): array
    {
        return ['successor_user_id' => ['sometimes', 'required', 'integer', 'min:1']];
    }
}
