<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Groups\AddGroupMembersAction;
use App\Actions\Groups\CloseGroupAction;
use App\Actions\Groups\CreateGroupAction;
use App\Actions\Groups\GetGroupAction;
use App\Actions\Groups\LeaveGroupAction;
use App\Actions\Groups\ListGroupsAction;
use App\Actions\Groups\RemoveGroupMemberAction;
use App\Actions\Groups\RenameGroupAction;
use App\Actions\Groups\SetGroupResponsibleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Groups\AddGroupMembersRequest;
use App\Http\Requests\Groups\CreateGroupRequest;
use App\Http\Requests\Groups\LeaveGroupRequest;
use App\Http\Requests\Groups\RenameGroupRequest;
use App\Http\Resources\Groups\GroupResource;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

final class GroupController extends Controller
{
    public function index(Request $request, ListGroupsAction $action): AnonymousResourceCollection
    {
        return GroupResource::collection($action->execute($request->user()));
    }

    public function show(Group $group, GetGroupAction $action): GroupResource
    {
        return new GroupResource($action->execute($group));
    }

    public function store(CreateGroupRequest $request, CreateGroupAction $action): JsonResponse
    {
        $data = $request->validated();

        return (new GroupResource($action->execute($request->user(), $data['name'], $data['member_ids'])))->response()->setStatusCode(201);
    }

    public function update(RenameGroupRequest $request, Group $group, RenameGroupAction $action): GroupResource
    {
        return new GroupResource($action->execute($group, $request->user(), $request->validated('name')));
    }

    public function addMembers(AddGroupMembersRequest $request, Group $group, AddGroupMembersAction $action): GroupResource
    {
        return new GroupResource($action->execute($group, $request->user(), $request->validated('member_ids')));
    }

    public function removeMember(Request $request, Group $group, User $user, RemoveGroupMemberAction $action): Response
    {
        $action->execute($group, $request->user(), $user);

        return response()->noContent();
    }

    public function promote(Request $request, Group $group, User $user, SetGroupResponsibleAction $action): Response
    {
        $action->execute($group, $request->user(), $user, true);

        return response()->noContent();
    }

    public function demote(Request $request, Group $group, User $user, SetGroupResponsibleAction $action): Response
    {
        $action->execute($group, $request->user(), $user, false);

        return response()->noContent();
    }

    public function leave(LeaveGroupRequest $request, Group $group, LeaveGroupAction $action): Response
    {
        $successor = $request->validated('successor_user_id');
        $action->execute($group, $request->user(), $successor === null ? null : (int) $successor);

        return response()->noContent();
    }

    public function destroy(Request $request, Group $group, CloseGroupAction $action): Response
    {
        $action->execute($group, $request->user());

        return response()->noContent();
    }
}
