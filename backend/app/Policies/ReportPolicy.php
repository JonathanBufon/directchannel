<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Gate;

final class ReportPolicy
{
    public function view(User $user, Report $report): Response
    {
        return $report->reporter_user_id === $user->id || Gate::forUser($user)->allows('admin')
            ? Response::allow()
            : Response::denyAsNotFound('Denúncia não encontrada.');
    }

    public function conclude(User $user, Report $report): Response
    {
        return Gate::forUser($user)->allows('admin')
            ? Response::allow()
            : Response::deny('Acesso permitido somente à administração.');
    }

    public function delete(User $user, Report $report): Response
    {
        return $this->conclude($user, $report);
    }
}
