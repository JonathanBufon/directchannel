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

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reporter_user_id')->constrained('users')->onDelete('restrict');
            $table->foreignId('reported_user_id')->constrained('users')->onDelete('restrict');
            $table->string('reason', 50);
            $table->text('narrative')->nullable();
            $table->string('status', 16)->default('recebida');
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->onDelete('restrict');
            $table->text('decision')->nullable();
            $table->timestampTz('decided_at', 6)->nullable();
            $table->timestampTz('created_at', 6)->useCurrent();
            $table->timestampTz('deleted_at', 6)->nullable();
            $table->index(['status', 'created_at', 'id']);
            $table->index(['reporter_user_id', 'id']);
            $table->index(['reported_user_id', 'id']);
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
