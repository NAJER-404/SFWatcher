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
        Schema::table('incidents', function (Blueprint $table) {
            $table->dateTime('archived_from_map_at')->nullable()->after('notes')->index();
            $table->foreignId('archived_by')->nullable()->after('archived_from_map_at')->constrained('users')->nullOnDelete();
            $table->text('archive_notes')->nullable()->after('archived_by');
        });

        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action')->index(); // 'INCIDENT_ARCHIVED', 'INCIDENT_RESTORED', 'RESPONDER_PROMOTED', 'USER_ROLE_CHANGED', 'ACCOUNT_UPDATED'
            $table->string('subject_type')->nullable()->index(); // Incident, User, etc.
            $table->unsignedBigInteger('subject_id')->nullable()->index();
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('archived_by');
            $table->dropColumn(['archived_from_map_at', 'archive_notes']);
        });
    }
};
