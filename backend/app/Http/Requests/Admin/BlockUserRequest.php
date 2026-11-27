<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

final class BlockUserRequest extends AdminRequest
{
    public function rules(): array
    {
        return ['reason' => ['required', 'string', 'max:2000']];
    }
}
