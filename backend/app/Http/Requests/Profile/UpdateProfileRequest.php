<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Requests\Users\ApiRequest;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class UpdateProfileRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'username' => [
                'bail', 'sometimes', 'required', 'string', 'min:3', 'max:30', 'regex:/\A[A-Za-z0-9_.]+\z/',
                function (string $attribute, mixed $value, Closure $fail): void {
                    if (DB::table('users')->where('id', '<>', $this->user()->id)->whereRaw('lower(username) = lower(?)', [$value])->exists()) {
                        $fail('Este nome de usuário já está em uso.');
                    }
                },
            ],
            'current_password' => [Rule::requiredIf($this->has('email') && $this->input('email') !== $this->user()->email), 'string', 'current_password:sanctum'],
        ];
    }
}
