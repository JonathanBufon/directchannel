<?php

declare(strict_types=1);

namespace App\Actions\Settings;

use App\Models\AppSetting;
use Illuminate\Support\Facades\DB;

final class UpdateSettingsAction
{
    public function execute(array $data): void
    {
        DB::transaction(function () use ($data): void {
            foreach ($data as $key => $value) {
                AppSetting::updateOrCreate(['key' => $key], ['value' => (int) $value]);
            }
        });
    }
}
