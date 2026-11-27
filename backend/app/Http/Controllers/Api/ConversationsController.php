<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Conversations\ArchiveConversationAction;
use App\Actions\Conversations\CreateDirectConversationAction;
use App\Actions\Conversations\ListConversationsAction;
use App\Actions\Conversations\ShowConversationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Conversations\StoreConversationRequest;
use App\Http\Requests\Users\PaginationRequest;
use App\Http\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

final class ConversationsController extends Controller
{
    public function index(PaginationRequest $request, ListConversationsAction $action): AnonymousResourceCollection
    {
        return ConversationResource::collection($action->execute($request->user()));
    }

    public function store(StoreConversationRequest $request, CreateDirectConversationAction $action, ShowConversationAction $show): JsonResponse
    {
        $result = $action->execute($request->user(), (int) $request->validated('user_id'));

        return (new ConversationResource($show->execute($result['conversation'], $request->user())))->response()->setStatusCode($result['created'] ? 201 : 200);
    }

    public function show(Request $request, Conversation $conversation, ShowConversationAction $action): ConversationResource
    {
        Gate::authorize('view', $conversation);

        return new ConversationResource($action->execute($conversation, $request->user()));
    }

    public function destroy(Request $request, Conversation $conversation, ArchiveConversationAction $action): JsonResponse
    {
        Gate::authorize('delete', $conversation);
        $action->execute($request->user(), $conversation);

        return response()->json(['data' => ['message' => 'Conversa arquivada.']]);
    }
}
