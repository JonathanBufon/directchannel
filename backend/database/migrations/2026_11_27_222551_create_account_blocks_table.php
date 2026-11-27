<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('account_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('blocked_by_user_id')->constrained('users')->onDelete('restrict');
            $table->text('reason');
            $table->timestampTz('blocked_at', 6)->useCurrent();
            $table->foreignId('unblocked_by_user_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestampTz('unblocked_at', 6)->nullable();
            $table->index(['user_id', 'blocked_at', 'id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_blocks');
    }
};
