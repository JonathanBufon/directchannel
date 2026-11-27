<?php

declare(strict_types=1);

namespace App\Actions\Profile;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteAccountAction
{
    public function __construct(
        private readonly VerifyCurrentPasswordAction $verifyPassword,
        private readonly RevokeTokensAction $revokeTokens,
    ) {}

    public function execute(User $user, string $password): void
    {
        DB::transaction(function () use ($user, $password): void {
            // Match group mutations: conversations in ID order, then their participants.
            $conversations = Conversation::query()->whereHas('group')
                ->whereHas('participants', fn (Builder $query): Builder => $query->active()->where('user_id', $user->id))
                ->orderBy('id')->lockForUpdate()->get();
            foreach ($conversations as $conversation) {
                $members = $conversation->participants()->active()->orderBy('id')->lockForUpdate()
                    ->with(['groupMember', 'user'])->get()
                    ->filter(fn (ConversationParticipant $participant): bool => $participant->user !== null && $participant->groupMember !== null);
                $own = $members->firstWhere('user_id', $user->id);
                if ($own?->groupMember->is_responsible && $members->count() > 1
                    && $members->filter(fn (ConversationParticipant $participant): bool => $participant->groupMember->is_responsible)->count() === 1) {
                    throw ValidationException::withMessages(['groups' => 'Transfira a responsabilidade dos grupos para outro membro ativo antes de desativar a conta.']);
                }
            }
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $this->verifyPassword->execute($locked, $password, 'password');
            $this->revokeTokens->execute($locked);
            DB::table('password_reset_tokens')->where('email', $locked->email)->delete();
            $locked->delete();
        }, 3);
    }
}
