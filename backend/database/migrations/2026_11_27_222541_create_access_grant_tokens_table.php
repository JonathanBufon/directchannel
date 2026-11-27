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

        Schema::create('access_grant_tokens', function (Blueprint $table) {
            $table->foreignId('personal_access_token_id')->constrained()->onDelete('restrict');
            $table->foreignId('access_grant_id')->constrained()->onDelete('restrict');
            $table->index('access_grant_id');
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_grant_tokens');
    }
};
