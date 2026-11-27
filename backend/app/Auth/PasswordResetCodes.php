<?php

declare(strict_types=1);

namespace App\Auth;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class PasswordResetCodes
{
    public function issue(User $user): string
    {
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::table('password_reset_tokens')->upsert([
            'email' => $user->email,
            'token' => Hash::make($code),
            'created_at' => now(),
            'attempts' => 0,
        ], ['email'], ['token', 'created_at', 'attempts']);

        return $code;
    }

    public function consume(string $email, string $code): bool
    {
        return DB::transaction(function () use ($email, $code): bool {
            $query = DB::table('password_reset_tokens')->where('email', $email);
            $record = $query->lockForUpdate()->first();

            if ($record === null) {
                return false;
            }

            if ($record->created_at === null || Carbon::parse($record->created_at)
                ->addMinutes((int) config('auth.passwords.users.expire'))->lte(now())) {
                $query->delete();

                return false;
            }

            if (! Hash::check($code, $record->token)) {
                if ($record->attempts >= 4) {
                    $query->delete();
                } else {
                    $query->increment('attempts');
                }

                return false;
            }

            $query->delete();

            return true;
        }, 3);
    }
}
