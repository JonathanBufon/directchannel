<?php

declare(strict_types=1);

namespace App\Http\Requests\Messages;

use Closure;

final class SendMessageRequest extends MessageRequest
{
    public function rules(): array
    {
        return [
            'encrypted_body' => [
                'bail', 'required', 'string', 'max:87384',
                function (string $attribute, mixed $value, Closure $fail): void {
                    $bytes = base64_decode($value, true);
                    if ($bytes === false || $bytes === '' || base64_encode($bytes) !== $value || strlen($bytes) > 65536) {
                        $fail('Informe um envelope cifrado em base64 válido, não vazio e de até 64 KB.');
                    }
                },
            ],
            'temporary_upload_ids' => ['sometimes', 'array', 'max:10'],
            'temporary_upload_ids.*' => ['required', 'integer', 'min:1', 'distinct'],
        ];
    }
}
