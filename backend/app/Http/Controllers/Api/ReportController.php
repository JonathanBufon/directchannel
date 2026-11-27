<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Reports\ListOwnReportsAction;
use App\Actions\Reports\StoreReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Reports\ListOwnReportsRequest;
use App\Http\Requests\Reports\StoreReportRequest;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class ReportController extends Controller
{
    public function index(ListOwnReportsRequest $request, ListOwnReportsAction $action): AnonymousResourceCollection
    {
        return ReportResource::collection($action->execute($request->user()));
    }

    public function store(StoreReportRequest $request, StoreReportAction $action): ReportResource
    {
        return new ReportResource($action->execute($request->user(), $request->validated()));
    }

    public function show(Report $report): ReportResource
    {
        Gate::authorize('view', $report);

        return new ReportResource($report);
    }
}
