<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('monitoring_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('check_type')->default('icmp');
            $table->unsignedInteger('interval_minutes')->default(5);
            $table->string('timezone')->default('Africa/Cairo');
            $table->boolean('is_active')->default(true);
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });
        Schema::create('monitoring_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_schedule_id')->nullable()->constrained()->nullOnDelete();
            $table->string('check_type');
            $table->enum('status', ['pending', 'running', 'completed', 'partial', 'failed', 'cancelled'])->default('pending');
            $table->unsignedInteger('expected_devices')->default(0);
            $table->unsignedInteger('received_results')->default(0);
            $table->unsignedInteger('down_devices')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'check_type']);
        });
        Schema::create('monitoring_run_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'success', 'failure', 'missed'])->default('pending');
            $table->foreignId('check_result_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('reported_at')->nullable();
            $table->timestamps();
            $table->unique(['monitoring_run_id', 'device_id']);
        });
        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitoring_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('notification_type');
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->unique(['monitoring_run_id', 'recipient_id', 'notification_type'], 'notification_delivery_once');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');
        Schema::dropIfExists('monitoring_run_devices');
        Schema::dropIfExists('monitoring_runs');
        Schema::dropIfExists('monitoring_schedules');
    }
};
