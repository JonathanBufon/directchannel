<?php

declare(strict_types=1);

namespace App\Http\Requests\Files;

use App\Models\Attachment;
use App\Support\AppSettings;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;

final class UploadRequest extends FormRequest
{
    // Ciphertext cannot be sniffed: this allowlist validates declared transport metadata only.
    public const ALLOWED_MIME_TYPES = [
        'application/octet-stream', 'application/pdf', 'application/zip',
        'image/jpeg', 'image/png', 'image/webp', 'text/plain',
        'audio/mpeg', 'audio/mp4', 'video/mp4',
    ];

    public function authorize(): bool
    {
        Gate::authorize('create', [Attachment::class, $this->route('conversation')]);

        return true;
    }

    public function rules(AppSettings $settings): array
    {
        return ['file' => ['required', 'file', function (string $attribute, mixed $value, Closure $fail) use ($settings): void {
            if (! $value instanceof UploadedFile || ! $value->isValid()) {
                return;
            }
            if ($value->getSize() <= 0 || $value->getSize() > $settings->maxUploadBytes()) {
                $fail('O arquivo deve ter entre 1 e '.$settings->maxUploadBytes().' bytes.');
            }
            if (! in_array($value->getClientMimeType(), self::ALLOWED_MIME_TYPES, true)) {
                $fail('O tipo de arquivo informado não é permitido.');
            }
            if (mb_strlen($value->getClientOriginalName()) > 255) {
                $fail('O nome do arquivo deve ter no máximo 255 caracteres.');
            }
        }]];
    }

    public function messages(): array
    {
        return ['file.required' => 'Selecione um arquivo.', 'file.file' => 'O arquivo enviado é inválido.', 'file.uploaded' => 'Não foi possível receber o arquivo.'];
    }
}
