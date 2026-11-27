<?php

declare(strict_types=1);

namespace App\Http\Requests\Keys;

use App\Http\Requests\Users\ApiRequest;
use Closure;

final class PublishKeyRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['public_payload' => [
            'bail', 'required', 'string', 'max:10924',
            function (string $attribute, mixed $value, Closure $fail): void {
                $bytes = base64_decode($value, true);
                if ($bytes === false || $bytes === '' || base64_encode($bytes) !== $value) {
                    $fail('A chave pública deve estar em base64 válido.');
                } elseif (strlen($bytes) > 8192) {
                    $fail('A chave pública deve ter no máximo 8 KB.');
                }
            },
        ]];
    }
}
