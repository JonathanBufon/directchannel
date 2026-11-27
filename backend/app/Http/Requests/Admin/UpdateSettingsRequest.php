<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Validator;

final class UpdateSettingsRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'max_upload_bytes' => ['sometimes', 'required', 'integer', 'min:1', 'max:2147483647'],
            'log_retention_days' => ['sometimes', 'required', 'integer', 'min:1', 'max:2147483647'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach (array_diff(array_keys($this->all()), array_keys($this->rules())) as $key) {
                $validator->errors()->add($key, 'Esta configuração não pode ser alterada.');
            }
            if ($this->all() === []) {
                $validator->errors()->add('settings', 'Informe ao menos uma configuração.');
            }
        }];
    }
}
