<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\AppSettings;
use Illuminate\Http\JsonResponse;

final class AdminSettingsController extends Controller
{
    public function show(AppSettings $settings): JsonResponse
    {
        return response()->json(['data' => $settings->all()]);
    }
}
