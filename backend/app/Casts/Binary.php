<?php

declare(strict_types=1);

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/** @implements CastsAttributes<string, string> */
final class Binary implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): string
    {
        return is_resource($value) ? stream_get_contents($value, -1, 0) : (string) $value;
    }

    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException('O conteúdo binário deve ser uma string de bytes.');
        }

        // PDO must bind bytea as a LOB, including null bytes and invalid UTF-8.
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $value);
        rewind($stream);

        return $stream;
    }
}
