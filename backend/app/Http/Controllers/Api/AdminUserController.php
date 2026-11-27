<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Admin\BlockAccountAction;
use App\Actions\Admin\ListUsersAction;
use App\Actions\Admin\UnblockAccountAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlockUserRequest;
use App\Http\Requests\Admin\ListUsersRequest;
use App\Http\Resources\AccountBlockResource;
use App\Http\Resources\AdminUserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AdminUserController extends Controller
{
    public function index(ListUsersRequest $request, ListUsersAction $action): AnonymousResourceCollection
    {
        return AdminUserResource::collection($action->execute($request->validated()));
    }

    public function show(User $user): AdminUserResource
    {
        return new AdminUserResource($user);
    }

    public function block(BlockUserRequest $request, User $user, BlockAccountAction $action): AccountBlockResource
    {
        return new AccountBlockResource($action->execute($user, $request->user(), $request->validated('reason')));
    }

    public function unblock(Request $request, User $user, UnblockAccountAction $action): AccountBlockResource
    {
        return new AccountBlockResource($action->execute($user, $request->user()));
    }
}
