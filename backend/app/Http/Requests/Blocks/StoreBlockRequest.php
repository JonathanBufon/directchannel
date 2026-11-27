<?php

declare(strict_types=1);

namespace App\Http\Requests\Blocks;

use App\Http\Requests\Users\ApiRequest;
use Illuminate\Validation\Rule;

final class StoreBlockRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['user_id' => ['required', 'integer', Rule::notIn([$this->user()->id]), Rule::exists('users', 'id')->whereNull('deleted_at')]];
    }
}
