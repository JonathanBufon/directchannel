<?php

declare(strict_types=1);

namespace App\Actions\Files;

use App\Models\TemporaryUpload;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class DeleteTemporaryUploadAction
{
    public function execute(TemporaryUpload $upload): void
    {
        DB::transaction(function () use ($upload): void {
            $locked = TemporaryUpload::whereKey($upload->id)->lockForUpdate()->firstOrFail();
            if ($locked->consumed_at !== null) {
                throw ValidationException::withMessages(['upload' => 'Este upload já foi associado a uma mensagem.']);
            }
            $this->deleteFile($locked);
            $locked->delete();
        });
    }

    public function deleteFile(TemporaryUpload $upload): void
    {
        if (! Storage::disk('local')->delete($upload->storage_key)) {
            throw new RuntimeException('Não foi possível remover o arquivo temporário.');
        }
    }
}
