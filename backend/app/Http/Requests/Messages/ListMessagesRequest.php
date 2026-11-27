<?php

declare(strict_types=1);

namespace App\Http\Requests\Messages;

final class ListMessagesRequest extends MessageRequest
{
    public function rules(): array
    {
        return [
            'before_id' => ['sometimes', 'required', 'integer', 'min:1'],
            'limit' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
        ];
    }
}
