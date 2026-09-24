<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('responder_status')->default('AVAILABLE')->after('responder_class');
        });
        Schema::table('incidents', function (Blueprint $table) {
            $table->dateTime('investigation_completed_at')->nullable()->after('notes');
            $table->string('investigation_result')->nullable()->after('investigation_completed_at');
        });
        Schema::table('investigations', function (Blueprint $table) {
            $table->dateTime('completed_at')->nullable()->after('result');
        });
        Schema::create('responder_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->foreignId('investigator_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('responder_id')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('assigned_at');
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('response_started_at')->nullable();
            $table->dateTime('response_completed_at')->nullable();
            $table->string('status')->default('ASSIGNED')->index();
            $table->unsignedInteger('anomaly_hp')->nullable();
            $table->unsignedInteger('anomaly_max_hp')->nullable();
            $table->unsignedInteger('responder_hp')->nullable();
            $table->unsignedInteger('responder_max_hp')->nullable();
            $table->unsignedTinyInteger('response_progress')->default(0);
            $table->dateTime('response_deadline')->nullable();
            $table->string('result')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('responder_assignments');
        Schema::table('investigations', fn (Blueprint $table) => $table->dropColumn('completed_at'));
        Schema::table('incidents', fn (Blueprint $table) => $table->dropColumn(['investigation_completed_at', 'investigation_result']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('responder_status'));
    }
};
