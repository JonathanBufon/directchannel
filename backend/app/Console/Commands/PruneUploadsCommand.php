<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Files\PruneTemporaryUploadsAction;
use Illuminate\Console\Command;

final class PruneUploadsCommand extends Command
{
    protected $signature = 'uploads:prune';

    protected $description = 'Remove uploads temporários expirados e não consumidos e seus arquivos privados';

    public function handle(PruneTemporaryUploadsAction $action): int
    {
        $this->info($action->execute().' upload(s) temporário(s) removido(s).');

        return self::SUCCESS;
    }
}
