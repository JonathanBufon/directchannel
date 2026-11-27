<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE UNIQUE INDEX access_grants_one_active_per_user ON access_grants (user_id) WHERE revoked_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX conversation_participants_one_active_per_user ON conversation_participants (conversation_id, user_id) WHERE left_at IS NULL');
        DB::statement('CREATE UNIQUE INDEX account_blocks_one_open_per_user ON account_blocks (user_id) WHERE unblocked_at IS NULL');
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_sender_conversation_foreign FOREIGN KEY (sender_participant_id, conversation_id) REFERENCES conversation_participants (id, conversation_id) ON DELETE RESTRICT ON UPDATE RESTRICT');
        DB::statement("ALTER TABLE conversations ADD CONSTRAINT conversations_kind_check CHECK (kind IN ('direct', 'group'))");
        DB::statement("ALTER TABLE reports ADD CONSTRAINT reports_status_check CHECK (status IN ('recebida', 'concluida'))");
        DB::statement("ALTER TABLE reports ADD CONSTRAINT reports_conclusion_check CHECK ((status = 'recebida' AND reviewer_user_id IS NULL AND decision IS NULL AND decided_at IS NULL) OR (status = 'concluida' AND reviewer_user_id IS NOT NULL AND decision IS NOT NULL AND decided_at IS NOT NULL))");
        DB::statement('ALTER TABLE reports ADD CONSTRAINT reports_distinct_users_check CHECK (reporter_user_id <> reported_user_id)');
        DB::statement('ALTER TABLE blocks ADD CONSTRAINT blocks_distinct_users_check CHECK (blocker_user_id <> blocked_user_id)');
        DB::statement('ALTER TABLE account_blocks ADD CONSTRAINT account_blocks_unblock_pair_check CHECK ((unblocked_by_user_id IS NULL) = (unblocked_at IS NULL))');
        DB::statement('ALTER TABLE access_grants ADD CONSTRAINT access_grants_period_check CHECK (revoked_at >= started_at)');
        DB::statement('ALTER TABLE conversation_participants ADD CONSTRAINT conversation_participants_period_check CHECK (left_at >= joined_at)');
        DB::statement('ALTER TABLE attachments ADD CONSTRAINT attachments_positive_size_check CHECK (size_bytes > 0)');
        DB::statement('ALTER TABLE temporary_uploads ADD CONSTRAINT temporary_uploads_positive_size_check CHECK (size_bytes > 0)');
        DB::statement('ALTER TABLE messages ADD CONSTRAINT messages_nonempty_body_check CHECK (octet_length(encrypted_body) > 0)');
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE messages DROP CONSTRAINT messages_nonempty_body_check');
        DB::statement('ALTER TABLE temporary_uploads DROP CONSTRAINT temporary_uploads_positive_size_check');
        DB::statement('ALTER TABLE attachments DROP CONSTRAINT attachments_positive_size_check');
        DB::statement('ALTER TABLE conversation_participants DROP CONSTRAINT conversation_participants_period_check');
        DB::statement('ALTER TABLE access_grants DROP CONSTRAINT access_grants_period_check');
        DB::statement('ALTER TABLE account_blocks DROP CONSTRAINT account_blocks_unblock_pair_check');
        DB::statement('ALTER TABLE blocks DROP CONSTRAINT blocks_distinct_users_check');
        DB::statement('ALTER TABLE reports DROP CONSTRAINT reports_distinct_users_check');
        DB::statement('ALTER TABLE reports DROP CONSTRAINT reports_conclusion_check');
        DB::statement('ALTER TABLE reports DROP CONSTRAINT reports_status_check');
        DB::statement('ALTER TABLE conversations DROP CONSTRAINT conversations_kind_check');
        DB::statement('ALTER TABLE messages DROP CONSTRAINT messages_sender_conversation_foreign');
        DB::statement('DROP INDEX account_blocks_one_open_per_user');
        DB::statement('DROP INDEX conversation_participants_one_active_per_user');
        DB::statement('DROP INDEX access_grants_one_active_per_user');
    }
};
