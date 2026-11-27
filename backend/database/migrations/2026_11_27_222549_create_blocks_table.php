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

        Schema::create('blocks', function (Blueprint $table) {
            $table->foreignId('blocker_user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('blocked_user_id')->constrained('users')->onDelete('restrict');
            $table->index('blocked_user_id');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocks');
    }
};
