<?php

declare(strict_types=1);

namespace App\Actions\Files;

use App\Models\TemporaryUpload;
use Illuminate\Support\Facades\DB;

final class PruneTemporaryUploadsAction
{
    public function __construct(private readonly DeleteTemporaryUploadAction $delete) {}

    public function execute(): int
    {
        $count = 0;
        TemporaryUpload::whereNull('consumed_at')->where('expires_at', '<=', now())
            ->select('id')->chunkById(100, function ($uploads) use (&$count): void {
                foreach ($uploads as $upload) {
                    $count += DB::transaction(function () use ($upload): int {
                        $locked = TemporaryUpload::whereKey($upload->id)->whereNull('consumed_at')
                            ->where('expires_at', '<=', now())->lockForUpdate()->first();
                        if ($locked === null) {
                            return 0;
                        }
                        $this->delete->deleteFile($locked);
                        $locked->delete();

                        return 1;
                    });
                }
            });

        return $count;
    }
}
