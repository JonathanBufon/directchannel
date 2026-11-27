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

        Schema::create('message_receipts', function (Blueprint $table) {
            $table->foreignId('message_id')->constrained()->onDelete('restrict');
            $table->foreignId('recipient_participant_id')->constrained('conversationparticipants')->onDelete('restrict');
            $table->timestampTz('delivered_at', 6)->nullable();
            $table->timestampTz('read_at', 6)->nullable();
            $table->index(['recipient_participant_id', 'message_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_receipts');
    }
};
