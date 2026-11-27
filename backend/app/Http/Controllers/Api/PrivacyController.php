<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Profile\UpdatePrivacyAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePrivacyRequest;
use App\Http\Resources\Profile\PrivacyResource;
use Illuminate\Http\Request;

final class PrivacyController extends Controller
{
    public function show(Request $request): PrivacyResource
    {
        return new PrivacyResource($request->user());
    }

    public function update(UpdatePrivacyRequest $request, UpdatePrivacyAction $action): PrivacyResource
    {
        return new PrivacyResource($action->execute($request->user(), $request->validated()));
    }
}
