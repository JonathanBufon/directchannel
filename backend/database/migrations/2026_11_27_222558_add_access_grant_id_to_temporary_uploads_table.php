<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('temporary_uploads', function (Blueprint $table): void {
            $table->foreignId('access_grant_id')->constrained()->restrictOnDelete()->restrictOnUpdate();
            $table->index('access_grant_id');
        });
    }

    public function down(): void
    {
        Schema::table('temporary_uploads', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('access_grant_id');
        });
    }
};
