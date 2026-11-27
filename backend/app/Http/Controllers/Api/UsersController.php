<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Users\SearchUsersAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SearchUsersRequest;
use App\Http\Resources\Users\PublicUserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class UsersController extends Controller
{
    public function index(SearchUsersRequest $request, SearchUsersAction $action): AnonymousResourceCollection
    {
        return PublicUserResource::collection($action->execute($request->user(), $request->validated('search')));
    }

    public function show(User $user): PublicUserResource
    {
        Gate::authorize('view', $user);

        return new PublicUserResource($user);
    }
}
