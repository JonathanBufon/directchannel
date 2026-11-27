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

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('restrict');
            $table->foreignId('sender_participant_id');
            $table->binary('encrypted_body');
            $table->timestampTz('accepted_at', 6)->useCurrent();
            $table->index(['conversation_id', 'accepted_at', 'id']);
            $table->index(['sender_participant_id', 'conversation_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
