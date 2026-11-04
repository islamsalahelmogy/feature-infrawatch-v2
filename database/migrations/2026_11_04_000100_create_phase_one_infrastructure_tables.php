<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->primary(['role_id', 'user_id']);
        });
        Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('timezone')->default('Africa/Cairo');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('device_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('monitoring_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('check_type');
            $table->unsignedInteger('interval_seconds');
            $table->unsignedSmallInteger('timeout_seconds')->default(5);
            $table->unsignedTinyInteger('retry_count')->default(2);
            $table->unsignedTinyInteger('failure_threshold')->default(3);
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->restrictOnDelete();
            $table->foreignId('device_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('monitoring_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('hostname')->nullable();
            $table->string('management_ip', 45)->nullable();
            $table->enum('status', ['unknown', 'up', 'down', 'degraded'])->default('unknown');
            $table->enum('criticality', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['site_id', 'management_ip']);
            $table->index(['site_id', 'status']);
        });
        Schema::create('monitoring_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->boolean('is_enabled')->default(true);
            $table->json('configuration')->nullable();
            $table->timestamp('last_run_at')->nullable();
            $table->timestamps();
            $table->index(['device_id', 'is_enabled']);
        });
        Schema::create('worker_heartbeats', function (Blueprint $table) {
            $table->id();
            $table->string('worker_name')->unique();
            $table->string('status');
            $table->timestamp('last_seen_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index('last_seen_at');
        });
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('event');
            $table->string('auditable_type');
            $table->unsignedBigInteger('auditable_id')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();
            $table->index(['auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('worker_heartbeats');
        Schema::dropIfExists('monitoring_checks');
        Schema::dropIfExists('devices');
        Schema::dropIfExists('monitoring_profiles');
        Schema::dropIfExists('device_types');
        Schema::dropIfExists('sites');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
