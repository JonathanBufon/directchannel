<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Actions\Messages\ListMessagesAction;
use App\Actions\Messages\MarkMessageReceiptAction;
use App\Actions\Messages\SendMessageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Messages\ListMessagesRequest;
use App\Http\Requests\Messages\SendMessageRequest;
use App\Http\Resources\Messages\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

final class MessageController extends Controller
{
    public function index(ListMessagesRequest $request, Conversation $conversation, ListMessagesAction $action): AnonymousResourceCollection
    {
        $data = $request->validated();
        $messages = $action->execute($conversation, $request->user(), isset($data['before_id']) ? (int) $data['before_id'] : null, (int) ($data['limit'] ?? 30));

        return MessageResource::collection($messages)->additional(['meta' => ['next_before_id' => $messages->last()?->id]]);
    }

    public function store(SendMessageRequest $request, Conversation $conversation, SendMessageAction $action): JsonResponse
    {
        $data = $request->validated();

        return (new MessageResource($action->execute($conversation, $request->user(), $data['encrypted_body'], $data['temporary_upload_ids'] ?? [])))->response()->setStatusCode(201);
    }

    public function delivered(Request $request, Message $message, MarkMessageReceiptAction $action): Response
    {
        $action->execute($message, $request->user(), false);

        return response()->noContent();
    }

    public function read(Request $request, Message $message, MarkMessageReceiptAction $action): Response
    {
        $action->execute($message, $request->user(), true);

        return response()->noContent();
    }

    public function future(Message $message): JsonResponse
    {
        Gate::authorize('view', $message);

        return response()->json(['message' => 'A edição e a exclusão de mensagens estarão disponíveis em uma versão futura.'], 501);
    }
}
