<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Admin\DashboardAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class AdminDashboardController extends Controller
{
    public function __invoke(DashboardAction $action): JsonResponse
    {
        return response()->json(['data' => $action->execute()]);
    }
}
