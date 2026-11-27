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

        Schema::create('group_members', function (Blueprint $table) {
            $table->foreignId('participant_id')->constrained('conversationparticipants')->onDelete('restrict');
            $table->foreignId('group_id')->constrained()->onDelete('restrict');
            $table->boolean('is_responsible')->default(false);
            $table->index(['group_id', 'participant_id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_members');
    }
};
