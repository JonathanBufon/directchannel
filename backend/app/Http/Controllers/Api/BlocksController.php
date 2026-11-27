<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Blocks\BlockUserAction;
use App\Actions\Blocks\ListBlocksAction;
use App\Actions\Blocks\UnblockUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Blocks\StoreBlockRequest;
use App\Http\Requests\Users\PaginationRequest;
use App\Http\Resources\Users\PublicUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class BlocksController extends Controller
{
    public function index(PaginationRequest $request, ListBlocksAction $action): AnonymousResourceCollection
    {
        return PublicUserResource::collection($action->execute($request->user()));
    }

    public function store(StoreBlockRequest $request, BlockUserAction $action): PublicUserResource
    {
        return new PublicUserResource($action->execute($request->user(), (int) $request->validated('user_id')));
    }

    public function destroy(Request $request, User $user, UnblockUserAction $action): JsonResponse
    {
        $action->execute($request->user(), $user);

        return response()->json(['data' => ['message' => 'Contato desbloqueado.']]);
    }
}
