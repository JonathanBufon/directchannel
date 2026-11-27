<?php

declare(strict_types=1);

namespace App\Http\Requests\Groups;

final class CreateGroupRequest extends GroupRequest
{
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'min:3', 'max:100']] + $this->memberRules();
    }
}
