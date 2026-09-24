<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('responder_class')->default('D')->after('role');
            $table->unsignedInteger('xp')->default(0)->after('responder_class');
            $table->unsignedInteger('successful_responses')->default(0)->after('xp');
            $table->unsignedInteger('investigations_completed')->default(0)->after('successful_responses');
        });

        Schema::table('incidents', function (Blueprint $table) {
            $table->unsignedInteger('anomaly_hp')->nullable()->after('notes');
            $table->unsignedInteger('anomaly_max_hp')->nullable()->after('anomaly_hp');
            $table->unsignedInteger('investigator_hp')->nullable()->after('anomaly_max_hp');
            $table->unsignedInteger('investigator_max_hp')->nullable()->after('investigator_hp');
            $table->unsignedTinyInteger('response_progress')->default(0)->after('investigator_max_hp');
            $table->foreignId('response_investigator_id')->nullable()->after('response_progress')->constrained('users')->nullOnDelete();
            $table->dateTime('response_started_at')->nullable()->after('response_investigator_id');
            $table->dateTime('response_deadline')->nullable()->after('response_started_at');
            $table->string('response_status')->nullable()->index()->after('response_deadline');
            $table->dateTime('support_requested_at')->nullable()->after('response_status');
        });

        Schema::create('promotion_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('promoted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_class');
            $table->string('to_class');
            $table->unsignedInteger('xp_at_promotion');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_histories');
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('response_investigator_id');
            $table->dropColumn(['anomaly_hp', 'anomaly_max_hp', 'investigator_hp', 'investigator_max_hp', 'response_progress', 'response_started_at', 'response_deadline', 'response_status', 'support_requested_at']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['responder_class', 'xp', 'successful_responses', 'investigations_completed']);
        });
    }
};
