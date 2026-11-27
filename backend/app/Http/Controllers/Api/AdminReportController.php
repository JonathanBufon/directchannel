<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Admin\ArchiveReportAction;
use App\Actions\Admin\ConcludeReportAction;
use App\Actions\Admin\ListReportsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ConcludeReportRequest;
use App\Http\Requests\Admin\ListReportsRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class AdminReportController extends Controller
{
    public function index(ListReportsRequest $request, ListReportsAction $action): AnonymousResourceCollection
    {
        return ReportResource::collection($action->execute($request->validated()));
    }

    public function show(Report $report): ReportResource
    {
        Gate::authorize('view', $report);

        return new ReportResource($report);
    }

    public function conclude(ConcludeReportRequest $request, Report $report, ConcludeReportAction $action): ReportResource
    {
        Gate::authorize('conclude', $report);

        return new ReportResource($action->execute($report, $request->user(), $request->validated('decision')));
    }

    public function destroy(Report $report, ArchiveReportAction $action): JsonResponse
    {
        Gate::authorize('delete', $report);
        $action->execute($report);

        return response()->json(['data' => ['message' => 'Denúncia arquivada.']]);
    }
}
