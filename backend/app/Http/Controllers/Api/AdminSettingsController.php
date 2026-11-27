<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Settings\UpdateSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Support\AppSettings;
use Illuminate\Http\JsonResponse;

final class AdminSettingsController extends Controller
{
    public function show(AppSettings $settings): JsonResponse
    {
        return response()->json(['data' => $settings->all()]);
    }

    public function update(UpdateSettingsRequest $request, UpdateSettingsAction $action, AppSettings $settings): JsonResponse
    {
        $action->execute($request->validated());

        return response()->json(['data' => $settings->all()]);
    }
}
