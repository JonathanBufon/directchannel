<?php

declare(strict_types=1);

namespace App\Http\Requests\Profile;

use App\Http\Requests\Users\ApiRequest;

final class UpdatePrivacyRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'read_receipts_enabled' => ['required_without:notification_preview_enabled', 'boolean'],
            'notification_preview_enabled' => ['required_without:read_receipts_enabled', 'boolean'],
        ];
    }
}
