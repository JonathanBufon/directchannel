<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('username', 30)->nullable();
            $table->boolean('is_admin')->default(false);
            $table->timestampTz('deleted_at', 6)->nullable();
        });
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX users_username_lower_unique ON users (lower(username))');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX users_username_lower_unique');
        }
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['username', 'is_admin', 'deleted_at']);
        });
    }
};
